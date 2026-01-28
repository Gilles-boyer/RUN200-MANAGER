@extends('emails.layout')

@section('content')
@if($techInspection->status === 'PASSED')
    <h2>✅ Contrôle technique validé !</h2>
@else
    <h2>❌ Contrôle technique refusé</h2>
@endif

<p>Bonjour <strong>{{ $techInspection->registration->pilot->user->name }}</strong>,</p>

@if($techInspection->status === 'PASSED')
    <p>Le contrôle technique de votre véhicule pour la course <strong>{{ $techInspection->registration->race->name }}</strong> a été <span class="status-badge status-success">validé avec succès</span> !</p>

    <div class="success-box">
        <h3>🔧 Contrôle technique OK</h3>
        <div class="detail-line">
            <span class="detail-label">Véhicule</span>
            <span class="detail-value">{{ $techInspection->registration->car->model }} ({{ $techInspection->registration->car->license_plate }})</span>
        </div>
        <div class="detail-line">
            <span class="detail-label">Date du contrôle</span>
            <span class="detail-value">{{ $techInspection->created_at->format('d/m/Y à H:i') }}</span>
        </div>
        <div class="detail-line">
            <span class="detail-label">Inspecteur</span>
            <span class="detail-value">{{ $techInspection->inspector->name }}</span>
        </div>
        @if($techInspection->notes)
        <div class="detail-line">
            <span class="detail-label">Observations</span>
            <span class="detail-value">{{ $techInspection->notes }}</span>
        </div>
        @endif
    </div>

    <p>Votre véhicule est maintenant <strong>prêt pour la course</strong> !</p>

    <div class="racing-card">
        <h3 style="color: #FFFFFF; margin-top: 0;">📅 Prochaines étapes</h3>
        <ol>
            <li>✅ Paiement effectué</li>
            <li>✅ Inscription validée</li>
            <li>✅ Validation technique effectuée</li>
            <li>⏳ Signature de la feuille d'engagement</li>
            <li>⏳ Réception de votre E-Card avec QR code</li>
        </ol>
    </div>

    <div class="info-box">
        <h3>🏁 Programme de la course</h3>
        <p>
            📅 <strong>Date :</strong> Dimanche {{ $techInspection->registration->race->race_date->format('d/m/Y') }}<br>
            📍 <strong>Lieu :</strong> {{ $techInspection->registration->race->location }}
        </p>
        <p style="margin-bottom: 0;">
            Présentez-vous <strong>30 minutes avant</strong> votre premier passage.<br>
            Plus d'infos sur votre E-Card.
        </p>
    </div>

@else
    <p>Malheureusement, le contrôle technique de votre véhicule pour la course <strong>{{ $techInspection->registration->race->name }}</strong> a été <span class="status-badge status-danger">refusé</span>.</p>

    <div class="danger-box">
        <h3>⚠️ Raisons du refus</h3>
        @if($techInspection->notes)
            <p style="margin-bottom: 0;">{{ $techInspection->notes }}</p>
        @else
            <p style="margin-bottom: 0;">Votre véhicule ne respecte pas les normes de sécurité requises.</p>
        @endif
    </div>

    <div class="info-box">
        <h3>🔧 Que faire ?</h3>
        <ol>
            <li>Corriger les problèmes identifiés sur votre véhicule</li>
            <li>Contacter l'organisation pour planifier un nouveau contrôle</li>
            <li>Vous représenter avant le début de la course</li>
        </ol>
        <p style="margin-bottom: 0;">
            <strong>Important :</strong> Sans validation technique, vous ne pourrez pas participer à la course.
        </p>
    </div>
@endif

<div style="text-align: center; margin-top: 30px;">
    <a href="{{ route('pilot.registrations.index') }}" class="button">
        Voir mes inscriptions
    </a>
</div>

<div class="signature">
    @if($techInspection->status === 'PASSED')
        <p>À très bientôt sur la piste !</p>
    @else
        <p>En espérant vous voir bientôt sur la piste,</p>
    @endif
    <p><strong>L'équipe Run200</strong></p>
</div>
@endsection
