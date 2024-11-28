<?php

declare(strict_types=1);

namespace Sigmie\Analytics\Metrics;

use Sigmie\Query\Aggregations\Metrics\Metric;
use Sigmie\Query\Aggs;

class AutoValueCountTrend extends AutoTrend
{
    protected function aggregation(Aggs $aggs): Metric
    {
        return $aggs->valueCount($this->trendName, $this->metricField);
    }
}
