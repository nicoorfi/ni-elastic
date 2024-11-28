<?php

declare(strict_types=1);

namespace Sigmie\Analytics\Metrics;

use Sigmie\Query\Aggregations\Metrics\Metric;
use Sigmie\Query\Aggs;

abstract class Score
{
    protected string $by;

    public function __construct(
        protected string $name,
        protected string $field,
        protected int $size
    ) {
    }

    abstract protected function aggregation(Aggs $aggs): Metric;

    public function extract(array $aggregations): array
    {
        return $aggregations;
    }
}
