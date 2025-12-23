<?php

namespace Ideacrafters\SatimLaravel\Client;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Ideacrafters\SatimLaravel\DTOs\ConfirmOrderData;
use Ideacrafters\SatimLaravel\DTOs\ConfirmOrderResponse;
use Ideacrafters\SatimLaravel\DTOs\RefundOrderData;
use Ideacrafters\SatimLaravel\DTOs\RefundOrderResponse;
use Ideacrafters\SatimLaravel\DTOs\RegisterOrderData;
use Ideacrafters\SatimLaravel\DTOs\RegisterOrderResponse;
use Ideacrafters\SatimLaravel\Exceptions\SatimAuthenticationException;
use Ideacrafters\SatimLaravel\Exceptions\SatimException;
use Ideacrafters\SatimLaravel\Exceptions\SatimPaymentException;

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

    /**
     * Register a new payment order
     *
     * @throws \Ideacrafters\SatimLaravel\Exceptions\SatimAuthenticationException When error code 5 (access denied, invalid credentials)
     * @throws \Ideacrafters\SatimLaravel\Exceptions\SatimPaymentException When error codes 1, 3, 4, 14 (payment-specific errors)
     * @throws \Ideacrafters\SatimLaravel\Exceptions\SatimException When error code 7 or other system errors
     */
    public function register(RegisterOrderData $data): RegisterOrderResponse
    {
        $response = $this->buildRequest()
            ->get("{$this->apiUrl}/register.do", array_merge(
                ['userName' => $this->username, 'password' => $this->password],
                $data->toArray()
            ));

        $responseData = $response->json();
        $this->handleRegisterErrors($responseData);

        return RegisterOrderResponse::fromArray($responseData);
    }

    /**
     * Confirm payment status
     *
     * @throws \Ideacrafters\SatimLaravel\Exceptions\SatimAuthenticationException When error code 5 (access denied)
     * @throws \Ideacrafters\SatimLaravel\Exceptions\SatimPaymentException When error codes 2 (order declined), 6 (unregistered orderId)
     * @throws \Ideacrafters\SatimLaravel\Exceptions\SatimException When error code 7 or other system errors
     */
    public function confirm(ConfirmOrderData $data): ConfirmOrderResponse
    {
        $response = $this->buildRequest()
            ->get("{$this->apiUrl}/public/acknowledgeTransaction.do", array_merge(
                ['userName' => $this->username, 'password' => $this->password],
                $data->toArray()
            ));

        $responseData = $response->json();

        $this->handleConfirmErrors($responseData);

        return ConfirmOrderResponse::fromArray($responseData);
    }

    /**
     * Refund a payment
     *
     * @throws \Ideacrafters\SatimLaravel\Exceptions\SatimAuthenticationException When error code 5 (access denied, invalid amount)
     * @throws \Ideacrafters\SatimLaravel\Exceptions\SatimPaymentException When error code 6 (unregistered orderId)
     * @throws \Ideacrafters\SatimLaravel\Exceptions\SatimException When error code 7 or other system errors
     */
    public function refund(RefundOrderData $data): RefundOrderResponse
    {
        $response = $this->buildRequest()
            ->get("{$this->apiUrl}/refund.do", array_merge(
                ['userName' => $this->username, 'password' => $this->password],
                $data->toArray()
            ));

        $responseData = $response->json();

        $this->handleRefundErrors($responseData);

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

    /**
     * Extract error code from response data (handles both 'errorCode' and 'ErrorCode')
     */
    private function getErrorCode(array $responseData): int
    {
        return (int) ($responseData['errorCode'] ?? $responseData['ErrorCode'] ?? 0);
    }

    /**
     * Extract error message from response data (handles both cases)
     */
    private function getErrorMessage(array $responseData): string
    {
        return $responseData['errorMessage'] ?? $responseData['ErrorMessage'] ?? 'Unknown error';
    }

    /**
     * Handle errors specific to register.do endpoint
     *
     * Error codes:
     * - 0: Success
     * - 1: Order already processed / childId incorrect / Submerchant blocked
     * - 3: Unknown currency
     * - 4: Missing parameters (orderNumber, userName, amount, returnUrl, password)
     * - 5: Invalid parameter / Invalid language / Access denied / Password change
     * - 7: System error
     * - 14: Invalid payment way
     */
    private function handleRegisterErrors(array $responseData): void
    {
        $errorCode = $this->getErrorCode($responseData);

        if ($errorCode === 0) {
            return;
        }

        $errorMessage = $this->getErrorMessage($responseData);

        // Error code 5: Authentication/Access denied
        if ($errorCode === 5) {
            throw new SatimAuthenticationException($errorMessage, $errorCode, $responseData);
        }

        // Payment-specific errors for register: 1, 3, 4, 14
        if (in_array($errorCode, [1, 3, 4, 14], true)) {
            throw new SatimPaymentException($errorMessage, $errorCode, $responseData);
        }

        // Generic error for other cases (7, etc.)
        throw new SatimException($errorMessage, $errorCode, $responseData);
    }

    /**
     * Handle errors specific to acknowledgeTransaction.do endpoint
     *
     * Error codes (ErrorCode - note the capital E):
     * - 0: Success
     * - 2: Order declined due to payment credentials error
     * - 5: Access denied / Password change required / orderId is empty
     * - 6: Unregistered orderId
     * - 7: System error
     */
    private function handleConfirmErrors(array $responseData): void
    {
        $errorCode = $this->getErrorCode($responseData);

        if ($errorCode === 0) {
            return;
        }

        $errorMessage = $this->getErrorMessage($responseData);

        // Error code 5: Authentication/Access denied
        if ($errorCode === 5) {
            throw new SatimAuthenticationException($errorMessage, $errorCode, $responseData);
        }

        // Payment-specific errors for confirm: 2 (declined), 6 (unregistered)
        if (in_array($errorCode, [2, 6], true)) {
            throw new SatimPaymentException($errorMessage, $errorCode, $responseData);
        }

        // Generic error for other cases (7, etc.)
        throw new SatimException($errorMessage, $errorCode, $responseData);
    }

    /**
     * Handle errors specific to refund.do endpoint
     *
     * Error codes:
     * - 0: Success
     * - 5: Access denied / Password change / Invalid amount / Duplicate refund
     * - 6: Unregistered orderId
     * - 7: System error / Payment not in correct state
     */
    private function handleRefundErrors(array $responseData): void
    {
        $errorCode = $this->getErrorCode($responseData);

        if ($errorCode === 0) {
            return;
        }

        $errorMessage = $this->getErrorMessage($responseData);

        // Error code 5: Authentication/Access denied/Invalid amount
        if ($errorCode === 5) {
            throw new SatimAuthenticationException($errorMessage, $errorCode, $responseData);
        }

        // Payment-specific errors for refund: 6 (unregistered orderId)
        if ($errorCode === 6) {
            throw new SatimPaymentException($errorMessage, $errorCode, $responseData);
        }

        // Generic error for other cases (7, etc.)
        throw new SatimException($errorMessage, $errorCode, $responseData);
    }
}
