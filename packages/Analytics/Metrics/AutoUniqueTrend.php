<?php

declare(strict_types=1);

namespace Sigmie\Analytics\Metrics;

use Sigmie\Query\Aggregations\Metrics\Metric;
use Sigmie\Query\Aggs;

class AutoUniqueTrend extends AutoTrend
{
    protected function aggregation(Aggs $aggs): Metric
    {
        return $aggs->cardinality($this->trendName, $this->metricField);
    }
}
