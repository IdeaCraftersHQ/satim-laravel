<?php

namespace Oss\SatimLaravel\Client;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
        private readonly bool $loggingEnabled = false,
        private readonly string $logChannel = 'stack',
    ) {
    }

    public function register(RegisterOrderData $data): RegisterOrderResponse
    {
        $requestData = array_merge(
            ['userName' => $this->username, 'password' => $this->password],
            $data->toArray()
        );

        $this->log('info', 'SATIM register.do request', [
            'endpoint' => '/register.do',
            'request' => $requestData,
        ]);

        try {
            $response = $this->buildRequest()->get("{$this->apiUrl}/register.do", $requestData);

            if ($response->failed()) {
                $this->log('error', 'SATIM API HTTP error', [
                    'endpoint' => '/register.do',
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new SatimException(
                    "SATIM API request failed with HTTP {$response->status()}",
                    $response->status()
                );
            }

            $responseData = $response->json();

            if ($responseData === null) {
                $this->log('error', 'SATIM API invalid JSON response', [
                    'endpoint' => '/register.do',
                    'body' => $response->body(),
                ]);
                throw new SatimException('Invalid JSON response from SATIM API');
            }

            $this->log('info', 'SATIM register.do response', [
                'endpoint' => '/register.do',
                'response' => $responseData,
            ]);

            $this->handleErrors($responseData);

            return RegisterOrderResponse::fromArray($responseData);
        } catch (ConnectionException $e) {
            $this->log('error', 'SATIM connection failed', [
                'endpoint' => '/register.do',
                'error' => $e->getMessage(),
            ]);
            throw new SatimException('Failed to connect to SATIM: '.$e->getMessage(), 0, null, $e);
        } catch (RequestException $e) {
            $this->log('error', 'SATIM request failed', [
                'endpoint' => '/register.do',
                'error' => $e->getMessage(),
            ]);
            throw new SatimException('SATIM API request failed: '.$e->getMessage(), 0, null, $e);
        } catch (SatimException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->log('error', 'SATIM register.do failed', [
                'endpoint' => '/register.do',
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function confirm(ConfirmOrderData $data): ConfirmOrderResponse
    {
        $requestData = array_merge(
            ['userName' => $this->username, 'password' => $this->password],
            $data->toArray()
        );

        $this->log('info', 'SATIM acknowledgeTransaction.do request', [
            'endpoint' => '/public/acknowledgeTransaction.do',
            'request' => $requestData,
        ]);

        try {
            $response = $this->buildRequest()->get("{$this->apiUrl}/public/acknowledgeTransaction.do", $requestData);

            if ($response->failed()) {
                $this->log('error', 'SATIM API HTTP error', [
                    'endpoint' => '/public/acknowledgeTransaction.do',
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new SatimException(
                    "SATIM API request failed with HTTP {$response->status()}",
                    $response->status()
                );
            }

            $responseData = $response->json();

            if ($responseData === null) {
                $this->log('error', 'SATIM API invalid JSON response', [
                    'endpoint' => '/public/acknowledgeTransaction.do',
                    'body' => $response->body(),
                ]);
                throw new SatimException('Invalid JSON response from SATIM API');
            }

            $this->log('info', 'SATIM acknowledgeTransaction.do response', [
                'endpoint' => '/public/acknowledgeTransaction.do',
                'response' => $responseData,
            ]);

            $this->handleErrors($responseData);

            return ConfirmOrderResponse::fromArray($responseData);
        } catch (ConnectionException $e) {
            $this->log('error', 'SATIM connection failed', [
                'endpoint' => '/public/acknowledgeTransaction.do',
                'error' => $e->getMessage(),
            ]);
            throw new SatimException('Failed to connect to SATIM: '.$e->getMessage(), 0, null, $e);
        } catch (RequestException $e) {
            $this->log('error', 'SATIM request failed', [
                'endpoint' => '/public/acknowledgeTransaction.do',
                'error' => $e->getMessage(),
            ]);
            throw new SatimException('SATIM API request failed: '.$e->getMessage(), 0, null, $e);
        } catch (SatimException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->log('error', 'SATIM acknowledgeTransaction.do failed', [
                'endpoint' => '/public/acknowledgeTransaction.do',
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function refund(RefundOrderData $data): RefundOrderResponse
    {
        $requestData = array_merge(
            ['userName' => $this->username, 'password' => $this->password],
            $data->toArray()
        );

        $this->log('info', 'SATIM refund.do request', [
            'endpoint' => '/refund.do',
            'request' => $requestData,
        ]);

        try {
            $response = $this->buildRequest()->get("{$this->apiUrl}/refund.do", $requestData);

            if ($response->failed()) {
                $this->log('error', 'SATIM API HTTP error', [
                    'endpoint' => '/refund.do',
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new SatimException(
                    "SATIM API request failed with HTTP {$response->status()}",
                    $response->status()
                );
            }

            $responseData = $response->json();

            if ($responseData === null) {
                $this->log('error', 'SATIM API invalid JSON response', [
                    'endpoint' => '/refund.do',
                    'body' => $response->body(),
                ]);
                throw new SatimException('Invalid JSON response from SATIM API');
            }

            $this->log('info', 'SATIM refund.do response', [
                'endpoint' => '/refund.do',
                'response' => $responseData,
            ]);

            $this->handleErrors($responseData);

            return RefundOrderResponse::fromArray($responseData);
        } catch (ConnectionException $e) {
            $this->log('error', 'SATIM connection failed', [
                'endpoint' => '/refund.do',
                'error' => $e->getMessage(),
            ]);
            throw new SatimException('Failed to connect to SATIM: '.$e->getMessage(), 0, null, $e);
        } catch (RequestException $e) {
            $this->log('error', 'SATIM request failed', [
                'endpoint' => '/refund.do',
                'error' => $e->getMessage(),
            ]);
            throw new SatimException('SATIM API request failed: '.$e->getMessage(), 0, null, $e);
        } catch (SatimException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->log('error', 'SATIM refund.do failed', [
                'endpoint' => '/refund.do',
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
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

    private function log(string $level, string $message, array $context = []): void
    {
        if (! $this->loggingEnabled) {
            return;
        }

        Log::channel($this->logChannel)
            ->$level($message, $this->sanitizeContext($context));
    }

    private function sanitizeContext(array $context): array
    {
        // Remove sensitive data from logs
        if (isset($context['request'])) {
            $context['request']['userName'] = '***';
            $context['request']['password'] = '***';
        }

        return $context;
    }
}
