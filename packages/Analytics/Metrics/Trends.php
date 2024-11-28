<?php

declare(strict_types=1);

namespace Sigmie\Analytics\Metrics;

use function Sigmie\Functions\random_letters;
use Sigmie\Shared\Contracts\ToRaw;

class Trends implements ToRaw
{
    protected array $trends = [];

    public function __construct(protected string $field)
    {
    }

    public function get(string $alias)
    {
        return $this->trends[$alias] ?? null;
    }

    public function autoValueCount(string $field, string $as, int $buckets): AutoTrend
    {
        $randomLetters = random_letters();

        $trend = new AutoValueCountTrend($as, $field, $this->field, "auto_value_count_trend_{$randomLetters}", $buckets);

        $this->trends[$as] = $trend;

        return $trend;
    }

    public function autoAvg(string $field, string $as, int $buckets): AutoTrend
    {
        $randomLetters = random_letters();

        $trend = new AutoAvgTrend($as, $field, $this->field, "auto_avg_trend_{$randomLetters}", $buckets);

        $this->trends[$as] = $trend;

        return $trend;
    }

    public function autoSum(string $field, string $as, int $buckets): AutoTrend
    {
        $randomLetters = random_letters();

        $trend = new AutoSumTrend($as, $field, $this->field, "auto_sum_trend_{$randomLetters}", $buckets);

        $this->trends[$as] = $trend;

        return $trend;
    }

    public function autoMin(string $field, string $as, int $buckets): AutoTrend
    {
        $randomLetters = random_letters();

        $trend = new AutoMinTrend($as, $field, $this->field, "auto_min_trend_{$randomLetters}", $buckets);

        $this->trends[$as] = $trend;

        return $trend;
    }

    public function autoMax(string $field, string $as, int $buckets): AutoTrend
    {
        $randomLetters = random_letters();

        $trend = new AutoMaxTrend($as, $field, $this->field, "auto_max_trend_{$randomLetters}", $buckets);

        $this->trends[$as] = $trend;

        return $trend;
    }

    public function autoUnique(string $field, string $as, int $buckets): AutoTrend
    {
        $randomLetters = random_letters();

        $trend = new AutoUniqueTrend($as, $field, $this->field, "auto_unique_trend_{$randomLetters}", $buckets);

        $this->trends[$as] = $trend;

        return $trend;
    }

    public function avg(string $field, string $as): Trend
    {
        $randomLetters = random_letters();

        $trend = new AvgTrend($as, $field, $this->field, "avg_trend_{$randomLetters}");

        $this->trends[$as] = $trend;

        return $trend;
    }

    public function unique(string $field, string $as): Trend
    {
        $randomLetters = random_letters();

        $trend = new UniqueTrend($as, $field, $this->field, "unique_trend_{$randomLetters}");

        $this->trends[$as] = $trend;

        return $trend;
    }

    public function max(string $field, string $as): Trend
    {
        $randomLetters = random_letters();
        $trend = new MaxTrend($as, $field, $this->field, "max_trend_{$randomLetters}");

        $this->trends[$as] = $trend;

        return $trend;
    }

    public function min(string $field, string $as): Trend
    {
        $randomLetters = random_letters();
        $trend = new MinTrend($as, $field, $this->field, "min_trend_{$randomLetters}");

        $this->trends[$as] = $trend;

        return $trend;
    }

    public function sum(string $field, string $as): Trend
    {
        $randomLetters = random_letters();
        $trend = new SumTrend($as, $field, $this->field, "sum_trend_{$randomLetters}");

        $this->trends[$as] = $trend;

        return $trend;
    }

    public function extract(array $aggregations)
    {
        $result = [];

        foreach ($this->trends as $trend) {
            $result = [...$result, ...$trend->extract($aggregations)];
        }

        return $result;
    }

    public function toRaw(): array
    {
        $res = [];

        foreach ($this->trends as $trend) {
            $res = [...$res, ...$trend->toRaw()];
        }

        return $res;
    }
}
