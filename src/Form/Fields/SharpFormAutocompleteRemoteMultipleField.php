<?php

namespace Code16\Sharp\Form\Fields;

use Code16\Sharp\Form\Fields\Formatters\AutocompleteRemoteMultipleFormatter;

class SharpFormAutocompleteRemoteMultipleField extends SharpFormAutocompleteRemoteField
{
    public static function make(string $key): self
    {
        $instance = new static($key, static::FIELD_TYPE, new AutocompleteRemoteMultipleFormatter());
        $instance->mode = 'remote';

        return $instance;
    }

    public function toArray(): array
    {
        return [
            ...parent::toArray(),
            'multiple' => true,
        ];
    }
}
