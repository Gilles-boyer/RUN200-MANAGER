@extends('emails.layout')

@section('content')
<h2>🎫 Votre E-Carte d'accès</h2>

<p>Bonjour <strong>{{ $registration->pilot->user->name }}</strong>,</p>

<p>Suite à la confirmation de votre paiement, voici votre <strong>E-Carte d'accès</strong> pour la course.</p>

<div class="racing-card">
    <div class="racing-card-header">
        <h3 class="racing-card-title">🏁 Informations de la course</h3>
    </div>
    <div class="detail-line">
        <span class="detail-label">Course</span>
        <span class="detail-value">{{ $registration->race->name }}</span>
    </div>
    <div class="detail-line">
        <span class="detail-label">Date</span>
        <span class="detail-value">{{ $registration->race->race_date->translatedFormat('l d F Y') }}</span>
    </div>
    <div class="detail-line">
        <span class="detail-label">Lieu</span>
        <span class="detail-value">{{ $registration->race->location }}</span>
    </div>
</div>

<div class="qr-container">
    <h3>📱 Votre QR Code d'accès</h3>
    <div class="qr-code-box">
        <img src="{{ $qrCodeDataUri }}" alt="QR Code" style="width: 200px; height: 200px; display: block;">
    </div>
    <p>Présentez ce QR code à votre arrivée<br>pour un pointage rapide</p>
</div>

<div class="success-box">
    <h3>👤 Informations du pilote</h3>
    <div class="detail-line">
        <span class="detail-label">Pilote</span>
        <span class="detail-value">{{ $registration->pilot->fullName }}</span>
    </div>
    <div class="detail-line">
        <span class="detail-label">N° Licence</span>
        <span class="detail-value">{{ $registration->pilot->license_number }}</span>
    </div>
    <div class="detail-line">
        <span class="detail-label">Véhicule</span>
        <span class="detail-value">{{ $registration->car?->name ?? 'N/A' }}</span>
    </div>
    <div class="detail-line">
        <span class="detail-label">Catégorie</span>
        <span class="detail-value">{{ $registration->car?->category?->name ?? 'N/A' }}</span>
    </div>
</div>

<div class="warning-box">
    <h3>⚠️ Important</h3>
    <ul style="margin: 10px 0; padding-left: 20px;">
        <li><strong>Conservez cet email</strong> - Il sera nécessaire lors de votre arrivée</li>
        <li><strong>Vérifications VA/VT :</strong> Samedi {{ $registration->race->race_date->subDay()->format('d/m/Y') }} à 14h00</li>
        <li><strong>Documents requis :</strong> Permis, carte grise, assurance, casque</li>
    </ul>
</div>

<div style="text-align: center; margin-top: 30px;">
    <a href="{{ route('pilot.registrations.ecard', $registration) }}" class="button">
        Voir ma E-Carte
    </a>
</div>

<p style="margin-top: 30px; text-align: center; color: #757575; font-size: 12px;">
    💡 <strong>Astuce :</strong> Vous pouvez également accéder à votre E-Carte depuis votre espace pilote.
</p>

<div class="signature">
    <p>À très bientôt sur le circuit !</p>
    <p>Sportivement,<br><strong>L'équipe Run200</strong></p>
</div>
@endsection
@endsection
