<?php

namespace App\Console\Commands;

use App\Models\CheckpointPassage;
use App\Models\RaceRegistration;
use Illuminate\Console\Command;

class CheckEngagementValidations extends Command
{
    protected $signature = 'engagement:check {registration_id}';

    protected $description = 'Check engagement form validation status';

    public function handle()
    {
        $registrationId = $this->argument('registration_id');

        $registration = RaceRegistration::with(['engagementForm', 'techInspection.inspector'])
            ->find($registrationId);

        if (! $registration) {
            $this->error("Registration #{$registrationId} not found");

            return 1;
        }

        $this->info("=== Registration #{$registrationId} ===");
        $this->info("Pilot: {$registration->pilot->full_name}");
        $this->info("Car: #{$registration->car->race_number}");

        $this->info("\n=== Tech Inspection ===");
        if ($registration->techInspection) {
            $this->info("Status: {$registration->techInspection->status}");
            $this->info("Inspector: {$registration->techInspection->inspector->name}");
            $this->info("Inspected at: {$registration->techInspection->checked_at}");
            $this->info('Notes: '.($registration->techInspection->notes ?? 'N/A'));
        } else {
            $this->warn('No tech inspection found');
        }

        $this->info("\n=== Admin Check (Checkpoint Passage) ===");
        $adminCheck = CheckpointPassage::where('registration_id', $registrationId)
            ->where('checkpoint_code', 'ADMIN_CHECK')
            ->with('scanner')
            ->first();

        if ($adminCheck) {
            $this->info("Scanned at: {$adminCheck->scanned_at}");
            $this->info("Scanned by: {$adminCheck->scanner->name}");
        } else {
            $this->warn('No admin check found');
        }

        $this->info("\n=== Engagement Form ===");
        if ($registration->engagementForm) {
            $form = $registration->engagementForm;
            $this->info("Signed at: {$form->signed_at}");
            $this->info('Tech controller name: '.($form->tech_controller_name ?? 'NULL'));
            $this->info('Tech checked at: '.($form->tech_checked_at ?? 'NULL'));
            $this->info('Admin validated by: '.($form->admin_validated_by ?? 'NULL'));
            $this->info('Admin validated at: '.($form->admin_validated_at ?? 'NULL'));

            if ($form->adminValidator) {
                $this->info("Admin validator name: {$form->adminValidator->name}");
            }
        } else {
            $this->error('No engagement form found');
        }

        return 0;
    }
}
