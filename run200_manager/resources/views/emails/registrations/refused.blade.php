@extends('emails.layout')

@section('content')
<h2 style="color: #dc3545; margin-top: 0;">❌ Inscription refusée</h2>

<p>Bonjour {{ $registration->pilot->user->name }},</p>

<p>Nous sommes désolés de vous informer que votre inscription à la course <strong>{{ $registration->race->name }}</strong> n'a pas pu être acceptée.</p>

<div class="warning-box">
    <h3 style="margin-top: 0; color: #856404;">Raison du refus</h3>
    @if($registration->reason)
        <p style="margin-bottom: 0;">{{ $registration->reason }}</p>
    @else
        <p style="margin-bottom: 0;">Votre inscription ne remplit pas tous les critères requis.</p>
    @endif
</div>

<div class="info-box">
    <h3 style="margin-top: 0; color: #2196F3;">💰 Remboursement</h3>
    <p style="margin-bottom: 0;">
        Si vous avez effectué un paiement, celui-ci vous sera <strong>automatiquement remboursé</strong> sous 5 à 10 jours ouvrés.
    </p>
</div>

<p>Nous vous invitons à consulter les autres courses disponibles sur notre plateforme.</p>

<div style="text-align: center; margin-top: 30px;">
    <a href="{{ route('pilot.races.index') }}" class="button">
        Voir les autres courses
    </a>
</div>

<p style="margin-top: 30px;">
    Pour toute question, n'hésitez pas à nous contacter.<br>
    <strong>L'équipe Run200</strong>
</p>
@endsection
