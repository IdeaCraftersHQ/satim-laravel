<?php

namespace Ideacrafters\SatimLaravel\Concerns;

trait ConvertsAmounts
{
    /**
     * Convert Algerian Dinars to cents (multiply by 100).
     */
    protected function convertToCents(float|int $amount): int
    {
        return (int) ($amount * 100);
    }

    /**
     * Convert cents to Algerian Dinars (divide by 100).
     */
    protected function convertToDinars(int $cents): float
    {
        return $cents / 100;
    }
}
