@extends('emails.layout')

@section('content')
<h2>❌ Inscription refusée</h2>

<p>Bonjour <strong>{{ $registration->pilot->user->name }}</strong>,</p>

<p>Votre inscription à la course <strong>{{ $registration->race->name }}</strong> est <span class="status-badge status-danger">refusée</span>. Vous ne participerez pas à cette course avec ce dossier.</p>

<div class="danger-box">
    <h3>Raison du refus</h3>
    @if($registration->reason)
        <p style="margin-bottom: 0;">{{ $registration->reason }}</p>
    @else
        <p style="margin-bottom: 0;">Votre inscription ne remplit pas tous les critères requis.</p>
    @endif
</div>

<div class="info-box">
    <h3>Paiement</h3>
    <p style="margin-bottom: 0;">
        Le refus ne modifie pas un paiement déjà effectué et ne déclenche pas de remboursement automatique. Si vous avez une question sur votre règlement, contactez l'organisation.
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
