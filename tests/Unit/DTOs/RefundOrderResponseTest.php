<?php

use Oss\SatimLaravel\DTOs\RefundOrderResponse;

test('creates RefundOrderResponse with success', function () {
    $response = new RefundOrderResponse(
        errorCode: 0
    );

    expect($response->errorCode)->toBe(0)
        ->and($response->errorMessage)->toBeNull();
});

test('creates RefundOrderResponse with error', function () {
    $response = new RefundOrderResponse(
        errorCode: 5,
        errorMessage: 'Access denied'
    );

    expect($response->errorCode)->toBe(5)
        ->and($response->errorMessage)->toBe('Access denied');
});

test('isSuccessful returns true when error code is 0', function () {
    $response = new RefundOrderResponse(errorCode: 0);

    expect($response->isSuccessful())->toBeTrue();
});

test('isSuccessful returns false when error code is not 0', function () {
    $response = new RefundOrderResponse(errorCode: 5);

    expect($response->isSuccessful())->toBeFalse();
});

test('creates from array with success response', function () {
    $data = [
        'errorCode' => 0,
        'errorMessage' => null,
    ];

    $response = RefundOrderResponse::fromArray($data);

    expect($response->errorCode)->toBe(0)
        ->and($response->errorMessage)->toBeNull();
});

test('creates from array with error response', function () {
    $data = [
        'errorCode' => 7,
        'errorMessage' => 'System error',
    ];

    $response = RefundOrderResponse::fromArray($data);

    expect($response->errorCode)->toBe(7)
        ->and($response->errorMessage)->toBe('System error');
});

test('creates from empty array with defaults', function () {
    $response = RefundOrderResponse::fromArray([]);

    expect($response->errorCode)->toBe(0)
        ->and($response->errorMessage)->toBeNull();
});
