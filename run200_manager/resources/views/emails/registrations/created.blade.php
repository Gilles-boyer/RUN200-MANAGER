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
        <span class="status-badge status-pending">{{ $registration->status === 'PENDING_PAYMENT' ? 'En attente de paiement' : 'En attente de validation' }}</span>
    </div>
</div>

@if($registration->status === 'PENDING_PAYMENT')
<div class="warning-box">
    <h3>⚠️ Paiement requis</h3>
    <p>Votre inscription sera validée après réception du paiement de l'engagement.</p>
    <p style="margin-bottom: 0;"><strong>Montant :</strong> {{ config('stripe.registration_fee_cents') / 100 }} {{ config('stripe.currency') }}</p>
</div>

<div style="text-align: center;">
    <a href="{{ route('pilot.registrations.payment', $registration) }}" class="button">
        💳 Payer mon engagement
    </a>
</div>
@endif

<div class="racing-card">
    <h3 style="color: #FFFFFF; margin-top: 0;">📅 Prochaines étapes</h3>
    <ol>
        <li>Paiement de l'engagement ({{ config('stripe.registration_fee_cents') / 100 }}€)</li>
        <li>Validation de votre inscription par l'organisation</li>
        <li><strong>Validation technique le samedi à 14h</strong> (vérifications administratives et techniques)</li>
        <li>Signature de la feuille d'engagement</li>
        <li>Réception de votre E-Card avec QR code</li>
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
