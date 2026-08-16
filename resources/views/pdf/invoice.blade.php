<h1>Facture #{{ $sale->id }}</h1>

<p><strong>Produit :</strong> {{ $sale->product->name }}</p>
<p><strong>Acheteur :</strong> {{ $sale->buyer->fullname ?? $sale->buyer->email }}</p>
<p><strong>Vendeur :</strong> {{ $sale->seller->fullname ?? $sale->seller->email }}</p>

<p><strong>Quantité :</strong> {{ $sale->quantity }}</p>
<p><strong>Prix unitaire :</strong> {{ number_format($sale->product->price, 2) }} €</p>
<p><strong>Total :</strong> {{ number_format($sale->total_price, 2) }} €</p>

<p><strong>Date :</strong> {{ $sale->created_at->format('d/m/Y') }}</p>