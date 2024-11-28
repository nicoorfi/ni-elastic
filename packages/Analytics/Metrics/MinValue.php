<?php

declare(strict_types=1);

namespace Sigmie\Analytics\Metrics;

use Sigmie\Query\Aggregations\Pipeline\MinBucket;
use Sigmie\Query\Aggregations\Pipeline\Pipeline;

class MinValue extends TrendValue
{
    protected function bucketAggregation(string $trendPath): Pipeline
    {
        return new MinBucket($this->name, $trendPath);
    }
}
