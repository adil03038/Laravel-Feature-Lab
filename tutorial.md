Alright 👍 I’ll **retell the full tutorial from scratch** — but now with all corrections included: **real Stripe Checkout integration in Laravel 12**, storing `amount` + `currency` in DB, showing success/cancel pages.

---

# 🚀 Laravel 12 – Stripe Checkout Integration Tutorial

### ✅ Features:

* Stripe Checkout for a real test product
* Save payment details in DB (`amount`, `currency`, `status`, etc.)
* Show success/failure pages

---

## 1. Install Stripe SDK

```bash
composer require stripe/stripe-php
```

---

## 2. Configure Stripe Keys

In `.env` (use your **test keys** from Stripe Dashboard):

```env
STRIPE_KEY=pk_test_51xxxxxxxxxxxxxxxx
STRIPE_SECRET=sk_test_51xxxxxxxxxxxxxxxx
```

In `config/services.php`:

```php
'stripe' => [
    'key' => env('STRIPE_KEY'),
    'secret' => env('STRIPE_SECRET'),
],
```

---

## 3. Create Migration

```bash
php artisan make:migration create_transactions_table
```

In the migration file:

```php
Schema::create('transactions', function (Blueprint $table) {
    $table->id();
    $table->string('payment_intent')->unique();
    $table->string('session_id')->unique();
    $table->string('customer_email')->nullable();
    $table->string('status');
    $table->integer('amount');   // stored in cents/paisa
    $table->string('currency', 10);
    $table->timestamps();
});
```

Run:

```bash
php artisan migrate
```

---

## 4. Transaction Model

`app/Models/Transaction.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'payment_intent',
        'session_id',
        'customer_email',
        'status',
        'amount',
        'currency',
    ];
}
```

---

## 5. Payment Controller

```bash
php artisan make:controller PaymentController
```

`app/Http/Controllers/PaymentController.php`

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\PaymentIntent;
use App\Models\Transaction;

class PaymentController extends Controller
{
    public function checkout()
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd', // change to 'pkr' if needed
                    'product_data' => [
                        'name' => 'Pro Subscription Plan',
                    ],
                    'unit_amount' => 1500, // $15.00 (1500 cents)
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('payment.success').'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('payment.cancel'),
        ]);

        return redirect($session->url);
    }

    public function success(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        $session = Session::retrieve($request->get('session_id'));
        $paymentIntent = PaymentIntent::retrieve($session->payment_intent);

        // Save or update transaction
        Transaction::updateOrCreate(
            ['payment_intent' => $paymentIntent->id],
            [
                'session_id' => $session->id,
                'customer_email' => $session->customer_details->email ?? null,
                'status' => $paymentIntent->status,
                'amount' => $paymentIntent->amount,
                'currency' => $paymentIntent->currency,
            ]
        );

        return view('payment.success', compact('paymentIntent'));
    }

    public function cancel()
    {
        return view('payment.cancel');
    }
}
```

---

## 6. Routes

`routes/web.php`

```php
use App\Http\Controllers\PaymentController;

Route::get('/checkout', [PaymentController::class, 'checkout'])->name('checkout');
Route::get('/payment/success', [PaymentController::class, 'success'])->name('payment.success');
Route::get('/payment/cancel', [PaymentController::class, 'cancel'])->name('payment.cancel');
```

---

## 7. Views

`resources/views/payment/success.blade.php`

```blade
<h1>✅ Payment Successful</h1>
<p>Payment ID: {{ $paymentIntent->id }}</p>
<p>Status: {{ $paymentIntent->status }}</p>
<p>Amount: {{ strtoupper($paymentIntent->currency) }} {{ number_format($paymentIntent->amount / 100, 2) }}</p>
```

`resources/views/payment/cancel.blade.php`

```blade
<h1>❌ Payment Canceled</h1>
<p>Your transaction was not completed.</p>
```

---

## 8. Test Payments

Run the server:

```bash
php artisan serve
```

Visit:

```
http://127.0.0.1:8000/checkout
```

Use **Stripe test cards**:

* Success → `4242 4242 4242 4242`
* Decline → `4000 0000 0000 9995`

After payment, you’ll be redirected to **success/cancel page** and transaction will be saved in DB.

---

✅ That’s it. You now have a **working Stripe Checkout flow in Laravel 12**, with all necessary details stored in the database.

---

Do you want me to also extend this tutorial with a **transaction history page (list all payments in a table)** for testing?
