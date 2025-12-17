<?php

use Oss\SatimLaravel\Concerns\GeneratesOrderNumbers;

beforeEach(function () {
    $this->trait = new class
    {
        use GeneratesOrderNumbers;

        public function testGenerateOrderNumber(): string
        {
            return $this->generateOrderNumber();
        }
    };
});

test('generates 10-digit order number', function () {
    $orderNumber = $this->trait->testGenerateOrderNumber();

    expect($orderNumber)
        ->toBeString()
        ->toHaveLength(10);
});

test('generated order number contains only digits', function () {
    $orderNumber = $this->trait->testGenerateOrderNumber();

    expect($orderNumber)->toMatch('/^\d{10}$/');
});

test('generates different order numbers', function () {
    $orderNumber1 = $this->trait->testGenerateOrderNumber();
    $orderNumber2 = $this->trait->testGenerateOrderNumber();

    expect($orderNumber1)->not->toBe($orderNumber2);
});

test('generates order number within valid range', function () {
    $orderNumber = (int) $this->trait->testGenerateOrderNumber();

    expect($orderNumber)
        ->toBeGreaterThanOrEqual(1000000000)
        ->toBeLessThanOrEqual(9999999999);
});

test('generates multiple unique order numbers', function () {
    $orderNumbers = [];

    for ($i = 0; $i < 100; $i++) {
        $orderNumbers[] = $this->trait->testGenerateOrderNumber();
    }

    $uniqueOrderNumbers = array_unique($orderNumbers);

    expect($uniqueOrderNumbers)->toHaveCount(100);
});
