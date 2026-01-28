@extends('emails.layout')

@section('content')
<h2>🏆 Résultats publiés !</h2>

<p>Bonjour <strong>{{ $pilotResult?->pilot?->user?->name ?? 'Pilote' }}</strong>,</p>

<p>Les résultats officiels de la course <strong>{{ $race->name }}</strong> sont maintenant disponibles !</p>

@if($pilotResult)
<div class="success-box">
    <h3>🎯 Votre résultat</h3>
    <div class="detail-line">
        <span class="detail-label">Position générale</span>
        <span class="detail-value" style="font-size: 24px; font-weight: bold;">
            {{ $pilotResult->position }}{{ $pilotResult->position <= 3 ? ($pilotResult->position == 1 ? ' 🥇' : ($pilotResult->position == 2 ? ' 🥈' : ' 🥉')) : 'e' }}
        </span>
    </div>
    @if($pilotResult->category_position)
    <div class="detail-line">
        <span class="detail-label">Position catégorie</span>
        <span class="detail-value">{{ $pilotResult->category_position }}e en {{ $pilotResult->car?->category?->name ?? 'N/A' }}</span>
    </div>
    @endif
    <div class="detail-line">
        <span class="detail-label">Meilleur tour</span>
        <span class="detail-value">{{ $pilotResult->best_lap_time ?? 'N/A' }}</span>
    </div>
    <div class="detail-line">
        <span class="detail-label">Points gagnés</span>
        <span class="detail-value"><strong>{{ $pilotResult->points ?? 0 }} pts</strong></span>
    </div>
</div>

@if($pilotResult->position <= 3)
<div class="qr-container" style="background: linear-gradient(135deg, #FFD600 0%, #FFC400 100%);">
    <h3 style="color: #121212; margin: 0;">
        🎉 Félicitations pour votre podium !
    </h3>
</div>
@endif

@else
<div class="info-box">
    <p style="margin: 0;">Les résultats détaillés sont disponibles sur la plateforme.</p>
</div>
@endif

<div class="racing-card">
    <div class="racing-card-header">
        <h3 class="racing-card-title">📊 Informations de la course</h3>
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
</div>

<div style="text-align: center; margin-top: 30px;">
    <a href="{{ route('public.results.race', $race) }}" class="button">
        Voir tous les résultats
    </a>
</div>

<p style="margin-top: 20px; text-align: center;">
    <a href="{{ route('public.results.season', $race->season) }}" style="color: #E53935; text-decoration: none;">
        📈 Voir le classement de la saison {{ $race->season?->year }}
    </a>
</p>

<div class="signature">
    <p>Merci pour votre participation et à bientôt pour la prochaine course !</p>
    <p>Sportivement,</p>
    <p><strong>L'équipe Run200</strong></p>
</div>
@endsection
