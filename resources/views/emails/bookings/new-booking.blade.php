<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Nouvelle réservation - Olten</title>

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

        .booking-card {
            margin: 25px 0;
            padding: 22px;
            background-color: #fff7f3;
            border: 1px solid #ffe0d2;
            border-radius: 10px;
        }

        .booking-title {
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

            .booking-card {
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
                            🎉 Nouvelle réservation !
                        </h1>

                        <p class="text">
                            Bonjour {{ $owner->firstname ?? $owner->name }},
                        </p>

                        <p class="text">
                            Vous avez reçu une nouvelle réservation
                            pour votre annonce sur <strong>Olten</strong>.
                        </p>

                        {{-- BOOKING CARD --}}
                        <div class="booking-card">

                            <p class="booking-title">
                                Détails de la réservation
                            </p>

                            <div class="info-row">
                                <span class="label">
                                    Annonce :
                                </span>

                                <span class="value">
                                    {{ $booking->ad->title }}
                                </span>
                            </div>

                            <div class="info-row">
                                <span class="label">
                                    📅 Début :
                                </span>

                                <span class="value">
                                    {{ \Carbon\Carbon::parse($booking->start_date)->format('d/m/Y') }}
                                </span>
                            </div>

                            <div class="info-row">
                                <span class="label">
                                    📅 Fin :
                                </span>

                                <span class="value">
                                    {{ \Carbon\Carbon::parse($booking->end_date)->format('d/m/Y') }}
                                </span>
                            </div>

                            @if($booking->delivery_requested)
                                <div class="info-row">
                                    <span class="label">
                                        🚚 Livraison :
                                    </span>

                                    <span class="value">
                                        Demandée
                                    </span>
                                </div>

                                @if($booking->delivery_address)
                                    <div class="info-row">
                                        <span class="label">
                                            📍 Adresse de livraison :
                                        </span>

                                        <span class="value">
                                            {{ $booking->delivery_address }}
                                        </span>
                                    </div>
                                @endif
                            @endif

                            @if(isset($booking->total_price))
                                <div class="info-row total">
                                    <span class="label">
                                        Montant total :
                                    </span>

                                    <span class="value">
                                        {{ number_format($booking->total_price, 2, ',', ' ') }} €
                                    </span>
                                </div>
                            @endif

                        </div>

                        {{-- BUTTON --}}
                        <div class="button-container">

                            <a
                                href="{{ url('/mes-reservations-recues') }}"
                                class="button"
                            >
                                Voir la réservation
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
