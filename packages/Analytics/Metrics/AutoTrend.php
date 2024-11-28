<?php

declare(strict_types=1);

namespace Sigmie\Analytics\Metrics;

use Sigmie\Query\Aggregations\Bucket\AutoDateHistogram;
use Sigmie\Query\Aggregations\Enums\MinimumInterval;
use Sigmie\Query\Aggregations\Metrics\Metric;
use Sigmie\Query\Aggs;
use Sigmie\Shared\Collection;

abstract class AutoTrend extends AutoDateHistogram
{
    protected string $alias;

    protected MinimumInterval $minimumInterval = MinimumInterval::Second;

    public function __construct(
        protected string $trendName,
        protected string $metricField,
        protected string $timestampField,
        protected string $histogramName,
        protected int $buckets,
    ) {
        parent::__construct("{$trendName}_histogram", $this->timestampField, $buckets);

        $this->aggregate(fn (Aggs $aggs) => $this->aggregation($aggs));
    }

    abstract protected function aggregation(Aggs $aggs): Metric;

    public function extract(array $aggregations): array
    {
        $collection = new Collection($aggregations["{$this->trendName}_histogram"]['buckets'] ?? []);

        $res = $collection->map(fn (array $bucket) => [
            'label' => $bucket['key_as_string'],
            'value' => $bucket[$this->trendName]['value'],
        ]);

        return [$this->trendName => [
            'values' => $res->toArray(),
            'interval' => $aggregations["{$this->trendName}_histogram"]['interval'],
        ]];
    }

    public function minimumIntervalMinute()
    {
        $this->minimumInterval = MinimumInterval::Minute;
    }

    public function minimumIntervalHour()
    {
        $this->minimumInterval = MinimumInterval::Hour;
    }

    public function minimumIntervalDay()
    {
        $this->minimumInterval = MinimumInterval::Day;
    }

    public function minimumIntervalMonth()
    {
        $this->minimumInterval = MinimumInterval::Month;
    }

    public function minimumIntervalYear()
    {
        $this->minimumInterval = MinimumInterval::Year;
    }

    public function minimumIntervalSeconds()
    {
        $this->minimumInterval = MinimumInterval::Second;
    }
}
