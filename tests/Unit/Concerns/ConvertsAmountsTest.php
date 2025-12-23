<?php

use Ideacrafters\SatimLaravel\Concerns\ConvertsAmounts;

beforeEach(function () {
    $this->trait = new class
    {
        use ConvertsAmounts;

        public function testConvertToCents(float|int $amount): int
        {
            return $this->convertToCents($amount);
        }

        public function testConvertToDinars(int $cents): float
        {
            return $this->convertToDinars($cents);
        }
    };
});

test('converts dinars to cents by multiplying by 100', function () {
    expect($this->trait->testConvertToCents(50))->toBe(5000)
        ->and($this->trait->testConvertToCents(100))->toBe(10000)
        ->and($this->trait->testConvertToCents(1))->toBe(100);
});

test('converts float dinars to cents', function () {
    expect($this->trait->testConvertToCents(50.50))->toBe(5050)
        ->and($this->trait->testConvertToCents(99.99))->toBe(9999)
        ->and($this->trait->testConvertToCents(1.5))->toBe(150);
});

test('converts zero dinars to zero cents', function () {
    expect($this->trait->testConvertToCents(0))->toBe(0);
});

test('converts large amounts correctly', function () {
    expect($this->trait->testConvertToCents(10000))->toBe(1000000)
        ->and($this->trait->testConvertToCents(99999))->toBe(9999900);
});

test('converts cents to dinars by dividing by 100', function () {
    expect($this->trait->testConvertToDinars(5000))->toBe(50.0)
        ->and($this->trait->testConvertToDinars(10000))->toBe(100.0)
        ->and($this->trait->testConvertToDinars(100))->toBe(1.0);
});

test('converts cents to dinars with decimals', function () {
    expect($this->trait->testConvertToDinars(5050))->toBe(50.5)
        ->and($this->trait->testConvertToDinars(9999))->toBe(99.99)
        ->and($this->trait->testConvertToDinars(150))->toBe(1.5);
});

test('converts zero cents to zero dinars', function () {
    expect($this->trait->testConvertToDinars(0))->toBe(0.0);
});

test('round trip conversion maintains value', function () {
    $original = 100.50;
    $cents = $this->trait->testConvertToCents($original);
    $backToDinars = $this->trait->testConvertToDinars($cents);

    expect($backToDinars)->toBe($original);
});
