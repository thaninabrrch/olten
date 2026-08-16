<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Commande acceptée</title>
</head>
<body>
    <h2>Bonjour {{ $order->buyer->fullname ?? $order->buyer->name }},</h2>

    <p>
        Bonne nouvelle ! Votre commande a été acceptée par le vendeur.
    </p>

    <p>
        <strong>Produit :</strong>
        {{ $order->product->name }}
    </p>

    <p>
        <strong>Quantité :</strong>
        {{ $order->quantity }}
    </p>

    <p>
        <strong>Montant :</strong>
        {{ number_format($order->total_price, 2, ',', ' ') }} €
    </p>

    @if($order->delivery_requested)
        <p>
            Une demande de livraison a été enregistrée. Un livreur pourra bientôt prendre en charge votre commande.
        </p>
    @endif

    <p>
        Vous pouvez consulter l'évolution de votre commande depuis votre espace personnel.
    </p>

    <p>
        Merci pour votre confiance.
    </p>
</body>
</html>