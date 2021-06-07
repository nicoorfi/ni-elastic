<?php

declare(strict_types=1);

namespace Sigmie\Base\Analysis\Languages\Greek;

use Sigmie\Base\Analysis\TokenFilter\Stopwords as TokenFilterStopwords;

class Stopwords extends TokenFilterStopwords
{
    protected string $name = 'greek_stopwords';

    public function __construct()
    {
    }

    public function value(): array
    {
        return [
            'stopwords' => '_greek_',
            'class' => static::class
        ];
    }
}