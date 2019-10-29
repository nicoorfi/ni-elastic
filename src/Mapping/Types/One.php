<?php

namespace Sigma\Mapping\Types;

use Sigma\Mapping\Type;

class One extends Type
{
    /**
     * Native field name
     *
     * @return string
     */
    public function field(): string
    {
        return 'object';
    }
}