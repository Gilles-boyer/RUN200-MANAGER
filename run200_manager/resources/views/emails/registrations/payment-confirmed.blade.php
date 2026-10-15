@extends('emails.layout')

@section('content')
<h2>Paiement enregistré</h2>

<p>Bonjour {{ $receipt->payer_name }},</p>

<p>Votre paiement pour la course <strong>{{ $receipt->race_name }}</strong> a été enregistré. Votre reçu n° <strong>{{ $receipt->number }}</strong> est joint à ce message au format PDF.</p>

<div class="success-box">
    <h3>Détails du paiement</h3>
    <div class="detail-line">
        <span class="detail-label">Montant</span>
        <span class="detail-value">{{ $receipt->formatted_amount }}</span>
    </div>
    @if($receipt->paid_at)
        <div class="detail-line">
            <span class="detail-label">Date du paiement</span>
            <span class="detail-value">{{ $receipt->paid_at->format('d/m/Y à H:i') }}</span>
        </div>
    @endif
    <div class="detail-line">
        <span class="detail-label">Mode</span>
        <span class="detail-value">{{ \App\Domain\Payment\Enums\PaymentMethod::tryFrom($receipt->payment_method)?->label() ?? $receipt->payment_method }}</span>
    </div>
</div>

<p>Vous pouvez également retrouver ce reçu dans votre espace pilote, rubrique « Mes inscriptions ».</p>

<div style="text-align: center; margin-top: 30px;">
    <a href="{{ route('pilot.registrations.index') }}" class="button">Voir mes inscriptions</a>
</div>

<div class="signature">
    <p>Sportivement,</p>
    <p><strong>L'équipe Run200</strong></p>
</div>
@endsection
