@extends('emails.layout')

@section('content')
<h2>✅ Paiement confirmé !</h2>

<p>Bonjour <strong>{{ $payment->registration->pilot->user->name }}</strong>,</p>

<p>Nous avons bien reçu votre paiement pour la course <strong>{{ $payment->registration->race->name }}</strong>.</p>

<div class="success-box">
    <h3>💳 Détails du paiement</h3>
    <div class="detail-line">
        <span class="detail-label">Montant</span>
        <span class="detail-value">{{ $payment->amount }} {{ $payment->currency }}</span>
    </div>
    <div class="detail-line">
        <span class="detail-label">Date</span>
        <span class="detail-value">{{ $payment->paid_at->format('d/m/Y à H:i') }}</span>
    </div>
    <div class="detail-line">
        <span class="detail-label">Méthode</span>
        <span class="detail-value">{{ $payment->method === 'stripe' ? 'Carte bancaire' : 'Paiement manuel' }}</span>
    </div>
    <div class="detail-line">
        <span class="detail-label">Statut</span>
        <span class="status-badge status-success">Payé</span>
    </div>
</div>

<p>Votre inscription est maintenant <strong>en attente de validation</strong> par notre équipe.</p>

<div class="info-box">
    <h3>🔔 Rappel important</h3>
    <p style="margin-bottom: 0;">
        <strong>Rendez-vous vérifications administratives et techniques (VA/VT) :</strong><br>
        📅 Samedi {{ $payment->registration->race->race_date->subDay()->format('d/m/Y') }} à 14h00<br>
        📍 Lieu : {{ $payment->registration->race->location }}
    </p>
</div>

<div class="warning-box">
    <h3>📋 Documents à apporter</h3>
    <ul>
        <li>Permis de conduire en cours de validité</li>
        <li>Casque homologué</li>
        <li>Extincteur dans le véhicule</li>
    </ul>
</div>

<div class="racing-card">
    <h3 style="color: #FFFFFF; margin-top: 0;">📅 Prochaines étapes</h3>
    <ol>
        <li>✅ Paiement effectué</li>
        <li>⏳ Validation de votre inscription par l'organisation</li>
        <li>⏳ Validation technique le samedi à 14h</li>
        <li>⏳ Signature de la feuille d'engagement</li>
        <li>⏳ Réception de votre E-Card avec QR code</li>
    </ol>
</div>

<p>Vous recevrez un email dès que votre inscription sera validée.</p>

<div style="text-align: center; margin-top: 30px;">
    <a href="{{ route('pilot.registrations.index') }}" class="button">
        Voir mes inscriptions
    </a>
</div>

<div class="signature">
    <p>Sportivement,</p>
    <p><strong>L'équipe Run200</strong></p>
</div>
@endsection
