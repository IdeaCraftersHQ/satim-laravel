# OSS Satim Laravel

Laravel package for integrating with the Satim payment gateway (official Algerian interbank payment system).

## Features

- Register payments (generate payment links)
- Confirm payment status
- Process refunds
- Auto-convert amounts (DA to cents)
- Comprehensive test coverage
- Fluent/chainable API
- Interface-based design
- Production logging support

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
            ->orderNumber('CMD-2024-001') // Required
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
        $statusName = $confirmation->getStatusName(); // "Amount deposited successfully"
        $amountDA = $confirmation->getAmountInDinars(); // e.g., 100.00
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

## Order Status Codes

After confirming a payment, you can check the order status:

| Code | Status | Description |
|------|--------|-------------|
| 0 | Registered | Order registered, but not paid |
| -1 | Failed | Transaction failed |
| 1 | Approved | Transaction approved / Pre-authorized |
| 2 | Paid | Amount deposited successfully |
| 3 | Reversed | Authorization reversed |
| 4 | Refunded | Transaction refunded |
| 6 | Declined | Authorization declined |
| 7 | Card Added | Card added to system |
| 8 | Card Updated | Card information updated |
| 9 | Card Verified | Card verified |
| 10 | Template Added | Recurring template added |
| 11 | Debited | Amount debited |

```php
$confirmation = $this->satim->confirm($request->mdOrder);

// Check status
echo $confirmation->getStatusName(); // "Amount deposited successfully"

// Helper methods
if ($confirmation->isPaid()) { /* OrderStatus = 2 */ }
if ($confirmation->isPreAuthorized()) { /* OrderStatus = 1 */ }
if ($confirmation->isDeclined()) { /* OrderStatus = 6 */ }
if ($confirmation->isRefunded()) { /* OrderStatus = 4 */ }
if ($confirmation->isReversed()) { /* OrderStatus = 3 */ }
```

## User Defined Fields (UDF)

SATIM provides 5 custom fields to store additional data:

```php
$payment = $this->satim
    ->orderNumber('CMD001')
    ->amount(100)
    ->returnUrl(route('payment.success'))
    ->udf1('INV-2024-001')      // Invoice number
    ->udf2('customer-123')       // Customer ID
    ->udf3('subscription')       // Payment type
    ->register();

// Retrieve UDF values from confirmation
$confirmation = $this->satim->confirm($mdOrder);
$udfFields = $confirmation->getUdfFields();
// ['udf1' => 'INV-2024-001', 'udf2' => 'customer-123', ...]
```

## Logging

Enable logging for production debugging by adding to `.env`:

```env
SATIM_LOGGING_ENABLED=true
SATIM_LOG_CHANNEL=stack
```

Logs include:
- API requests (credentials sanitized)
- API responses
- Error details

All sensitive data (username, password) is automatically removed from logs.

## Testing

```bash
composer test
```

## License

MIT License
