<?php declare(strict_types=1);

namespace Sigmie\Helpers {

    function is_text_field(string $string): bool
    {
        return in_array($string, ['search_as_you_type', 'text']);
    }
}