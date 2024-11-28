<?php

declare(strict_types=1);

namespace Sigmie\Analytics\Metrics;

use Sigmie\Query\Aggregations\Pipeline\MaxBucket;
use Sigmie\Query\Aggregations\Pipeline\Pipeline;

class MaxValue extends TrendValue
{
    protected function bucketAggregation(string $trendPath): Pipeline
    {
        return new MaxBucket($this->name, $trendPath);
    }
}
