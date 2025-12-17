<?php

namespace Oss\SatimLaravel\Exceptions;

use Exception;
use Throwable;

class SatimException extends Exception
{
    protected int $errorCode;
    protected ?array $context;

    public function __construct(
        string $message,
        int $errorCode = 0,
        ?array $context = null,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
        $this->errorCode = $errorCode;
        $this->context = $context;
    }

    public function getErrorCode(): int
    {
        return $this->errorCode;
    }

    public function getContext(): ?array
    {
        return $this->context;
    }
}
