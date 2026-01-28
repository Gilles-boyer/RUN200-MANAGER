@extends('emails.layout')

@section('content')
<h2>🏁 Nouvelle course ouverte aux inscriptions !</h2>

<p>Bonjour <strong>{{ $pilotName }}</strong>,</p>

<p>Nous avons le plaisir de vous informer que les inscriptions sont maintenant <span class="status-badge status-success">ouvertes</span> pour la prochaine course :</p>

<div class="racing-card">
    <div class="racing-card-header">
        <h3 class="racing-card-title">📋 Détails de la course</h3>
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
        <span class="detail-label">Saison</span>
        <span class="detail-value">{{ $race->season?->year ?? 'N/A' }}</span>
    </div>
    <div class="detail-line">
        <span class="detail-label">Frais d'inscription</span>
        <span class="detail-value">{{ $race->formatted_entry_fee }}</span>
    </div>
</div>

<div class="success-box">
    <h3>✅ Pourquoi s'inscrire rapidement ?</h3>
    <ul>
        <li>Places limitées - Premier arrivé, premier servi</li>
        <li>Choix privilégié d'emplacement paddock</li>
        <li>Meilleure organisation pour vos préparatifs</li>
    </ul>
</div>

<div class="warning-box">
    <h3>📋 Documents requis pour l'inscription</h3>
    <ul>
        <li>Permis de conduire en cours de validité</li>
        <li>Carte grise du véhicule</li>
        <li>Attestation d'assurance</li>
        <li>Licence FFSA ou équivalent</li>
    </ul>
</div>

<div style="text-align: center; margin-top: 30px;">
    <a href="{{ route('pilot.races.index') }}" class="button">
        🏎️ M'inscrire maintenant
    </a>
</div>

<div class="info-box">
    <p style="margin: 0;">
        <strong>Rappel :</strong> Les vérifications administratives et techniques (VA/VT) auront lieu le samedi précédant la course à 14h00.
    </p>
</div>

<div class="signature">
    <p>Sportivement,</p>
    <p><strong>L'équipe Run200</strong></p>
</div>
@endsection
