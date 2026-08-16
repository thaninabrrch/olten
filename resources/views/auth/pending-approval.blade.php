<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Compte en attente</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">

    <div class="bg-white p-10 rounded-2xl shadow-lg text-center max-w-md">
        
        <div class="text-yellow-500 text-5xl mb-4">
            ⏳
        </div>

        <h1 class="text-2xl font-bold mb-2">
            @php
                if (auth()->user()->hasVerifiedEmail()) {
                    $message = "Votre adresse e-mail a été vérifiée avec succès. Votre compte est maintenant en attente d'approbation par l'administrateur.";
                } else {
                    $message = "Votre compte est en attente de vérification de votre adresse e-mail.";
                }
            @endphp
            {{ $message }}
        </h1>

        <p class="text-gray-600 mb-6">
            Votre compte n’est pas encore approuvé par l’administrateur.  
            Vous ne pouvez pas encore accéder à cette fonctionnalité.
        </p>

        <a href="{{ url('/') }}"
           class="bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700">
            Retour à l’accueil
        </a>

    </div>

</body>
</html>