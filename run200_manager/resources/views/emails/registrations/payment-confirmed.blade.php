@extends('emails.layout')

@section('content')
<h2 style="color: #28a745; margin-top: 0;">✅ Paiement confirmé !</h2>

<p>Bonjour {{ $payment->registration->pilot->user->name }},</p>

<p>Nous avons bien reçu votre paiement pour la course <strong>{{ $payment->registration->race->name }}</strong>.</p>

<div class="success-box">
    <h3 style="margin-top: 0; color: #155724;">💳 Détails du paiement</h3>
    <div class="detail-line">
        <span class="detail-label">Montant :</span>
        <span>{{ $payment->amount }} {{ $payment->currency }}</span>
    </div>
    <div class="detail-line">
        <span class="detail-label">Date :</span>
        <span>{{ $payment->paid_at->format('d/m/Y à H:i') }}</span>
    </div>
    <div class="detail-line">
        <span class="detail-label">Méthode :</span>
        <span>{{ $payment->method === 'stripe' ? 'Carte bancaire' : 'Paiement manuel' }}</span>
    </div>
    <div class="detail-line">
        <span class="detail-label">Statut :</span>
        <span>Payé</span>
    </div>
</div>

<p>Votre inscription est maintenant <strong>en attente de validation</strong> par notre équipe.</p>

<div class="info-box">
    <h3 style="margin-top: 0; color: #2196F3;">🔔 Rappel important</h3>
    <p style="margin-bottom: 0;">
        <strong>Rendez-vous vérifications administratives et techniques (VA/VT) :</strong><br>
        📅 Samedi {{ $payment->registration->race->race_date->subDay()->format('d/m/Y') }} à 14h00<br>
        📍 Lieu : {{ $payment->registration->race->location }}
    </p>
</div>

<div class="warning-box">
    <h3 style="margin-top: 0; color: #856404;">📋 Documents à apporter</h3>
    <ul style="margin: 10px 0;">
        <li>Permis de conduire en cours de validité</li>
        <li>Carte grise du véhicule</li>
        <li>Attestation d'assurance</li>
        <li>Casque homologué</li>
    </ul>
</div>

<h3 style="color: #333; margin-top: 30px;">📅 Prochaines étapes</h3>
<ol>
    <li>✅ Paiement effectué</li>
    <li>⏳ Validation de votre inscription par l'organisation</li>
    <li>⏳ Validation technique le samedi à 14h</li>
    <li>⏳ Signature de la feuille d'engagement</li>
    <li>⏳ Réception de votre E-Card avec QR code</li>
</ol>

<p>Vous recevrez un email dès que votre inscription sera validée.</p>

<div style="text-align: center; margin-top: 30px;">
    <a href="{{ route('pilot.registrations.index') }}" class="button">
        Voir mes inscriptions
    </a>
</div>

<p style="margin-top: 30px;">
    Sportivement,<br>
    <strong>L'équipe Run200</strong>
</p>
@endsection
