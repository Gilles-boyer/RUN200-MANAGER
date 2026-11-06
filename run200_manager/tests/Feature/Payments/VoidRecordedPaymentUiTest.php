<?php

use App\Application\Payments\UseCases\IssuePaymentReceipt;
use App\Livewire\Staff\Registrations\Index as RegistrationIndex;
use App\Livewire\Staff\Registrations\PaymentManager;
use App\Models\Payment;
use App\Models\RaceRegistration;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

test('admin can confirm an erroneous local payment from its dossier and sees the corrected state', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $admin = User::factory()->create();
    $admin->assignRole('ADMIN');
    $registration = RaceRegistration::factory()->accepted()->create();
    $payment = Payment::create([
        'race_registration_id' => $registration->id,
        'user_id' => $admin->id,
        'amount' => '100.00',
        'amount_cents' => 10000,
        'currency' => 'EUR',
        'method' => 'cash',
        'status' => 'paid',
        'paid_at' => now(),
    ]);
    $this->actingAs($admin);

    Livewire::test(PaymentManager::class, ['registration' => $registration])
        ->assertSee('Annuler ce paiement saisi par erreur')
        ->call('openVoidPaymentModal', $payment->id)
        ->assertSet('showVoidPaymentModal', true)
        ->assertSee('Cette action corrige l’enregistrement')
        ->set('voidReason', 'Aucun règlement reçu, saisie en espèces erronée')
        ->call('voidRecordedPayment')
        ->assertHasNoErrors()
        ->assertSet('showVoidPaymentModal', false)
        ->assertSee('Non payé')
        ->assertSee('Saisie initiale du')
        ->assertSee('Aucun règlement reçu');

    expect($payment->fresh()->status->value)->toBe('cancelled')
        ->and($registration->fresh()->status)->toBe('ACCEPTED');
});

test('staff without void permission cannot see or invoke the paid correction', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $staff = User::factory()->create();
    $staff->assignRole('STAFF_ADMINISTRATIF');
    $registration = RaceRegistration::factory()->accepted()->create();
    $payment = Payment::create([
        'race_registration_id' => $registration->id,
        'user_id' => $staff->id,
        'amount' => '100.00',
        'amount_cents' => 10000,
        'currency' => 'EUR',
        'method' => 'cash',
        'status' => 'paid',
        'paid_at' => now(),
    ]);
    $this->actingAs($staff);

    Livewire::test(PaymentManager::class, ['registration' => $registration])
        ->assertDontSee('Annuler ce paiement saisi par erreur')
        ->call('openVoidPaymentModal', $payment->id)
        ->assertForbidden();

    expect($payment->fresh()->status->value)->toBe('paid');
});

test('a receipt delivered after opening the modal stops the correction and asks for a reload', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $admin = User::factory()->create();
    $admin->assignRole('ADMIN');
    $registration = RaceRegistration::factory()->accepted()->create();
    $payment = Payment::create([
        'race_registration_id' => $registration->id,
        'user_id' => $admin->id,
        'amount' => '100.00',
        'amount_cents' => 10000,
        'currency' => 'EUR',
        'method' => 'cash',
        'status' => 'paid',
        'paid_at' => now(),
    ]);
    $receipt = app(IssuePaymentReceipt::class)->issue($payment);
    $this->actingAs($admin);

    $component = Livewire::test(PaymentManager::class, ['registration' => $registration])
        ->call('openVoidPaymentModal', $payment->id)
        ->assertSet('voidSnapshot.receipt_id', $receipt->id)
        ->assertSet('voidSnapshot.receipt_email_status', 'pending');

    $receipt->update(['email_status' => 'sent', 'email_sent_at' => now()]);

    $component->set('voidReason', 'Aucun règlement reçu, saisie erronée')
        ->call('voidRecordedPayment')
        ->assertSet('showVoidPaymentModal', true)
        ->assertSee('Le paiement, le pilote ou le reçu a changé. Rechargez la page avant de décider.');

    expect($payment->fresh()->status->value)->toBe('paid');
});

test('registrations list does not present a cancelled amount as collected', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $admin = User::factory()->create();
    $admin->assignRole('ADMIN');
    $registration = RaceRegistration::factory()->accepted()->create();
    $registration->pilot->update(['last_name' => 'SansEncaissementUnique']);
    Payment::create([
        'race_registration_id' => $registration->id,
        'user_id' => $admin->id,
        'amount' => '100.00',
        'amount_cents' => 10000,
        'currency' => 'EUR',
        'method' => 'cash',
        'status' => 'cancelled',
        'paid_at' => now(),
    ]);
    $this->actingAs($admin);

    Livewire::test(RegistrationIndex::class)
        ->set('search', 'SansEncaissementUnique')
        ->assertSee('Non payé')
        ->assertSee('Dernière saisie annulée')
        ->assertDontSee('💵 100,00 EUR');
});

test('registrations list shows an earlier valid payment when the latest one was cancelled', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $admin = User::factory()->create();
    $admin->assignRole('ADMIN');
    $registration = RaceRegistration::factory()->accepted()->create();
    $registration->pilot->update(['last_name' => 'AncienPaiementUnique']);
    $older = Payment::create([
        'race_registration_id' => $registration->id,
        'user_id' => $admin->id,
        'amount' => '100.00',
        'amount_cents' => 10000,
        'currency' => 'EUR',
        'method' => 'card_onsite',
        'status' => 'paid',
        'paid_at' => now()->subDay(),
    ]);
    $older->forceFill(['created_at' => now()->subDay()])->save();
    Payment::create([
        'race_registration_id' => $registration->id,
        'user_id' => $admin->id,
        'amount' => '100.00',
        'amount_cents' => 10000,
        'currency' => 'EUR',
        'method' => 'cash',
        'status' => 'cancelled',
        'paid_at' => now(),
    ]);
    $this->actingAs($admin);

    DB::enableQueryLog();
    DB::flushQueryLog();
    Livewire::test(RegistrationIndex::class)
        ->set('search', 'AncienPaiementUnique')
        ->assertSee('Payé')
        ->assertSee('💳')
        ->assertDontSee('💵')
        ->assertDontSee('Non payé · saisie annulée');
    $paymentPerRowQueries = collect(DB::getQueryLog())->filter(function ($row) {
        return preg_match('/\bfrom\s+["`]?payments["`]?/i', $row['query'])
            && preg_match('/race_registration_id["`]?\s*=/', $row['query']);
    });
    DB::disableQueryLog();

    expect($paymentPerRowQueries)->toHaveCount(0);
});
