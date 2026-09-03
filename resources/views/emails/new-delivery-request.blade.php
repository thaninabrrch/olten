<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Nouvelle demande de livraison - Olten</title>

    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            font-family: Arial, Helvetica, sans-serif;
            color: #333333;
        }

        table {
            border-collapse: collapse;
        }

        .email-container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
        }

        .header {
            background-color: #ff5a1f;
            padding: 28px 20px;
            text-align: center;
        }

        .logo {
            color: #ffffff;
            font-size: 32px;
            font-weight: bold;
            text-decoration: none;
            letter-spacing: 1px;
        }

        .content {
            background-color: #ffffff;
            padding: 40px 35px;
        }

        .title {
            margin: 0 0 15px;
            font-size: 25px;
            color: #222222;
        }

        .text {
            margin: 0 0 20px;
            font-size: 15px;
            line-height: 1.7;
            color: #555555;
        }

        .delivery-card {
            margin: 25px 0;
            padding: 22px;
            background-color: #fff7f3;
            border: 1px solid #ffe0d2;
            border-radius: 10px;
        }

        .delivery-title {
            margin: 0 0 18px;
            font-size: 18px;
            font-weight: bold;
            color: #222222;
        }

        .info-row {
            padding: 7px 0;
            font-size: 14px;
            line-height: 1.5;
        }

        .label {
            font-weight: bold;
            color: #333333;
        }

        .value {
            color: #555555;
        }

        .premium-note {
            margin-top: 25px;
            padding: 14px 16px;
            background-color: #fafafa;
            border-left: 4px solid #ff5a1f;
            font-size: 13px;
            line-height: 1.6;
            color: #666666;
        }

        .button-container {
            text-align: center;
            padding: 10px 0 25px;
        }

        .button {
            display: inline-block;
            padding: 14px 28px;
            background-color: #ff5a1f;
            color: #ffffff !important;
            text-decoration: none;
            font-size: 15px;
            font-weight: bold;
            border-radius: 7px;
        }

        .footer {
            background-color: #222222;
            padding: 25px 20px;
            text-align: center;
        }

        .footer-text {
            margin: 0 0 8px;
            font-size: 12px;
            color: #aaaaaa;
        }

        .footer-brand {
            color: #ffffff;
            font-weight: bold;
        }
    </style>
</head>

<body>

<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center">

            <table class="email-container" width="100%" cellpadding="0" cellspacing="0">

                {{-- HEADER --}}
                <tr>
                    <td class="header">
                        <a href="{{ config('app.url') }}" class="logo">
                            OLTEN
                        </a>
                    </td>
                </tr>

                {{-- CONTENT --}}
                <tr>
                    <td class="content">

                        <h1 class="title">
                            🚚 Nouvelle demande de livraison
                        </h1>

                        <p class="text">
                            Bonjour {{ $owner->firstname ?? $owner->name }},
                        </p>

                        <p class="text">
                            Un livreur vient de vous envoyer une demande
                            pour effectuer une livraison sur Olten.
                        </p>

                        <div class="delivery-card">

                            <p class="delivery-title">
                                Détails de la livraison
                            </p>

                            @if($isProduct)

                                <div class="info-row">
                                    <span class="label">
                                        Produit :
                                    </span>

                                    <span class="value">
                                        {{ $deliveryRequest->productSale->product->name }}
                                    </span>
                                </div>

                                <div class="info-row">
                                    <span class="label">
                                        Quantité :
                                    </span>

                                    <span class="value">
                                        {{ $deliveryRequest->productSale->quantity }}
                                    </span>
                                </div>

                            @else

                                <div class="info-row">
                                    <span class="label">
                                        Annonce :
                                    </span>

                                    <span class="value">
                                        {{ $deliveryRequest->booking->ad->title }}
                                    </span>
                                </div>

                                <div class="info-row">
                                    <span class="label">
                                        Réservation :
                                    </span>

                                    <span class="value">
                                        Du
                                        {{ \Carbon\Carbon::parse($deliveryRequest->booking->start_date)->format('d/m/Y') }}
                                        au
                                        {{ \Carbon\Carbon::parse($deliveryRequest->booking->end_date)->format('d/m/Y') }}
                                    </span>
                                </div>

                            @endif

                            <div class="info-row">
                                <span class="label">
                                    Statut :
                                </span>

                                <span class="value">
                                    En attente
                                </span>
                            </div>

                        </div>

                        <div class="button-container">

                            <a
                                href="{{ url('/demandes-de-livraison') }}"
                                class="button"
                            >
                                Voir la demande
                            </a>

                        </div>

                        <div class="premium-note">
                            Vous recevez cette notification car vous bénéficiez
                            d'un <strong>abonnement Premium</strong> sur Olten.
                        </div>

                    </td>
                </tr>

                {{-- FOOTER --}}
                <tr>
                    <td class="footer">

                        <p class="footer-text">
                            Merci d'utiliser
                            <span class="footer-brand">Olten</span>.
                        </p>

                        <p class="footer-text">
                            Cet e-mail a été envoyé automatiquement.
                        </p>

                        <p class="footer-text">
                            © {{ date('Y') }} Olten. Tous droits réservés.
                        </p>

                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>