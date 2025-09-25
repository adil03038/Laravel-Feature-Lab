<h1>✅ Payment Successful</h1>
<p>Payment ID: {{ $paymentIntent->id }}</p>
<p>Status: {{ $paymentIntent->status }}</p>
<p>Amount: ${{ number_format($paymentIntent->amount / 100, 2) }}</p>