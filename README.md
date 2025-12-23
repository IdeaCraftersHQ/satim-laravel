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

## Quick Start

### Understanding the Payment Flow

```
1. Register Payment → Receive formUrl and mdOrder
2. Redirect User → User pays on Satim's secure page
3. Callback → Satim redirects back to your site
4. Confirm Status → Verify the payment was successful
```

**Key Concepts:**
- **mdOrder**: Unique payment identifier from Satim (you'll need this to confirm payment)
- **returnUrl**: Where Satim redirects after successful payment
- **failUrl**: Where Satim redirects if payment fails or is cancelled
- **Amounts**: Always in Algerian Dinars (DA) - automatically converted to cents

### Step 1: Register Payment

```php
use Oss\SatimLaravel\Facades\Satim;
use Oss\SatimLaravel\Exceptions\SatimException;

class PaymentController extends Controller
{
    public function create(Request $request)
    {
        try {
            $payment = Satim::amount(100.00) // Amount in DA
                ->returnUrl(route('payment.success'))
                ->failUrl(route('payment.failed'))
                ->description('Order #123')
                ->register();

            // Redirect user to Satim payment page
            return redirect($payment->formUrl);

        } catch (SatimException $e) {
            return back()->withErrors(['error' => 'Payment initialization failed']);
        }
    }
}
```

### Step 2: Handle Success Callback

```php
public function success(Request $request)
{
    $mdOrder = $request->input('mdOrder');

    try {
        $confirmation = Satim::confirm($mdOrder);

        if ($confirmation->isPaid()) {
            // Payment successful! Process the order here
            // Access payment details: $confirmation->amount, $confirmation->pan, etc.

            return view('payment.success', [
                'amount' => $confirmation->amount / 100, // Convert back to DA
                'reference' => $confirmation->orderNumber,
            ]);
        }

        return redirect()->route('payment.failed');

    } catch (SatimException $e) {
        return redirect()->route('payment.failed');
    }
}
```

### Step 3: Handle Failed Callback

```php
public function failed(Request $request)
{
    // Payment was declined, cancelled, or failed
    return view('payment.failed');
}
```

## Refund Payment

Process full or partial refunds for completed payments:

```php
public function refund(Request $request)
{
    try {
        $refund = Satim::refund(
            orderId: $request->order_id,     // mdOrder from Satim
            amount: $request->amount         // Amount in DA (auto-converted to cents)
        );

        if ($refund->isSuccessful()) {
            return back()->with('success', 'Refund processed');
        }

        return back()->withErrors(['error' => $refund->errorMessage]);

    } catch (SatimException $e) {
        return back()->withErrors(['error' => 'Refund failed']);
    }
}
```

## Payment Status Reference

Payment status codes returned by `confirm()`:

| Status | Helper Method | Description |
|--------|---------------|-------------|
| 0 | - | Created (payment not completed) |
| 1 | `isPreAuthorized()` | Pre-authorized |
| 2 | `isPaid()` | **Payment completed** ✓ |
| 3 | `isReversed()` | Reversed |
| 4 | `isRefunded()` | Refunded |
| 5 | `isDeclined()` | Declined |
| 6 | `isDeclined()` | Declined by fraud filter |

## Response Objects

### RegisterOrderResponse

```php
$payment = Satim::amount(100)->returnUrl(...)->register();

$payment->orderId;      // Satim order ID (mdOrder) - store this!
$payment->formUrl;      // Redirect user to this URL
$payment->isSuccessful(); // Check if registration succeeded
```

### ConfirmOrderResponse

```php
$confirmation = Satim::confirm($mdOrder);

$confirmation->isPaid();       // Payment completed (status 2)
$confirmation->orderStatus;    // Status code (0-6)
$confirmation->amount;         // Amount in cents
$confirmation->pan;            // Masked card number
$confirmation->orderNumber;    // Order reference
$confirmation->approvalCode;   // Bank approval code
```

### RefundOrderResponse

```php
$refund = Satim::refund($orderId, $amount);

$refund->isSuccessful();  // Refund succeeded
$refund->errorMessage;    // Error description if failed
```

## Error Handling

The package throws three types of exceptions:

```php
use Oss\SatimLaravel\Exceptions\SatimAuthenticationException;
use Oss\SatimLaravel\Exceptions\SatimPaymentException;
use Oss\SatimLaravel\Exceptions\SatimException;

try {
    $payment = Satim::amount(100)->returnUrl(...)->register();

} catch (SatimAuthenticationException $e) {
    // Error code 5 - Invalid credentials

} catch (SatimPaymentException $e) {
    // Error codes 1, 2, 3, 4, 6, 14 - Payment issues

} catch (SatimException $e) {
    // Error code 7 or unknown - System errors
}
```

### Common Error Codes

| Code | Exception | Meaning | Solution |
|------|-----------|---------|----------|
| 0 | - | Success | - |
| 1 | `SatimPaymentException` | Duplicate order number | Use unique order numbers |
| 2 | `SatimPaymentException` | Payment declined | Try another card |
| 5 | `SatimAuthenticationException` | Invalid credentials | Check username/password |
| 6 | `SatimPaymentException` | Order not found | Verify orderId |
| 14 | `SatimPaymentException` | Invalid amount | Min 50 DA, whole numbers |

## Advanced Usage

### Using Dependency Injection

```php
use Oss\SatimLaravel\Contracts\SatimInterface;

class PaymentController extends Controller
{
    public function __construct(private SatimInterface $satim) {}

    public function create()
    {
        $payment = $this->satim->amount(100)->returnUrl(...)->register();
        return redirect($payment->formUrl);
    }
}
```

### Custom Order Numbers

```php
$payment = Satim::amount(100)
    ->orderNumber('INV' . time()) // Max 10 characters
    ->returnUrl(route('payment.success'))
    ->register();
```

### User-Defined Fields (UDF)

Pass custom data through the payment:

```php
$payment = Satim::amount(100)
    ->udf1('customer_123')    // Max 20 chars each
    ->udf2('campaign_summer')
    ->udf3('referral_code')
    ->returnUrl(route('payment.success'))
    ->register();
```

### Language Override

```php
// Change language per request (default: 'fr')
$payment = Satim::amount(100)
    ->language('en') // 'fr', 'en', or 'ar'
    ->returnUrl(route('payment.success'))
    ->register();

// Or during confirmation
$confirmation = Satim::confirm($mdOrder, 'en');
```

### Partial Refunds

```php
// Refund partial amount
Satim::refund($orderId, 50.00); // Refund 50 DA only

// Full refund
$confirmation = Satim::confirm($mdOrder);
$fullAmount = $confirmation->amount / 100;
Satim::refund($orderId, $fullAmount);
```

## Best Practices

- **Always verify with confirm()**: Don't trust the redirect alone, always confirm payment status
- **Store mdOrder**: Save it with your order so you can verify or refund later
- **Use HTTPS**: All callback URLs must be HTTPS in production
- **Handle exceptions**: Catch specific exception types for better error handling
- **Validate amounts**: Minimum 50 DA, use whole numbers
- **Test first**: Use test environment before going to production

## Troubleshooting

### "Error code 5: Authentication failed"

Check your credentials in `.env`:
```env
SATIM_USERNAME=your_correct_username
SATIM_PASSWORD=your_correct_password
```

### "Amount must be multiple of 100"

Use whole DA amounts (integers or floats like 100, 250.00):
```php
Satim::amount(100);    // ✓ Good
Satim::amount(100.50); // ✗ Bad - not a whole DA amount
```

### "Invalid return URL"

Ensure URLs are fully qualified and use HTTPS:
```php
->returnUrl(route('payment.success')) // ✓ Good
->returnUrl('/payment/success')       // ✗ Bad - relative URL
```

### Callback not received

- Check firewall allows incoming requests
- Verify URLs are publicly accessible
- Use `https://` (not `http://`) in production
- For local testing, use ngrok or similar tunneling service

### SSL verification issues (local only)

```env
# ONLY for local development with self-signed certificates
SATIM_HTTP_VERIFY_SSL=false
```

⚠️ **Never disable SSL verification in production**

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
