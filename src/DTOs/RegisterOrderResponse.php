<?php

namespace Ideacrafters\SatimLaravel\DTOs;

readonly class RegisterOrderResponse
{
    public function __construct(
        public int $errorCode,
        public ?string $orderId = null,
        public ?string $formUrl = null,
        public ?string $errorMessage = null,
    ) {
    }

    public function isSuccessful(): bool
    {
        return $this->errorCode === 0;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            errorCode: $data['errorCode'] ?? 0,
            orderId: $data['orderId'] ?? null,
            formUrl: $data['formUrl'] ?? null,
        );
    }
}
