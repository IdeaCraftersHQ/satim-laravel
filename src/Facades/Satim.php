<?php

namespace Ideacrafters\SatimLaravel\Facades;

use Illuminate\Support\Facades\Facade;
use Ideacrafters\SatimLaravel\Contracts\SatimInterface;

/**
 * @method static \Ideacrafters\SatimLaravel\Contracts\SatimInterface amount(float|int $amount)
 * @method static \Ideacrafters\SatimLaravel\Contracts\SatimInterface orderNumber(string $orderNumber)
 * @method static \Ideacrafters\SatimLaravel\Contracts\SatimInterface returnUrl(string $url)
 * @method static \Ideacrafters\SatimLaravel\Contracts\SatimInterface failUrl(string $url)
 * @method static \Ideacrafters\SatimLaravel\Contracts\SatimInterface description(string $description)
 * @method static \Ideacrafters\SatimLaravel\Contracts\SatimInterface language(string $language)
 * @method static \Ideacrafters\SatimLaravel\Contracts\SatimInterface udf1(string $value)
 * @method static \Ideacrafters\SatimLaravel\Contracts\SatimInterface udf2(string $value)
 * @method static \Ideacrafters\SatimLaravel\Contracts\SatimInterface udf3(string $value)
 * @method static \Ideacrafters\SatimLaravel\Contracts\SatimInterface udf4(string $value)
 * @method static \Ideacrafters\SatimLaravel\Contracts\SatimInterface udf5(string $value)
 * @method static \Ideacrafters\SatimLaravel\DTOs\RegisterOrderResponse register()
 * @method static \Ideacrafters\SatimLaravel\DTOs\ConfirmOrderResponse confirm(string $mdOrder, ?string $language = null)
 * @method static \Ideacrafters\SatimLaravel\DTOs\RefundOrderResponse refund(string $orderId, float|int $amount)
 *
 * @see \Ideacrafters\SatimLaravel\Contracts\SatimInterface
 */
class Satim extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SatimInterface::class;
    }
}
