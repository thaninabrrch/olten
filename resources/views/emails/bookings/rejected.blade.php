<html>
<head>
    <meta charset="UTF-8">
    <title>Réservation refusée</title>
</head>
<body>

    <h2>Bonjour {{ $booking->user->firstname ?? '' }},</h2>

    <p>
        Votre demande de réservation pour l’annonce :
        <strong>{{ $booking->ad->title }}</strong>
        a été refusée.
    </p>

    <p>
        📅 Du {{ $booking->start_date->format('d/m/Y') }}
        au {{ $booking->end_date->format('d/m/Y') }}
    </p>

    <p>
        💳 Si un paiement a été effectué, le remboursement sera traité automatiquement.
    </p>

    <p>Merci pour votre compréhension.</p>

</body>
</html>