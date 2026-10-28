<?php

use App\Application\Payments\UseCases\RecordManualPayment;
use App\Application\Registrations\UseCases\ValidateRegistration;
use App\Events\PaymentConfirmed;
use App\Events\RegistrationAccepted as RegistrationAcceptedEvent;
use App\Livewire\Staff\Registrations\Index;
use App\Mail\EngagementFormSigned;
use App\Mail\RegistrationAccepted;
use App\Mail\RegistrationCreated;
use App\Mail\TechInspectionCompleted;
use App\Models\EngagementForm;
use App\Models\RaceRegistration;
use App\Models\TechInspection;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    Mail::fake();
});

test('an explicit staff decision accepts an unpaid registration without confirming payment', function () {
    Event::fake([RegistrationAcceptedEvent::class, PaymentConfirmed::class]);
    $staff = User::factory()->create();
    $staff->assignRole('STAFF_ADMINISTRATIF');
    $registration = RaceRegistration::factory()->create(['status' => 'PENDING_PAYMENT']);

    expect(fn () => app(ValidateRegistration::class)->accept($registration, $staff))
        ->toThrow(InvalidArgumentException::class);

    $accepted = app(ValidateRegistration::class)->acceptBeforePayment($registration, $staff);

    expect($accepted->status)->toBe('ACCEPTED')
        ->and($accepted->validated_by)->toBe($staff->id)
        ->and($accepted->validated_at)->not->toBeNull()
        ->and($accepted->isPaid())->toBeFalse()
        ->and($accepted->payments()->count())->toBe(0);
    Event::assertDispatchedTimes(RegistrationAcceptedEvent::class, 1);
    Event::assertNotDispatched(PaymentConfirmed::class);

    expect(fn () => app(ValidateRegistration::class)->acceptBeforePayment($accepted, $staff))
        ->toThrow(InvalidArgumentException::class);
});

test('late manual payment preserves acceptance and is confirmed separately', function () {
    Event::fake([RegistrationAcceptedEvent::class, PaymentConfirmed::class]);
    $staff = User::factory()->create();
    $registration = RaceRegistration::factory()->create(['status' => 'PENDING_PAYMENT']);
    app(ValidateRegistration::class)->acceptBeforePayment($registration, $staff);

    $payment = app(RecordManualPayment::class)->execute($registration, $staff, 100.00, 'EUR', 'VA/VT', 'cash');

    expect($registration->fresh()->status)->toBe('ACCEPTED')
        ->and($payment->isPaid())->toBeTrue();
    Event::assertDispatchedTimes(PaymentConfirmed::class, 1);
});

test('administrative staff sees and can accept before payment from the registration list', function () {
    $staff = User::factory()->create();
    $staff->assignRole('STAFF_ADMINISTRATIF');
    $this->actingAs($staff);
    $registration = RaceRegistration::factory()->create(['status' => 'PENDING_PAYMENT']);

    Livewire::test(Index::class)
        ->assertSee('Accepter avant paiement')
        ->call('openValidationModal', $registration->id, 'accept')
        ->assertSee('Le paiement n\'est pas encore enregistré', false)
        ->call('confirmValidation')
        ->assertSee('Solde à régler');

    expect($registration->fresh()->status)->toBe('ACCEPTED')
        ->and($registration->payments()->count())->toBe(0);
});

test('staff without validation permission cannot accept an unpaid registration', function () {
    $staff = User::factory()->create();
    $staff->assignRole('STAFF_ENTREE');
    $this->actingAs($staff);
    $registration = RaceRegistration::factory()->create(['status' => 'PENDING_PAYMENT']);

    Livewire::test(Index::class)
        ->call('openValidationModal', $registration->id, 'accept')
        ->assertForbidden();
    Livewire::test(Index::class)
        ->set('selectedRegistration', $registration)
        ->set('validationAction', 'accept')
        ->call('confirmValidation')
        ->assertForbidden();

    expect($registration->fresh()->status)->toBe('PENDING_PAYMENT');
});

test('a changed registration state is explained before confirmation without overwriting it', function () {
    $staff = User::factory()->create();
    $staff->assignRole('STAFF_ADMINISTRATIF');
    $this->actingAs($staff);
    $registration = RaceRegistration::factory()->create(['status' => 'PENDING_PAYMENT']);

    $component = Livewire::test(Index::class)
        ->call('openValidationModal', $registration->id, 'accept');
    $registration->update(['status' => 'CANCELLED']);
    $component->call('confirmValidation')
        ->assertSee('Le statut de cette inscription a changé. Actualisez la liste avant de continuer.');

    expect($registration->fresh()->status)->toBe('CANCELLED');
});

test('unpaid registration cannot be refused through the acceptance exception', function () {
    $registration = RaceRegistration::factory()->create(['status' => 'PENDING_PAYMENT']);

    expect(fn () => app(ValidateRegistration::class)->execute(
        $registration, 'REFUSED', 'Documents non conformes', User::factory()->create(), true
    ))->toThrow(InvalidArgumentException::class);
    expect($registration->fresh()->status)->toBe('PENDING_PAYMENT');
});

test('registration and checkpoint emails do not claim an unpaid engagement was paid', function () {
    $registration = RaceRegistration::factory()->create(['status' => 'ACCEPTED']);
    $signed = EngagementForm::factory()->create(['race_registration_id' => $registration->id]);
    $inspection = TechInspection::factory()->create(['race_registration_id' => $registration->id]);

    expect((new RegistrationCreated($registration))->render())->toContain('Paiement restant dû');

    foreach ([
        new RegistrationAccepted($registration),
        new EngagementFormSigned($signed),
        new TechInspectionCompleted($inspection),
    ] as $mail) {
        $html = $mail->render();
        expect($html)->not->toContain('Paiement effectué')
            ->and($html)->toContain('Solde à régler');
    }
});

test('accepted email shows the remaining balance after a partial payment', function () {
    Event::fake([PaymentConfirmed::class]);
    $registration = RaceRegistration::factory()->create(['status' => 'ACCEPTED']);
    $registration->race->update(['entry_fee_cents' => 10000]);
    app(RecordManualPayment::class)->execute($registration, User::factory()->create(), 50.00, 'EUR', null, 'cash');

    $html = (new RegistrationAccepted($registration->fresh()))->render();

    expect($html)->toContain('Solde à régler')
        ->toContain('50,00 €')
        ->not->toContain('Règlement enregistré');
});

test('staff list shows the outstanding balance after a partial payment', function () {
    Event::fake([PaymentConfirmed::class]);
    $staff = User::factory()->create();
    $staff->assignRole('STAFF_ADMINISTRATIF');
    $this->actingAs($staff);
    $registration = RaceRegistration::factory()->create(['status' => 'ACCEPTED']);
    $registration->race->update(['entry_fee_cents' => 10000]);
    app(RecordManualPayment::class)->execute($registration, $staff, 50.00, 'EUR', null, 'cash');

    Livewire::test(Index::class)
        ->assertSee('À régler · 50,00 €');
});
