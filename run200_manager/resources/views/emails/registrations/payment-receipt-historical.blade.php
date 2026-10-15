@extends('emails.layout')

@section('content')
<h2>Votre reçu de paiement</h2>

<p>Bonjour {{ $receipt->payer_name }},</p>

<p>Vous trouverez en pièce jointe le reçu n° {{ $receipt->number }} correspondant au paiement de l'engagement à la course {{ $receipt->race_name }}.</p>

<div class="success-box">
    <p><strong>Paiement initial :</strong> {{ $receipt->formatted_amount }}</p>
    @if($receipt->refund_amount_cents_at_issue > 0)
        <p><strong>Remboursé à l'émission :</strong> {{ number_format($receipt->refund_amount_cents_at_issue / 100, 2, ',', ' ') }} {{ $receipt->currency }}</p>
    @endif
    <p><strong>Date du paiement :</strong> {{ $receipt->paid_at?->format('d/m/Y') ?? 'Non enregistrée dans le dossier historique' }}</p>
</div>

<p>Ce reçu atteste un paiement et ne constitue pas un reçu fiscal de don.</p>

<div class="signature"><p>L'équipe Run200</p></div>
@endsection
