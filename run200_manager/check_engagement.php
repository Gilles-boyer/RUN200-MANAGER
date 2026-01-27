<?php

$engagement = \App\Models\EngagementForm::where('car_race_number', 263)->first();
echo 'Engagement ID: '.$engagement->id."\n";
echo 'Pilot: '.$engagement->pilot_name."\n";
echo 'Tech Controller: '.($engagement->tech_controller_name ?? 'NULL')."\n";
echo 'Tech Checked At: '.($engagement->tech_checked_at ?? 'NULL')."\n";
echo 'Admin Validated By: '.($engagement->admin_validated_by ?? 'NULL')."\n";
echo 'Admin Validated At: '.($engagement->admin_validated_at ?? 'NULL')."\n";

$registration = $engagement->registration;
echo "\nRegistration ID: ".$registration->id."\n";
echo 'Registration Status: '.$registration->status."\n";
echo "Passages:\n";
foreach ($registration->passages as $p) {
    echo '  - '.$p->checkpoint->code.' at '.$p->scanned_at."\n";
}
$tech = $registration->techInspection;
echo 'Tech Inspection: '.($tech ? $tech->status.' by '.$tech->inspector->name : 'NULL')."\n";
