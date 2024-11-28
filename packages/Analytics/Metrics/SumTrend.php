<?php

declare(strict_types=1);

namespace Sigmie\Analytics\Metrics;

use Sigmie\Query\Aggregations\Metrics\Metric;
use Sigmie\Query\Aggs;

class SumTrend extends Trend
{
    protected function aggregation(Aggs $aggs): Metric
    {
        return $aggs->sum($this->trendName, $this->metricField);
    }
}
