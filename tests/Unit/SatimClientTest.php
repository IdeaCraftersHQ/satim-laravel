<?php

use Illuminate\Support\Facades\Http;
use Ideacrafters\SatimLaravel\Client\SatimClient;
use Ideacrafters\SatimLaravel\DTOs\ConfirmOrderData;
use Ideacrafters\SatimLaravel\DTOs\RefundOrderData;
use Ideacrafters\SatimLaravel\DTOs\RegisterOrderData;
use Ideacrafters\SatimLaravel\Exceptions\SatimAuthenticationException;
use Ideacrafters\SatimLaravel\Exceptions\SatimException;
use Ideacrafters\SatimLaravel\Exceptions\SatimPaymentException;

beforeEach(function () {
    $this->client = new SatimClient(
        apiUrl: 'https://test.satim.dz/payment/rest',
        username: 'test_user',
        password: 'test_pass',
        verifySSL: true,
        timeout: 30,
        connectTimeout: 10
    );
});

test('register sends correct request and returns RegisterOrderResponse', function () {
    Http::fake([
        'test.satim.dz/*' => Http::response([
            'errorCode' => 0,
            'orderId' => 'V721uPPfNNofVQAAABL3',
            'formUrl' => 'https://test.satim.dz/payment/epg/form',
        ], 200),
    ]);

    $data = new RegisterOrderData(
        orderNumber: '1234567890',
        amount: 10000,
        currency: '012',
        returnUrl: 'https://example.com/success',
        language: 'fr',
        terminalId: 'TEST123',
        udf1: 'test123'
    );

    $response = $this->client->register($data);

    expect($response->isSuccessful())->toBeTrue()
        ->and($response->orderId)->toBe('V721uPPfNNofVQAAABL3')
        ->and($response->formUrl)->toBe('https://test.satim.dz/payment/epg/form');

    Http::assertSent(function ($request) {
        return str_contains($request->url(), '/register.do') &&
            str_contains($request->url(), 'userName=test_user') &&
            str_contains($request->url(), 'password=test_pass') &&
            str_contains($request->url(), 'orderNumber=1234567890') &&
            str_contains($request->url(), 'amount=10000');
    });
});

test('confirm sends correct request and returns ConfirmOrderResponse', function () {
    Http::fake([
        'test.satim.dz/*' => Http::response([
            'ErrorCode' => 0,
            'OrderStatus' => 2,
            'OrderNumber' => 'CMD0000004',
            'Pan' => '6280****7215',
            'Amount' => 100320,
            'depositAmount' => 100320,
            'currency' => '012',
            'actionCode' => 0,
            'actionCodeDescription' => 'Payment accepted',
        ], 200),
    ]);

    $data = new ConfirmOrderData(
        mdOrder: 'V721uPPfNNofVQAAABL3',
        language: 'fr'
    );

    $response = $this->client->confirm($data);

    expect($response->isSuccessful())->toBeTrue()
        ->and($response->isPaid())->toBeTrue()
        ->and($response->orderNumber)->toBe('CMD0000004')
        ->and($response->amount)->toBe(100320);

    Http::assertSent(function ($request) {
        return str_contains($request->url(), '/public/acknowledgeTransaction.do') &&
            $request['userName'] === 'test_user' &&
            $request['password'] === 'test_pass' &&
            $request['mdOrder'] === 'V721uPPfNNofVQAAABL3';
    });
});

test('refund sends correct request and returns RefundOrderResponse', function () {
    Http::fake([
        'test.satim.dz/*' => Http::response([
            'errorCode' => 0,
            'errorMessage' => null,
        ], 200),
    ]);

    $data = new RefundOrderData(
        orderId: 'abc123',
        amount: 10000
    );

    $response = $this->client->refund($data);

    expect($response->isSuccessful())->toBeTrue()
        ->and($response->errorMessage)->toBeNull();

    Http::assertSent(function ($request) {
        return str_contains($request->url(), '/refund.do') &&
            $request['userName'] === 'test_user' &&
            $request['password'] === 'test_pass' &&
            $request['orderId'] === 'abc123' &&
            $request['amount'] === 10000;
    });
});

test('throws SatimAuthenticationException when error code is 5', function () {
    Http::fake([
        'test.satim.dz/*' => Http::response([
            'errorCode' => 5,
            'errorMessage' => 'Access denied',
        ], 200),
    ]);

    $data = new RegisterOrderData(
        orderNumber: '123',
        amount: 10000,
        currency: '012',
        returnUrl: 'https://example.com',
        language: 'fr',
        terminalId: 'TEST',
        udf1: 'test123'
    );

    $this->client->register($data);
})->throws(SatimAuthenticationException::class, 'Access denied');

test('throws SatimPaymentException when error code is 1', function () {
    Http::fake([
        'test.satim.dz/*' => Http::response([
            'errorCode' => 1,
            'errorMessage' => 'Order already processed',
        ], 200),
    ]);

    $data = new RegisterOrderData(
        orderNumber: '123',
        amount: 10000,
        currency: '012',
        returnUrl: 'https://example.com',
        language: 'fr',
        terminalId: 'TEST',
        udf1: 'test123'
    );

    $this->client->register($data);
})->throws(SatimPaymentException::class, 'Order already processed');

test('throws SatimPaymentException when error code is 3', function () {
    Http::fake([
        'test.satim.dz/*' => Http::response([
            'errorCode' => 3,
            'errorMessage' => 'Unknown currency',
        ], 200),
    ]);

    $data = new RegisterOrderData(
        orderNumber: '123',
        amount: 10000,
        currency: '999',
        returnUrl: 'https://example.com',
        language: 'fr',
        terminalId: 'TEST',
        udf1: 'test123'
    );

    $this->client->register($data);
})->throws(SatimPaymentException::class, 'Unknown currency');

test('throws SatimPaymentException when error code is 4', function () {
    Http::fake([
        'test.satim.dz/*' => Http::response([
            'errorCode' => 4,
            'errorMessage' => 'Missing parameter',
        ], 200),
    ]);

    $data = new RegisterOrderData(
        orderNumber: '123',
        amount: 10000,
        currency: '012',
        returnUrl: 'https://example.com',
        language: 'fr',
        terminalId: 'TEST',
        udf1: 'test123'
    );

    $this->client->register($data);
})->throws(SatimPaymentException::class, 'Missing parameter');

test('throws SatimPaymentException when error code is 14', function () {
    Http::fake([
        'test.satim.dz/*' => Http::response([
            'errorCode' => 14,
            'errorMessage' => 'Invalid payment way',
        ], 200),
    ]);

    $data = new RegisterOrderData(
        orderNumber: '123',
        amount: 10000,
        currency: '012',
        returnUrl: 'https://example.com',
        language: 'fr',
        terminalId: 'TEST',
        udf1: 'test123'
    );

    $this->client->register($data);
})->throws(SatimPaymentException::class, 'Invalid payment way');

test('throws SatimException for other error codes', function () {
    Http::fake([
        'test.satim.dz/*' => Http::response([
            'errorCode' => 7,
            'errorMessage' => 'System error',
        ], 200),
    ]);

    $data = new RegisterOrderData(
        orderNumber: '123',
        amount: 10000,
        currency: '012',
        returnUrl: 'https://example.com',
        language: 'fr',
        terminalId: 'TEST',
        udf1: 'test123'
    );

    $this->client->register($data);
})->throws(SatimException::class, 'System error');

test('handles capitalized ErrorCode in error responses', function () {
    Http::fake([
        'test.satim.dz/*' => Http::response([
            'ErrorCode' => 5,
            'errorMessage' => 'Access denied',
        ], 200),
    ]);

    $data = new ConfirmOrderData(
        mdOrder: 'V721uPPfNNofVQAAABL3',
        language: 'fr'
    );

    $this->client->confirm($data);
})->throws(SatimAuthenticationException::class);

test('exception includes response context', function () {
    Http::fake([
        'test.satim.dz/*' => Http::response([
            'errorCode' => 5,
            'errorMessage' => 'Access denied',
            'additionalInfo' => 'Extra data',
        ], 200),
    ]);

    $data = new RegisterOrderData(
        orderNumber: '123',
        amount: 10000,
        currency: '012',
        returnUrl: 'https://example.com',
        language: 'fr',
        terminalId: 'TEST',
        udf1: 'test123'
    );

    try {
        $this->client->register($data);
    } catch (SatimAuthenticationException $e) {
        expect($e->getErrorCode())->toBe(5)
            ->and($e->getContext())->toHaveKey('additionalInfo', 'Extra data');
    }
});

test('throws SatimPaymentException when confirm error code is 2', function () {
    Http::fake([
        'test.satim.dz/*' => Http::response([
            'ErrorCode' => 2,
            'ErrorMessage' => 'Order declined due to payment credentials error',
        ], 200),
    ]);

    $data = new ConfirmOrderData(
        mdOrder: 'V721uPPfNNofVQAAABL3',
        language: 'fr'
    );

    $this->client->confirm($data);
})->throws(SatimPaymentException::class, 'Order declined due to payment credentials error');

test('throws SatimPaymentException when confirm error code is 6', function () {
    Http::fake([
        'test.satim.dz/*' => Http::response([
            'ErrorCode' => 6,
            'ErrorMessage' => 'Unregistered orderId',
        ], 200),
    ]);

    $data = new ConfirmOrderData(
        mdOrder: 'INVALID_ORDER',
        language: 'fr'
    );

    $this->client->confirm($data);
})->throws(SatimPaymentException::class, 'Unregistered orderId');

test('throws SatimPaymentException when refund error code is 6', function () {
    Http::fake([
        'test.satim.dz/*' => Http::response([
            'errorCode' => 6,
            'errorMessage' => 'Unregistered OrderId',
        ], 200),
    ]);

    $data = new RefundOrderData(
        orderId: 'INVALID_ORDER',
        amount: 10000
    );

    $this->client->refund($data);
})->throws(SatimPaymentException::class, 'Unregistered OrderId');

test('throws SatimAuthenticationException when refund amount is invalid', function () {
    Http::fake([
        'test.satim.dz/*' => Http::response([
            'errorCode' => 5,
            'errorMessage' => 'Invalid amount',
        ], 200),
    ]);

    $data = new RefundOrderData(
        orderId: 'ORDER123',
        amount: 10000
    );

    $this->client->refund($data);
})->throws(SatimAuthenticationException::class, 'Invalid amount');
