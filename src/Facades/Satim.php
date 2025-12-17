<?php

namespace Oss\SatimLaravel\Facades;

use Illuminate\Support\Facades\Facade;
use Oss\SatimLaravel\Contracts\SatimInterface;

/**
 * @method static \Oss\SatimLaravel\Contracts\SatimInterface amount(float|int $amount)
 * @method static \Oss\SatimLaravel\Contracts\SatimInterface orderNumber(string $orderNumber)
 * @method static \Oss\SatimLaravel\Contracts\SatimInterface returnUrl(string $url)
 * @method static \Oss\SatimLaravel\Contracts\SatimInterface failUrl(string $url)
 * @method static \Oss\SatimLaravel\Contracts\SatimInterface description(string $description)
 * @method static \Oss\SatimLaravel\Contracts\SatimInterface language(string $language)
 * @method static \Oss\SatimLaravel\Contracts\SatimInterface udf1(string $value)
 * @method static \Oss\SatimLaravel\Contracts\SatimInterface udf2(string $value)
 * @method static \Oss\SatimLaravel\Contracts\SatimInterface udf3(string $value)
 * @method static \Oss\SatimLaravel\Contracts\SatimInterface udf4(string $value)
 * @method static \Oss\SatimLaravel\Contracts\SatimInterface udf5(string $value)
 * @method static \Oss\SatimLaravel\DTOs\RegisterOrderResponse register()
 * @method static \Oss\SatimLaravel\DTOs\ConfirmOrderResponse confirm(string $mdOrder, ?string $language = null)
 * @method static \Oss\SatimLaravel\DTOs\RefundOrderResponse refund(string $orderId, float|int $amount)
 *
 * @see \Oss\SatimLaravel\Contracts\SatimInterface
 */
class Satim extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SatimInterface::class;
    }
}
