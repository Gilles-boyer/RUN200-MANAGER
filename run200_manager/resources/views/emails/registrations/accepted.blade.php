@extends('emails.layout')

@section('content')
<h2>🎉 Inscription acceptée !</h2>

<p>Bonjour <strong>{{ $registration->pilot->user->name }}</strong>,</p>

<p>Excellente nouvelle ! Votre inscription à la course <strong>{{ $registration->race->name }}</strong> a été <span class="status-badge status-success">acceptée</span> par notre équipe.</p>

<div class="success-box">
    <h3>✅ Votre inscription est validée</h3>
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
    @if($registration->paddock)
    <div class="detail-line">
        <span class="detail-label">Paddock</span>
        <span class="detail-value">{{ $registration->paddock }}</span>
    </div>
    @endif
</div>

<div class="info-box">
    <h3>🔔 Rendez-vous OBLIGATOIRE</h3>
    <p><strong>Vérifications administratives et techniques (VA/VT)</strong></p>
    <p>
        📅 <strong>Date :</strong> Samedi {{ $registration->race->race_date->subDay()->format('d/m/Y') }}<br>
        🕐 <strong>Horaire :</strong> 14h00<br>
        📍 <strong>Lieu :</strong> {{ $registration->race->location }}<br>
        ⏱️ <strong>Durée :</strong> Environ 30 minutes
    </p>
</div>

<div class="warning-box">
    <h3>📋 Documents OBLIGATOIRES à apporter</h3>
    <ul>
        <li><strong>Permis de conduire</strong> en cours de validité</li>
        <li><strong>Extincteur dans le véhicule</strong> </li>
        <li><strong>Casque</strong> homologué (norme FIA ou Snell)</li>
        <li>Vêtements adaptés (combinaison recommandée)</li>
    </ul>
</div>

<div class="racing-card">
    <h3 style="color: #FFFFFF; margin-top: 0;">📋 Déroulement des vérifications</h3>
    <ol>
        <li><strong>Accueil et émargement</strong> - Présentez-vous au poste VA/VT</li>
        <li><strong>Contrôle administratif</strong> - Vérification de vos documents</li>
        <li><strong>Contrôle technique</strong> - Inspection de votre véhicule</li>
        <li><strong>Signature de la feuille d'engagement</strong></li>
        <li><strong>E-Card disponible dans votre espace pilote</strong> avec QR code</li>
    </ol>
</div>

<div class="info-box">
    <h3>⚡ Important</h3>
    <p style="margin-bottom: 0;">
        Sans ces vérifications, vous <strong>ne pourrez pas participer</strong> à la course.<br>
        Merci d'arriver <strong>5 minutes en avance</strong> pour faciliter le traitement.
    </p>
</div>

<div class="racing-card">
    <h3 style="color: #FFFFFF; margin-top: 0;">📅 Prochaines étapes</h3>
    <ol>
        @php
            $balanceCents = max(0, (int) $registration->race->entry_fee_cents - $registration->getPaidAmountCents());
        @endphp
        @if($balanceCents === 0)
            <li>✅ Règlement enregistré</li>
        @else
            <li>⏳ Solde à régler auprès de l'organisation : {{ number_format($balanceCents / 100, 2, ',', ' ') }} €</li>
        @endif
        <li>✅ Inscription validée</li>
        <li>⏳ <strong>Validation technique le samedi à 14h</strong></li>
        <li>⏳ Signature de la feuille d'engagement</li>
        <li>✅ E-Card avec QR code accessible dans votre espace pilote</li>
    </ol>
</div>

<div style="text-align: center; margin-top: 30px;">
    <a href="{{ route('pilot.registrations.index') }}" class="button">
        Voir mes inscriptions
    </a>
</div>

<div class="signature">
    <p>À très bientôt sur la piste !</p>
    <p><strong>L'équipe Run200</strong></p>
</div>
@endsection
