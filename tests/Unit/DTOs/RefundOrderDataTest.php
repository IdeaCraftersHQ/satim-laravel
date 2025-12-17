<?php

use Oss\SatimLaravel\DTOs\RefundOrderData;
use Oss\SatimLaravel\Exceptions\SatimValidationException;

test('creates RefundOrderData with valid data', function () {
    $data = new RefundOrderData(
        orderId: 'abc123',
        amount: 10000
    );

    expect($data->orderId)->toBe('abc123')
        ->and($data->amount)->toBe(10000);
});

test('throws exception when order ID is empty', function () {
    new RefundOrderData(
        orderId: '',
        amount: 10000
    );
})->throws(SatimValidationException::class, 'Order ID is required');

test('throws exception when amount is less than 5000 cents', function () {
    new RefundOrderData(
        orderId: 'abc123',
        amount: 4999
    );
})->throws(SatimValidationException::class, 'Amount must be at least 5000 cents (50 DA)');

test('throws exception when amount is not multiple of 100', function () {
    new RefundOrderData(
        orderId: 'abc123',
        amount: 5050
    );
})->throws(SatimValidationException::class, 'Amount must be a multiple of 100 cents');

test('converts to array correctly', function () {
    $data = new RefundOrderData(
        orderId: 'abc123',
        amount: 10000
    );

    $array = $data->toArray();

    expect($array)
        ->toHaveKey('orderId', 'abc123')
        ->toHaveKey('amount', 10000);
});
