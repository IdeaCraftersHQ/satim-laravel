<?php

namespace Oss\SatimLaravel\DTOs;

use Oss\SatimLaravel\Exceptions\SatimValidationException;

readonly class RegisterOrderData
{
    public function __construct(
        public string $orderNumber,
        public int $amount,
        public string $currency,
        public string $returnUrl,
        public string $language,
        public string $terminalId,
        public ?string $failUrl = null,
        public ?string $description = null,
        public ?string $udf1 = null,
        public ?string $udf2 = null,
        public ?string $udf3 = null,
        public ?string $udf4 = null,
        public ?string $udf5 = null,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        // Order Number validation
        if (empty($this->orderNumber)) {
            throw new SatimValidationException('Order number is required');
        }

        if (strlen($this->orderNumber) > 10) {
            throw new SatimValidationException('Order number must not exceed 10 characters');
        }

        // Amount validation
        if ($this->amount < 5000) {
            throw new SatimValidationException('Amount must be at least 5000 cents (50 DA)');
        }

        if ($this->amount % 100 !== 0) {
            throw new SatimValidationException('Amount must be a multiple of 100 cents');
        }

        // Currency validation
        if (empty($this->currency)) {
            throw new SatimValidationException('Currency is required');
        }

        // Return URL validation
        if (empty($this->returnUrl)) {
            throw new SatimValidationException('Return URL is required');
        }

        if (!filter_var($this->returnUrl, FILTER_VALIDATE_URL)) {
            throw new SatimValidationException('Return URL must be a valid URL');
        }

        // Fail URL validation (optional)
        if ($this->failUrl !== null && !filter_var($this->failUrl, FILTER_VALIDATE_URL)) {
            throw new SatimValidationException('Fail URL must be a valid URL');
        }

        // Description validation (optional)
        if ($this->description !== null && strlen($this->description) > 512) {
            throw new SatimValidationException('Description must not exceed 512 characters');
        }

        // Language validation
        if (empty($this->language)) {
            throw new SatimValidationException('Language is required');
        }

        $validLanguages = ['FR', 'EN', 'AR'];
        if (!in_array(strtoupper($this->language), $validLanguages)) {
            throw new SatimValidationException('Language must be FR, EN, or AR');
        }

        // Terminal ID validation
        if (empty($this->terminalId)) {
            throw new SatimValidationException('Terminal ID is required');
        }

        // UDF fields validation (optional, max 20 chars each)
        foreach (['udf1', 'udf2', 'udf3', 'udf4', 'udf5'] as $field) {
            if ($this->$field !== null && strlen($this->$field) > 20) {
                throw new SatimValidationException(ucfirst($field).' must not exceed 20 characters');
            }
        }
    }

    public function toArray(): array
    {
        $data = [
            'orderNumber' => $this->orderNumber,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'returnUrl' => $this->returnUrl,
            'language' => $this->language,
            'jsonParams' => json_encode(array_filter([
                'force_terminal_id' => $this->terminalId,
                'udf1' => $this->udf1,
                'udf2' => $this->udf2,
                'udf3' => $this->udf3,
                'udf4' => $this->udf4,
                'udf5' => $this->udf5,
            ])),
        ];

        if ($this->failUrl !== null) {
            $data['failUrl'] = $this->failUrl;
        }

        if ($this->description !== null) {
            $data['description'] = $this->description;
        }

        return $data;
    }
}
