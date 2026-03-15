<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Résumé de vos leads</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.6; max-width: 600px; margin: 0 auto; padding: 20px;">
    
    <h2>Bonjour {{ $user->name }},</h2>

    <p>Vous avez reçu <strong>{{ $leadCount }}</strong> nouveau(x) prospect(s) au cours de la dernière heure via vos widgets clientux.</p>

    <div style="margin: 30px 0;">
        <a href="{{ url('/leads') }}" style="background-color: #4f46e5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold;">
            Gérer mes prospects
        </a>
    </div>

    <p style="font-size: 14px; color: #666; margin-top: 40px; border-top: 1px solid #eee; padding-top: 20px;">
        Cet email récapitulatif a été envoyé car vos préférences de notification sont réglées sur "Envoi groupé".<br>
        Vous pouvez modifier ce comportement dans les paramètres de votre compte.
    </p>
    
</body>
</html>
