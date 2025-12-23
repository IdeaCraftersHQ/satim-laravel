<?php

namespace Ideacrafters\SatimLaravel\Concerns;

trait GeneratesOrderNumbers
{
    /**
     * Generate a unique 10-digit order number.
     */
    protected function generateOrderNumber(): string
    {
        return (string) random_int(1000000000, 9999999999);
    }
}
