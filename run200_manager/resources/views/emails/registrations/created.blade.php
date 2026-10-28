@extends('emails.layout')

@section('content')
<h2>📝 Inscription enregistrée !</h2>

<p>Bonjour <strong>{{ $registration->pilot->user->name }}</strong>,</p>

<p>Votre inscription à la course <strong>{{ $registration->race->name }}</strong> a bien été enregistrée.</p>

<div class="racing-card">
    <div class="racing-card-header">
        <h3 class="racing-card-title">📋 Détails de votre inscription</h3>
    </div>
    <div class="detail-line">
        <span class="detail-label">Course</span>
        <span class="detail-value">{{ $registration->race->name }}</span>
    </div>
    <div class="detail-line">
        <span class="detail-label">Date</span>
        <span class="detail-value">{{ $registration->race->race_date->format('d/m/Y') }}</span>
    </div>
    <div class="detail-line">
        <span class="detail-label">Lieu</span>
        <span class="detail-value">{{ $registration->race->location }}</span>
    </div>
    <div class="detail-line">
        <span class="detail-label">Véhicule</span>
        <span class="detail-value">{{ $registration->car->model }} ({{ $registration->car->license_plate }})</span>
    </div>
    <div class="detail-line">
        <span class="detail-label">Statut</span>
        <span class="detail-value">Consultez votre espace pilote pour connaître l'état à jour.</span>
    </div>
</div>

@php
    $balanceCents = max(0, (int) $registration->race->entry_fee_cents - $registration->getPaidAmountCents());
@endphp

@if($balanceCents > 0 && ! in_array($registration->status, ['REFUSED', 'CANCELLED'], true))
<div class="warning-box">
    <h3>⚠️ Paiement restant dû</h3>
    <p>L'organisateur peut accepter l'inscription avant encaissement. Le règlement reste dû et sa réception sera confirmée séparément lors des VA/VT.</p>
    @if(in_array($registration->status, ['PENDING_PAYMENT', 'ACCEPTED'], true))
        <p>Vous pouvez aussi payer en ligne depuis votre espace pilote.</p>
    @endif
    <p style="margin-bottom: 0;"><strong>Solde :</strong> {{ number_format($balanceCents / 100, 2, ',', ' ') }} €</p>
</div>

@if(in_array($registration->status, ['PENDING_PAYMENT', 'ACCEPTED'], true))
<div style="text-align: center;">
    <a href="{{ route('pilot.registrations.payment', $registration) }}" class="button">
        💳 Payer mon engagement
    </a>
</div>
@endif
@endif

<div class="racing-card">
    <h3 style="color: #FFFFFF; margin-top: 0;">📅 Prochaines étapes</h3>
    <ol>
        @if($balanceCents === 0)
            <li>Règlement de l'engagement enregistré</li>
        @else
            <li>Solde de l'engagement ({{ number_format($balanceCents / 100, 2, ',', ' ') }} €) : à régler séparément</li>
        @endif
        <li>Décision de l'organisation concernant votre inscription</li>
        <li><strong>Validation technique le samedi à 14h</strong> (vérifications administratives et techniques)</li>
        <li>Signature de la feuille d'engagement</li>
        <li>E-Card avec QR code accessible dès l'acceptation de l'inscription</li>
    </ol>
</div>

<div class="info-box">
    <h3>🔔 Rappel important</h3>
    <p style="margin-bottom: 0;">
        <strong>Rendez-vous vérifications administratives et techniques :</strong><br>
        Samedi {{ $registration->race->race_date->subDay()->format('d/m/Y') }} à 14h00<br>
        Lieu : {{ $registration->race->location }}
    </p>
</div>

<p>Vous recevrez un email à chaque étape de votre inscription.</p>

<div class="signature">
    <p>Sportivement,</p>
    <p><strong>L'équipe Run200</strong></p>
</div>
@endsection
