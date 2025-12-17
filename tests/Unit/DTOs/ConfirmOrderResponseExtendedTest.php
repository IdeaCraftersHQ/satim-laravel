<?php

use Oss\SatimLaravel\DTOs\ConfirmOrderResponse;

test('creates ConfirmOrderResponse with extended fields', function () {
    $data = [
        'ErrorCode' => 0,
        'OrderStatus' => 2,
        'OrderNumber' => 'CMD0000004',
        'Pan' => '6280****7215',
        'Amount' => 100320,
        'depositAmount' => 100320,
        'currency' => '012',
        'actionCode' => 0,
        'actionCodeDescription' => 'Payment accepted',
        'expiration' => '202701',
        'cardholderName' => 'John Doe',
        'authorizationResponseId' => '913180',
        'approvalCode' => '913180',
        'Ip' => '10.12.12.14',
        'clientId' => 'customer-123',
        'bindingId' => 'binding-456',
        'paymentAccountReference' => 'ref-789',
        'Description' => 'Test payment',
        'params' => [
            'respCode' => '00',
            'respCode_desc' => 'Payment accepted',
            'udf1' => 'Bill00001',
            'udf2' => 'customer-123',
        ],
    ];

    $response = ConfirmOrderResponse::fromArray($data);

    expect($response->errorCode)->toBe(0)
        ->and($response->orderStatus)->toBe(2)
        ->and($response->expiration)->toBe('202701')
        ->and($response->cardholderName)->toBe('John Doe')
        ->and($response->authorizationResponseId)->toBe('913180')
        ->and($response->approvalCode)->toBe('913180')
        ->and($response->ip)->toBe('10.12.12.14')
        ->and($response->clientId)->toBe('customer-123')
        ->and($response->bindingId)->toBe('binding-456')
        ->and($response->paymentAccountReference)->toBe('ref-789')
        ->and($response->description)->toBe('Test payment')
        ->and($response->params)->toBeArray()
        ->and($response->params['respCode'])->toBe('00')
        ->and($response->params['udf1'])->toBe('Bill00001');
});

test('getStatusName returns correct status names', function () {
    $statuses = [
        0 => 'Order registered, but not paid',
        -1 => 'Transaction failed',
        1 => 'Transaction approved / Pre-authorized',
        2 => 'Amount deposited successfully',
        3 => 'Authorization reversed',
        4 => 'Transaction refunded',
        6 => 'Authorization declined',
        7 => 'Card added',
        8 => 'Card updated',
        9 => 'Card verified',
        10 => 'Recurring template added',
        11 => 'Debited',
        999 => 'Unknown status',
    ];

    foreach ($statuses as $code => $expectedName) {
        $response = new ConfirmOrderResponse(
            errorCode: 0,
            orderStatus: $code
        );

        expect($response->getStatusName())->toBe($expectedName);
    }
});

test('getAmountInDinars converts cents to dinars', function () {
    $response = new ConfirmOrderResponse(
        errorCode: 0,
        orderStatus: 2,
        amount: 100320
    );

    expect($response->getAmountInDinars())->toBe(1003.20);
});

test('getAmountInDinars returns 0 when amount is null', function () {
    $response = new ConfirmOrderResponse(
        errorCode: 0,
        orderStatus: 2
    );

    expect($response->getAmountInDinars())->toBe(0.0);
});

test('getDepositAmountInDinars converts cents to dinars', function () {
    $response = new ConfirmOrderResponse(
        errorCode: 0,
        orderStatus: 2,
        depositAmount: 50000
    );

    expect($response->getDepositAmountInDinars())->toBe(500.0);
});

test('getDepositAmountInDinars returns 0 when depositAmount is null', function () {
    $response = new ConfirmOrderResponse(
        errorCode: 0,
        orderStatus: 2
    );

    expect($response->getDepositAmountInDinars())->toBe(0.0);
});

test('getUdfFields returns UDF values from params', function () {
    $response = new ConfirmOrderResponse(
        errorCode: 0,
        orderStatus: 2,
        params: [
            'udf1' => 'Invoice-001',
            'udf2' => 'Customer-123',
            'udf3' => 'Subscription',
            'respCode' => '00',
        ]
    );

    $udfFields = $response->getUdfFields();

    expect($udfFields)->toBeArray()
        ->and($udfFields['udf1'])->toBe('Invoice-001')
        ->and($udfFields['udf2'])->toBe('Customer-123')
        ->and($udfFields['udf3'])->toBe('Subscription')
        ->and($udfFields['udf4'])->toBeNull()
        ->and($udfFields['udf5'])->toBeNull();
});

test('getUdfFields returns empty array when params is null', function () {
    $response = new ConfirmOrderResponse(
        errorCode: 0,
        orderStatus: 2
    );

    expect($response->getUdfFields())->toBe([]);
});

test('getResponseCode returns response code from params', function () {
    $response = new ConfirmOrderResponse(
        errorCode: 0,
        orderStatus: 2,
        params: [
            'respCode' => '00',
            'respCode_desc' => 'Success',
        ]
    );

    expect($response->getResponseCode())->toBe('00');
});

test('getResponseCode returns null when params is null', function () {
    $response = new ConfirmOrderResponse(
        errorCode: 0,
        orderStatus: 2
    );

    expect($response->getResponseCode())->toBeNull();
});

test('getResponseCodeDescription returns description from params', function () {
    $response = new ConfirmOrderResponse(
        errorCode: 0,
        orderStatus: 2,
        params: [
            'respCode' => '00',
            'respCode_desc' => 'Payment accepted',
        ]
    );

    expect($response->getResponseCodeDescription())->toBe('Payment accepted');
});

test('getResponseCodeDescription returns null when params is null', function () {
    $response = new ConfirmOrderResponse(
        errorCode: 0,
        orderStatus: 2
    );

    expect($response->getResponseCodeDescription())->toBeNull();
});

test('fromArray handles lowercase ErrorMessage field', function () {
    $data = [
        'ErrorCode' => 5,
        'errorMessage' => 'Access denied',
        'OrderStatus' => 0,
    ];

    $response = ConfirmOrderResponse::fromArray($data);

    expect($response->errorMessage)->toBe('Access denied');
});

test('fromArray handles uppercase ErrorMessage field', function () {
    $data = [
        'ErrorCode' => 5,
        'ErrorMessage' => 'Access denied uppercase',
        'OrderStatus' => 0,
    ];

    $response = ConfirmOrderResponse::fromArray($data);

    expect($response->errorMessage)->toBe('Access denied uppercase');
});

