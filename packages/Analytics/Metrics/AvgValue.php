<?php

declare(strict_types=1);

namespace Sigmie\Analytics\Metrics;

use Sigmie\Query\Aggregations\Pipeline\AvgBucket;
use Sigmie\Query\Aggregations\Pipeline\Pipeline;

class AvgValue extends TrendValue
{
    protected function bucketAggregation(string $trendPath): Pipeline
    {
        return new AvgBucket($this->name, $trendPath);
    }
}
