<?php

namespace Ideacrafters\SatimLaravel\Contracts;

use Ideacrafters\SatimLaravel\DTOs\ConfirmOrderResponse;
use Ideacrafters\SatimLaravel\DTOs\RefundOrderResponse;
use Ideacrafters\SatimLaravel\DTOs\RegisterOrderResponse;

interface SatimInterface
{
    /**
     * Set the payment amount in Algerian Dinars (will be auto-converted to cents).
     */
    public function amount(float|int $amount): self;

    /**
     * Set the order number (max 10 characters).
     */
    public function orderNumber(string $orderNumber): self;

    /**
     * Set the success return URL.
     */
    public function returnUrl(string $url): self;

    /**
     * Set the failure return URL.
     */
    public function failUrl(string $url): self;

    /**
     * Set the payment description.
     */
    public function description(string $description): self;

    /**
     * Set the language (FR, EN, or AR).
     */
    public function language(string $language): self;

    /**
     * Set user-defined field 1 (max 20 characters).
     */
    public function udf1(string $value): self;

    /**
     * Set user-defined field 2 (max 20 characters).
     */
    public function udf2(string $value): self;

    /**
     * Set user-defined field 3 (max 20 characters).
     */
    public function udf3(string $value): self;

    /**
     * Set user-defined field 4 (max 20 characters).
     */
    public function udf4(string $value): self;

    /**
     * Set user-defined field 5 (max 20 characters).
     */
    public function udf5(string $value): self;

    /**
     * Register a new payment and get the payment form URL.
     */
    public function register(): RegisterOrderResponse;

    /**
     * Confirm the payment status.
     */
    public function confirm(string $mdOrder, ?string $language = null): ConfirmOrderResponse;

    /**
     * Refund a payment.
     */
    public function refund(string $orderId, float|int $amount): RefundOrderResponse;
}
