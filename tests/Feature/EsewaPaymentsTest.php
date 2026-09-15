<?php

namespace Tests\Feature;

use App\Models\Entitlement;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\EsewaGateway;
use App\Services\MembershipPayments;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class EsewaPaymentsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['payments.enabled' => true, 'payments.secret' => 'khalti-test', 'payments.environment' => 'sandbox', 'payments.esewa.enabled' => true, 'payments.esewa.environment' => 'sandbox', 'payments.esewa.secret' => 'test-signing-secret', 'payments.esewa.product_code' => 'EPAYTEST']);
        Http::preventStrayRequests();
    }

    private function order(): Order
    {
        $user = User::factory()->create();
        $product = Product::create(['title' => 'Test membership', 'description' => 'A test offer', 'amount_paisa' => 50025, 'duration_days' => 30, 'is_active' => true]);

        return app(MembershipPayments::class)->create($user, $product, (string) Str::uuid(), 'esewa');
    }

    private function response(Order $order, array $replace = []): array
    {
        return array_replace(['pid' => $order->reference, 'scd' => 'EPAYTEST', 'totalAmount' => '500.25', 'status' => 'COMPLETE', 'refId' => 'esewa-'.$order->id], $replace);
    }

    private function fake(array $body): void
    {
        Http::swap(new Factory);
        Http::preventStrayRequests();
        Http::fake(['https://uat.esewa.com.np/*' => Http::response($body)]);
    }

    private function signedReturn(Order $order, array $replace = []): string
    {
        $data = array_replace(['transaction_code' => 'esewa-'.$order->id, 'status' => 'COMPLETE', 'total_amount' => '500.25', 'transaction_uuid' => $order->reference, 'product_code' => 'EPAYTEST', 'signed_field_names' => 'transaction_code,status,total_amount,transaction_uuid,product_code,signed_field_names'], $replace);
        $parts = [];
        foreach (explode(',', $data['signed_field_names']) as $field) {
            $parts[] = $field.'='.$data[$field];
        }
        $data['signature'] = base64_encode(hash_hmac('sha256', implode(',', $parts), 'test-signing-secret', true));

        return base64_encode(json_encode($data));
    }

    public function test_checkout_lists_configured_methods_and_disabled_method_is_rejected(): void
    {
        $order = $this->order();
        $this->actingAs($order->user);
        $this->get(route('billing.checkout', $order->product_id))->assertOk()->assertSee('eSewa')->assertSee('Khalti');
        config(['payments.esewa.enabled' => false]);
        $this->get(route('billing.checkout', $order->product_id))->assertOk()->assertDontSee('value="esewa"', false);
        $this->post(route('billing.store', $order->product_id), ['provider' => 'esewa', 'key' => (string) Str::uuid()])->assertForbidden();
        $this->post(route('billing.store', $order->product_id), ['provider' => 'connectips', 'key' => (string) Str::uuid()])->assertSessionHasErrors('provider');
        Http::assertNothingSent();
    }

    public function test_esewa_form_is_signed_server_side_without_leaking_secrets_or_csrf(): void
    {
        $order = $this->order();
        $gateway = app(EsewaGateway::class);
        $form = $gateway->form($order);
        $message = 'total_amount=500.25,transaction_uuid='.$order->reference.',product_code=EPAYTEST';
        $this->assertSame(base64_encode(hash_hmac('sha256', $message, 'test-signing-secret', true)), $form['fields']['signature']);
        $this->assertSame('500.25', $form['fields']['total_amount']);
        $this->assertSame('https://rc-epay.esewa.com.np/api/epay/main/v2/form', $form['url']);
        $this->assertArrayNotHasKey('_token', $form['fields']);
        $this->actingAs($order->user)->get(route('billing.show', $order))->assertOk()->assertSee('Continue to eSewa')->assertDontSee('test-signing-secret')->assertDontSee('khalti-test');
        $existing = app(MembershipPayments::class)->create($order->user, Product::find($order->product_id), $order->idempotency_key, 'esewa');
        $this->assertSame($order->id, $existing->id);
        $this->assertDatabaseCount('orders', 1);
        Http::assertNothingSent();
    }

    public function test_signed_callback_still_requires_lookup_and_grants_only_once(): void
    {
        $order = $this->order();
        $this->fake($this->response($order));
        $this->actingAs($order->user)->get(route('billing.return', $order).'?'.http_build_query(['data' => $this->signedReturn($order)]))->assertRedirect(route('billing.show', $order));
        app(MembershipPayments::class)->verify($order);
        $this->assertDatabaseCount('entitlements', 1);
        $this->assertSame('paid', $order->fresh()->status);
        Http::assertSent(fn ($request) => $request['transaction_uuid'] === $order->reference && $request['product_code'] === 'EPAYTEST' && $request['total_amount'] === '500.25');
    }

    public function test_invalid_signed_fields_and_cross_order_callback_are_rejected(): void
    {
        $order = $this->order();
        $this->actingAs($order->user);
        foreach (['not-base64', $this->signedReturn($order, ['total_amount' => '1']), $this->signedReturn($order, ['transaction_uuid' => (string) Str::uuid()]), $this->signedReturn($order, ['signed_field_names' => 'status']), $this->signedReturn($order, ['product_code' => 'other-merchant'])] as $data) {
            $this->get(route('billing.return', $order).'?'.http_build_query(['data' => $data]))->assertStatus(422);
        }
        $data = json_decode(base64_decode($this->signedReturn($order)), true);
        $data['signature'] = 'fake';
        $this->get(route('billing.return', $order).'?'.http_build_query(['data' => base64_encode(json_encode($data))]))->assertStatus(422);
        $this->assertDatabaseCount('entitlements', 0);
        Http::assertNothingSent();
    }

    public function test_valid_callback_cannot_override_pending_or_mismatched_lookup(): void
    {
        $order = $this->order();
        $service = app(MembershipPayments::class);
        foreach ([['status' => 'PENDING', 'refId' => null], ['totalAmount' => '1'], ['pid' => 'other-order'], ['scd' => 'other-merchant'], ['refId' => null], ['totalAmount' => '500.251'], ['totalAmount' => ['500.25']]] as $override) {
            $this->fake($this->response($order, $override));
            $service->verify($order);
            $this->assertDatabaseCount('entitlements', 0);
        }
        $this->fake($this->response($order, ['status' => 'PENDING', 'refId' => null]));
        $this->actingAs($order->user)->get(route('billing.return', $order).'?'.http_build_query(['data' => $this->signedReturn($order)]))->assertRedirect();
        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_refund_revokes_pass_and_replay_does_not_restore_it(): void
    {
        $order = $this->order();
        $service = app(MembershipPayments::class);
        $this->fake($this->response($order));
        $service->verify($order);
        $this->fake($this->response($order, ['status' => 'PARTIAL_REFUND']));
        $service->verify($order);
        $this->assertNotNull(Entitlement::first()->revoked_at);
        $this->assertSame('needs_review', $order->fresh()->status);
        $this->fake($this->response($order));
        $service->verify($order);
        $this->assertSame('needs_review', $order->fresh()->status);
    }

    public function test_provider_cannot_change_for_an_existing_order_key(): void
    {
        $order = $this->order();
        $this->actingAs($order->user)->post(route('billing.store', $order->product_id), ['provider' => 'khalti', 'key' => $order->idempotency_key])->assertStatus(409);
        Http::assertNothingSent();
    }

    public function test_environment_and_merchant_mismatch_block_verification(): void
    {
        $order = $this->order();
        $service = app(MembershipPayments::class);
        config(['payments.esewa.environment' => 'live']);
        $this->assertFalse($service->canVerify($order));
        config(['payments.esewa.environment' => 'sandbox', 'payments.esewa.product_code' => 'different']);
        $this->assertFalse($service->canVerify($order));
        config(['payments.esewa.product_code' => 'EPAYTEST']);
        $this->app->instance('env', 'production');
        $this->assertFalse($service->available('esewa'));
        $this->assertFalse($service->canVerify($order));
        Http::assertNothingSent();
    }

    public function test_reconciliation_checks_esewa_even_when_khalti_is_unconfigured(): void
    {
        $order = $this->order();
        config(['payments.secret' => null]);
        $this->fake($this->response($order));
        $this->artisan('preboard:reconcile-payments')->assertSuccessful();
        $this->assertSame('paid',$order->fresh()->status);
    }
}
