<?php

declare(strict_types=1);

namespace Sigmie\Analytics\Metrics;

use Sigmie\Shared\Contracts\ToRaw;

class Values implements ToRaw
{
    protected array $values = [];

    public function __construct(
        protected string $field,
        protected Trends $trends
    ) {
    }

    public function extract(array $aggregations)
    {
        $result = [];

        foreach ($this->values as $values) {
            $result = [...$result, ...$values->extract($aggregations)];
        }

        return $result;
    }

    public function max(string $field, string $as): TrendValue
    {
        $trend = new MaxValue($as, $field);

        $this->values[$as] = $trend;

        return $trend;
    }

    public function min(string $field, string $as): TrendValue
    {
        $trend = new MinValue($as, $field);

        $this->values[$as] = $trend;

        return $trend;
    }

    public function percent(string $field, string $as): PercentileValue
    {
        $trend = new PercentileValue($as, $field);

        $this->values[$as] = $trend;

        return $trend;
    }

    public function sum(string $field, string $as): TrendValue
    {
        $trend = new SumValue($as, $field);

        $this->values[$as] = $trend;

        return $trend;
    }

    public function avg(string $field, string $as): TrendValue
    {
        $trend = new AvgValue($as, $field);

        $this->values[$as] = $trend;

        return $trend;
    }

    public function toRaw(): array
    {
        $res = [];

        foreach ($this->values as $value) {
            $res = [...$res, ...$value->toRaw()];
        }

        return $res;
    }
}
