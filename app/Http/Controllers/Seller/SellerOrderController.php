<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\ProductSale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Refund;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderCancelledMail;
use App\Mail\OrderAcceptedMail;

class SellerOrderController extends Controller
{
    public function orders(Request $request)
    {
        $query = ProductSale::with(['product', 'buyer'])
                            ->where('buyer_id', Auth::id());

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('product', function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
            });
        }

        if ($request->filled('status')) {
            $query->where('order_status', $request->input('status'));
        }

        $orders = $query->latest()->paginate(25)->withQueryString();

        return view('pages.seller.orders.index', compact('orders'));
    }
    
    public function clientOrders(Request $request)
    {
        $query = ProductSale::with(['product', 'seller'])
                            ->where('user_id', auth()->id());

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('product', function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
            });
        }

        if ($request->filled('status')) {
            $query->where('order_status', $request->input('status'));
        }

        $orders = $query->latest()->paginate(25)->withQueryString();

        return view('pages.seller.orders.client_orders', compact('orders'));
    }

    public function cancelOrder($id)
    {
        $order = ProductSale::where('user_id', auth()->id())->findOrFail($id);

        if (in_array($order->order_status, ['delivered', 'cancelled', 'shipped'])) {
            return back()->with('error', 'Impossible d’annuler cette commande');
        }

        $product = $order->product;

        if ($product) {
            $product->stock += $order->quantity;
            $product->save();
        }

        if ($order->status === 'paid') {

            if (!$order->payment_intent_id) {
                return back()->with('error', 'Impossible de rembourser : PaymentIntent manquant.');
            }

            Stripe::setApiKey(config('services.stripe.secret'));

            Refund::create([
                'payment_intent' => $order->payment_intent_id,
            ]);

            $order->status = 'refunded';
        }

        $order->order_status = 'cancelled';

        if ($order->buyer && $order->buyer->email) {
            Mail::to($order->buyer->email)->send(new OrderCancelledMail($order));
        }

        $order->save();

        return back()->with('success', 'Commande annulée et remboursée avec succès');
    }

    public function confirmOrder($id)
    {
        $order = ProductSale::where('user_id', auth()->id())
                            ->findOrFail($id);

        if (in_array($order->order_status, ['delivered', 'cancelled', 'shipped'])) {
            return back()->with('error', 'Impossible de confirmer cette commande');
        }

        $order->update([
            'order_status' => 'confirmed',
        ]);

        Mail::to($order->buyer->email)->send(
            new OrderAcceptedMail($order)
        );

        return back()->with('success', 'Commande acceptée avec succès.');
    }

    public function show(ProductSale $order)
    {
        $order->load([
            'product.images',
            'seller',
            'delivery.deliveryPerson'
        ]);

        return view('pages.seller.orders.show', compact('order'));
    }
}