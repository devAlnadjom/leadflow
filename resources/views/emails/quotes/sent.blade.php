<x-mail::message>
# Bonjour {{ $quote->leadRecord->name ?? 'Client' }},

Voici votre devis **{{ $quote->quote_number }}** pour un montant de **{{ number_format($quote->total, 2, ',', ' ') }}$**.

<x-mail::panel>
Le devis est valable jusqu'au {{ $quote->expire_at ? \Carbon\Carbon::parse($quote->expire_at)->format('d/m/Y') : 'Non spécifié' }}.
</x-mail::panel>

Vous pouvez consulter les détails du devis, l'accepter ou le refuser en cliquant sur le bouton ci-dessous :

<x-mail::button :url="route('quotes.public.show', $quote->public_uid)">
Consulter et Répondre au Devis
</x-mail::button>

Si vous avez des questions, n'hésitez pas à nous contacter.

Cordialement,<br>
{{ $quote->company->name ?? config('app.name') }}
</x-mail::message>
