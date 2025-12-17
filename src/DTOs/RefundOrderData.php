<?php

namespace Oss\SatimLaravel\DTOs;

use Oss\SatimLaravel\Exceptions\SatimValidationException;

readonly class RefundOrderData
{
    public function __construct(
        public string $orderId,
        public int $amount,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        // Order ID validation
        if (empty($this->orderId)) {
            throw new SatimValidationException('Order ID is required');
        }

        // Amount validation
        if ($this->amount < 5000) {
            throw new SatimValidationException('Amount must be at least 5000 cents (50 DA)');
        }

        if ($this->amount % 100 !== 0) {
            throw new SatimValidationException('Amount must be a multiple of 100 cents');
        }
    }

    public function toArray(): array
    {
        return [
            'orderId' => $this->orderId,
            'amount' => $this->amount,
        ];
    }
}
