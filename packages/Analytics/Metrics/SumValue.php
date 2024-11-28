<?php

declare(strict_types=1);

namespace Sigmie\Analytics\Metrics;

use Sigmie\Query\Aggregations\Pipeline\Pipeline;
use Sigmie\Query\Aggregations\Pipeline\SumBucket;

class SumValue extends TrendValue
{
    protected function bucketAggregation(string $trendPath): Pipeline
    {
        return new SumBucket($this->name, $trendPath);
    }
}
