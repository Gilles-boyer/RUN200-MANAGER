@extends('emails.layout')

@section('content')
<h2 style="color: #ffc107; margin-top: 0;">🔔 Rappel : Rendez-vous demain !</h2>

<p>Bonjour {{ $registration->pilot->user->name }},</p>

<p>Nous vous rappelons votre <strong>rendez-vous obligatoire</strong> pour les vérifications administratives et techniques demain !</p>

<div class="warning-box">
    <h3 style="margin-top: 0; color: #856404;">📅 RENDEZ-VOUS DEMAIN</h3>
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
    <h3 style="margin-top: 0; color: #2196F3;">📋 Documents OBLIGATOIRES à apporter</h3>
    <ul style="margin: 10px 0;">
        <li>✅ <strong>Permis de conduire</strong> en cours de validité</li>
        <li>✅ <strong>Carte grise</strong> du véhicule (original)</li>
        <li>✅ <strong>Attestation d'assurance</strong> en cours de validité</li>
        <li>✅ <strong>Casque</strong> homologué (norme FIA ou Snell)</li>
        <li>✅ Vêtements adaptés (combinaison recommandée)</li>
    </ul>
</div>

<h3 style="color: #333; margin-top: 30px;">🔧 Vérifications effectuées</h3>
<div style="background-color: #f8f9fa; padding: 15px; border-radius: 6px; margin: 15px 0;">
    <p style="margin: 5px 0;"><strong>1. Contrôle administratif</strong></p>
    <ul style="margin: 5px 0 15px 20px;">
        <li>Vérification des documents</li>
        <li>Validation des permis et assurances</li>
    </ul>

    <p style="margin: 5px 0;"><strong>2. Contrôle technique du véhicule</strong></p>
    <ul style="margin: 5px 0 15px 20px;">
        <li>État général du véhicule</li>
        <li>Système de freinage</li>
        <li>Pneus et suspensions</li>
        <li>Éclairages et signalisation</li>
        <li>Ceintures de sécurité</li>
    </ul>

    <p style="margin: 5px 0;"><strong>3. Signature de la feuille d'engagement</strong></p>
    <p style="margin: 5px 0;"><strong>4. Remise de votre E-Card</strong></p>
</div>

<div class="warning-box">
    <h3 style="margin-top: 0; color: #856404;">⚠️ IMPORTANT</h3>
    <p style="margin-bottom: 0;">
        Sans ces vérifications, vous <strong>ne pourrez PAS participer</strong> à la course dimanche.<br>
        <strong>Merci d'arriver 5 minutes en avance</strong> pour faciliter le traitement.
    </p>
</div>

<div class="success-box">
    <h3 style="margin-top: 0; color: #155724;">🏁 Programme du week-end</h3>
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

<p style="margin-top: 30px;">
    À demain !<br>
    <strong>L'équipe Run200</strong>
</p>
@endsection
