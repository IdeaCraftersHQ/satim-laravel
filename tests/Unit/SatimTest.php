<?php

use Illuminate\Support\Facades\Http;
use Oss\SatimLaravel\Client\SatimClient;
use Oss\SatimLaravel\Contracts\SatimInterface;
use Oss\SatimLaravel\DTOs\ConfirmOrderResponse;
use Oss\SatimLaravel\DTOs\RefundOrderResponse;
use Oss\SatimLaravel\DTOs\RegisterOrderResponse;
use Oss\SatimLaravel\Satim;

beforeEach(function () {
    $this->client = new SatimClient(
        apiUrl: 'https://test.satim.dz/payment/rest',
        username: 'test_user',
        password: 'test_pass',
        verifySSL: true,
        timeout: 30,
        connectTimeout: 10
    );

    $this->satim = new Satim(
        client: $this->client,
        defaultLanguage: 'fr',
        currency: '012',
        terminalId: 'TEST123'
    );
});

test('Satim implements SatimInterface', function () {
    expect($this->satim)->toBeInstanceOf(SatimInterface::class);
});

test('amount method is chainable and converts to cents', function () {
    Http::fake([
        '*' => Http::response([
            'errorCode' => 0,
            'orderId' => 'test',
            'formUrl' => 'https://example.com',
        ]),
    ]);

    $result = $this->satim->amount(50);

    expect($result)->toBeInstanceOf(Satim::class);

    $response = $this->satim->returnUrl('https://example.com')->register();

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'amount=5000'); // 50 DA = 5000 cents
    });
});

test('orderNumber method is chainable', function () {
    $result = $this->satim->orderNumber('1234567890');

    expect($result)->toBeInstanceOf(Satim::class);
});

test('returnUrl method is chainable', function () {
    $result = $this->satim->returnUrl('https://example.com');

    expect($result)->toBeInstanceOf(Satim::class);
});

test('failUrl method is chainable', function () {
    $result = $this->satim->failUrl('https://example.com/fail');

    expect($result)->toBeInstanceOf(Satim::class);
});

test('description method is chainable', function () {
    $result = $this->satim->description('Test payment');

    expect($result)->toBeInstanceOf(Satim::class);
});

test('language method is chainable', function () {
    $result = $this->satim->language('en');

    expect($result)->toBeInstanceOf(Satim::class);
});

test('udf methods are chainable', function () {
    expect($this->satim->udf1('value1'))->toBeInstanceOf(Satim::class)
        ->and($this->satim->udf2('value2'))->toBeInstanceOf(Satim::class)
        ->and($this->satim->udf3('value3'))->toBeInstanceOf(Satim::class)
        ->and($this->satim->udf4('value4'))->toBeInstanceOf(Satim::class)
        ->and($this->satim->udf5('value5'))->toBeInstanceOf(Satim::class);
});

test('register uses fluent API and returns RegisterOrderResponse', function () {
    Http::fake([
        '*' => Http::response([
            'errorCode' => 0,
            'orderId' => 'V721uPPfNNofVQAAABL3',
            'formUrl' => 'https://test.satim.dz/payment/epg/form',
        ]),
    ]);

    $response = $this->satim
        ->amount(100)
        ->orderNumber('CMD123')
        ->returnUrl('https://example.com/success')
        ->failUrl('https://example.com/fail')
        ->description('Test payment')
        ->language('en')
        ->udf1('invoice123')
        ->register();

    expect($response)->toBeInstanceOf(RegisterOrderResponse::class)
        ->and($response->isSuccessful())->toBeTrue()
        ->and($response->orderId)->toBe('V721uPPfNNofVQAAABL3');

    Http::assertSent(function ($request) {
        $url = $request->url();
        return str_contains($url, 'amount=10000') &&
            str_contains($url, 'orderNumber=CMD123') &&
            str_contains($url, 'returnUrl=') &&
            str_contains($url, 'failUrl=') &&
            str_contains($url, 'description=') &&
            str_contains($url, 'language=en');
    });
});

test('register auto-generates order number if not provided', function () {
    Http::fake([
        '*' => Http::response([
            'errorCode' => 0,
            'orderId' => 'test',
            'formUrl' => 'https://example.com',
        ]),
    ]);

    $response = $this->satim
        ->amount(50)
        ->returnUrl('https://example.com')
        ->register();

    Http::assertSent(function ($request) {
        $url = $request->url();
        preg_match('/orderNumber=(\d+)/', $url, $matches);
        return isset($matches[1]) && strlen($matches[1]) === 10;
    });
});

test('register uses default language if not provided', function () {
    Http::fake([
        '*' => Http::response([
            'errorCode' => 0,
            'orderId' => 'test',
            'formUrl' => 'https://example.com',
        ]),
    ]);

    $response = $this->satim
        ->amount(50)
        ->returnUrl('https://example.com')
        ->register();

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'language=fr'); // default language
    });
});

test('register resets state after call', function () {
    Http::fake([
        '*' => Http::response([
            'errorCode' => 0,
            'orderId' => 'test',
            'formUrl' => 'https://example.com',
        ]),
    ]);

    // First call with specific values
    $this->satim
        ->amount(100)
        ->orderNumber('ORDER1')
        ->returnUrl('https://example.com/1')
        ->description('First payment')
        ->register();

    // Second call should not have values from first call
    $this->satim
        ->amount(200)
        ->returnUrl('https://example.com/2')
        ->register();

    Http::assertSent(function ($request) {
        $url = $request->url();
        return str_contains($url, 'amount=20000') &&
            str_contains($url, 'returnUrl=') &&
            !str_contains($url, 'description=') && // Should be reset
            !str_contains($url, 'orderNumber=ORDER1'); // Should be different
    });
});

test('confirm returns ConfirmOrderResponse', function () {
    Http::fake([
        '*' => Http::response([
            'ErrorCode' => 0,
            'OrderStatus' => 2,
            'OrderNumber' => 'CMD123',
            'Amount' => 10000,
        ]),
    ]);

    $response = $this->satim->confirm('mdOrder123');

    expect($response)->toBeInstanceOf(ConfirmOrderResponse::class)
        ->and($response->isSuccessful())->toBeTrue()
        ->and($response->isPaid())->toBeTrue();

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'mdOrder=mdOrder123') &&
            str_contains($request->url(), 'language=fr'); // default language
    });
});

test('confirm accepts custom language', function () {
    Http::fake([
        '*' => Http::response([
            'ErrorCode' => 0,
            'OrderStatus' => 2,
        ]),
    ]);

    $response = $this->satim->confirm('mdOrder123', 'en');

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'language=en');
    });
});

test('refund returns RefundOrderResponse and converts amount', function () {
    Http::fake([
        '*' => Http::response([
            'errorCode' => 0,
            'errorMessage' => null,
        ]),
    ]);

    $response = $this->satim->refund('orderId123', 100); // 100 DA

    expect($response)->toBeInstanceOf(RefundOrderResponse::class)
        ->and($response->isSuccessful())->toBeTrue();

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'orderId=orderId123') &&
            str_contains($request->url(), 'amount=10000'); // 100 DA = 10000 cents
    });
});

test('refund accepts integer amount', function () {
    Http::fake([
        '*' => Http::response([
            'errorCode' => 0,
        ]),
    ]);

    $this->satim->refund('orderId123', 50);

    Http::assertSent(function ($request) {
        return $request['amount'] === 5000; // 50 DA = 5000 cents
    });
});

test('refund accepts float amount', function () {
    Http::fake([
        '*' => Http::response([
            'errorCode' => 0,
        ]),
    ]);

    $this->satim->refund('orderId123', 51.00);

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'amount=5100'); // 51.00 DA = 5100 cents
    });
});

test('fluent API allows method chaining in any order', function () {
    Http::fake([
        '*' => Http::response([
            'errorCode' => 0,
            'orderId' => 'test',
            'formUrl' => 'https://example.com',
        ]),
    ]);

    $response = $this->satim
        ->udf1('value1')
        ->language('ar')
        ->description('Payment')
        ->amount(75)
        ->udf2('value2')
        ->returnUrl('https://example.com')
        ->orderNumber('CMD999')
        ->failUrl('https://example.com/fail')
        ->register();

    expect($response->isSuccessful())->toBeTrue();

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'amount=7500') &&
            str_contains($request->url(), 'language=ar') &&
            str_contains($request->url(), 'orderNumber=CMD999');
    });
});
