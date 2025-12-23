<?php

use Ideacrafters\SatimLaravel\DTOs\RegisterOrderData;
use Ideacrafters\SatimLaravel\Exceptions\SatimValidationException;

test('creates RegisterOrderData with valid data', function () {
    $data = new RegisterOrderData(
        orderNumber: '1234567890',
        amount: 10000,
        currency: '012',
        returnUrl: 'https://example.com/success',
        language: 'fr',
        terminalId: 'TEST12345'
    );

    expect($data->orderNumber)->toBe('1234567890')
        ->and($data->amount)->toBe(10000)
        ->and($data->currency)->toBe('012')
        ->and($data->returnUrl)->toBe('https://example.com/success')
        ->and($data->language)->toBe('fr')
        ->and($data->terminalId)->toBe('TEST12345');
});

test('throws exception when order number is empty', function () {
    new RegisterOrderData(
        orderNumber: '',
        amount: 10000,
        currency: '012',
        returnUrl: 'https://example.com',
        language: 'fr',
        terminalId: 'TEST'
    );
})->throws(SatimValidationException::class, 'Order number is required');

test('throws exception when order number exceeds 10 characters', function () {
    new RegisterOrderData(
        orderNumber: '12345678901',
        amount: 10000,
        currency: '012',
        returnUrl: 'https://example.com',
        language: 'fr',
        terminalId: 'TEST'
    );
})->throws(SatimValidationException::class, 'Order number must not exceed 10 characters');

test('throws exception when amount is less than 5000 cents', function () {
    new RegisterOrderData(
        orderNumber: '123',
        amount: 4999,
        currency: '012',
        returnUrl: 'https://example.com',
        language: 'fr',
        terminalId: 'TEST'
    );
})->throws(SatimValidationException::class, 'Amount must be at least 5000 cents (50 DA)');

test('throws exception when amount is not multiple of 100', function () {
    new RegisterOrderData(
        orderNumber: '123',
        amount: 5050,
        currency: '012',
        returnUrl: 'https://example.com',
        language: 'fr',
        terminalId: 'TEST'
    );
})->throws(SatimValidationException::class, 'Amount must be a multiple of 100 cents');

test('throws exception when currency is empty', function () {
    new RegisterOrderData(
        orderNumber: '123',
        amount: 10000,
        currency: '',
        returnUrl: 'https://example.com',
        language: 'fr',
        terminalId: 'TEST'
    );
})->throws(SatimValidationException::class, 'Currency is required');

test('throws exception when return URL is empty', function () {
    new RegisterOrderData(
        orderNumber: '123',
        amount: 10000,
        currency: '012',
        returnUrl: '',
        language: 'fr',
        terminalId: 'TEST'
    );
})->throws(SatimValidationException::class, 'Return URL is required');

test('throws exception when return URL is invalid', function () {
    new RegisterOrderData(
        orderNumber: '123',
        amount: 10000,
        currency: '012',
        returnUrl: 'not-a-valid-url',
        language: 'fr',
        terminalId: 'TEST'
    );
})->throws(SatimValidationException::class, 'Return URL must be a valid URL');

test('throws exception when fail URL is invalid', function () {
    new RegisterOrderData(
        orderNumber: '123',
        amount: 10000,
        currency: '012',
        returnUrl: 'https://example.com',
        language: 'fr',
        terminalId: 'TEST',
        failUrl: 'invalid-url'
    );
})->throws(SatimValidationException::class, 'Fail URL must be a valid URL');

test('throws exception when description exceeds 512 characters', function () {
    new RegisterOrderData(
        orderNumber: '123',
        amount: 10000,
        currency: '012',
        returnUrl: 'https://example.com',
        language: 'fr',
        terminalId: 'TEST',
        description: str_repeat('a', 513)
    );
})->throws(SatimValidationException::class, 'Description must not exceed 512 characters');

test('throws exception when language is empty', function () {
    new RegisterOrderData(
        orderNumber: '123',
        amount: 10000,
        currency: '012',
        returnUrl: 'https://example.com',
        language: '',
        terminalId: 'TEST'
    );
})->throws(SatimValidationException::class, 'Language is required');

test('throws exception when language is invalid', function () {
    new RegisterOrderData(
        orderNumber: '123',
        amount: 10000,
        currency: '012',
        returnUrl: 'https://example.com',
        language: 'ES',
        terminalId: 'TEST'
    );
})->throws(SatimValidationException::class, 'Language must be FR, EN, or AR');

test('accepts valid languages in any case', function () {
    $languages = ['fr', 'FR', 'en', 'EN', 'ar', 'AR'];

    foreach ($languages as $language) {
        $data = new RegisterOrderData(
            orderNumber: '123',
            amount: 10000,
            currency: '012',
            returnUrl: 'https://example.com',
            language: $language,
            terminalId: 'TEST'
        );

        expect($data->language)->toBe($language);
    }
});

test('throws exception when terminal ID is empty', function () {
    new RegisterOrderData(
        orderNumber: '123',
        amount: 10000,
        currency: '012',
        returnUrl: 'https://example.com',
        language: 'fr',
        terminalId: ''
    );
})->throws(SatimValidationException::class, 'Terminal ID is required');

test('throws exception when udf field exceeds 20 characters', function () {
    new RegisterOrderData(
        orderNumber: '123',
        amount: 10000,
        currency: '012',
        returnUrl: 'https://example.com',
        language: 'fr',
        terminalId: 'TEST',
        udf1: str_repeat('a', 21)
    );
})->throws(SatimValidationException::class, 'Udf1 must not exceed 20 characters');

test('converts to array correctly', function () {
    $data = new RegisterOrderData(
        orderNumber: '123',
        amount: 10000,
        currency: '012',
        returnUrl: 'https://example.com/success',
        language: 'fr',
        terminalId: 'TEST123',
        failUrl: 'https://example.com/fail',
        description: 'Test payment',
        udf1: 'value1',
        udf2: 'value2'
    );

    $array = $data->toArray();

    expect($array)
        ->toHaveKey('orderNumber', '123')
        ->toHaveKey('amount', 10000)
        ->toHaveKey('currency', '012')
        ->toHaveKey('returnUrl', 'https://example.com/success')
        ->toHaveKey('language', 'fr')
        ->toHaveKey('failUrl', 'https://example.com/fail')
        ->toHaveKey('description', 'Test payment')
        ->toHaveKey('jsonParams');

    $jsonParams = json_decode($array['jsonParams'], true);
    expect($jsonParams)
        ->toHaveKey('force_terminal_id', 'TEST123')
        ->toHaveKey('udf1', 'value1')
        ->toHaveKey('udf2', 'value2');
});

test('toArray excludes null optional fields', function () {
    $data = new RegisterOrderData(
        orderNumber: '123',
        amount: 10000,
        currency: '012',
        returnUrl: 'https://example.com',
        language: 'fr',
        terminalId: 'TEST'
    );

    $array = $data->toArray();

    expect($array)
        ->not->toHaveKey('failUrl')
        ->not->toHaveKey('description');
});

test('toArray includes only non-null udf fields in jsonParams', function () {
    $data = new RegisterOrderData(
        orderNumber: '123',
        amount: 10000,
        currency: '012',
        returnUrl: 'https://example.com',
        language: 'fr',
        terminalId: 'TEST',
        udf1: 'value1',
        udf3: 'value3'
    );

    $array = $data->toArray();
    $jsonParams = json_decode($array['jsonParams'], true);

    expect($jsonParams)
        ->toHaveKey('force_terminal_id')
        ->toHaveKey('udf1', 'value1')
        ->not->toHaveKey('udf2')
        ->toHaveKey('udf3', 'value3')
        ->not->toHaveKey('udf4')
        ->not->toHaveKey('udf5');
});
