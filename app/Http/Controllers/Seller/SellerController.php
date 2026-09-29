<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\ProductSale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class SellerController extends Controller
{
    public function sales(Request $request)
    {
        $query = ProductSale::with(['product', 'buyer'])
                            ->where('user_id', Auth::id())
                            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;

            // whereLike : insensible à la casse sur les trois moteurs (ILIKE
            // n'existe que sous PostgreSQL et faisait planter la recherche en MySQL)
            $query->where(function ($q) use ($search) {

                $q->whereHas('product', function ($q2) use ($search) {
                    $q2->whereLike('name', "%{$search}%");
                });

                $q->orWhereHas('buyer', function ($q3) use ($search) {
                    $q3->whereLike('firstname', "%{$search}%")
                        ->orWhereLike('lastname', "%{$search}%")
                        ->orWhereLike('email', "%{$search}%");
                });

                $q->orWhereLike('status', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $sales = $query->paginate(10)->withQueryString();

        return view('pages.seller.sales.list', compact('sales'));
    }

    public function showSale(ProductSale $sale)
    {
        return view('pages.seller.sales.show', compact('sale'));
    }

    public function markAsDelivered(ProductSale $sale)
    {
        if ($sale->seller_id !== auth()->id()) {
            abort(403);
        }

        $sale->update([
            'status' => 'delivered'
        ]);

        return back()->with('success', 'Commande marquée comme livrée ✅');
    }

    public function invoice(ProductSale $sale)
    {
        $sale->load(['product', 'buyer', 'seller']);
        $pdf = Pdf::loadView('pdf.invoice', compact('sale'));

        return $pdf->download('facture-'.$sale->id.'.pdf');
    }

    public function markAsPaid(ProductSale $sale)
    {
        if ($sale->product->user_id !== auth()->id()) {
            abort(403);
        }

        $sale->update([
            'status' => 'paid'
        ]);

        return back()->with('success', 'Commande marquée comme payée ✅');
    }
}