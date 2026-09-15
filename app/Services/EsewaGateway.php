<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;

class EsewaGateway
{
    public function amount(Order $order): string
    {
        return intdiv($order->amount_paisa, 100).'.'.str_pad((string) ($order->amount_paisa % 100), 2, '0', STR_PAD_LEFT);
    }

    public function paisa(mixed $amount): ?int
    {
        if (! is_string($amount) && ! is_int($amount) && ! is_float($amount)) {
            return null;
        }
        $value = (string) $amount;
        if (! preg_match('/^(0|[1-9][0-9]{0,8})(?:\.([0-9]{1,2}))?$/D', $value, $matches)) {
            return null;
        }

        return ((int) $matches[1]) * 100 + (int) str_pad($matches[2] ?? '', 2, '0');
    }

    private function sign(string $message): string
    {
        return base64_encode(hash_hmac('sha256', $message, config('payments.esewa.secret'), true));
    }

    public function form(Order $order): array
    {
        abort_unless($order->provider === 'esewa' && $order->status === 'pending' && app(MembershipPayments::class)->available('esewa') && app(MembershipPayments::class)->canVerify($order), 403);
        $fields = [
            'amount' => $this->amount($order), 'tax_amount' => '0', 'total_amount' => $this->amount($order),
            'transaction_uuid' => $order->reference, 'product_code' => $order->merchant_code,
            'product_service_charge' => '0', 'product_delivery_charge' => '0',
            'success_url' => route('billing.return', $order), 'failure_url' => route('billing.return', $order),
            'signed_field_names' => 'total_amount,transaction_uuid,product_code',
        ];
        $fields['signature'] = $this->sign('total_amount='.$fields['total_amount'].',transaction_uuid='.$fields['transaction_uuid'].',product_code='.$fields['product_code']);

        return ['url' => $order->environment === 'live' ? 'https://epay.esewa.com.np/api/epay/main/v2/form' : 'https://rc-epay.esewa.com.np/api/epay/main/v2/form', 'fields' => $fields];
    }

    public function validCallback(Order $order, string $encoded): bool
    {
        if (strlen($encoded) > 8192) {
            return false;
        }
        $decoded = base64_decode($encoded, true);
        if ($decoded === false) {
            return false;
        }
        $data = json_decode($decoded, true);
        $names = 'transaction_code,status,total_amount,transaction_uuid,product_code,signed_field_names';
        if (! is_array($data) || ($data['signed_field_names'] ?? null) !== $names || ! is_string($data['signature'] ?? null)) {
            return false;
        }
        $parts = [];
        foreach (explode(',', $names) as $name) {
            if (! isset($data[$name]) || ! is_scalar($data[$name])) {
                return false;
            }
            $parts[] = $name.'='.$data[$name];
        }

        return hash_equals($this->sign(implode(',', $parts)), $data['signature'])
            && ($data['transaction_uuid'] ?? null) === $order->reference
            && ($data['product_code'] ?? null) === $order->merchant_code
            && $this->paisa($data['total_amount'] ?? null) === $order->amount_paisa;
    }

    public function lookup(Order $order): ?array
    {
        $base = $order->environment === 'live' ? 'https://epay.esewa.com.np' : 'https://uat.esewa.com.np';
        $response = Http::acceptJson()->timeout(15)->connectTimeout(5)->withoutRedirecting()->get($base.'/api/epay/transaction/status/', [
            'product_code' => $order->merchant_code, 'total_amount' => $this->amount($order), 'transaction_uuid' => $order->reference,
        ]);
        if (! $response->successful()) {
            return null;
        }
        $data = $response->json();
        if (! is_array($data) || ($data['pid'] ?? null) !== $order->reference || ($data['scd'] ?? null) !== $order->merchant_code || $this->paisa($data['totalAmount'] ?? null) !== $order->amount_paisa) {
            return null;
        }

        return [
            'pidx' => $order->provider_reference, 'total_amount' => $order->amount_paisa,
            'status' => match ($data['status'] ?? '') {
                'COMPLETE' => 'Completed', 'FULL_REFUND' => 'Refunded','PARTIAL_REFUND' => 'Partially Refunded',
                'CANCELED' => 'User canceled', default => 'Pending',
            },
            'transaction_id' => $data['refId'] ?? null,
            'refunded' => in_array($data['status'] ?? '', ['FULL_REFUND', 'PARTIAL_REFUND'], true),
        ];
    }
}
