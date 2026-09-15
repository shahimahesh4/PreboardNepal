<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Services\EsewaGateway;
use App\Services\MembershipPayments;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BillingController extends Controller
{
    public function checkout(Product $product, MembershipPayments $payments)
    {
        abort_unless($payments->available() && $product->is_active, 404);

        return view('billing.checkout', ['product' => $product, 'key' => (string) Str::uuid(), 'methods' => $payments->methods()]);
    }

    public function store(Request $request, Product $product, MembershipPayments $payments)
    {
        $data = $request->validate(['key' => 'required|uuid', 'provider' => 'required|in:esewa,khalti']);
        $order = $payments->create($request->user(), $product, $data['key'], $data['provider']);

        return redirect()->route('billing.show', $order);
    }

    public function show(Order $order)
    {
        abort_unless($order->user_id === auth()->id(), 404);

        return view('billing.order', ['order' => $order, 'esewaForm' => $order->provider === 'esewa' && $order->status === 'pending' && app(MembershipPayments::class)->available('esewa') && app(MembershipPayments::class)->canVerify($order) ? app(EsewaGateway::class)->form($order) : null]);
    }

    public function verify(Request $request, Order $order, MembershipPayments $payments)
    {
        abort_unless($order->user_id === auth()->id(), 404);
        abort_unless($payments->canVerify($order), 503);
        if ($order->provider === 'esewa' && $request->has('data')) {
            $data = $request->input('data');
            abort_unless(is_string($data) && app(EsewaGateway::class)->validCallback($order, $data), 422, 'The payment return could not be verified. Check this order from your order history.');
        }
        $payments->verify($order);

        return redirect()->route('billing.show', $order)->with('status', 'Payment status checked. Access is granted only after confirmation from your payment provider.');
    }

    public function history()
    {
        return view('billing.history', ['orders' => Order::where('user_id', auth()->id())->latest()->paginate(15)]);
    }
}
