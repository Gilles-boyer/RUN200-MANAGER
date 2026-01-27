@extends('emails.layout')

@section('content')
<h2 style="color: #28a745; margin-top: 0;">🎉 Inscription acceptée !</h2>

<p>Bonjour {{ $registration->pilot->user->name }},</p>

<p>Excellente nouvelle ! Votre inscription à la course <strong>{{ $registration->race->name }}</strong> a été <strong>acceptée</strong> par notre équipe.</p>

<div class="success-box">
    <h3 style="margin-top: 0; color: #155724;">✅ Votre inscription est validée</h3>
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
    @if($registration->paddock)
    <div class="detail-line">
        <span class="detail-label">Paddock :</span>
        <span>{{ $registration->paddock }}</span>
    </div>
    @endif
</div>

<div class="info-box">
    <h3 style="margin-top: 0; color: #2196F3;">🔔 Rendez-vous OBLIGATOIRE</h3>
    <p><strong>Vérifications administratives et techniques (VA/VT)</strong></p>
    <p style="margin: 10px 0;">
        📅 <strong>Date :</strong> Samedi {{ $registration->race->race_date->subDay()->format('d/m/Y') }}<br>
        🕐 <strong>Horaire :</strong> 14h00<br>
        📍 <strong>Lieu :</strong> {{ $registration->race->location }}<br>
        ⏱️ <strong>Durée :</strong> Environ 30 minutes
    </p>
</div>

<div class="warning-box">
    <h3 style="margin-top: 0; color: #856404;">📋 Documents OBLIGATOIRES à apporter</h3>
    <ul style="margin: 10px 0;">
        <li><strong>Permis de conduire</strong> en cours de validité</li>
        <li><strong>Carte grise</strong> du véhicule (original)</li>
        <li><strong>Attestation d'assurance</strong> en cours de validité</li>
        <li><strong>Casque</strong> homologué (norme FIA ou Snell)</li>
        <li>Vêtements adaptés (combinaison recommandée)</li>
    </ul>
</div>

<h3 style="color: #333; margin-top: 30px;">📋 Déroulement des vérifications</h3>
<ol>
    <li><strong>Accueil et émargement</strong> - Présentez-vous au poste VA/VT</li>
    <li><strong>Contrôle administratif</strong> - Vérification de vos documents</li>
    <li><strong>Contrôle technique</strong> - Inspection de votre véhicule</li>
    <li><strong>Signature de la feuille d'engagement</strong></li>
    <li><strong>Remise de votre E-Card</strong> avec QR code</li>
</ol>

<div class="info-box">
    <h3 style="margin-top: 0; color: #2196F3;">⚡ Important</h3>
    <p style="margin-bottom: 0;">
        Sans ces vérifications, vous <strong>ne pourrez pas participer</strong> à la course.<br>
        Merci d'arriver <strong>5 minutes en avance</strong> pour faciliter le traitement.
    </p>
</div>

<h3 style="color: #333; margin-top: 30px;">📅 Prochaines étapes</h3>
<ol>
    <li>✅ Paiement effectué</li>
    <li>✅ Inscription validée</li>
    <li>⏳ <strong>Validation technique le samedi à 14h</strong></li>
    <li>⏳ Signature de la feuille d'engagement</li>
    <li>⏳ Réception de votre E-Card avec QR code</li>
</ol>

<div style="text-align: center; margin-top: 30px;">
    <a href="{{ route('pilot.registrations.index') }}" class="button">
        Voir mes inscriptions
    </a>
</div>

<p style="margin-top: 30px;">
    À très bientôt sur la piste !<br>
    <strong>L'équipe Run200</strong>
</p>
@endsection
