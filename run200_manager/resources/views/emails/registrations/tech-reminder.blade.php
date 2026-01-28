@extends('emails.layout')

@section('content')
<h2>🔔 Rappel : Rendez-vous demain !</h2>

<p>Bonjour <strong>{{ $registration->pilot->user->name }}</strong>,</p>

<p>Nous vous rappelons votre <strong>rendez-vous obligatoire</strong> pour les vérifications administratives et techniques demain !</p>

<div class="warning-box">
    <h3>📅 RENDEZ-VOUS DEMAIN</h3>
    <p style="font-size: 18px; margin: 15px 0;">
        <strong>Samedi {{ $registration->race->race_date->subDay()->format('d/m/Y') }} à 14h00</strong>
    </p>
    <p style="margin-bottom: 0;">
        📍 <strong>Lieu :</strong> {{ $registration->race->location }}<br>
        ⏱️ <strong>Durée :</strong> Environ 30 minutes<br>
        🚗 <strong>Véhicule :</strong> {{ $registration->car->model }} ({{ $registration->car->license_plate }})
    </p>
</div>

<div class="info-box">
    <h3>📋 Documents OBLIGATOIRES à apporter</h3>
    <ul>
        <li>✅ <strong>Permis de conduire</strong> en cours de validité</li>
        <li>✅ <strong>Carte grise</strong> du véhicule (original)</li>
        <li>✅ <strong>Attestation d'assurance</strong> en cours de validité</li>
        <li>✅ <strong>Casque</strong> homologué (norme FIA ou Snell)</li>
        <li>✅ Vêtements adaptés (combinaison recommandée)</li>
    </ul>
</div>

<div class="racing-card">
    <h3 style="color: #FFFFFF; margin-top: 0;">🔧 Vérifications effectuées</h3>

    <p style="margin: 15px 0 5px;"><strong>1. Contrôle administratif</strong></p>
    <ul>
        <li>Vérification des documents</li>
        <li>Validation des permis et assurances</li>
    </ul>

    <p style="margin: 15px 0 5px;"><strong>2. Contrôle technique du véhicule</strong></p>
    <ul>
        <li>État général du véhicule</li>
        <li>Système de freinage</li>
        <li>Pneus et suspensions</li>
        <li>Éclairages et signalisation</li>
        <li>Ceintures de sécurité</li>
    </ul>

    <p style="margin: 15px 0 5px;"><strong>3. Signature de la feuille d'engagement</strong></p>
    <p style="margin: 5px 0;"><strong>4. Remise de votre E-Card</strong></p>
</div>

<div class="danger-box">
    <h3>⚠️ IMPORTANT</h3>
    <p style="margin-bottom: 0;">
        Sans ces vérifications, vous <strong>ne pourrez PAS participer</strong> à la course dimanche.<br>
        <strong>Merci d'arriver 5 minutes en avance</strong> pour faciliter le traitement.
    </p>
</div>

<div class="success-box">
    <h3>🏁 Programme du week-end</h3>
    <p>
        <strong>Samedi {{ $registration->race->race_date->subDay()->format('d/m/Y') }}</strong><br>
        14h00 - Vérifications administratives et techniques
    </p>
    <p style="margin-bottom: 0;">
        <strong>Dimanche {{ $registration->race->race_date->format('d/m/Y') }}</strong><br>
        {{ $registration->race->location }}<br>
        (Horaires précis sur votre E-Card après validation)
    </p>
</div>

<div style="text-align: center; margin-top: 30px;">
    <a href="{{ route('pilot.registrations.index') }}" class="button">
        Voir mes inscriptions
    </a>
</div>

<div class="signature">
    <p>À demain !</p>
    <p><strong>L'équipe Run200</strong></p>
</div>
@endsection
