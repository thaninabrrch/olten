<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">

    <title>Nouvelle annonce</title>
</head>

<body style="margin:0; padding:0; background:#f5f5f5; font-family:Arial, sans-serif;">

    <div style="max-width:600px; margin:30px auto; background:white; border-radius:12px; overflow:hidden;">

        <div style="background:#ff5a1f; padding:25px; text-align:center;">
            <h1 style="color:white; margin:0;">
                Nouvelle annonce 🎉
            </h1>
        </div>

        <div style="padding:30px;">

            <h2 style="margin-top:0;">
                {{ $ad->title }}
            </h2>

            @if($ad->summary)
                <p style="color:#555;">
                    {{ $ad->summary }}
                </p>
            @endif

            <p>
                Une nouvelle annonce correspondant à une catégorie
                que vous suivez vient d'être publiée sur Olten.
            </p>

            <div style="margin:25px 0; padding:20px; background:#f8f8f8; border-radius:8px;">

                <p>
                    <strong>Prix :</strong>
                    {{ number_format($ad->price_per_day, 2, ',', ' ') }} € / jour
                </p>

                <p>
                    <strong>Disponible du :</strong>
                    {{ \Carbon\Carbon::parse($ad->available_from)->format('d/m/Y') }}
                </p>

                <p>
                    <strong>Jusqu'au :</strong>
                    {{ \Carbon\Carbon::parse($ad->available_until)->format('d/m/Y') }}
                </p>

            </div>

            <div style="text-align:center; margin-top:30px;">

                <a href="{{ route('ads.show', $ad->id) }}"
                style="display:inline-block;
                        padding:12px 25px;
                        background:#ff5a1f;
                        color:#ffffff;
                        text-decoration:none;
                        border-radius:6px;
                        font-weight:bold;">
                    Voir l'annonce
                </a>

            </div>

            <p style="margin-top:35px; color:#888; font-size:13px;">
                Vous recevez cet email car vous avez activé les notifications
                pour cette catégorie sur Olten.
            </p>

        </div>

    </div>

</body>
</html>