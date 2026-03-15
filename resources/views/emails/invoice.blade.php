<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Votre Facture {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 8px; }
        .header { text-align: center; margin-bottom: 20px; }
        .content { margin-bottom: 20px; }
        .footer { text-align: center; font-size: 0.9em; color: #777; margin-top: 30px; padding-top: 10px; border-top: 1px solid #eee; }
        .btn { display: inline-block; padding: 10px 20px; background-color: {{ $invoice->company->primary_color ?? '#4f46e5' }}; color: #fff; text-decoration: none; border-radius: 5px; font-weight: bold; }
        .summary-box { background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .total { font-size: 1.2em; font-weight: bold; color: {{ $invoice->company->primary_color ?? '#4f46e5' }}; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Nouvelle facture de {{ $invoice->company->name ?? 'Notre Entreprise' }}</h2>
        </div>
        
        <div class="content">
            <p>Bonjour {{ $invoice->client->name ?? 'Cher Client' }},</p>
            
            <p>Vous trouverez ci-dessous le récapitulatif de votre facture <strong>N°{{ $invoice->invoice_number }}</strong> émise le <strong>{{ $invoice->issue_date ? $invoice->issue_date->format('d/m/Y') : '' }}</strong>.</p>
            
            <div class="summary-box">
                <p><strong>Montant Total TTC :</strong> <span class="total">{{ number_format($invoice->total, 2, ',', ' ') }} {{ $invoice->company->settings->currency ?? '$' }}</span></p>
                @if($invoice->due_date)
                    <p style="color: #666; font-size: 0.9em;">À régler avant le : {{ $invoice->due_date->format('d/m/Y') }}</p>
                @endif
            </div>
            
            <p style="text-align: center; margin: 30px 0;">
                <a href="{{ url('/facture/' . $invoice->public_uid) }}" class="btn">Consulter la facture</a>
            </p>
            
            <p>Ce lien sécurisé vous permet de la consulter, de l'imprimer ou de la télécharger au format PDF à tout moment.</p>
            <p>Merci pour votre confiance.</p>
        </div>
        
        <div class="footer">
            <p>Créé et envoyé par {{ $invoice->company->name }} <br> via <strong>clientux</strong></p>
        </div>
    </div>
</body>
</html>
