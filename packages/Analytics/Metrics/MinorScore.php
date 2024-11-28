<?php

declare(strict_types=1);

namespace Sigmie\Analytics\Metrics;

use Sigmie\Query\Aggregations\Bucket\RareTerms;
use Sigmie\Query\Aggregations\Pipeline\SortBucket;
use Sigmie\Query\Aggs;
use Sigmie\Shared\Collection;

class MinorScore extends RareTerms
{
    protected string $byField;

    public function __construct(
        protected string $scoreName,
        protected string $termsField,
        protected int $size
    ) {
        parent::__construct("{$scoreName}_rare_terms", $termsField);
    }

    public function extract(array $aggregations): array
    {
        $collection = new Collection($aggregations["{$this->scoreName}_rare_terms"]['buckets'] ?? []);

        $res = $collection->map(fn (array $bucket) => [
            'label' => $bucket['key'],
            'value' => $bucket[$this->byField]['value'],
        ]);

        return [$this->scoreName => $res->toArray()];
    }

    public function byMax(string $field, string $sort)
    {
        $this->byField = "{$this->name}_max";
        $this->aggregate(function (Aggs $aggs) use ($field, $sort) {
            $sort = new SortBucket($this->name, $this->byField, $sort);
            $aggs->max($this->byField, $field);
            $aggs->add($sort);
        });
    }

    public function byAvg(string $field, string $sort)
    {
        $this->byField = "{$this->name}_avg";
        $this->aggregate(function (Aggs $aggs) use ($field, $sort) {
            $sort = new SortBucket($this->name, $this->byField, $sort);
            $aggs->avg($this->byField, $field);
            $aggs->add($sort);
        });
    }

    public function byMin(string $field, string $sort)
    {
        $this->byField = "{$this->name}_min";
        $this->aggregate(function (Aggs $aggs) use ($field, $sort) {
            $sort = new SortBucket($this->name, $this->byField, $sort);
            $aggs->min($this->byField, $field);
            $aggs->add($sort);
        });
    }

    public function bySum(string $field, string $sort)
    {
        $this->byField = "{$this->name}_sum";
        $this->aggregate(function (Aggs $aggs) use ($field, $sort) {
            $sort = new SortBucket($this->name, $this->byField, $sort);
            $aggs->sum($this->byField, $field);
            $aggs->add($sort);
        });
    }
}
