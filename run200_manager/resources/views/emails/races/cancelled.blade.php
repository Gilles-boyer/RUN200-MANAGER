@extends('emails.layout')

@section('content')
<h2>❌ Course annulée</h2>

<p>Bonjour <strong>{{ $registration->pilot->user->name }}</strong>,</p>

<p>Nous sommes au regret de vous informer que la course <strong>{{ $race->name }}</strong> a été <span class="status-badge status-danger">annulée</span>.</p>

<div class="danger-box">
    <h3>📋 Informations sur l'annulation</h3>
    <div class="detail-line">
        <span class="detail-label">Course</span>
        <span class="detail-value">{{ $race->name }}</span>
    </div>
    <div class="detail-line">
        <span class="detail-label">Date prévue</span>
        <span class="detail-value">{{ $race->race_date->translatedFormat('l d F Y') }}</span>
    </div>
    <div class="detail-line">
        <span class="detail-label">Lieu</span>
        <span class="detail-value">{{ $race->location }}</span>
    </div>
    @if($cancellationReason)
    <div class="detail-line">
        <span class="detail-label">Motif</span>
        <span class="detail-value">{{ $cancellationReason }}</span>
    </div>
    @endif
</div>

<div class="info-box">
    <h3>💳 Concernant votre inscription</h3>
    <p style="margin-bottom: 0;">
        <strong>Votre véhicule inscrit :</strong> {{ $registration->car?->name ?? 'N/A' }}<br>
        @if($registration->paidPayment)
        <strong>Paiement effectué :</strong> {{ number_format($registration->paidPayment->amount / 100, 2, ',', ' ') }} €<br>
        <br>
        <strong>Le remboursement sera traité dans les meilleurs délais.</strong><br>
        Vous recevrez un email de confirmation lorsque le remboursement sera effectué.
        @endif
    </p>
</div>

<p>Nous nous excusons pour ce désagrément et espérons vous retrouver lors d'une prochaine course.</p>

<div style="text-align: center; margin-top: 30px;">
    <a href="{{ route('pilot.races.index') }}" class="button">
        Voir les prochaines courses
    </a>
</div>

<div class="signature">
    <p>Nous restons à votre disposition pour toute question.</p>
    <p>Sportivement,</p>
    <p><strong>L'équipe Run200</strong></p>
</div>
@endsection
