@extends('emails.layout')

@section('content')
<h2>❌ Inscription refusée</h2>

<p>Bonjour <strong>{{ $registration->pilot->user->name }}</strong>,</p>

<p>Nous sommes désolés de vous informer que votre inscription à la course <strong>{{ $registration->race->name }}</strong> n'a pas pu être <span class="status-badge status-danger">acceptée</span>.</p>

<div class="danger-box">
    <h3>Raison du refus</h3>
    @if($registration->reason)
        <p style="margin-bottom: 0;">{{ $registration->reason }}</p>
    @else
        <p style="margin-bottom: 0;">Votre inscription ne remplit pas tous les critères requis.</p>
    @endif
</div>

<div class="info-box">
    <h3>💰 Remboursement</h3>
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

<div class="signature">
    <p>Pour toute question, n'hésitez pas à nous contacter.</p>
    <p><strong>L'équipe Run200</strong></p>
</div>
@endsection
