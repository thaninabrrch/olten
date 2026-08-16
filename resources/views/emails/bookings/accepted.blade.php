<html>
<head>
    <meta charset="UTF-8">
</head>
<body>

    <h2>Bonne nouvelle {{ $booking->user->firstname ?? '' }} 🎉</h2>

    <p>
        Votre réservation pour <strong>{{ $booking->ad->title }}</strong> a été confirmée.
    </p>

    <p>
        📅 Du {{ $booking->start_date->format('d/m/Y') }}
        au {{ $booking->end_date->format('d/m/Y') }}
    </p>

    <p>
        💰 Montant total : {{ $booking->total_price }} €
    </p>

    <p>Merci pour votre confiance 🙌</p>

</body>
</html>