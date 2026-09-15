<x-layouts.app title="Checkout">
    <div class="page-container">
        <a href="{{ route('plans') }}">← Membership plans</a>
        <div class="page-heading"><div><p class="eyebrow">ONE STEP CLOSER</p><h1>Choose how you pay.</h1><p>Your study pass, your preferred payment method.</p></div></div>
        <div class="checkout-grid">
            <form class="panel checkout-payment" method="POST" action="{{ route('billing.store', $product) }}">
                @csrf
                <input type="hidden" name="key" value="{{ $key }}">
                <fieldset><legend>Payment method</legend><p>Continue securely with your preferred provider.</p>
                    <div class="payment-methods">
                    @foreach($methods as $provider => $label)
                        <label class="payment-method">
                            <input type="radio" name="provider" value="{{ $provider }}" @checked(old('provider', array_key_first($methods)) === $provider) required>
                            <span class="payment-monogram {{ $provider }}" aria-hidden="true">{{ $provider === 'esewa' ? 'e' : 'K' }}</span>
                            <span><strong>{{ $label }}</strong><small>{{ $provider === 'esewa' ? 'Pay with your eSewa wallet' : 'Continue with Khalti' }}@if(app(\App\Services\MembershipPayments::class)->environment($provider) === 'sandbox') · Test mode @endif</small></span>
                        </label>
                    @endforeach
                    </div>
                </fieldset>
                @error('provider')<p role="alert">{{ $message }}</p>@enderror
                <p class="checkout-reassurance">Your wallet password and PIN stay with your payment provider. We confirm your payment before activating access.</p>
                <button class="btn btn-primary" type="submit">Continue · NPR {{ number_format($product->amount_paisa / 100, 2) }} →</button>
            </form>
            <aside class="pricing-card checkout-summary"><p class="eyebrow">YOUR STUDY PASS</p><h2>{{ $product->title }}</h2><p>{{ $product->description }}</p><dl><div><dt>Access</dt><dd>{{ $product->duration_days }} days</dd></div><div><dt>Total</dt><dd>NPR {{ number_format($product->amount_paisa / 100, 2) }}</dd></div></dl><p>Premium library access. One payment; no automatic renewal. Renewals begin after your existing pass ends.</p><a href="{{ route('help') }}">Need a hand? →</a></aside>
        </div>
    </div>
</x-layouts.app>
