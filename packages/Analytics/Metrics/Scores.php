<?php

declare(strict_types=1);

namespace Sigmie\Analytics\Metrics;

use Sigmie\Shared\Contracts\ToRaw;

class Scores implements ToRaw
{
    protected array $scores = [];

    public function top(int $number, string $field, string $as): TopScore
    {
        $score = new TopScore($as, $field, $number);

        $this->scores[$as] = $score;

        return $score;
    }

    public function minor(int $number, string $field, string $as): MinorScore
    {
        $score = new MinorScore($as, $field, $number);

        $this->scores[$as] = $score;

        return $score;
    }

    public function extract(array $aggregations)
    {
        $result = [];

        foreach ($this->scores as $trend) {
            $result = [...$result, ...$trend->extract($aggregations)];
        }

        return $result;
    }

    public function toRaw(): array
    {
        $res = [];

        foreach ($this->scores as $trend) {
            $res = [...$res, ...$trend->toRaw()];
        }

        return $res;
    }
}
