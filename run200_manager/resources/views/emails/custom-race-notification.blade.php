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

<h2 style="color: #333; margin-top: 0;">{{ $icon }} {{ $notification->subject }}</h2>

<p>Bonjour {{ $pilotName }},</p>

<div class="{{ $boxClass }}">
    <div style="white-space: pre-wrap;">{{ $notification->message }}</div>
</div>

<div class="info-box">
    <h3 style="margin-top: 0; color: #2196F3;">📋 Informations sur la course</h3>
    <div class="detail-line">
        <span class="detail-label">Course :</span>
        <span>{{ $notification->race->name }}</span>
    </div>
    <div class="detail-line">
        <span class="detail-label">Date :</span>
        <span>{{ $notification->race->race_date->format('d/m/Y') }}</span>
    </div>
    <div class="detail-line">
        <span class="detail-label">Lieu :</span>
        <span>{{ $notification->race->location }}</span>
    </div>
</div>

<div style="text-align: center; margin-top: 30px;">
    <a href="{{ route('pilot.registrations.index') }}" class="button">
        Voir mes inscriptions
    </a>
</div>

<p style="margin-top: 30px;">
    Sportivement,<br>
    <strong>L'équipe Run200</strong>
</p>
@endsection
