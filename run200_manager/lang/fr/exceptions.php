<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Messages d'exceptions métier
    |--------------------------------------------------------------------------
    */

    'pilot' => [
        'license_duplicate' => 'Ce numéro de licence est déjà utilisé par un autre pilote.',
    ],

    'car' => [
        'race_number_taken' => 'Ce numéro de course est déjà attribué à une autre voiture.',
    ],

    'registration' => [
        'closed' => 'Les inscriptions pour cette course sont fermées.',
        'pilot_already_registered' => 'Vous êtes déjà inscrit à cette course.',
        'car_already_registered' => 'Cette voiture est déjà inscrite à cette course.',
    ],

    'payment' => [
        'failed' => 'Le paiement a échoué. Veuillez réessayer ou utiliser un autre moyen de paiement.',
    ],

    'entity' => [
        'not_found' => 'L\'élément demandé est introuvable.',
    ],

    'qrcode' => [
        'invalid' => 'Ce QR code est invalide ou a expiré.',
    ],

    'import' => [
        'failed' => 'L\'import a échoué. Veuillez vérifier le format du fichier.',
    ],

    'business' => [
        'max_cars_per_pilot' => 'Vous avez atteint le nombre maximum de voitures autorisées.',
        'race_capacity_reached' => 'Cette course est complète, les inscriptions sont fermées.',
        'season_not_active' => 'Cette saison n\'est plus active.',
        'invalid_status_transition' => 'Cette action n\'est pas possible dans l\'état actuel.',
        'registration_deadline_passed' => 'La date limite d\'inscription est dépassée.',
        'pilot_not_verified' => 'Votre profil pilote doit être vérifié pour vous inscrire.',
        'car_not_approved' => 'Cette voiture doit être approuvée avant inscription.',
    ],
];
