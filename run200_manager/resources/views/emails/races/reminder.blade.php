@extends('emails.layout')

@section('content')
<h2>📅 J-3 : Rappel pour votre course</h2>

<p>Bonjour <strong>{{ $registration->pilot->user->name }}</strong>,</p>

<p>Plus que <strong>3 jours</strong> avant la course ! Voici un récapitulatif de votre inscription :</p>

<div class="racing-card">
    <div class="racing-card-header">
        <h3 class="racing-card-title">🏁 Votre inscription</h3>
    </div>
    <div class="detail-line">
        <span class="detail-label">Course</span>
        <span class="detail-value">{{ $race->name }}</span>
    </div>
    <div class="detail-line">
        <span class="detail-label">Date</span>
        <span class="detail-value">{{ $race->race_date->translatedFormat('l d F Y') }}</span>
    </div>
    <div class="detail-line">
        <span class="detail-label">Lieu</span>
        <span class="detail-value">{{ $race->location }}</span>
    </div>
    <div class="detail-line">
        <span class="detail-label">Véhicule</span>
        <span class="detail-value">{{ $registration->car?->name ?? 'N/A' }} ({{ $registration->car?->category?->name ?? 'N/A' }})</span>
    </div>
    @if($registration->paddockSpot)
    <div class="detail-line">
        <span class="detail-label">Emplacement paddock</span>
        <span class="detail-value">{{ $registration->paddockSpot->name }}</span>
    </div>
    @endif
</div>

<div class="warning-box">
    <h3>⚠️ Rappel important : VA/VT Samedi</h3>
    <p style="margin-bottom: 10px;">
        <strong>📅 Date :</strong> {{ $race->race_date->subDay()->translatedFormat('l d F Y') }}<br>
        <strong>🕐 Heure :</strong> 14h00<br>
        <strong>📍 Lieu :</strong> {{ $race->location }}
    </p>
    <p style="margin-bottom: 0;">
        <strong>Pensez à apporter :</strong>
    </p>
    <ul>
        <li>Permis de conduire</li>
        <li>Carte grise originale</li>
        <li>Attestation d'assurance</li>
        <li>Casque homologué</li>
        <li>Combinaison / équipement</li>
    </ul>
</div>

<div class="success-box">
    <h3>✅ Checklist avant la course</h3>
    <ul>
        <li>Vérifier les niveaux (huile, liquide de refroidissement, frein)</li>
        <li>Contrôler la pression des pneus</li>
        <li>S'assurer du bon fonctionnement des feux</li>
        <li>Préparer l'équipement de sécurité</li>
        <li>Consulter la météo prévue</li>
    </ul>
</div>

<div style="text-align: center; margin-top: 30px;">
    <a href="{{ route('pilot.registrations.index') }}" class="button">
        Voir mon inscription
    </a>
</div>

<div class="signature">
    <p>Bonne préparation et à dimanche !</p>
    <p>Sportivement,</p>
    <p><strong>L'équipe Run200</strong></p>
</div>
@endsection
