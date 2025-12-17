<?php

namespace Oss\SatimLaravel;

use Oss\SatimLaravel\Client\SatimClient;
use Oss\SatimLaravel\Concerns\ConvertsAmounts;
use Oss\SatimLaravel\Concerns\GeneratesOrderNumbers;
use Oss\SatimLaravel\Contracts\SatimInterface;
use Oss\SatimLaravel\DTOs\ConfirmOrderData;
use Oss\SatimLaravel\DTOs\ConfirmOrderResponse;
use Oss\SatimLaravel\DTOs\RefundOrderData;
use Oss\SatimLaravel\DTOs\RefundOrderResponse;
use Oss\SatimLaravel\DTOs\RegisterOrderData;
use Oss\SatimLaravel\DTOs\RegisterOrderResponse;

class Satim implements SatimInterface
{
    use ConvertsAmounts, GeneratesOrderNumbers;

    private ?int $amount = null;
    private ?string $orderNumber = null;
    private ?string $returnUrl = null;
    private ?string $failUrl = null;
    private ?string $description = null;
    private ?string $language = null;
    private ?string $udf1 = null;
    private ?string $udf2 = null;
    private ?string $udf3 = null;
    private ?string $udf4 = null;
    private ?string $udf5 = null;

    public function __construct(
        private readonly SatimClient $client,
        private readonly string $defaultLanguage,
        private readonly string $currency,
        private readonly string $terminalId,
    ) {
    }

    public function amount(float|int $amount): self
    {
        $this->amount = $this->convertToCents($amount);

        return $this;
    }

    public function orderNumber(string $orderNumber): self
    {
        $this->orderNumber = $orderNumber;

        return $this;
    }

    public function returnUrl(string $url): self
    {
        $this->returnUrl = $url;

        return $this;
    }

    public function failUrl(string $url): self
    {
        $this->failUrl = $url;

        return $this;
    }

    public function description(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function language(string $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function udf1(string $value): self
    {
        $this->udf1 = $value;

        return $this;
    }

    public function udf2(string $value): self
    {
        $this->udf2 = $value;

        return $this;
    }

    public function udf3(string $value): self
    {
        $this->udf3 = $value;

        return $this;
    }

    public function udf4(string $value): self
    {
        $this->udf4 = $value;

        return $this;
    }

    public function udf5(string $value): self
    {
        $this->udf5 = $value;

        return $this;
    }

    public function register(): RegisterOrderResponse
    {
        $data = new RegisterOrderData(
            orderNumber: $this->orderNumber ?? $this->generateOrderNumber(),
            amount: $this->amount,
            currency: $this->currency,
            returnUrl: $this->returnUrl,
            language: $this->language ?? $this->defaultLanguage,
            terminalId: $this->terminalId,
            failUrl: $this->failUrl,
            description: $this->description,
            udf1: $this->udf1,
            udf2: $this->udf2,
            udf3: $this->udf3,
            udf4: $this->udf4,
            udf5: $this->udf5,
        );

        $response = $this->client->register($data);

        $this->reset();

        return $response;
    }

    public function confirm(string $mdOrder, ?string $language = null): ConfirmOrderResponse
    {
        $data = new ConfirmOrderData(
            mdOrder: $mdOrder,
            language: $language ?? $this->defaultLanguage,
        );

        return $this->client->confirm($data);
    }

    public function refund(string $orderId, float|int $amount): RefundOrderResponse
    {
        $data = new RefundOrderData(
            orderId: $orderId,
            amount: $this->convertToCents($amount),
        );

        return $this->client->refund($data);
    }

    private function reset(): void
    {
        $this->amount = null;
        $this->orderNumber = null;
        $this->returnUrl = null;
        $this->failUrl = null;
        $this->description = null;
        $this->language = null;
        $this->udf1 = null;
        $this->udf2 = null;
        $this->udf3 = null;
        $this->udf4 = null;
        $this->udf5 = null;
    }
}
