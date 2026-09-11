<?php

use Code16\Sharp\Form\Fields\Formatters\AutocompleteRemoteMultipleFormatter;
use Code16\Sharp\Form\Fields\SharpFormAutocompleteRemoteMultipleField;

it('formats multiple remote values to front', function () {
    $field = SharpFormAutocompleteRemoteMultipleField::make('items')
        ->setListItemTemplate('{{ $name }}');

    $value = [
        ['id' => 1, 'name' => 'First'],
        ['id' => 2, 'name' => 'Second'],
    ];

    expect((new AutocompleteRemoteMultipleFormatter())->toFront($field, $value))
        ->toEqual([
            ['id' => 1, 'name' => 'First', '_html' => 'First'],
            ['id' => 2, 'name' => 'Second', '_html' => 'Second'],
        ]);
});

it('formats multiple remote values from front', function () {
    $field = SharpFormAutocompleteRemoteMultipleField::make('items')
        ->setItemIdAttribute('uuid');

    $value = [
        ['uuid' => 'first', 'label' => 'First'],
        ['uuid' => 'second', 'label' => 'Second'],
    ];

    expect((new AutocompleteRemoteMultipleFormatter())->fromFront($field, 'items', $value))
        ->toBe(['first', 'second']);
});

it('formats an empty multiple remote value', function () {
    $field = SharpFormAutocompleteRemoteMultipleField::make('items');
    $formatter = new AutocompleteRemoteMultipleFormatter();

    expect($formatter->toFront($field, null))->toBe([])
        ->and($formatter->fromFront($field, 'items', null))->toBe([]);
});
