<?php

declare(strict_types=1);

namespace Sigmie\Base\Index;

use Exception;
use Sigmie\Base\Analysis\Analyzer;
use Sigmie\Base\Analysis\Tokenizers\Whitespaces;
use Sigmie\Base\Contracts\Type;
use Sigmie\Base\Mappings\Properties;
use Sigmie\Base\Mappings\Types\Boolean;
use Sigmie\Base\Mappings\Types\Date;
use Sigmie\Base\Mappings\Types\Number;
use Sigmie\Base\Mappings\Types\Text;
use Sigmie\Support\Collection;

class Mappings
{
    protected Properties $properties;

    protected Analyzer $defaultAnalyzer;

    public function __construct(
        ?Properties $properties = null,
        ?Analyzer $defaultAnalyzer = null
    ) {
        $this->properties = $properties ?: new Properties();
        //TODO change
        $this->defaultAnalyzer = $defaultAnalyzer ?: new Analyzer('', new Whitespaces);
    }

    public function properties(): Properties
    {
        return $this->properties;
    }

    public function analyzers(): Collection
    {
        $collection = new Collection($this->properties->toArray());

        return $collection->filter(fn (Type $field) => $field instanceof Text)
            ->map(fn (Text $field) => $field->analyzer());
    }

    public function toRaw(): array
    {
        return [
            'properties' => $this->properties->toRaw(),
            // 'dynamic_templates' => $this->dynamicTemplate()
        ];
    }

    public static function fromRaw(array $data, array $analyzers): Mappings
    {
        $fields = [];

        $analyzer = $analyzers[array_key_first($analyzers)];

        if (isset($data['properties']) === false) {
            return new DynamicMappings($analyzer);
        }

        $defaultAnalyzerName = 'default';
        $defaultAnalyzer = $analyzers[$defaultAnalyzerName];

        foreach ($data['properties'] as $fieldName => $value) {

            $field = match ($value['type']) {
                'search_as_you_type' => (new Text($fieldName))->searchAsYouType(),
                'text' => (new Text($fieldName))->unstructuredText(),
                'integer' => (new Number($fieldName))->integer(),
                'float' => (new Number($fieldName))->float(),
                'boolean' => new Boolean($fieldName),
                'date' => new Date($fieldName),
                default => throw new Exception('Field couldn\'t be mapped')
            };

            if ($field instanceof Text && isset($value['analyzer'])) {
                $analyzerName = $value['analyzer'];
                $analyzer = $analyzers[$analyzerName];

                $field->withAnalyzer($analyzer);
            }

            $fields[$fieldName] = $field;
        }

        $properties = new Properties($fields);

        return new static($properties, $defaultAnalyzer);
    }
}