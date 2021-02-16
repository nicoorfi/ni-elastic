<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Indexer;
use App\Models\Cluster;
use App\Models\IndexingType;
use Carbon\Carbon;
use Sigmie\Base\Index\Actions as IndexActions;
use Sigmie\Base\Index\AliasActions;
use Sigmie\Base\Index\Index;

abstract class BaseIndexer extends BaseSigmieService implements Indexer
{
    use IndexActions;
    use AliasActions;

    protected ?Index $oldIndex;

    protected Index $index;

    protected string $alias;

    public function __construct(Cluster $cluster, IndexingType $type)
    {
        parent::__construct($cluster);

        $this->type = $type;

        $this->alias =  $this->type->index_alias;
    }

    public function index()
    {
        $indexName = Carbon::now()->format('YmdHis');

        $this->index = new Index($indexName);

        $this->oldIndex = $this->getIndex($this->alias);

        $this->createIndex($this->index);

        $this();

        $this->switchIndex();
    }

    abstract protected function __invoke();

    private function switchIndex()
    {
        if (is_null($this->oldIndex)) {
            $this->index->setAlias($this->alias);
        } elseif ($this->oldIndex instanceof Index) {
            $this->switchAlias($this->alias, $this->oldIndex, $this->index);
        } else {
            throw new \Exception("Unexpected old index value.");
        }
        return;
    }
}
