@extends('emails.layout')

@section('content')
@php
    $icon = match($notification->type) {
        'warning' => '⚠️',
        'success' => '✅',
        default => 'ℹ️'
    };

    $boxClass = match($notification->type) {
        'warning' => 'warning-box',
        'success' => 'success-box',
        default => 'info-box'
    };
@endphp

<h2>{{ $icon }} {{ $notification->subject }}</h2>

<p>Bonjour <strong>{{ $pilotName }}</strong>,</p>

<div class="{{ $boxClass }}">
    <div style="white-space: pre-wrap;">{{ $notification->message }}</div>
</div>

<div class="racing-card">
    <div class="racing-card-header">
        <h3 class="racing-card-title">📋 Informations sur la course</h3>
    </div>
    <div class="detail-line">
        <span class="detail-label">Course</span>
        <span class="detail-value">{{ $notification->race->name }}</span>
    </div>
    <div class="detail-line">
        <span class="detail-label">Date</span>
        <span class="detail-value">{{ $notification->race->race_date->format('d/m/Y') }}</span>
    </div>
    <div class="detail-line">
        <span class="detail-label">Lieu</span>
        <span class="detail-value">{{ $notification->race->location }}</span>
    </div>
</div>

<div style="text-align: center; margin-top: 30px;">
    <a href="{{ route('pilot.registrations.index') }}" class="button">
        Voir mes inscriptions
    </a>
</div>

<div class="signature">
    <p>Sportivement,</p>
    <p><strong>L'équipe Run200</strong></p>
</div>
@endsection
