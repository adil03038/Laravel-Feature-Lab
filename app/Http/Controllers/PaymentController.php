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
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => 'Pro Subscription Plan',
                    ],
                    'unit_amount' => 1500, // $15
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

        // Save transaction if not already saved
        Transaction::updateOrCreate(
            ['payment_intent' => $paymentIntent->id],
            [
                'session_id' => $session->id,
                'customer_email' => $session->customer_details->email ?? null,
                'status' => $paymentIntent->status,
                'currency' => $paymentIntent->currency,
                'amount' => $paymentIntent->amount,
            ]
        );

        return view('payment.success', compact('paymentIntent'));
    }

    public function cancel()
    {
        return view('payment.cancel');
    }
}
