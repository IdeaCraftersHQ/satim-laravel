# OSS Satim Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/oss/satim-laravel.svg?style=flat-square)](https://packagist.org/packages/oss/satim-laravel)
[![Total Downloads](https://img.shields.io/packagist/dt/oss/satim-laravel.svg?style=flat-square)](https://packagist.org/packages/oss/satim-laravel)
[![License](https://img.shields.io/packagist/l/oss/satim-laravel.svg?style=flat-square)](https://packagist.org/packages/oss/satim-laravel)
[![PHP Version](https://img.shields.io/packagist/php-v/oss/satim-laravel.svg?style=flat-square)](https://packagist.org/packages/oss/satim-laravel)

Laravel package for integrating with the Satim payment gateway (official Algerian interbank payment system).

## Features

- Register payments (generate payment links)
- Confirm payment status
- Process refunds
- Auto-convert amounts (DA to cents)
- Auto-generate order numbers
- Comprehensive test coverage
- Fluent/chainable API
- Interface-based design

## Requirements

- PHP 8.2+
- Laravel 12.x

## Installation

```bash
composer require oss/satim-laravel
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=satim-config
```

Add your Satim credentials to `.env`:

```env
SATIM_USERNAME=your_username
SATIM_PASSWORD=your_password
SATIM_TERMINAL_ID=your_terminal_id
SATIM_LANGUAGE=fr
SATIM_CURRENCY=012
SATIM_API_URL=https://test2.satim.dz/payment/rest
```

### Configuration Options

| Variable | Description | Default | Required |
|----------|-------------|---------|----------|
| `SATIM_USERNAME` | Your Satim merchant username | - | Yes |
| `SATIM_PASSWORD` | Your Satim merchant password | - | Yes |
| `SATIM_TERMINAL_ID` | Your terminal ID | - | Yes |
| `SATIM_LANGUAGE` | Default language (fr, en, ar) | `fr` | No |
| `SATIM_CURRENCY` | Currency code (012 for DZD) | `012` | No |
| `SATIM_API_URL` | API base URL | `https://test2.satim.dz/payment/rest` | No |
| `SATIM_HTTP_VERIFY_SSL` | Enable SSL verification | `true` | No |
| `SATIM_HTTP_TIMEOUT` | Request timeout (seconds) | `30` | No |
| `SATIM_HTTP_CONNECT_TIMEOUT` | Connection timeout (seconds) | `10` | No |

**Environment URLs:**
- Test: `https://test2.satim.dz/payment/rest`
- Production: `https://satim.dz/payment/rest`

## Usage

### Register Payment

```php
use Oss\SatimLaravel\Contracts\SatimInterface;

class PaymentController extends Controller
{
    public function __construct(private SatimInterface $satim) {}

    public function create(Request $request)
    {
        $payment = $this->satim
            ->amount($request->amount) // Auto-converted to cents
            ->returnUrl(route('payment.success'))
            ->failUrl(route('payment.failed'))
            ->description('Order #123')
            ->register();

        return redirect($payment->formUrl);
    }
}
```

### Confirm Payment

```php
public function success(Request $request)
{
    $confirmation = $this->satim->confirm($request->mdOrder);

    if ($confirmation->isPaid()) {
        // Payment successful - OrderStatus = 2
    }

    return view('payment.success', compact('confirmation'));
}
```

### Refund Payment

```php
public function refund(Request $request)
{
    $refund = $this->satim->refund(
        orderId: $request->order_id,
        amount: $request->amount // Auto-converted to cents
    );

    if ($refund->isSuccessful()) {
        // Refund processed successfully
    }

    return back()->with('success', 'Refund processed');
}
```

## Testing

```bash
composer test
```

## Security

This package handles sensitive payment information. Please ensure:

- Always use HTTPS in production
- Keep your Satim credentials secure (never commit to version control)
- SSL verification is enabled by default - only disable for local development with self-signed certificates
- Regularly update the package to receive security fixes

If you discover any security-related issues, please email security@ideacrafters.dz instead of using the issue tracker.

## License

MIT License
