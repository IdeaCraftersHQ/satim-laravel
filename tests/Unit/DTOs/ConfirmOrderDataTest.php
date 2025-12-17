<?php

use Oss\SatimLaravel\DTOs\ConfirmOrderData;
use Oss\SatimLaravel\Exceptions\SatimValidationException;

test('creates ConfirmOrderData with valid data', function () {
    $data = new ConfirmOrderData(
        mdOrder: 'V721uPPfNNofVQAAABL3',
        language: 'fr'
    );

    expect($data->mdOrder)->toBe('V721uPPfNNofVQAAABL3')
        ->and($data->language)->toBe('fr');
});

test('throws exception when mdOrder is empty', function () {
    new ConfirmOrderData(
        mdOrder: '',
        language: 'fr'
    );
})->throws(SatimValidationException::class, 'mdOrder is required');

test('throws exception when language is empty', function () {
    new ConfirmOrderData(
        mdOrder: 'V721uPPfNNofVQAAABL3',
        language: ''
    );
})->throws(SatimValidationException::class, 'Language is required');

test('throws exception when language is invalid', function () {
    new ConfirmOrderData(
        mdOrder: 'V721uPPfNNofVQAAABL3',
        language: 'ES'
    );
})->throws(SatimValidationException::class, 'Language must be FR, EN, or AR');

test('accepts valid languages in any case', function () {
    $languages = ['fr', 'FR', 'en', 'EN', 'ar', 'AR'];

    foreach ($languages as $language) {
        $data = new ConfirmOrderData(
            mdOrder: 'V721uPPfNNofVQAAABL3',
            language: $language
        );

        expect($data->language)->toBe($language);
    }
});

test('converts to array correctly', function () {
    $data = new ConfirmOrderData(
        mdOrder: 'V721uPPfNNofVQAAABL3',
        language: 'fr'
    );

    $array = $data->toArray();

    expect($array)
        ->toHaveKey('mdOrder', 'V721uPPfNNofVQAAABL3')
        ->toHaveKey('language', 'fr');
});
