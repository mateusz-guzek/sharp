<?php

use Code16\Sharp\Form\Fields\SharpFormAutocompleteRemoteMultipleField;

it('sets default values for multiple remote autocomplete', function () {
    $field = SharpFormAutocompleteRemoteMultipleField::make('field')
        ->setRemoteEndpoint('/endpoint');

    expect($field->toArray())
        ->toEqual([
            'key' => 'field',
            'type' => 'autocomplete',
            'mode' => 'remote',
            'remoteEndpoint' => '/endpoint',
            'itemIdAttribute' => 'id',
            'searchMinChars' => 1,
            'debounceDelay' => 300,
            'multiple' => true,
        ]);
});

it('inherits remote autocomplete configuration', function () {
    $callback = fn (string $query) => [['id' => 1, 'label' => $query]];
    $field = SharpFormAutocompleteRemoteMultipleField::make('field')
        ->setRemoteCallback($callback)
        ->setSearchMinChars(2)
        ->setDebounceDelayInMilliseconds(500)
        ->allowEmptySearch();

    expect($field->getRemoteCallback())->toBe($callback)
        ->and($field->toArray())
        ->toHaveKey('multiple', true)
        ->toHaveKey('searchMinChars', 0)
        ->toHaveKey('debounceDelay', 500);
});
