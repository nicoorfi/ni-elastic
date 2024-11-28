<?php

declare(strict_types=1);

namespace Sigmie\Analytics\Metrics;

use Sigmie\Query\Aggregations\Bucket\DateHistogram;
use Sigmie\Query\Aggregations\Enums\CalendarInterval;
use Sigmie\Query\Aggregations\Metrics\Metric;
use Sigmie\Query\Aggs;
use Sigmie\Shared\Collection;

abstract class Trend extends DateHistogram
{
    protected string $alias;

    public function __construct(
        protected string $trendName,
        protected string $metricField,
        protected string $timestampField,
        protected string $histogramName,
        public CalendarInterval $interval = CalendarInterval::Month
    ) {
        parent::__construct("{$trendName}_histogram", $this->timestampField, $interval);

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

        return [$this->trendName => $res->toArray()];
    }

    public function perMonth()
    {
        $this->interval = CalendarInterval::Month;
    }

    public function per(CalendarInterval $interval)
    {
        $this->interval = $interval;
    }

    public function perYear()
    {
        $this->interval = CalendarInterval::Year;
    }

    public function perHour()
    {
        $this->interval = CalendarInterval::Hour;
    }

    public function perMinute()
    {
        $this->interval = CalendarInterval::Minute;
    }

    public function perWeek()
    {
        $this->interval = CalendarInterval::Week;
    }

    public function perQuarter()
    {
        $this->interval = CalendarInterval::Quarter;
    }
}
