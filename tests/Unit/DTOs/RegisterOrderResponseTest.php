<?php

use Ideacrafters\SatimLaravel\DTOs\RegisterOrderResponse;

test('creates RegisterOrderResponse with success data', function () {
    $response = new RegisterOrderResponse(
        errorCode: 0,
        orderId: 'V721uPPfNNofVQAAABL3',
        formUrl: 'https://test.satim.dz/payment/epg/...'
    );

    expect($response->errorCode)->toBe(0)
        ->and($response->orderId)->toBe('V721uPPfNNofVQAAABL3')
        ->and($response->formUrl)->toBe('https://test.satim.dz/payment/epg/...')
        ->and($response->errorMessage)->toBeNull();
});

test('creates RegisterOrderResponse with error data', function () {
    $response = new RegisterOrderResponse(
        errorCode: 5,
        errorMessage: 'Access denied'
    );

    expect($response->errorCode)->toBe(5)
        ->and($response->orderId)->toBeNull()
        ->and($response->formUrl)->toBeNull()
        ->and($response->errorMessage)->toBe('Access denied');
});

test('isSuccessful returns true when error code is 0', function () {
    $response = new RegisterOrderResponse(errorCode: 0);

    expect($response->isSuccessful())->toBeTrue();
});

test('isSuccessful returns false when error code is not 0', function () {
    $response = new RegisterOrderResponse(errorCode: 5);

    expect($response->isSuccessful())->toBeFalse();
});

test('creates from array with success response', function () {
    $data = [
        'errorCode' => 0,
        'orderId' => 'V721uPPfNNofVQAAABL3',
        'formUrl' => 'https://test.satim.dz/payment/epg/...',
    ];

    $response = RegisterOrderResponse::fromArray($data);

    expect($response->errorCode)->toBe(0)
        ->and($response->orderId)->toBe('V721uPPfNNofVQAAABL3')
        ->and($response->formUrl)->toBe('https://test.satim.dz/payment/epg/...');
});

test('creates from array with error response', function () {
    $data = [
        'errorCode' => 5,
        'errorMessage' => 'Access denied',
    ];

    $response = RegisterOrderResponse::fromArray($data);

    expect($response->errorCode)->toBe(5)
        ->and($response->errorMessage)->toBe('Access denied')
        ->and($response->orderId)->toBeNull()
        ->and($response->formUrl)->toBeNull();
});

test('creates from empty array with defaults', function () {
    $response = RegisterOrderResponse::fromArray([]);

    expect($response->errorCode)->toBe(0)
        ->and($response->orderId)->toBeNull()
        ->and($response->formUrl)->toBeNull()
        ->and($response->errorMessage)->toBeNull();
});
