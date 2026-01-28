@extends('emails.layout')

@section('content')
<h2>🏎️ Bienvenue sur Run200 Manager !</h2>

<p>Bonjour <strong>{{ $pilot->first_name }}</strong>,</p>

<p>Votre profil pilote a été créé avec succès. Vous faites désormais partie de la communauté Run200 !</p>

<div class="success-box">
    <h3>✅ Votre profil pilote</h3>
    <div class="detail-line">
        <span class="detail-label">Nom</span>
        <span class="detail-value">{{ $pilot->fullName }}</span>
    </div>
    <div class="detail-line">
        <span class="detail-label">N° Licence</span>
        <span class="detail-value">{{ $pilot->license_number }}</span>
    </div>
    <div class="detail-line">
        <span class="detail-label">Email</span>
        <span class="detail-value">{{ $pilot->user->email }}</span>
    </div>
</div>

<div class="racing-card">
    <div class="racing-card-header">
        <h3 class="racing-card-title">🚀 Prochaines étapes</h3>
    </div>
    <ol style="margin: 15px 20px; padding-left: 20px; color: #e0e0e0;">
        <li style="margin-bottom: 10px;"><strong>Complétez votre profil</strong> - Ajoutez votre photo et vérifiez vos informations</li>
        <li style="margin-bottom: 10px;"><strong>Ajoutez vos véhicules</strong> - Enregistrez votre ou vos véhicules de course</li>
        <li style="margin-bottom: 10px;"><strong>Inscrivez-vous aux courses</strong> - Consultez le calendrier des prochaines courses</li>
    </ol>
</div>

<div class="warning-box">
    <h3>📋 Documents à préparer pour les courses</h3>
    <ul>
        <li>Permis de conduire en cours de validité</li>
        <li> Extincteur dans le véhicule</li>
        <li>Casque homologué</li>
        <li>Combinaison / équipement de sécurité</li>
    </ul>
</div>

<div style="text-align: center; margin-top: 30px;">
    <a href="{{ route('pilot.dashboard') }}" class="button">
        Accéder à mon espace pilote
    </a>
</div>

<p style="margin-top: 20px; text-align: center;">
    <a href="{{ route('pilot.cars.index') }}" style="color: #E53935; text-decoration: none;">
        🚗 Ajouter un véhicule
    </a>
    &nbsp;&nbsp;|&nbsp;&nbsp;
    <a href="{{ route('pilot.races.index') }}" style="color: #E53935; text-decoration: none;">
        🏁 Voir les courses
    </a>
</p>

<div class="signature">
    <p>Bienvenue dans l'aventure Run200 !</p>
    <p>Sportivement,</p>
    <p><strong>L'équipe Run200</strong></p>
</div>
@endsection
