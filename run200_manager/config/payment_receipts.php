<?php

return [
    'auto_send' => env('PAYMENT_RECEIPTS_AUTO_SEND', false),

    // Historical delivery is explicitly opened only after the selected
    // campaign scope has been reviewed. Unknown values fail closed.
    'historical_delivery' => [
        'mode' => env('PAYMENT_RECEIPTS_HISTORICAL_MODE', 'off'),
        'pilot_user_id' => env('PAYMENT_RECEIPTS_PILOT_USER_ID'),
        'pilot_payment_id' => env('PAYMENT_RECEIPTS_PILOT_PAYMENT_ID'),
    ],

    'issuer' => [
        'name' => 'ASSOCIATION SPORTIVE AUTOMOBILE DU CIRCUIT FELIX GUICHARD',
        'siret' => '51060913400018',
        'address' => 'LES ORANGERS STE ANNE, 400 CHEMIN ROBESPIERRE, 97437 SAINT-BENOIT',
        'email' => 'contact@cfg.re',
        'phone' => '0692 725 584',
    ],
];
