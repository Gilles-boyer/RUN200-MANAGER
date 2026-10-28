@extends('emails.layout')

@section('content')
<h2>✍️ Feuille d'engagement signée !</h2>

<p>Bonjour <strong>{{ $engagementForm->registration->pilot->user->name }}</strong>,</p>

<p>Votre feuille d'engagement pour la course <strong>{{ $engagementForm->registration->race->name }}</strong> a bien été signée et enregistrée.</p>

<div class="success-box">
    <h3>📝 Engagement validé</h3>
    <div class="detail-line">
        <span class="detail-label">Course</span>
        <span class="detail-value">{{ $engagementForm->registration->race->name }}</span>
    </div>
    <div class="detail-line">
        <span class="detail-label">Date de signature</span>
        <span class="detail-value">{{ $engagementForm->signed_at->format('d/m/Y à H:i') }}</span>
    </div>
    <div class="detail-line">
        <span class="detail-label">Signature</span>
        <span class="detail-value">{{ $engagementForm->signature_type === 'electronic' ? 'Signature électronique' : 'Signature manuscrite' }}</span>
    </div>
</div>

<p>Vous êtes maintenant <strong>officiellement engagé</strong> pour cette course ! 🎉</p>

<div class="racing-card">
    <h3 style="color: #FFFFFF; margin-top: 0;">📅 Suivi de vos étapes</h3>
    <ol>
        @php
            $balanceCents = max(0, (int) $engagementForm->registration->race->entry_fee_cents - $engagementForm->registration->getPaidAmountCents());
        @endphp
        @if($balanceCents === 0)
            <li>✅ Règlement enregistré</li>
        @else
            <li>⏳ Solde à régler auprès de l'organisation : {{ number_format($balanceCents / 100, 2, ',', ' ') }} €</li>
        @endif
        <li>✅ Inscription validée</li>
        <li>✅ Validation technique effectuée</li>
        <li>✅ Feuille d'engagement signée</li>
        <li>✅ E-Card disponible avec QR code</li>
    </ol>
</div>

<div class="info-box">
    <h3>📱 Votre E-Card</h3>
    <p style="margin-bottom: 0;">
        Votre E-Card avec QR code est maintenant disponible.<br>
        Elle contient toutes les informations nécessaires pour la course.<br>
        <strong>Pensez à la présenter à chaque point de contrôle !</strong>
    </p>
</div>

<div style="text-align: center; margin-top: 20px;">
    <a href="{{ route('pilot.registrations.ecard', $engagementForm->registration) }}" class="button">
        📱 Voir mon E-Card
    </a>
</div>

<div class="success-box" style="margin-top: 30px;">
    <h3>🏁 Programme de la course</h3>
    <p>
        📅 <strong>Date :</strong> Dimanche {{ $engagementForm->registration->race->race_date->format('d/m/Y') }}<br>
        📍 <strong>Lieu :</strong> {{ $engagementForm->registration->race->location }}
    </p>
    <p style="margin-bottom: 0;">
        ⏰ <strong>Présentez-vous 30 minutes avant votre premier passage</strong><br>
        📱 Gardez votre E-Card à portée de main<br>
        🏆 Bonne chance !
    </p>
</div>

<div class="warning-box">
    <h3>⚠️ Rappels importants</h3>
    <ul>
        <li>Présentez votre E-Card à chaque checkpoint</li>
        <li>Respectez les consignes de sécurité</li>
        <li>Suivez les instructions des commissaires</li>
        <li>Consultez votre E-Card pour le programme détaillé</li>
    </ul>
</div>

<div style="text-align: center; margin-top: 30px;">
    <a href="{{ route('pilot.registrations.index') }}" class="button button-secondary">
        Voir mes inscriptions
    </a>
</div>

<div class="signature">
    <p>Bonne course et profitez bien ! 🏁</p>
    <p><strong>L'équipe Run200</strong></p>
</div>
@endsection
