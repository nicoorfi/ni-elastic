<?php

declare(strict_types=1);

namespace Sigmie;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Uri;
use Sigmie\Analytics\Analytics;
use Sigmie\Base\Contracts\ElasticsearchConnection as Connection;
use Sigmie\Base\Contracts\ElasticsearchRequest as ElasticsearchRequestInterface;
use Sigmie\Base\Contracts\ElasticsearchResponse;
use Sigmie\Base\Http\ElasticsearchConnection as HttpConnection;
use Sigmie\Base\Http\ElasticsearchRequest;
use Sigmie\Document\AliveCollection;
use Sigmie\Http\Contracts\Auth;
use Sigmie\Http\JSONClient;
use Sigmie\Index\Actions as IndexActions;
use Sigmie\Index\AliasedIndex;
use Sigmie\Index\Index;
use Sigmie\Index\NewIndex;
use Sigmie\Query\Aggs;
use Sigmie\Query\Contracts\Aggs as AggsInterface;
use Sigmie\Query\Queries\MatchAll;
use Sigmie\Query\Queries\Query;
use Sigmie\Search\Contracts\SearchQueryBuilder as ContractsSearchQueryBuilder;
use Sigmie\Search\Contracts\SearchTemplateBuilder as ContractsSearchTemplateBuilder;
use Sigmie\Search\Search;
use Sigmie\Search\NewSearch;
use Sigmie\Search\SearchQueryBuilder;
use Sigmie\Search\SearchTemplateBuilder;

class Sigmie
{
    use IndexActions;

    public function __construct(Connection $httpConnection)
    {
        $this->elasticsearchConnection = $httpConnection;
    }

    public function newIndex(string $name): NewIndex
    {
        $builder = new NewIndex($this->elasticsearchConnection);

        return $builder->alias($name);
    }

    public function index(string $name): null|AliasedIndex|Index
    {
        return $this->getIndex($name);
    }

    public function collect(string $name, bool $refresh = false): AliveCollection
    {
        $aliveIndex = new AliveCollection($name, $this->elasticsearchConnection);

        if ($refresh) {
            return $aliveIndex->refresh();
        }

        return $aliveIndex;
    }

    public function query(
        string $index,
        Query $query = new MatchAll(),
        AggsInterface $aggs = new Aggs()
    ) {
        $search = new Search($query, $aggs);

        $search->setElasticsearchConnection($this->elasticsearchConnection);

        return $search->index($index);
    }

    public function newSearch(string $index): NewSearch
    {
        return new NewSearch($index, $this->elasticsearchConnection);
    }

    public function search(string $index): ContractsSearchQueryBuilder
    {
        return new SearchQueryBuilder($this->newSearch($index));
    }

    public function template(string $index): ContractsSearchTemplateBuilder
    {
        return new SearchTemplateBuilder($this->newSearch($index),$this->elasticsearchConnection);
    }

    public function analytics(string $index, string $field)
    {
        return new Analytics($this->newSearch($index), $field);
    }

    public function indices(): array
    {
        return $this->listIndices();
    }

    public function isConnected(): bool
    {
        try {
            $request = new ElasticsearchRequest('GET', new Uri());

            $res = ($this->elasticsearchConnection)($request);

            return !$res->failed();
        } catch (ConnectException) {
            return false;
        }
    }

    public static function create(array|string $hosts, array $config = []): static
    {
        $hosts = (is_string($hosts)) ? explode(',', $hosts) : $hosts;

        $client = JSONClient::create($hosts, $config);

        return new static(new HttpConnection($client));
    }

    public function delete(string $index): bool
    {
        return $this->deleteIndex($index);
    }
}
