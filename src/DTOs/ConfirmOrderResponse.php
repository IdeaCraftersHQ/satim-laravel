<?php

namespace Oss\SatimLaravel\DTOs;

readonly class ConfirmOrderResponse
{
    public function __construct(
        public int $errorCode,
        public int $orderStatus,
        public ?string $orderNumber = null,
        public ?string $pan = null,
        public ?int $amount = null,
        public ?int $depositAmount = null,
        public ?string $currency = null,
        public ?int $actionCode = null,
        public ?string $actionCodeDescription = null,
        public ?string $errorMessage = null,
    ) {
    }

    public function isSuccessful(): bool
    {
        return $this->errorCode === 0;
    }

    public function isPaid(): bool
    {
        return $this->orderStatus === 2;
    }

    public function isPreAuthorized(): bool
    {
        return $this->orderStatus === 1;
    }

    public function isDeclined(): bool
    {
        return $this->orderStatus === 6;
    }

    public function isRefunded(): bool
    {
        return $this->orderStatus === 4;
    }

    public function isReversed(): bool
    {
        return $this->orderStatus === 3;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            errorCode: $data['ErrorCode'] ?? $data['errorCode'] ?? 0,
            orderStatus: $data['OrderStatus'] ?? $data['orderStatus'] ?? 0,
            orderNumber: $data['OrderNumber'] ?? $data['orderNumber'] ?? null,
            pan: $data['Pan'] ?? $data['pan'] ?? null,
            amount: isset($data['Amount']) ? (int) $data['Amount'] : (isset($data['amount']) ? (int) $data['amount'] : null),
            depositAmount: isset($data['depositAmount']) ? (int) $data['depositAmount'] : null,
            currency: $data['currency'] ?? null,
            actionCode: isset($data['actionCode']) ? (int) $data['actionCode'] : null,
            actionCodeDescription: $data['actionCodeDescription'] ?? null,
        );
    }
}
