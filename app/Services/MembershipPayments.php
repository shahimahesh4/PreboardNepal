<?php

namespace App\Services;

use App\Models\AuditEntry;
use App\Models\Entitlement;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class MembershipPayments
{
    public function environment(string $provider): ?string
    {
        return match ($provider) {
            'khalti' => config('payments.environment'), 'esewa' => config('payments.esewa.environment'), default => null
        };
    }

    public function configured(string $provider): bool
    {
        $environment = $this->environment($provider);
        if (! in_array($environment, ['sandbox', 'live'], true) || (app()->environment('production') && $environment !== 'live')) {
            return false;
        }

        return match ($provider) {
            'khalti' => filled(config('payments.secret')),
            'esewa' => filled(config('payments.esewa.secret')) && filled(config('payments.esewa.product_code')),
            default => false,
        };
    }

    public function available(?string $provider = null): bool
    {
        if ($provider === null) {
            return count($this->methods()) > 0;
        }
        if (! config('payments.enabled') || ! $this->configured($provider)) {
            return false;
        }

        return match ($provider) {
            'khalti' => (bool) config('payments.khalti_enabled'), 'esewa' => (bool) config('payments.esewa.enabled'), default => false
        };
    }

    public function methods(): array
    {
        return array_filter(['esewa' => 'eSewa', 'khalti' => 'Khalti'], fn ($label, $provider) => $this->available($provider), ARRAY_FILTER_USE_BOTH);
    }

    public function canVerify(Order $order): bool
    {
        return $this->configured($order->provider) && $order->environment === $this->environment($order->provider)
            && ($order->provider !== 'esewa' || $order->merchant_code === config('payments.esewa.product_code'));
    }

    private function request(string $endpoint, array $payload)
    {
        $base = config('payments.environment') === 'live' ? 'https://khalti.com' : 'https://dev.khalti.com';

        return Http::withHeaders(['Authorization' => 'key '.config('payments.secret')])->acceptJson()->timeout(15)->connectTimeout(5)->withoutRedirecting()->post($base.'/api/v2/epayment/'.$endpoint.'/', $payload);
    }

    public function create(User $user, Product $product, string $key, string $provider = 'khalti'): Order
    {
        abort_unless($this->available($provider) && $user->hasVerifiedEmail(), 403);
        abort_unless(Str::isUuid($key), 422);
        $order = DB::transaction(function () use ($user, $product, $key, $provider) {
            User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            if ($existing = Order::where('idempotency_key', $key)->first()) {
                abort_unless($existing->user_id === $user->id && $existing->product_id === $product->id && $existing->provider === $provider, 409);

                return $existing;
            }
            $product = Product::whereKey($product->id)->where('is_active', true)->lockForUpdate()->firstOrFail();
            abort_unless($product->amount_paisa >= 1000 && $product->duration_days > 0 && $product->duration_days <= 366, 422);

            return Order::create(['reference' => (string) Str::uuid(), 'idempotency_key' => $key, 'user_id' => $user->id, 'product_id' => $product->id, 'title' => $product->title, 'amount_paisa' => $product->amount_paisa, 'duration_days' => $product->duration_days, 'environment' => $this->environment($provider), 'provider' => $provider, 'merchant_code' => $provider === 'esewa' ? config('payments.esewa.product_code') : null, 'status' => 'created']);
        });
        if ($provider === 'esewa') {
            Order::whereKey($order->id)->where('status', 'created')->update(['status' => 'pending', 'provider_reference' => $order->reference]);

            return $order->refresh();
        }
        // Claim once before contacting the gateway. A timeout must never trigger a second charge initiation.
        if (! Order::whereKey($order->id)->where('status', 'created')->update(['status' => 'initiating'])) {
            return $order->refresh();
        }
        try {
            $response = $this->request('initiate', ['return_url' => route('billing.return', $order), 'website_url' => config('app.url'), 'amount' => $order->amount_paisa, 'purchase_order_id' => $order->reference, 'purchase_order_name' => $order->title]);
            $data = $response->json();
            $url = $data['payment_url'] ?? '';
            $host = config('payments.environment') === 'live' ? 'pay.khalti.com' : 'test-pay.khalti.com';
            if (! $response->successful() || ! is_string($data['pidx'] ?? null) || empty($data['pidx']) || ! is_string($url) || parse_url($url, PHP_URL_SCHEME) !== 'https' || parse_url($url, PHP_URL_HOST) !== $host || parse_url($url, PHP_URL_USER) !== null || parse_url($url, PHP_URL_PORT) !== null) {
                $order->update(['status' => 'initiation_unknown']);
            } else {
                $order->update(['status' => 'pending', 'provider_reference' => $data['pidx'], 'payment_url' => $url]);
            }
        } catch (ConnectionException $e) {
            $order->update(['status' => 'initiation_unknown']);
        }

        return $order->refresh();
    }

    public function verify(Order $order): Order
    {
        abort_unless($this->canVerify($order), 503);
        if (! $order->provider_reference) {
            return $order;
        }
        try {
            if ($order->provider === 'esewa') {
                $data = app(EsewaGateway::class)->lookup($order);
            } else {
                $response = $this->request('lookup', ['pidx' => $order->provider_reference]);
                $data = in_array($response->status(), [200, 400], true) ? $response->json() : null;
            }
        } catch (ConnectionException $e) {
            return $order;
        }
        if (! is_array($data)) {
            return $order;
        }

        return DB::transaction(function () use ($order, $data) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $order->update(['last_checked_at' => now()]);
            if (($data['pidx'] ?? null) !== $order->provider_reference || ($data['total_amount'] ?? null) !== $order->amount_paisa) {
                return $order;
            }
            $status = $data['status'] ?? '';
            if (in_array($status, ['Refunded', 'Partially Refunded'], true)) {
                Entitlement::where('source', 'order:'.$order->reference)->update(['revoked_at' => now()]);
                $order->update(['status' => $status === 'Refunded' ? 'refunded' : 'needs_review']);
            } elseif ($status === 'Completed' && ($data['refunded'] ?? null) === false && is_string($data['transaction_id'] ?? null) && filled($data['transaction_id'])) {
                if (in_array($order->status, ['paid', 'refunded', 'needs_review'], true)) {
                    return $order;
                }
                User::whereKey($order->user_id)->lockForUpdate()->firstOrFail();
                if (Order::where('provider', $order->provider)->where('environment', $order->environment)->where('transaction_id', $data['transaction_id'])->whereKeyNot($order->id)->exists()) {
                    return $order;
                }
                $end = Entitlement::where('user_id', $order->user_id)->whereNull('revoked_at')->max('ends_at');
                $start = $end && now()->parse($end)->isFuture() ? now()->parse($end) : now();
                Entitlement::firstOrCreate(['source' => 'order:'.$order->reference], ['user_id' => $order->user_id, 'starts_at' => $start, 'ends_at' => $start->copy()->addDays($order->duration_days)]);
                $order->update(['status' => 'paid', 'transaction_id' => $data['transaction_id'], 'verified_at' => now()]);
                AuditEntry::create(['user_id' => $order->user_id, 'action' => 'payment.verified', 'resource' => 'order:'.$order->reference, 'metadata' => ['amount_paisa' => $order->amount_paisa]]);
            } elseif (! in_array($order->status, ['paid', 'refunded', 'needs_review'], true) && in_array($status, ['Expired', 'User canceled'], true)) {
                $order->update(['status' => $status === 'Expired' ? 'expired' : 'canceled']);
            }

            return $order;
        });
    }
}
