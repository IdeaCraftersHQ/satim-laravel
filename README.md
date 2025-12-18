# OSS Satim Laravel

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

## License

MIT License
