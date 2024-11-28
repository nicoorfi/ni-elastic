<?php

declare(strict_types=1);

namespace Sigmie\Analytics\Metrics;

use Sigmie\Query\Aggregations\Metrics\Metric;
use Sigmie\Query\Aggs;

class MinTrend extends Trend
{
    protected function aggregation(Aggs $aggs): Metric
    {
        return $aggs->max($this->trendName, $this->metricField);
    }
}
