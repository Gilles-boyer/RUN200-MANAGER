<?php

use App\Livewire\Pilot\Registrations\Index as PilotRegistrations;
use App\Livewire\Staff\Registrations\Index as StaffRegistrations;
use App\Models\Pilot;
use App\Models\RaceRegistration;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    Mail::fake();
});

test('staff sees refusal on accepted and checked registrations on desktop and mobile while licence column remains', function () {
    $staff = User::factory()->create();
    $staff->assignRole('STAFF_ADMINISTRATIF');
    $this->actingAs($staff);

    $accepted = RaceRegistration::factory()->create(['status' => 'ACCEPTED']);
    $checked = RaceRegistration::factory()->create(['status' => 'ADMIN_CHECKED']);

    Livewire::test(StaffRegistrations::class)
        ->assertSee('registration-refuse-desktop-'.$accepted->id, false)
        ->assertSee('registration-refuse-mobile-'.$accepted->id, false)
        ->assertSee('registration-refuse-desktop-'.$checked->id, false)
        ->assertSee('registration-refuse-mobile-'.$checked->id, false)
        ->assertSee('Contrôle licence');
});

test('refusal modal explains financial independence and refuses only the selected participation', function () {
    $staff = User::factory()->create();
    $staff->assignRole('STAFF_ADMINISTRATIF');
    $this->actingAs($staff);

    $registration = RaceRegistration::factory()->create(['status' => 'ACCEPTED']);

    Livewire::test(StaffRegistrations::class)
        ->call('openValidationModal', $registration->id, 'refuse')
        ->assertSee('Refuser la participation')
        ->assertSee('Cette décision n\'annule ni ne rembourse un paiement', false)
        ->assertSee('Un paiement en ligne déjà ouvert peut encore aboutir')
        ->assertSee('Statut actuel')
        ->set('refusalReason', 'Document non conforme')
        ->call('confirmValidation')
        ->assertHasNoErrors()
        ->assertSee('Participation refusée pour cette course')
        ->assertSee('Ne participe pas');

    expect($registration->fresh()->status)->toBe('REFUSED');
});

test('staff reopening requires a reason and attestation of payment review', function () {
    $staff = User::factory()->create();
    $staff->assignRole('STAFF_ADMINISTRATIF');
    $this->actingAs($staff);

    $registration = RaceRegistration::factory()->refused()->create();

    Livewire::test(StaffRegistrations::class)
        ->assertSee('registration-reopen-desktop-'.$registration->id, false)
        ->assertSee('registration-reopen-mobile-'.$registration->id, false)
        ->call('openReopenModal', $registration->id)
        ->assertSee('J\'ai vérifié les paiements', false)
        ->assertSee('Statut proposé')
        ->set('reopenReason', 'Dossier corrigé après échange')
        ->call('confirmReopen')
        ->assertHasErrors(['reopenPaymentsReviewed']);

    expect($registration->fresh()->status)->toBe('REFUSED');
});

test('reopening rejects an identity change made after the modal opened', function () {
    $staff = User::factory()->create();
    $staff->assignRole('STAFF_ADMINISTRATIF');
    $this->actingAs($staff);

    $registration = RaceRegistration::factory()->refused()->create();
    $component = Livewire::test(StaffRegistrations::class)
        ->call('openReopenModal', $registration->id)
        ->set('reopenReason', 'Dossier corrigé après échange')
        ->set('reopenPaymentsReviewed', true);

    $registration->update(['pilot_id' => Pilot::factory()->create()->id]);

    $component->call('confirmReopen')
        ->assertSee('Le dossier a changé. Actualisez la liste avant de confirmer.');

    expect($registration->fresh()->status)->toBe('REFUSED');
});

test('pilot sees organizer contact without a self reactivation action for a refused registration', function () {
    $registration = RaceRegistration::factory()->refused()->create();
    $pilotUser = $registration->pilot->user;
    $pilotUser->assignRole('PILOTE');
    $this->actingAs($pilotUser);

    Livewire::test(PilotRegistrations::class)
        ->assertSee('Votre participation à cette course est refusée')
        ->assertSee('contact@cfg.re')
        ->assertSee('Aucun paiement encaissé pour cette inscription')
        ->assertDontSee('Paiement requis')
        ->assertDontSee('Montant dû')
        ->assertDontSee('Réactiver l’inscription');
});
