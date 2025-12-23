<?php

use Ideacrafters\SatimLaravel\Exceptions\SatimAuthenticationException;
use Ideacrafters\SatimLaravel\Exceptions\SatimException;
use Ideacrafters\SatimLaravel\Exceptions\SatimPaymentException;
use Ideacrafters\SatimLaravel\Exceptions\SatimValidationException;

test('base SatimException can be instantiated with message only', function () {
    $exception = new SatimException('Test error message');

    expect($exception)
        ->toBeInstanceOf(SatimException::class)
        ->and($exception->getMessage())->toBe('Test error message')
        ->and($exception->getErrorCode())->toBe(0)
        ->and($exception->getContext())->toBeNull();
});

test('SatimException stores error code', function () {
    $exception = new SatimException('Error message', 5);

    expect($exception->getErrorCode())->toBe(5);
});

test('SatimException stores context data', function () {
    $context = ['request' => 'data', 'response' => 'data'];
    $exception = new SatimException('Error message', 0, $context);

    expect($exception->getContext())->toBe($context);
});

test('SatimException stores previous exception', function () {
    $previous = new Exception('Previous error');
    $exception = new SatimException('Current error', 0, null, $previous);

    expect($exception->getPrevious())->toBe($previous);
});

test('SatimException can be instantiated with all parameters', function () {
    $context = ['key' => 'value'];
    $previous = new Exception('Previous');
    $exception = new SatimException('Message', 7, $context, $previous);

    expect($exception->getMessage())->toBe('Message')
        ->and($exception->getErrorCode())->toBe(7)
        ->and($exception->getContext())->toBe($context)
        ->and($exception->getPrevious())->toBe($previous);
});

test('SatimValidationException extends SatimException', function () {
    $exception = new SatimValidationException('Validation failed');

    expect($exception)
        ->toBeInstanceOf(SatimValidationException::class)
        ->toBeInstanceOf(SatimException::class)
        ->and($exception->getMessage())->toBe('Validation failed');
});

test('SatimValidationException can store validation context', function () {
    $context = ['field' => 'amount', 'rule' => 'minimum', 'value' => 1000];
    $exception = new SatimValidationException('Amount too small', 0, $context);

    expect($exception->getContext())->toBe($context);
});

test('SatimAuthenticationException extends SatimException', function () {
    $exception = new SatimAuthenticationException('Authentication failed');

    expect($exception)
        ->toBeInstanceOf(SatimAuthenticationException::class)
        ->toBeInstanceOf(SatimException::class)
        ->and($exception->getMessage())->toBe('Authentication failed');
});

test('SatimAuthenticationException typically uses error code 5', function () {
    $exception = new SatimAuthenticationException('Invalid credentials', 5);

    expect($exception->getErrorCode())->toBe(5);
});

test('SatimPaymentException extends SatimException', function () {
    $exception = new SatimPaymentException('Payment failed');

    expect($exception)
        ->toBeInstanceOf(SatimPaymentException::class)
        ->toBeInstanceOf(SatimException::class)
        ->and($exception->getMessage())->toBe('Payment failed');
});

test('SatimPaymentException can store different error codes', function () {
    $exception1 = new SatimPaymentException('Order already processed', 1);
    $exception3 = new SatimPaymentException('Unknown currency', 3);
    $exception4 = new SatimPaymentException('Missing parameter', 4);
    $exception14 = new SatimPaymentException('Invalid payment way', 14);

    expect($exception1->getErrorCode())->toBe(1)
        ->and($exception3->getErrorCode())->toBe(3)
        ->and($exception4->getErrorCode())->toBe(4)
        ->and($exception14->getErrorCode())->toBe(14);
});

test('all exception types can be caught as SatimException', function () {
    $exceptions = [
        new SatimValidationException('Validation'),
        new SatimAuthenticationException('Auth'),
        new SatimPaymentException('Payment'),
    ];

    foreach ($exceptions as $exception) {
        expect($exception)->toBeInstanceOf(SatimException::class);
    }
});

test('exception hierarchy allows specific catch blocks', function () {
    try {
        throw new SatimValidationException('Test');
    } catch (SatimValidationException $e) {
        expect($e)->toBeInstanceOf(SatimValidationException::class);
    }

    try {
        throw new SatimAuthenticationException('Test');
    } catch (SatimAuthenticationException $e) {
        expect($e)->toBeInstanceOf(SatimAuthenticationException::class);
    }

    try {
        throw new SatimPaymentException('Test');
    } catch (SatimPaymentException $e) {
        expect($e)->toBeInstanceOf(SatimPaymentException::class);
    }
});
