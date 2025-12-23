<?php

use Ideacrafters\SatimLaravel\DTOs\ConfirmOrderResponse;

test('creates ConfirmOrderResponse with paid order', function () {
    $response = new ConfirmOrderResponse(
        errorCode: 0,
        orderStatus: 2,
        orderNumber: 'CMD0000004',
        pan: '6280****7215',
        amount: 100320,
        depositAmount: 100320,
        currency: '012',
        actionCode: 0,
        actionCodeDescription: 'Votre paiement a été accepté'
    );

    expect($response->errorCode)->toBe(0)
        ->and($response->orderStatus)->toBe(2)
        ->and($response->orderNumber)->toBe('CMD0000004')
        ->and($response->pan)->toBe('6280****7215')
        ->and($response->amount)->toBe(100320)
        ->and($response->depositAmount)->toBe(100320)
        ->and($response->currency)->toBe('012')
        ->and($response->actionCode)->toBe(0)
        ->and($response->actionCodeDescription)->toBe('Votre paiement a été accepté');
});

test('isSuccessful returns true when error code is 0', function () {
    $response = new ConfirmOrderResponse(errorCode: 0, orderStatus: 2);

    expect($response->isSuccessful())->toBeTrue();
});

test('isSuccessful returns false when error code is not 0', function () {
    $response = new ConfirmOrderResponse(errorCode: 5, orderStatus: 0);

    expect($response->isSuccessful())->toBeFalse();
});

test('isPaid returns true when order status is 2', function () {
    $response = new ConfirmOrderResponse(errorCode: 0, orderStatus: 2);

    expect($response->isPaid())->toBeTrue();
});

test('isPaid returns false when order status is not 2', function () {
    $response = new ConfirmOrderResponse(errorCode: 0, orderStatus: 1);

    expect($response->isPaid())->toBeFalse();
});

test('isPreAuthorized returns true when order status is 1', function () {
    $response = new ConfirmOrderResponse(errorCode: 0, orderStatus: 1);

    expect($response->isPreAuthorized())->toBeTrue();
});

test('isDeclined returns true when order status is 6', function () {
    $response = new ConfirmOrderResponse(errorCode: 0, orderStatus: 6);

    expect($response->isDeclined())->toBeTrue();
});

test('isRefunded returns true when order status is 4', function () {
    $response = new ConfirmOrderResponse(errorCode: 0, orderStatus: 4);

    expect($response->isRefunded())->toBeTrue();
});

test('isReversed returns true when order status is 3', function () {
    $response = new ConfirmOrderResponse(errorCode: 0, orderStatus: 3);

    expect($response->isReversed())->toBeTrue();
});

test('creates from array with capitalized keys', function () {
    $data = [
        'ErrorCode' => 0,
        'OrderStatus' => 2,
        'OrderNumber' => 'CMD0000004',
        'Pan' => '6280****7215',
        'Amount' => 100320,
        'depositAmount' => 100320,
        'currency' => '012',
        'actionCode' => 0,
        'actionCodeDescription' => 'Votre paiement a été accepté',
    ];

    $response = ConfirmOrderResponse::fromArray($data);

    expect($response->errorCode)->toBe(0)
        ->and($response->orderStatus)->toBe(2)
        ->and($response->orderNumber)->toBe('CMD0000004')
        ->and($response->pan)->toBe('6280****7215')
        ->and($response->amount)->toBe(100320);
});

test('creates from array with lowercase keys', function () {
    $data = [
        'errorCode' => 0,
        'orderStatus' => 2,
        'orderNumber' => 'CMD0000004',
        'pan' => '6280****7215',
        'amount' => 100320,
    ];

    $response = ConfirmOrderResponse::fromArray($data);

    expect($response->errorCode)->toBe(0)
        ->and($response->orderStatus)->toBe(2)
        ->and($response->orderNumber)->toBe('CMD0000004')
        ->and($response->pan)->toBe('6280****7215')
        ->and($response->amount)->toBe(100320);
});

test('creates from empty array with defaults', function () {
    $response = ConfirmOrderResponse::fromArray([]);

    expect($response->errorCode)->toBe(0)
        ->and($response->orderStatus)->toBe(0)
        ->and($response->orderNumber)->toBeNull()
        ->and($response->pan)->toBeNull()
        ->and($response->amount)->toBeNull();
});

test('converts string amounts to integers', function () {
    $data = [
        'errorCode' => 0,
        'orderStatus' => 2,
        'Amount' => '100320',
        'depositAmount' => '100320',
    ];

    $response = ConfirmOrderResponse::fromArray($data);

    expect($response->amount)->toBe(100320)
        ->and($response->depositAmount)->toBe(100320);
});
