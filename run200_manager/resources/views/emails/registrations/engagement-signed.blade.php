@extends('emails.layout')

@section('content')
<h2 style="color: #28a745; margin-top: 0;">✍️ Feuille d'engagement signée !</h2>

<p>Bonjour {{ $engagementForm->registration->pilot->user->name }},</p>

<p>Votre feuille d'engagement pour la course <strong>{{ $engagementForm->registration->race->name }}</strong> a bien été signée et enregistrée.</p>

<div class="success-box">
    <h3 style="margin-top: 0; color: #155724;">📝 Engagement validé</h3>
    <div class="detail-line">
        <span class="detail-label">Course :</span>
        <span>{{ $engagementForm->registration->race->name }}</span>
    </div>
    <div class="detail-line">
        <span class="detail-label">Date de signature :</span>
        <span>{{ $engagementForm->signed_at->format('d/m/Y à H:i') }}</span>
    </div>
    <div class="detail-line">
        <span class="detail-label">Signature :</span>
        <span>{{ $engagementForm->signature_type === 'electronic' ? 'Signature électronique' : 'Signature manuscrite' }}</span>
    </div>
</div>

<p>Vous êtes maintenant <strong>officiellement engagé</strong> pour cette course ! 🎉</p>

<h3 style="color: #333; margin-top: 30px;">📅 Toutes les étapes complétées !</h3>
<ol>
    <li>✅ Paiement effectué</li>
    <li>✅ Inscription validée</li>
    <li>✅ Validation technique effectuée</li>
    <li>✅ Feuille d'engagement signée</li>
    <li>✅ E-Card disponible avec QR code</li>
</ol>

<div class="info-box">
    <h3 style="margin-top: 0; color: #2196F3;">📱 Votre E-Card</h3>
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
    <h3 style="margin-top: 0; color: #155724;">🏁 Programme de la course</h3>
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
    <h3 style="margin-top: 0; color: #856404;">⚠️ Rappels importants</h3>
    <ul style="margin: 10px 0;">
        <li>Présentez votre E-Card à chaque checkpoint</li>
        <li>Respectez les consignes de sécurité</li>
        <li>Suivez les instructions des commissaires</li>
        <li>Consultez votre E-Card pour le programme détaillé</li>
    </ul>
</div>

<div style="text-align: center; margin-top: 30px;">
    <a href="{{ route('pilot.registrations.index') }}" class="button">
        Voir mes inscriptions
    </a>
</div>

<p style="margin-top: 30px;">
    Bonne course et profitez bien !<br>
    <strong>L'équipe Run200</strong> 🏁
</p>
@endsection
