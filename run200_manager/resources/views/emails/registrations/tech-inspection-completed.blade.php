@extends('emails.layout')

@section('content')
@if($techInspection->status === 'PASSED')
    <h2 style="color: #28a745; margin-top: 0;">✅ Contrôle technique validé !</h2>
@else
    <h2 style="color: #dc3545; margin-top: 0;">❌ Contrôle technique refusé</h2>
@endif

<p>Bonjour {{ $techInspection->registration->pilot->user->name }},</p>

@if($techInspection->status === 'PASSED')
    <p>Le contrôle technique de votre véhicule pour la course <strong>{{ $techInspection->registration->race->name }}</strong> a été <strong>validé avec succès</strong> !</p>

    <div class="success-box">
        <h3 style="margin-top: 0; color: #155724;">🔧 Contrôle technique OK</h3>
        <div class="detail-line">
            <span class="detail-label">Véhicule :</span>
            <span>{{ $techInspection->registration->car->model }} ({{ $techInspection->registration->car->license_plate }})</span>
        </div>
        <div class="detail-line">
            <span class="detail-label">Date du contrôle :</span>
            <span>{{ $techInspection->created_at->format('d/m/Y à H:i') }}</span>
        </div>
        <div class="detail-line">
            <span class="detail-label">Inspecteur :</span>
            <span>{{ $techInspection->inspector->name }}</span>
        </div>
        @if($techInspection->notes)
        <div class="detail-line">
            <span class="detail-label">Observations :</span>
            <span>{{ $techInspection->notes }}</span>
        </div>
        @endif
    </div>

    <p>Votre véhicule est maintenant <strong>prêt pour la course</strong> !</p>

    <h3 style="color: #333; margin-top: 30px;">📅 Prochaines étapes</h3>
    <ol>
        <li>✅ Paiement effectué</li>
        <li>✅ Inscription validée</li>
        <li>✅ Validation technique effectuée</li>
        <li>⏳ Signature de la feuille d'engagement</li>
        <li>⏳ Réception de votre E-Card avec QR code</li>
    </ol>

    <div class="info-box">
        <h3 style="margin-top: 0; color: #2196F3;">🏁 Programme de la course</h3>
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
    <p>Malheureusement, le contrôle technique de votre véhicule pour la course <strong>{{ $techInspection->registration->race->name }}</strong> a été <strong>refusé</strong>.</p>

    <div class="warning-box">
        <h3 style="margin-top: 0; color: #856404;">⚠️ Raisons du refus</h3>
        @if($techInspection->notes)
            <p>{{ $techInspection->notes }}</p>
        @else
            <p>Votre véhicule ne respecte pas les normes de sécurité requises.</p>
        @endif
    </div>

    <div class="info-box">
        <h3 style="margin-top: 0; color: #2196F3;">🔧 Que faire ?</h3>
        <ol style="margin: 10px 0;">
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

<p style="margin-top: 30px;">
    @if($techInspection->status === 'PASSED')
        À très bientôt sur la piste !<br>
    @else
        En espérant vous voir bientôt sur la piste,<br>
    @endif
    <strong>L'équipe Run200</strong>
</p>
@endsection
