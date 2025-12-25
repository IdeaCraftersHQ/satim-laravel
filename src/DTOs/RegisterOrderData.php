<?php

namespace Ideacrafters\SatimLaravel\DTOs;

use Ideacrafters\SatimLaravel\Exceptions\SatimValidationException;

readonly class RegisterOrderData
{
    private const MAX_AMOUNT = 99999999999999999999;

    public string $orderNumber;
    public int $amount;
    public string $currency;
    public string $returnUrl;
    public string $language;
    public string $terminalId;
    public ?string $udf1;
    public ?string $failUrl;
    public ?string $description;
    public ?string $udf2;
    public ?string $udf3;
    public ?string $udf4;
    public ?string $udf5;

    public function __construct(
        string $orderNumber,
        int $amount,
        string $currency,
        string $returnUrl,
        string $language,
        string $terminalId,
        ?string $failUrl = null,
        ?string $description = null,
        ?string $udf1 = null,
        ?string $udf2 = null,
        ?string $udf3 = null,
        ?string $udf4 = null,
        ?string $udf5 = null,
    ) {
        
        $this->orderNumber = trim($orderNumber);
        $this->amount = $amount;
        $this->currency = trim($currency);
        $this->returnUrl = trim($returnUrl);
        $this->language = strtoupper(trim($language));
        $this->terminalId = trim($terminalId);

        // Trim optional fields only if not null
        $this->failUrl = $failUrl !== null ? trim($failUrl) : null;
        $this->description = $description !== null ? trim($description) : null;
        $this->udf1 = $udf1 !== null ? trim($udf1) : null;
        $this->udf2 = $udf2 !== null ? trim($udf2) : null;
        $this->udf3 = $udf3 !== null ? trim($udf3) : null;
        $this->udf4 = $udf4 !== null ? trim($udf4) : null;
        $this->udf5 = $udf5 !== null ? trim($udf5) : null;

        $this->validate();
    }

    private function validate(): void
    {
        // Order Number validation
        if (!preg_match('/^[A-Za-z0-9]{1,10}$/', $this->orderNumber)) {
            throw new SatimValidationException(
                'Order number must be alphanumeric (A-Z, a-z, 0-9) and 1-10 characters'
            );
        }

        // 2. Amount validation (N..20, min 5000 cents, max 20 digits, multiple of 100)
        if ($this->amount < 5000) {
            throw new SatimValidationException('Amount must be at least 5000 cents (50 DA)');
        }

        if ($this->amount > self::MAX_AMOUNT) {
            throw new SatimValidationException(
                'Amount exceeds maximum allowed value (20 digits = 999,999,999,999,999,999.99 DA)'
            );
        }

        if ($this->amount % 100 !== 0) {
            throw new SatimValidationException('Amount must be a multiple of 100 cents');
        }

        // Currency validation
        if (!preg_match('/^\d{3}$/', $this->currency)) {
            throw new SatimValidationException(
                'Currency must be a 3-digit ISO 4217 code (e.g., "012" for DZD)'
            );
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
        if ($this->description !== null && mb_strlen($this->description, 'UTF-8') > 512) {
            throw new SatimValidationException('Description must not exceed 512 characters');
        }

        $validLanguages = ['FR', 'EN', 'AR'];
        if (!in_array($this->language, $validLanguages)) {
            throw new SatimValidationException('Language must be FR, EN, or AR');
        }

        if (!preg_match('/^[A-Za-z0-9]{1,16}$/', $this->terminalId)) {
            throw new SatimValidationException(
                'Terminal ID must be alphanumeric and 1-16 characters'
            );
        }

        // UDF1-5 validation (all optional, but must be alphanumeric if provided)
        foreach (['udf1', 'udf2', 'udf3', 'udf4', 'udf5'] as $field) {
            if ($this->$field !== null) {
                if (!preg_match('/^[A-Za-z0-9]{1,20}$/', $this->$field)) {
                    throw new SatimValidationException(
                        ucfirst($field).' must be alphanumeric and 1-20 characters'
                    );
                }
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
