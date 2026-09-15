<?php

namespace Tests\Feature;

use App\Filament\Resources\Orders\Pages\ManageOrders;
use App\Filament\Resources\Products\Pages\ManageProducts;
use App\Models\Entitlement;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\MembershipPayments;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class BillingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['payments.enabled' => true, 'payments.environment' => 'sandbox', 'payments.secret' => 'test-secret']);
        Http::preventStrayRequests();
    }

    private function fake(array $responses): void
    {
        Http::swap(new Factory);
        Http::preventStrayRequests();
        Http::fake($responses);
    }

    private function product(): Product
    {
        return Product::create(['title' => 'Test pass', 'description' => 'Test offer', 'amount_paisa' => 50000, 'duration_days' => 30, 'is_active' => true]);
    }

    private function order(User $user): Order
    {
        return Order::create(['reference' => (string) Str::uuid(), 'idempotency_key' => (string) Str::uuid(), 'user_id' => $user->id, 'product_id' => $this->product()->id, 'title' => 'Test pass', 'amount_paisa' => 50000, 'duration_days' => 30, 'environment' => 'sandbox', 'status' => 'pending', 'provider_reference' => (string) Str::uuid()]);
    }

    private function completed(Order $order, array $override = []): array
    {
        return array_replace(['pidx' => $order->provider_reference, 'total_amount' => 50000, 'status' => 'Completed', 'transaction_id' => 'txn-'.$order->id, 'refunded' => false], $override);
    }

    public function test_checkout_uses_server_price_and_initiates_only_once(): void
    {
        $this->fake(['*/initiate/' => Http::response(['pidx' => 'payment-one', 'payment_url' => 'https://test-pay.khalti.com/?pidx=payment-one'])]);
        $user = User::factory()->create();
        $product = $this->product();
        $key = (string) Str::uuid();
        $this->actingAs($user)->get(route('billing.checkout', $product))->assertOk();
        for ($i = 0; $i < 2; $i++) {
            $this->post(route('billing.store', $product), ['provider' => 'khalti', 'key' => $key, 'amount_paisa' => 1])->assertRedirect();
        }
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseHas('orders', ['amount_paisa' => 50000, 'status' => 'pending']);
        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => $request['amount'] === 50000);
        $this->get(route('billing.show', Order::first()))->assertOk();
        $this->get('/billing')->assertOk();
    }

    public function test_disabled_and_unverified_checkout_never_contacts_gateway(): void
    {
        $product = $this->product();
        $user = User::factory()->unverified()->create();
        $this->actingAs($user)->post(route('billing.store', $product), ['provider' => 'khalti', 'key' => (string) Str::uuid()])->assertRedirect(route('verification.notice'));
        $user->forceFill(['email_verified_at' => now()])->save();
        config(['payments.enabled' => false]);
        $this->post(route('billing.store', $product), ['provider' => 'khalti', 'key' => (string) Str::uuid()])->assertForbidden();
        Http::assertNothingSent();
    }

    public function test_verified_payment_grants_once_and_renewal_extends_access(): void
    {
        $this->freezeTime();
        $user = User::factory()->create();
        $order = $this->order($user);
        $this->fake(['*/lookup/' => Http::response($this->completed($order))]);
        $service = app(MembershipPayments::class);
        $service->verify($order);
        $service->verify($order);
        $this->assertDatabaseCount('entitlements', 1);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'paid']);
        $renewal = $this->order($user);
        $this->fake(['*/lookup/' => Http::response($this->completed($renewal))]);
        $service->verify($renewal);
        $this->assertEquals(now()->addDays(60)->timestamp, Entitlement::latest('id')->first()->ends_at->timestamp);
    }

    public function test_callback_query_cannot_fake_payment_and_mismatched_lookup_cannot_grant(): void
    {
        $user = User::factory()->create();
        $order = $this->order($user);
        $this->fake(['*/lookup/' => Http::response($this->completed($order, ['total_amount' => 1]))]);
        $this->actingAs($user)->get(route('billing.return', $order).'?status=Completed&amount=50000&pidx=fake')->assertRedirect();
        $this->assertDatabaseCount('entitlements', 0);
        Http::assertSent(fn ($request) => $request['pidx'] === $order->provider_reference);
        $this->fake(['*/lookup/' => Http::response($this->completed($order, ['pidx' => 'wrong']))]);
        app(MembershipPayments::class)->verify($order);
        $this->assertDatabaseCount('entitlements', 0);
        $this->actingAs(User::factory()->create())->get(route('billing.return', $order))->assertNotFound();
    }

    public function test_refund_revokes_access_and_cannot_be_replayed_as_paid(): void
    {
        $order = $this->order(User::factory()->create());
        $service = app(MembershipPayments::class);
        $this->fake(['*/lookup/' => Http::response($this->completed($order))]);
        $service->verify($order);
        $this->fake(['*/lookup/' => Http::response($this->completed($order, ['status' => 'Refunded', 'refunded' => true]))]);
        $service->verify($order);
        $this->assertNotNull(Entitlement::first()->revoked_at);
        $this->fake(['*/lookup/' => Http::response($this->completed($order))]);
        $service->verify($order);
        $this->assertSame('refunded', $order->fresh()->status);
        $this->assertDatabaseCount('entitlements', 1);
    }

    public function test_uncertain_initiation_is_not_retried_and_untrusted_redirect_is_rejected(): void
    {
        $this->fake(['*/initiate/' => Http::response(['pidx' => 'unsafe', 'payment_url' => 'https://evil.example/pay'])]);
        $user = User::factory()->create();
        $product = $this->product();
        $key = (string) Str::uuid();
        $service = app(MembershipPayments::class);
        $order = $service->create($user, $product, $key);
        $service->create($user, $product, $key);
        $this->assertSame('initiation_unknown', $order->status);
        $this->assertNull($order->payment_url);
        Http::assertSentCount(1);
    }

    public function test_gateway_outage_does_not_grant_access(): void
    {
        $order = $this->order(User::factory()->create());
        $this->fake(['*/lookup/' => Http::response([], 503)]);
        app(MembershipPayments::class)->verify($order);
        $this->assertDatabaseCount('entitlements', 0);
        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_billing_admin_resources_render(): void
    {
        $user = User::factory()->create();
        $user->role = 'admin';
        $user->save();
        $this->actingAs($user);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Livewire::test(ManageProducts::class)->assertOk()->mountAction('create')->assertOk();
        Livewire::test(ManageOrders::class)->assertOk();
    }
}
