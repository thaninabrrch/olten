<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau produit</title>
</head>

<body style="margin:0; padding:0; background:#f5f5f5; font-family:Arial, sans-serif;">

<div style="max-width:600px; margin:30px auto; background:#ffffff; border-radius:10px; overflow:hidden;">

    <div style="background:#ff5a1f; padding:25px; text-align:center;">
        <h1 style="margin:0; color:#ffffff;">
            Olten
        </h1>
    </div>

    <div style="padding:30px;">

        <h2 style="color:#333;">
            Nouveau produit disponible 🎉
        </h2>

        <p style="color:#555; font-size:15px;">
            Bonjour,
        </p>

        <p style="color:#555; font-size:15px;">
            Un nouveau produit correspondant à l'une de vos catégories
            de notification vient d'être publié sur Olten.
        </p>

        <div style="margin:25px 0; padding:20px; background:#f8f8f8; border-radius:8px;">

            <h3 style="margin-top:0; color:#333;">
                {{ $product->name }}
            </h3>

            @if($product->description)
                <p style="color:#666;">
                    {{ Str::limit($product->description, 200) }}
                </p>
            @endif

            <p style="font-size:18px; font-weight:bold; color:#ff5a1f;">
                {{ number_format($product->price, 2, ',', ' ') }} €
            </p>

        </div>

        <div style="text-align:center; margin:30px 0;">

            <a href="{{ route('products.show', $product->id) }}"
               style="display:inline-block;
                      padding:12px 25px;
                      background:#ff5a1f;
                      color:#ffffff;
                      text-decoration:none;
                      border-radius:6px;
                      font-weight:bold;">
                Voir le produit
            </a>

        </div>

        <p style="color:#888; font-size:13px;">
            Vous recevez cet email car vous avez activé les notifications
            pour cette catégorie.
        </p>

        <p style="color:#555;">
            À bientôt sur Olten 👋
        </p>

    </div>

    <div style="padding:15px; background:#f5f5f5; text-align:center;">
        <p style="margin:0; color:#999; font-size:12px;">
            © {{ date('Y') }} Olten — Tous droits réservés.
        </p>
    </div>

</div>

</body>
</html>