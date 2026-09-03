<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Nouvelle commande - Olten</title>

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
            font-weight: 700;
            color: #222222;
        }

        .text {
            margin: 0 0 20px;
            font-size: 15px;
            line-height: 1.7;
            color: #555555;
        }

        .order-card {
            margin: 25px 0;
            padding: 22px;
            background-color: #fff7f3;
            border: 1px solid #ffe0d2;
            border-radius: 10px;
        }

        .order-title {
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

        .total {
            margin-top: 12px;
            padding-top: 15px;
            border-top: 1px solid #ffd5c4;
            font-size: 17px;
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

        .premium-note {
            margin-top: 25px;
            padding: 14px 16px;
            background-color: #fafafa;
            border-left: 4px solid #ff5a1f;
            font-size: 13px;
            line-height: 1.6;
            color: #666666;
        }

        .footer {
            background-color: #222222;
            padding: 25px 20px;
            text-align: center;
        }

        .footer-text {
            margin: 0 0 8px;
            font-size: 12px;
            line-height: 1.6;
            color: #aaaaaa;
        }

        .footer-brand {
            color: #ffffff;
            font-weight: bold;
        }

        @media only screen and (max-width: 600px) {
            .content {
                padding: 30px 20px !important;
            }

            .title {
                font-size: 22px !important;
            }

            .order-card {
                padding: 18px !important;
            }
        }
    </style>
</head>

<body>

<table width="100%" cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td align="center">

            <table
                class="email-container"
                width="100%"
                cellpadding="0"
                cellspacing="0"
                border="0"
            >

                {{-- HEADER --}}
                <tr>
                    <td class="header">

                        <a
                            href="{{ config('app.url') }}"
                            class="logo"
                        >
                            OLTEN
                        </a>

                    </td>
                </tr>

                {{-- CONTENT --}}
                <tr>
                    <td class="content">

                        <h1 class="title">
                            🛒 Nouvelle commande !
                        </h1>

                        <p class="text">
                            Bonjour {{ $seller->firstname ?? $seller->name }},
                        </p>

                        <p class="text">
                            Vous avez reçu une nouvelle commande
                            pour votre produit sur <strong>Olten</strong>.
                        </p>

                        {{-- ORDER CARD --}}
                        <div class="order-card">

                            <p class="order-title">
                                Détails de la commande
                            </p>

                            <div class="info-row">
                                <span class="label">
                                    Produit :
                                </span>

                                <span class="value">
                                    {{ $sale->product->name }}
                                </span>
                            </div>

                            <div class="info-row">
                                <span class="label">
                                    Quantité :
                                </span>

                                <span class="value">
                                    {{ $sale->quantity }}
                                </span>
                            </div>

                            @if($sale->delivery_requested)

                                <div class="info-row">
                                    <span class="label">
                                        🚚 Livraison :
                                    </span>

                                    <span class="value">
                                        Demandée
                                    </span>
                                </div>

                                @if($sale->delivery_address)
                                    <div class="info-row">
                                        <span class="label">
                                            📍 Adresse :
                                        </span>

                                        <span class="value">
                                            {{ $sale->delivery_address }}
                                        </span>
                                    </div>
                                @endif

                            @endif

                            @if($sale->phone)
                                <div class="info-row">
                                    <span class="label">
                                        📞 Téléphone :
                                    </span>

                                    <span class="value">
                                        {{ $sale->phone }}
                                    </span>
                                </div>
                            @endif

                            <div class="info-row total">
                                <span class="label">
                                    Montant total :
                                </span>

                                <span class="value">
                                    {{ number_format($sale->total_price, 2, ',', ' ') }} €
                                </span>
                            </div>

                        </div>

                        {{-- BUTTON --}}
                        <div class="button-container">

                            <a
                                href="{{ url('/vendeur/commandes-clients') }}"
                                class="button"
                            >
                                Voir la commande
                            </a>

                        </div>

                        {{-- PREMIUM MESSAGE --}}
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
                            Merci de ne pas y répondre.
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
