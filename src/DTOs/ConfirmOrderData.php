<?php

namespace Ideacrafters\SatimLaravel\DTOs;

use Ideacrafters\SatimLaravel\Exceptions\SatimValidationException;

readonly class ConfirmOrderData
{
    public function __construct(
        public string $mdOrder,
        public string $language,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        // mdOrder validation
        if (empty($this->mdOrder)) {
            throw new SatimValidationException('mdOrder is required');
        }

        // Language validation
        if (empty($this->language)) {
            throw new SatimValidationException('Language is required');
        }

        $validLanguages = ['FR', 'EN', 'AR'];
        if (!in_array(strtoupper($this->language), $validLanguages)) {
            throw new SatimValidationException('Language must be FR, EN, or AR');
        }
    }

    public function toArray(): array
    {
        return [
            'mdOrder' => $this->mdOrder,
            'language' => $this->language,
        ];
    }
}
