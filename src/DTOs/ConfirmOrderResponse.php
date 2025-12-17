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
        public ?string $expiration = null,
        public ?string $cardholderName = null,
        public ?string $authorizationResponseId = null,
        public ?string $approvalCode = null,
        public ?string $ip = null,
        public ?string $clientId = null,
        public ?string $bindingId = null,
        public ?string $paymentAccountReference = null,
        public ?string $description = null,
        public ?array $params = null,
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

    public function getStatusName(): string
    {
        return match ($this->orderStatus) {
            0 => 'Order registered, but not paid',
            -1 => 'Transaction failed',
            1 => 'Transaction approved / Pre-authorized',
            2 => 'Amount deposited successfully',
            3 => 'Authorization reversed',
            4 => 'Transaction refunded',
            6 => 'Authorization declined',
            7 => 'Card added',
            8 => 'Card updated',
            9 => 'Card verified',
            10 => 'Recurring template added',
            11 => 'Debited',
            default => 'Unknown status',
        };
    }

    public function getAmountInDinars(): float
    {
        return $this->amount ? $this->amount / 100 : 0;
    }

    public function getDepositAmountInDinars(): float
    {
        return $this->depositAmount ? $this->depositAmount / 100 : 0;
    }

    public function getUdfFields(): array
    {
        if (! $this->params) {
            return [];
        }

        return [
            'udf1' => $this->params['udf1'] ?? null,
            'udf2' => $this->params['udf2'] ?? null,
            'udf3' => $this->params['udf3'] ?? null,
            'udf4' => $this->params['udf4'] ?? null,
            'udf5' => $this->params['udf5'] ?? null,
        ];
    }

    public function getResponseCode(): ?string
    {
        return $this->params['respCode'] ?? null;
    }

    public function getResponseCodeDescription(): ?string
    {
        return $this->params['respCode_desc'] ?? null;
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
            errorMessage: $data['errorMessage'] ?? $data['ErrorMessage'] ?? null,
            expiration: $data['expiration'] ?? null,
            cardholderName: $data['cardholderName'] ?? null,
            authorizationResponseId: $data['authorizationResponseId'] ?? null,
            approvalCode: $data['approvalCode'] ?? null,
            ip: $data['Ip'] ?? $data['ip'] ?? null,
            clientId: $data['clientId'] ?? null,
            bindingId: $data['bindingId'] ?? null,
            paymentAccountReference: $data['paymentAccountReference'] ?? null,
            description: $data['Description'] ?? $data['description'] ?? null,
            params: $data['params'] ?? null,
        );
    }
}
