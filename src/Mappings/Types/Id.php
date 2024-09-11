<?php

declare(strict_types=1);

namespace Sigmie\Mappings\Types;

class Id extends CaseSensitiveKeyword
{
    public function toRaw(): array
    {
        $raw = [
            $this->name => [
                'type' => $this->type(),
                'fields' => [
                    ...(new Number('sortable'))->integer()->toRaw(),
                ],
            ],
        ];

        return $raw;
    }

    public function sortableName(): null|string
    {
        return 'id.sortable';
    }

    public function validate(string $key, mixed $value): array
    {
        if (!is_int($value)) {
            return [false, "The field {$key} mapped as {$this->typeName()} must be an integer"];
        }

        return [true, ''];
    }

    public function typeName(): string
    {
        return 'identifier';
    }
}
