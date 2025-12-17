<?php

namespace Oss\SatimLaravel\DTOs;

readonly class RefundOrderResponse
{
    public function __construct(
        public int $errorCode,
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
            errorMessage: $data['errorMessage'] ?? null,
        );
    }
}
