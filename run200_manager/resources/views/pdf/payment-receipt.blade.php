<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Reçu de paiement {{ $receipt->number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 12px; line-height: 1.5; }
        h1 { font-size: 22px; color: #b91c1c; margin-bottom: 4px; }
        h2 { font-size: 13px; margin-top: 28px; }
        .muted { color: #6b7280; }
        .card { border: 1px solid #d1d5db; border-radius: 6px; padding: 16px; margin-top: 20px; }
        .row { margin: 8px 0; }
        .label { display: inline-block; width: 180px; color: #4b5563; }
        .amount { font-size: 19px; font-weight: bold; }
        .footer { margin-top: 48px; padding-top: 12px; border-top: 1px solid #d1d5db; font-size: 10px; color: #6b7280; }
    </style>
</head>
<body>
    <h1>Reçu de paiement</h1>
    <div class="muted">Numéro {{ $receipt->number }} · Émis le {{ $receipt->issued_at->format('d/m/Y') }}</div>

    <h2>Association émettrice</h2>
    <strong>{{ $receipt->issuer_name }}</strong><br>
    {{ $receipt->issuer_address }}<br>
    SIRET {{ $receipt->issuer_siret }}<br>
    {{ $receipt->issuer_email }} · {{ $receipt->issuer_phone }}

    <div class="card">
        <div class="row"><span class="label">Pilote concerné</span><strong>{{ $receipt->payer_name }}</strong></div>
        <div class="row"><span class="label">Objet</span>Engagement à la course {{ $receipt->race_name }}</div>
        @if($receipt->race_date)
            <div class="row"><span class="label">Date de la course</span>{{ $receipt->race_date->format('d/m/Y') }}</div>
        @endif
        <div class="row"><span class="label">Mode de paiement</span>{{ \App\Domain\Payment\Enums\PaymentMethod::tryFrom($receipt->payment_method)?->label() ?? $receipt->payment_method }}</div>
        <div class="row"><span class="label">Date du paiement</span>{{ $receipt->paid_at?->format('d/m/Y H:i') ?? 'Non enregistrée dans le dossier historique' }}</div>
        <div class="row"><span class="label">Paiement initial</span><span class="amount">{{ $receipt->formatted_amount }}</span></div>
        @if($receipt->refund_amount_cents_at_issue > 0)
            <div class="row"><span class="label">Remboursé à l'émission</span>{{ number_format($receipt->refund_amount_cents_at_issue / 100, 2, ',', ' ') }} {{ $receipt->currency }}</div>
            <div class="row"><span class="label">Solde à l'émission</span>{{ number_format(($receipt->amount_cents - $receipt->refund_amount_cents_at_issue) / 100, 2, ',', ' ') }} {{ $receipt->currency }}</div>
        @endif
    </div>

    <div class="footer">Ce document atteste un paiement d'engagement. Il ne constitue pas un reçu fiscal de don. Sa situation est arrêtée à la date d'émission ; tout remboursement ultérieur n'est pas reporté sur ce reçu.</div>
</body>
</html>
