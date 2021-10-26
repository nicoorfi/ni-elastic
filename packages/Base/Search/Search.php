<?php

declare(strict_types=1);

namespace Sigmie\Base\Search;

use Sigmie\Base\APIs\Search as APIsSearch;
use Sigmie\Base\Http\Responses\Search as SearchResponse;
use Sigmie\Base\Pagination\Paginator;
use Sigmie\Base\Search\Queries\MatchAll;
use Sigmie\Base\Search\Queries\Query;

class Search
{
    use APIsSearch;

    protected string $index;

    protected int $from = 0;

    protected int $size = 500;

    protected array $fields = ['*'];

    protected array $sort = [];

    public function __construct(protected Query $query = new MatchAll)
    {
    }

    public function fields(array $fields): self
    {
        $this->fields = $fields;

        return $this;
    }

    public function from(int $from): self
    {
        $this->from = $from;

        return $this;
    }

    public function size(int $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function index(string $index): self
    {
        $this->index = $index;

        return $this;
    }

    public function sort(string $field, string $direction): self
    {
        $this->sort[] = [$field => $direction];

        return $this;
    }

    public function paginate(int $perPage, int $currentPage,)
    {
        return new Paginator($perPage, $currentPage, $this);
    }

    public function get(): SearchResponse
    {
        ray($this->toRaw());
        return $this->searchAPICall($this->index, $this->toRaw());
    }

    public function getDSL(): array
    {
        return $this->toRaw();
    }

    public function query(Query $query)
    {
        $this->query = $query;

        return $this;
    }

    public function toRaw(): array
    {
        ray([
            '_source' => $this->fields,
            'query' => $this->query->toRaw(),
            'from' => $this->from,
            'size' => $this->size,
            'sort' => [...$this->sort]
        ]);

        return [
            '_source' => $this->fields,
            'query' => $this->query->toRaw(),
            'from' => $this->from,
            'size' => $this->size,
            'sort' => [...$this->sort, ]
        ];
    }
}