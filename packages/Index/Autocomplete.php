<?php

declare(strict_types=1);

namespace Sigmie\Index;

use Sigmie\Base\APIs\Ingest;
use Sigmie\English\Filter\Lowercase;
use Sigmie\English\Filter\Stemmer;
use Sigmie\English\Filter\Stopwords;
use Sigmie\Index\Analysis\Analyzer;
use Sigmie\Index\Analysis\TokenFilter\Shingle;
use Sigmie\Index\Analysis\TokenFilter\Stopwords as TokenFilterStopwords;
use Sigmie\Index\Analysis\TokenFilter\Trim;
use Sigmie\Index\Analysis\TokenFilter\Truncate;
use Sigmie\Index\Analysis\TokenFilter\Unique;
use Sigmie\Index\Analysis\Tokenizers\WordBoundaries;
use Sigmie\Index\Contracts\Mappings;
use Sigmie\Mappings\Properties;
use Sigmie\Mappings\Types\Address;
use Sigmie\Mappings\Types\CaseSensitiveKeyword;
use Sigmie\Mappings\Types\Category;
use Sigmie\Mappings\Types\Email;
use Sigmie\Mappings\Types\Keyword;
use Sigmie\Mappings\Types\Name;
use Sigmie\Mappings\Types\Path;
use Sigmie\Mappings\Types\SearchableNumber;
use Sigmie\Mappings\Types\Sentence;
use Sigmie\Mappings\Types\Tags;
use Sigmie\Mappings\Types\Text;
use Sigmie\Search\Autocomplete\NewPipeline;
use Sigmie\Search\Autocomplete\Pipeline;
use Sigmie\Search\Autocomplete\Script;
use Sigmie\Search\Autocomplete\Set;
use Sigmie\Shared\Collection;

trait Autocomplete
{
    use Ingest;

    protected bool $autocomplete = false;

    protected bool $lowercaseAutocomplete = false;

    protected array $autocompleteFields = [];

    public function autocomplete(array $fields): static
    {
        $this->autocomplete = true;

        $this->autocompleteFields = $fields;

        return $this;
    }

    public function lowercaseAutocompletions(): static
    {
        $this->lowercaseAutocomplete = true;

        return $this;
    }

    public function createAutocompletePipeline(Mappings $mappings): Pipeline
    {
        /** @var  Properties */
        $properties = $mappings->properties();

        $combinableFields = $this->combinableFields($properties);
        $nonCombinableFields = $this->nonCombinableFields($properties);

        $fields = implode(',', [...$combinableFields, ...$nonCombinableFields]);

        $newPipeline = new NewPipeline($this->elasticsearchConnection, 'create_autocomplete_field');

        $processor = new Script;
        $processor->params([
            'lowercase' => $this->lowercaseAutocomplete,
            'max_permutations' => 3
        ]);
        $processor->source("
      def fields = [{$fields}];
      def lowercase = params.lowercase;
      def flattenedFields = [];
      def permutations = [];
      def max_permutations = params.max_permutations ?: 10;
      
      // Flatten any nested arrays and convert to string
      for (def i = 0; i < fields.length; i++) {
        if (fields[i] == null) {
          continue;
        }
        if (fields[i] instanceof List) {
          flattenedFields.add(fields[i].join(' '));
        } else {
          flattenedFields.add(fields[i].toString());
        }
      }

      // Lowercase all field values if requested
      if (lowercase) {
        for (def i = 0; i < flattenedFields.length; i++) {
          if (flattenedFields[i] != null) {
            flattenedFields[i] = flattenedFields[i].toLowerCase();
          }
        }
      }
      
      // Convert permutations to list of maps with input and weight keys
      def result = [];
      for (int i = 0; i < flattenedFields.length; i++) {
        def perm = flattenedFields[i].trim();
        def map = [:];
        map['input'] = perm;
        map['weight'] = 1;
        result.add(map);
      }

        def uniqueValues = new HashSet();
        for (value in result) {
        uniqueValues.add(value);
        }
        result = uniqueValues.toArray();
      
        ctx.autocomplete = result;
        ");

        return $newPipeline
            ->addPocessor($processor)
            ->create();
    }

    private function nonCombinableFields(Properties $properties)
    {
        $collection = new Collection($properties->toArray());

        $fieldNames = $collection->filter(fn ($type) => $type instanceof Text)
            ->filter(fn (Text $type, $name) => in_array($name, $this->autocompleteFields))
            ->filter(fn (Text $type) => in_array($type::class, [
                Email::class, SearchableNumber::class,
                Path::class,
                Keyword::class, CaseSensitiveKeyword::class, Tags::class,
                Sentence::class,
                Name::class
            ]))
            ->mapWithKeys(fn (Text $type, string $name) => [$name => "(ctx.{$name} != null ? (ctx.{$name} instanceof List ? ctx.{$name}.join(' ') : ctx.{$name}?.trim() + ' ') : '')"])
            ->values();

        return $fieldNames;
    }

    private function combinableFields(Properties $properties)
    {
        $collection = new Collection($properties->toArray());

        $categoryFieldsPermutations = $collection->filter(fn ($type) => $type instanceof Text)
            ->filter(fn (Text $type) => in_array($type::class, [
                Category::class
            ]))
            ->filter(fn (Text $type, $name) => in_array($name, $this->autocompleteFields))
            ->mapWithKeys(fn (Text $type, string $name) => [$name => "(ctx.{$name} != null ? (ctx.{$name} instanceof List ? ctx.{$name}.join(' ') + ' ' : ctx.{$name}?.trim() + ' ') : '')"])
            ->values();

        $categoryFieldsPermutations = $this->permutations($categoryFieldsPermutations, 10);
        $categoryFieldsValues = array_map(fn ($values) => "(" . implode('+', $values) . ")", $categoryFieldsPermutations);

        return $categoryFieldsValues;
    }

    private function permutations($array, $maxLength = 10)
    {
        if (count($array) == 1) {
            return [$array];
        }

        $result = [];
        foreach ($array as $index => $element) {
            $subarray = $array;
            unset($subarray[$index]);
            foreach ($this->permutations($subarray, $maxLength) as $permutation) {
                array_unshift($permutation, $element);
                $result[] = $permutation;
                if (count($permutation) >= $maxLength) {
                    break;
                }
            }
            if (count($result) >= $maxLength) {
                break;
            }
        }

        return $result;
    }

    public function createAutocompleteAnalyzer(): Analyzer
    {
        $autocompleteAnalyzer = new Analyzer(
            'autocomplete_analyzer',
            new WordBoundaries(),
            [
                new Stopwords(),
                new TokenFilterStopwords('custom_stopwords', ['and', 'the']),
                new Truncate('demo', 2),
                new Lowercase(),
                new Trim('autocomplete_trim'),
                new Stemmer(),
                new Unique('autocomplete_unique'),
                new Shingle('autocomplete_shingle', 2, 3),
            ]
        );

        return $autocompleteAnalyzer;
    }
}
