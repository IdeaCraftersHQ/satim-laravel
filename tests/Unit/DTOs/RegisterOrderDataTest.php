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
        terminalId: 'TEST12345',
        udf1: 'customer123'
    );

    expect($data->orderNumber)->toBe('1234567890')
        ->and($data->amount)->toBe(10000)
        ->and($data->currency)->toBe('012')
        ->and($data->returnUrl)->toBe('https://example.com/success')
        ->and($data->language)->toBe('FR') // Normalized to uppercase
        ->and($data->terminalId)->toBe('TEST12345')
        ->and($data->udf1)->toBe('customer123');
});

// Order Number validation tests
test('throws exception when order number contains special characters', function () {
    new RegisterOrderData(
        orderNumber: 'ORDER-123',
        amount: 10000,
        currency: '012',
        returnUrl: 'https://example.com',
        language: 'fr',
        terminalId: 'TEST',
        udf1: 'test123'
    );
})->throws(SatimValidationException::class, 'Order number must be alphanumeric');

test('throws exception when order number contains spaces', function () {
    new RegisterOrderData(
        orderNumber: 'ORDER 123',
        amount: 10000,
        currency: '012',
        returnUrl: 'https://example.com',
        language: 'fr',
        terminalId: 'TEST',
        udf1: 'test123'
    );
})->throws(SatimValidationException::class, 'Order number must be alphanumeric');

test('throws exception when order number is empty', function () {
    new RegisterOrderData(
        orderNumber: '',
        amount: 10000,
        currency: '012',
        returnUrl: 'https://example.com',
        language: 'fr',
        terminalId: 'TEST',
        udf1: 'test123'
    );
})->throws(SatimValidationException::class, 'Order number must be alphanumeric');

test('throws exception when order number exceeds 10 characters', function () {
    new RegisterOrderData(
        orderNumber: '12345678901',
        amount: 10000,
        currency: '012',
        returnUrl: 'https://example.com',
        language: 'fr',
        terminalId: 'TEST',
        udf1: 'test123'
    );
})->throws(SatimValidationException::class, 'Order number must be alphanumeric');

test('accepts alphanumeric order numbers', function () {
    $validOrderNumbers = ['ABC123', '123456', 'ORDER1', '1234567890', 'abc123xyz'];

    foreach ($validOrderNumbers as $orderNumber) {
        $data = new RegisterOrderData(
            orderNumber: $orderNumber,
            amount: 10000,
            currency: '012',
            returnUrl: 'https://example.com',
            language: 'fr',
            terminalId: 'TEST',
            udf1: 'test123'
        );

        expect($data->orderNumber)->toBe($orderNumber);
    }
});

// Amount validation tests
test('throws exception when amount is less than 5000 cents', function () {
    new RegisterOrderData(
        orderNumber: '123',
        amount: 4999,
        currency: '012',
        returnUrl: 'https://example.com',
        language: 'fr',
        terminalId: 'TEST',
        udf1: 'test123'
    );
})->throws(SatimValidationException::class, 'Amount must be at least 5000 cents (50 DA)');

test('throws exception when amount is not multiple of 100', function () {
    new RegisterOrderData(
        orderNumber: '123',
        amount: 5050,
        currency: '012',
        returnUrl: 'https://example.com',
        language: 'fr',
        terminalId: 'TEST',
        udf1: 'test123'
    );
})->throws(SatimValidationException::class, 'Amount must be a multiple of 100 cents');

// Currency validation tests
test('throws exception when currency is not 3 digits', function () {
    new RegisterOrderData(
        orderNumber: '123',
        amount: 10000,
        currency: '01',
        returnUrl: 'https://example.com',
        language: 'fr',
        terminalId: 'TEST',
        udf1: 'test123'
    );
})->throws(SatimValidationException::class, 'Currency must be a 3-digit ISO 4217 code');

test('throws exception when currency contains non-numeric characters', function () {
    new RegisterOrderData(
        orderNumber: '123',
        amount: 10000,
        currency: 'DZD',
        returnUrl: 'https://example.com',
        language: 'fr',
        terminalId: 'TEST',
        udf1: 'test123'
    );
})->throws(SatimValidationException::class, 'Currency must be a 3-digit ISO 4217 code');

test('accepts valid 3-digit currency codes', function () {
    $validCurrencies = ['012', '840', '978', '001'];

    foreach ($validCurrencies as $currency) {
        $data = new RegisterOrderData(
            orderNumber: '123',
            amount: 10000,
            currency: $currency,
            returnUrl: 'https://example.com',
            language: 'fr',
            terminalId: 'TEST',
            udf1: 'test123'
        );

        expect($data->currency)->toBe($currency);
    }
});

// URL validation tests
test('throws exception when return URL is empty', function () {
    new RegisterOrderData(
        orderNumber: '123',
        amount: 10000,
        currency: '012',
        returnUrl: '',
        language: 'fr',
        terminalId: 'TEST',
        udf1: 'test123'
    );
})->throws(SatimValidationException::class, 'Return URL is required');

test('throws exception when return URL is invalid', function () {
    new RegisterOrderData(
        orderNumber: '123',
        amount: 10000,
        currency: '012',
        returnUrl: 'not-a-valid-url',
        language: 'fr',
        terminalId: 'TEST',
        udf1: 'test123'
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
        udf1: 'test123',
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
        udf1: 'test123',
        description: str_repeat('a', 513)
    );
})->throws(SatimValidationException::class, 'Description must not exceed 512 characters');

// Language validation tests
test('throws exception when language is invalid', function () {
    new RegisterOrderData(
        orderNumber: '123',
        amount: 10000,
        currency: '012',
        returnUrl: 'https://example.com',
        language: 'ES',
        terminalId: 'TEST',
        udf1: 'test123'
    );
})->throws(SatimValidationException::class, 'Language must be FR, EN, or AR');

test('normalizes language to uppercase', function () {
    $languages = ['fr' => 'FR', 'en' => 'EN', 'ar' => 'AR', 'FR' => 'FR', 'EN' => 'EN', 'AR' => 'AR'];

    foreach ($languages as $input => $expected) {
        $data = new RegisterOrderData(
            orderNumber: '123',
            amount: 10000,
            currency: '012',
            returnUrl: 'https://example.com',
            language: $input,
            terminalId: 'TEST',
            udf1: 'test123'
        );

        expect($data->language)->toBe($expected);
    }
});

// Terminal ID validation tests
test('throws exception when terminal ID contains special characters', function () {
    new RegisterOrderData(
        orderNumber: '123',
        amount: 10000,
        currency: '012',
        returnUrl: 'https://example.com',
        language: 'fr',
        terminalId: 'TEST@123',
        udf1: 'test123'
    );
})->throws(SatimValidationException::class, 'Terminal ID must be alphanumeric');

test('throws exception when terminal ID exceeds 16 characters', function () {
    new RegisterOrderData(
        orderNumber: '123',
        amount: 10000,
        currency: '012',
        returnUrl: 'https://example.com',
        language: 'fr',
        terminalId: '12345678901234567',
        udf1: 'test123'
    );
})->throws(SatimValidationException::class, 'Terminal ID must be alphanumeric');

test('accepts valid terminal IDs', function () {
    $validTerminalIds = ['TEST123', 'E0123456789', 'TERMINAL1', '1234567890123456'];

    foreach ($validTerminalIds as $terminalId) {
        $data = new RegisterOrderData(
            orderNumber: '123',
            amount: 10000,
            currency: '012',
            returnUrl: 'https://example.com',
            language: 'fr',
            terminalId: $terminalId,
            udf1: 'test123'
        );

        expect($data->terminalId)->toBe($terminalId);
    }
});

// UDF1 validation tests (optional)
test('accepts null for udf1', function () {
    $data = new RegisterOrderData(
        orderNumber: '123',
        amount: 10000,
        currency: '012',
        returnUrl: 'https://example.com',
        language: 'fr',
        terminalId: 'TEST'
    );

    expect($data->udf1)->toBeNull();
});

test('throws exception when udf1 is empty string', function () {
    new RegisterOrderData(
        orderNumber: '123',
        amount: 10000,
        currency: '012',
        returnUrl: 'https://example.com',
        language: 'fr',
        terminalId: 'TEST',
        udf1: ''
    );
})->throws(SatimValidationException::class, 'Udf1 must be alphanumeric');

test('throws exception when udf1 contains special characters', function () {
    new RegisterOrderData(
        orderNumber: '123',
        amount: 10000,
        currency: '012',
        returnUrl: 'https://example.com',
        language: 'fr',
        terminalId: 'TEST',
        udf1: 'customer_123'
    );
})->throws(SatimValidationException::class, 'Udf1 must be alphanumeric');

test('throws exception when udf1 exceeds 20 characters', function () {
    new RegisterOrderData(
        orderNumber: '123',
        amount: 10000,
        currency: '012',
        returnUrl: 'https://example.com',
        language: 'fr',
        terminalId: 'TEST',
        udf1: str_repeat('a', 21)
    );
})->throws(SatimValidationException::class, 'Udf1 must be alphanumeric');

test('accepts valid udf1 values', function () {
    $validUdf1Values = ['customer123', 'ORDER456', '12345678901234567890', 'ABC'];

    foreach ($validUdf1Values as $udf1) {
        $data = new RegisterOrderData(
            orderNumber: '123',
            amount: 10000,
            currency: '012',
            returnUrl: 'https://example.com',
            language: 'fr',
            terminalId: 'TEST',
            udf1: $udf1
        );

        expect($data->udf1)->toBe($udf1);
    }
});

// UDF2-5 validation tests (optional)
test('throws exception when udf2 contains special characters', function () {
    new RegisterOrderData(
        orderNumber: '123',
        amount: 10000,
        currency: '012',
        returnUrl: 'https://example.com',
        language: 'fr',
        terminalId: 'TEST',
        udf1: 'test123',
        udf2: 'value_2'
    );
})->throws(SatimValidationException::class, 'Udf2 must be alphanumeric');

test('throws exception when udf3 exceeds 20 characters', function () {
    new RegisterOrderData(
        orderNumber: '123',
        amount: 10000,
        currency: '012',
        returnUrl: 'https://example.com',
        language: 'fr',
        terminalId: 'TEST',
        udf1: 'test123',
        udf3: str_repeat('a', 21)
    );
})->throws(SatimValidationException::class, 'Udf3 must be alphanumeric');

test('accepts null for optional udf1-5 fields', function () {
    $data = new RegisterOrderData(
        orderNumber: '123',
        amount: 10000,
        currency: '012',
        returnUrl: 'https://example.com',
        language: 'fr',
        terminalId: 'TEST'
    );

    expect($data->udf1)->toBeNull()
        ->and($data->udf2)->toBeNull()
        ->and($data->udf3)->toBeNull()
        ->and($data->udf4)->toBeNull()
        ->and($data->udf5)->toBeNull();
});

test('converts to array correctly', function () {
    $data = new RegisterOrderData(
        orderNumber: '123',
        amount: 10000,
        currency: '012',
        returnUrl: 'https://example.com/success',
        language: 'fr',
        terminalId: 'TEST123',
        udf1: 'value1',
        failUrl: 'https://example.com/fail',
        description: 'Test payment',
        udf2: 'value2'
    );

    $array = $data->toArray();

    expect($array)
        ->toHaveKey('orderNumber', '123')
        ->toHaveKey('amount', 10000)
        ->toHaveKey('currency', '012')
        ->toHaveKey('returnUrl', 'https://example.com/success')
        ->toHaveKey('language', 'FR') // Uppercase
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
        terminalId: 'TEST',
        udf1: 'test123'
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

// Input Sanitization Tests (trim whitespace)

test('trims whitespace from order number', function () {
    $data = new RegisterOrderData(
        orderNumber: '  ORDER123  ',
        amount: 10000,
        currency: '012',
        returnUrl: 'https://example.com',
        language: 'fr',
        terminalId: 'TEST',
        udf1: 'test123'
    );

    expect($data->orderNumber)->toBe('ORDER123');
});

test('trims whitespace from currency', function () {
    $data = new RegisterOrderData(
        orderNumber: 'ORDER123',
        amount: 10000,
        currency: '  012  ',
        returnUrl: 'https://example.com',
        language: 'fr',
        terminalId: 'TEST',
        udf1: 'test123'
    );

    expect($data->currency)->toBe('012');
});

test('trims whitespace from language before uppercase normalization', function () {
    $data = new RegisterOrderData(
        orderNumber: 'ORDER123',
        amount: 10000,
        currency: '012',
        returnUrl: 'https://example.com',
        language: '  fr  ',
        terminalId: 'TEST',
        udf1: 'test123'
    );

    expect($data->language)->toBe('FR');  // Trimmed and uppercased
});

test('trims whitespace from all UDF fields', function () {
    $data = new RegisterOrderData(
        orderNumber: 'ORDER123',
        amount: 10000,
        currency: '012',
        returnUrl: 'https://example.com',
        language: 'fr',
        terminalId: 'TEST',
        udf1: '  value1  ',
        udf2: '  value2  ',
        udf3: '  value3  '
    );

    expect($data->udf1)->toBe('value1')
        ->and($data->udf2)->toBe('value2')
        ->and($data->udf3)->toBe('value3');
});

test('throws exception when order number is only whitespace', function () {
    new RegisterOrderData(
        orderNumber: '     ',  // Only spaces
        amount: 10000,
        currency: '012',
        returnUrl: 'https://example.com',
        language: 'fr',
        terminalId: 'TEST',
        udf1: 'test123'
    );
})->throws(SatimValidationException::class, 'Order number must be alphanumeric');

test('trims whitespace from terminal ID', function () {
    $data = new RegisterOrderData(
        orderNumber: 'ORDER123',
        amount: 10000,
        currency: '012',
        returnUrl: 'https://example.com',
        language: 'fr',
        terminalId: '  TEST123  ',
        udf1: 'test123'
    );

    expect($data->terminalId)->toBe('TEST123');
});

// Maximum Amount Validation Tests

test('validates maximum amount constant is set correctly', function () {
    // PHP_INT_MAX on 64-bit systems is 9223372036854775807 (19 digits)
    // We can't test the full 20-digit limit as an integer, but we can verify the constant exists

    // Use reflection to test the constant value directly
    $reflection = new ReflectionClass(RegisterOrderData::class);
    $maxAmount = $reflection->getConstant('MAX_AMOUNT');

    expect($maxAmount)->toBe(99999999999999999999);
});

test('accepts large valid amount near PHP_INT_MAX', function () {
    // Note: PHP_INT_MAX on 64-bit systems is 9223372036854775807 (19 digits)
    // We can't test the full 20-digit limit, but we can test a large value
    $largeAmount = 9223372036854775800; // Close to PHP_INT_MAX, divisible by 100

    $data = new RegisterOrderData(
        orderNumber: '123',
        amount: $largeAmount,
        currency: '012',
        returnUrl: 'https://example.com',
        language: 'fr',
        terminalId: 'TEST',
        udf1: 'test123'
    );

    expect($data->amount)->toBe($largeAmount);
});

// Multibyte Character Handling Tests

test('validates description length with multibyte characters', function () {
    // Arabic text: 512 characters (but more than 512 bytes)
    $arabicText = str_repeat('م', 512);  // 512 Arabic chars

    $data = new RegisterOrderData(
        orderNumber: '123',
        amount: 10000,
        currency: '012',
        returnUrl: 'https://example.com',
        language: 'fr',
        terminalId: 'TEST',
        udf1: 'test123',
        description: $arabicText
    );

    expect(mb_strlen($data->description, 'UTF-8'))->toBe(512);
});

test('throws exception when description exceeds 512 multibyte characters', function () {
    $arabicText = str_repeat('م', 513);  // 513 Arabic chars

    new RegisterOrderData(
        orderNumber: '123',
        amount: 10000,
        currency: '012',
        returnUrl: 'https://example.com',
        language: 'fr',
        terminalId: 'TEST',
        udf1: 'test123',
        description: $arabicText
    );
})->throws(SatimValidationException::class, 'must not exceed 512 characters');

test('handles French accented characters correctly in description', function () {
    // French text with accents
    $frenchText = "Café français à côté de l'église"; // 32 characters with accents

    $data = new RegisterOrderData(
        orderNumber: '123',
        amount: 10000,
        currency: '012',
        returnUrl: 'https://example.com',
        language: 'fr',
        terminalId: 'TEST',
        udf1: 'test123',
        description: $frenchText
    );

    expect(mb_strlen($data->description, 'UTF-8'))->toBe(32);
});
