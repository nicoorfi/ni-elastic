<?php

declare(strict_types=1);

namespace Sigmie\Base\Analysis\TokenFilter;

class Stemmer extends TokenFilter
{
    protected function getName(): string
    {
        return  'stemmer_overrides';
    }

    public function name(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function type(): string
    {
        return 'stemmer_override';
    }

    public static function fromRaw(array $raw)
    {
        $settings = [];

        foreach ($raw['rules'] as $value) {
            [$to, $from] = explode('=>', $value);
            $to = explode(', ', $to);
            $from = trim($from);
            $to = array_map(fn ($value) => trim($value), $to);

            $settings[$from] = $to;
        }

        $instance = new static('', $settings);

        return $instance;
    }

    protected function getValues(): array
    {
        $rules = [];

        foreach ($this->settings as $to => $from) {
            $from = implode(', ', $from);
            $rules[] = "{$from} => {$to}";
        }

        return [
            'rules' => $rules,
        ];
    }
}