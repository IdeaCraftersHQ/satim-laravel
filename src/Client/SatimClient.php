<?php

namespace Oss\SatimLaravel\Client;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Oss\SatimLaravel\DTOs\ConfirmOrderData;
use Oss\SatimLaravel\DTOs\ConfirmOrderResponse;
use Oss\SatimLaravel\DTOs\RefundOrderData;
use Oss\SatimLaravel\DTOs\RefundOrderResponse;
use Oss\SatimLaravel\DTOs\RegisterOrderData;
use Oss\SatimLaravel\DTOs\RegisterOrderResponse;
use Oss\SatimLaravel\Exceptions\SatimAuthenticationException;
use Oss\SatimLaravel\Exceptions\SatimException;
use Oss\SatimLaravel\Exceptions\SatimPaymentException;

class SatimClient
{
    public function __construct(
        private readonly string $apiUrl,
        private readonly string $username,
        private readonly string $password,
        private readonly bool $verifySSL,
        private readonly int $timeout,
        private readonly int $connectTimeout,
    ) {
    }

    public function register(RegisterOrderData $data): RegisterOrderResponse
    {
        $response = $this->buildRequest()
            ->get("{$this->apiUrl}/register.do", array_merge(
                ['userName' => $this->username, 'password' => $this->password],
                $data->toArray()
            ));

        $responseData = $response->json();

        $this->handleErrors($responseData);

        return RegisterOrderResponse::fromArray($responseData);
    }

    public function confirm(ConfirmOrderData $data): ConfirmOrderResponse
    {
        $response = $this->buildRequest()
            ->get("{$this->apiUrl}/public/acknowledgeTransaction.do", array_merge(
                ['userName' => $this->username, 'password' => $this->password],
                $data->toArray()
            ));

        $responseData = $response->json();

        $this->handleErrors($responseData);

        return ConfirmOrderResponse::fromArray($responseData);
    }

    public function refund(RefundOrderData $data): RefundOrderResponse
    {
        $response = $this->buildRequest()
            ->get("{$this->apiUrl}/refund.do", array_merge(
                ['userName' => $this->username, 'password' => $this->password],
                $data->toArray()
            ));

        $responseData = $response->json();

        $this->handleErrors($responseData);

        return RefundOrderResponse::fromArray($responseData);
    }

    private function buildRequest(): PendingRequest
    {
        $request = Http::timeout($this->timeout)
            ->connectTimeout($this->connectTimeout);

        if (! $this->verifySSL) {
            $request->withoutVerifying();
        }

        return $request;
    }

    private function handleErrors(array $responseData): void
    {
        $errorCode = $responseData['errorCode'] ?? $responseData['ErrorCode'] ?? 0;

        if ($errorCode === 0) {
            return;
        }

        $errorMessage = $responseData['errorMessage'] ?? 'Unknown error';

        // Error code 5: Authentication/Access denied
        if ($errorCode === 5) {
            throw new SatimAuthenticationException($errorMessage, $errorCode, $responseData);
        }

        // Payment-specific errors: 1, 3, 4, 14
        if (in_array($errorCode, [1, 3, 4, 14])) {
            throw new SatimPaymentException($errorMessage, $errorCode, $responseData);
        }

        // Generic error for other cases (7, etc.)
        throw new SatimException($errorMessage, $errorCode, $responseData);
    }
}
