<?php

namespace App\Application\Registrations\UseCases;

use App\Models\RaceRegistration;
use App\Models\RaceResult;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;

class ReopenRefusedRegistration
{
    /** Show the staff the sporting state that would be restored. The action recalculates it under lock. */
    public function previewStatus(RaceRegistration $registration): ?string
    {
        return $this->restoredStatus($registration->fresh());
    }

    public function execute(
        RaceRegistration $registration,
        string $reason,
        User $user,
        bool $paymentsReviewed,
        ?array $expected = null,
        ?string $expectedProposedStatus = null
    ): RaceRegistration {
        Gate::authorize('race_registration.validate');
        if ($user->id !== Auth::id()) {
            throw new InvalidArgumentException('L’agent de réouverture ne correspond pas à la session active.');
        }

        $reason = trim($reason);
        if (mb_strlen($reason) < 10) {
            throw new InvalidArgumentException('Indiquez un motif de réouverture d’au moins 10 caractères.');
        }
        if (! $paymentsReviewed) {
            throw new InvalidArgumentException('Confirmez avoir vérifié les paiements avant de rouvrir le dossier.');
        }

        $reopened = DB::transaction(function () use ($registration, $reason, $user, $expected, $expectedProposedStatus) {
            $locked = RaceRegistration::query()->lockForUpdate()->findOrFail($registration->id);
            if ($expected !== null) {
                foreach (['status', 'pilot_id', 'race_id', 'car_id'] as $field) {
                    if (array_key_exists($field, $expected) && (string) $locked->{$field} !== (string) $expected[$field]) {
                        throw new InvalidArgumentException('Le dossier a changé. Actualisez la liste avant de confirmer.');
                    }
                }
            }
            if ($locked->status !== 'REFUSED') {
                throw new InvalidArgumentException('Le dossier a changé. Actualisez la liste avant de confirmer.');
            }

            $target = $this->restoredStatus($locked);
            if ($expected !== null && $target !== $expectedProposedStatus) {
                throw new InvalidArgumentException('Les étapes du dossier ont changé. Actualisez la liste avant de confirmer.');
            }
            if ($target === null) {
                activity()->performedOn($locked)->causedBy($user)
                    ->withProperties(['reason' => $reason, 'payments_reviewed' => true])
                    ->log('registration.reopen_blocked');

                return null;
            }

            $locked->update([
                'status' => $target,
                'reason' => null,
                'validated_at' => now(),
                'validated_by' => $user->id,
            ]);
            activity()->performedOn($locked)->causedBy($user)
                ->withProperties([
                    'previous_status' => 'REFUSED',
                    'new_status' => $target,
                    'reason' => $reason,
                    'payments_reviewed' => true,
                ])->log('registration.reopened');

            return $locked->fresh();
        });

        if ($reopened === null) {
            throw new InvalidArgumentException('Les étapes de ce dossier sont incohérentes. Demandez à un administrateur de corriger les passages avant de réessayer.');
        }

        return $reopened;
    }

    private function restoredStatus(RaceRegistration $registration): ?string
    {
        if ($registration->status !== 'REFUSED'
            || RaceResult::query()->where('race_registration_id', $registration->id)->exists()) {
            return null;
        }

        $passages = $registration->passages()->with('checkpoint')->orderBy('scanned_at')->get();
        $codes = $passages->map(fn ($passage) => $passage->checkpoint?->code)->all();
        if (in_array(null, $codes, true) || count(array_unique($codes)) !== count($codes)
            || array_diff($codes, ['ADMIN_CHECK', 'TECH_CHECK']) !== []) {
            return null;
        }

        $inspection = $registration->techInspection()->first();
        if ($codes === []) {
            return $inspection === null ? ($registration->isPaid() ? 'PENDING_VALIDATION' : 'PENDING_PAYMENT') : null;
        }
        if (count($codes) === 1) {
            return $codes[0] === 'ADMIN_CHECK' && $inspection === null ? 'ADMIN_CHECKED' : null;
        }
        if ($codes !== ['ADMIN_CHECK', 'TECH_CHECK'] || $inspection === null) {
            return null;
        }

        return match ($inspection->status) {
            'OK' => 'TECH_CHECKED_OK',
            'FAIL' => 'TECH_CHECKED_FAIL',
            default => null,
        };
    }
}
