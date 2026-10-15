<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

test('repairing an email with spaces invalidates the old verification', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    DB::table('users')->where('id', $user->id)->update(['email' => 'broken @example.com']);

    $this->artisan('users:fix-invalid-emails')->assertSuccessful();

    expect($user->fresh()->email)->toBe('broken@example.com')
        ->and($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('normalizing an email invalidates verification only when the address changes', function () {
    $changed = User::factory()->create(['email_verified_at' => now()]);
    $unchanged = User::factory()->create(['email_verified_at' => now()]);
    DB::table('users')->where('id', $changed->id)->update(['email' => 'UPPER@example.com']);

    $this->artisan('data:normalize --model=user --dry-run')->assertSuccessful();
    expect($changed->fresh()->hasVerifiedEmail())->toBeTrue();

    $this->artisan('data:normalize --model=user')->assertSuccessful();

    expect($changed->fresh()->email)->toBe('upper@example.com')
        ->and($changed->fresh()->hasVerifiedEmail())->toBeFalse()
        ->and($unchanged->fresh()->hasVerifiedEmail())->toBeTrue();
});
