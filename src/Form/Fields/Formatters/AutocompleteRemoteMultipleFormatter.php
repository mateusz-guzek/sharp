<?php

namespace Code16\Sharp\Form\Fields\Formatters;

use Code16\Sharp\Form\Fields\SharpFormAutocompleteRemoteMultipleField;
use Code16\Sharp\Form\Fields\SharpFormField;
use Code16\Sharp\Utils\Transformers\ArrayConverter;

class AutocompleteRemoteMultipleFormatter extends SharpFieldFormatter
{
    public function toFront(SharpFormField $field, $value): array
    {
        /** @var SharpFormAutocompleteRemoteMultipleField $field */
        return collect($value ?? [])
            ->map(fn ($item) => $field->itemWithRenderedTemplates(
                ArrayConverter::modelToArray($item),
            ))
            ->values()
            ->all();
    }

    public function fromFront(SharpFormField $field, string $attribute, $value): array
    {
        /** @var SharpFormAutocompleteRemoteMultipleField $field */
        return collect($value ?? [])
            ->map(fn ($item) => is_array($item)
                ? ($item[$field->itemIdAttribute()] ?? null)
                : $item)
            ->filter(fn ($id) => $id !== null)
            ->values()
            ->all();
    }
}
