@extends('emails.layout')

@section('content')
<h2 style="color: #333; margin-top: 0;">Inscription enregistrée !</h2>

<p>Bonjour {{ $registration->pilot->user->name }},</p>

<p>Votre inscription à la course <strong>{{ $registration->race->name }}</strong> a bien été enregistrée.</p>

<div class="info-box">
    <h3 style="margin-top: 0; color: #2196F3;">📋 Détails de votre inscription</h3>
    <div class="detail-line">
        <span class="detail-label">Course :</span>
        <span>{{ $registration->race->name }}</span>
    </div>
    <div class="detail-line">
        <span class="detail-label">Date :</span>
        <span>{{ $registration->race->race_date->format('d/m/Y') }}</span>
    </div>
    <div class="detail-line">
        <span class="detail-label">Lieu :</span>
        <span>{{ $registration->race->location }}</span>
    </div>
    <div class="detail-line">
        <span class="detail-label">Véhicule :</span>
        <span>{{ $registration->car->model }} ({{ $registration->car->license_plate }})</span>
    </div>
    <div class="detail-line">
        <span class="detail-label">Statut :</span>
        <span>{{ $registration->status === 'PENDING_PAYMENT' ? 'En attente de paiement' : 'En attente de validation' }}</span>
    </div>
</div>

@if($registration->status === 'PENDING_PAYMENT')
<div class="warning-box">
    <h3 style="margin-top: 0; color: #856404;">⚠️ Paiement requis</h3>
    <p>Votre inscription sera validée après réception du paiement de l'engagement.</p>
    <p style="margin-bottom: 0;"><strong>Montant :</strong> {{ config('stripe.registration_fee_cents') / 100 }} {{ config('stripe.currency') }}</p>
</div>

<div style="text-align: center;">
    <a href="{{ route('pilot.registrations.payment', $registration) }}" class="button">
        💳 Payer mon engagement
    </a>
</div>
@endif

<h3 style="color: #333; margin-top: 30px;">📅 Prochaines étapes</h3>
<ol>
    <li>Paiement de l'engagement ({{ config('stripe.registration_fee_cents') / 100 }}€)</li>
    <li>Validation de votre inscription par l'organisation</li>
    <li><strong>Validation technique le samedi à 14h</strong> (vérifications administratives et techniques)</li>
    <li>Signature de la feuille d'engagement</li>
    <li>Réception de votre E-Card avec QR code</li>
</ol>

<div class="info-box">
    <h3 style="margin-top: 0; color: #2196F3;">🔔 Rappel important</h3>
    <p style="margin-bottom: 0;">
        <strong>Rendez-vous vérifications administratives et techniques :</strong><br>
        Samedi {{ $registration->race->race_date->subDay()->format('d/m/Y') }} à 14h00<br>
        Lieu : {{ $registration->race->location }}
    </p>
</div>

<p>Vous recevrez un email à chaque étape de votre inscription.</p>

<p style="margin-top: 30px;">
    Sportivement,<br>
    <strong>L'équipe Run200</strong>
</p>
@endsection
