<?php

declare(strict_types=1);

namespace App\Livewire\Staff\Registrations;

use App\Application\Payments\UseCases\RecordManualPayment;
use App\Application\Payments\UseCases\RefundStripePayment;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Models\RaceRegistration;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Stripe\Exception\ApiErrorException;

class PaymentManager extends Component
{
    use AuthorizesRequests;

    public RaceRegistration $registration;

    public bool $showManualPaymentModal = false;

    public bool $showRefundModal = false;

    public string $manualAmount = '50,00';

    public string $manualMethod = 'cash';

    public string $manualNotes = '';

    public ?int $selectedPaymentId = null;

    public string $refundReason = '';

    public function mount(RaceRegistration $registration): void
    {
        abort_unless(auth()->user()?->can('payment.view'), 403);
        $this->registration = $registration->load(['payments', 'pilot', 'car', 'race']);
    }

    #[Computed]
    public function payments(): Collection
    {
        return $this->registration->payments()->orderByDesc('created_at')->get();
    }

    #[Computed]
    public function isPaid(): bool
    {
        return $this->registration->isPaid();
    }

    #[Computed]
    public function totalPaid(): int
    {
        return $this->registration->getPaidAmountCents();
    }

    #[Computed]
    public function formattedTotalPaid(): string
    {
        return number_format($this->totalPaid / 100, 2, ',', ' ').' €';
    }

    public function openManualPaymentModal(): void
    {
        abort_unless(auth()->user()?->can('payment.manage'), 403);
        $this->manualAmount = number_format($this->registration->race->entry_fee_cents / 100, 2, ',', '');
        $this->manualMethod = 'cash';
        $this->manualNotes = '';
        $this->showManualPaymentModal = true;
    }

    public function closeManualPaymentModal(): void
    {
        $this->showManualPaymentModal = false;
    }

    public function recordManualPayment(RecordManualPayment $useCase): void
    {
        abort_unless(auth()->user()?->can('payment.manage'), 403);
        $this->validate([
            'manualAmount' => ['required', 'string', 'regex:/^\d{1,6}(?:[.,]\d{1,2})?$/'],
            'manualMethod' => ['required', 'string', 'in:cash,card_onsite,bank_transfer,manual'],
            'manualNotes' => ['nullable', 'string', 'max:1000'],
        ], [
            'manualAmount.required' => 'Saisissez un montant en euros.',
            'manualAmount.regex' => 'Saisissez un montant en euros avec au maximum deux décimales (ex. 50 ou 50,50).',
        ]);

        [$euros, $cents] = array_pad(explode('.', str_replace(',', '.', $this->manualAmount), 2), 2, '0');
        $amountCents = ((int) $euros * 100) + (int) str_pad($cents, 2, '0');
        if ($amountCents < 100) {
            throw ValidationException::withMessages([
                'manualAmount' => 'Le montant doit être d’au moins 1 €.',
            ]);
        }

        /** @var User $user */
        $user = auth()->user();

        $useCase->execute(
            $this->registration,
            $user,
            $amountCents / 100,
            'EUR',
            $this->manualNotes ?: null,
            $this->manualMethod
        );

        $this->closeManualPaymentModal();
        $this->registration->refresh();

        session()->flash('success', 'Paiement manuel enregistré avec succès.');
    }

    public function openRefundModal(int $paymentId): void
    {
        abort_unless(auth()->user()?->can('payment.refund'), 403);
        $this->registration->payments()->findOrFail($paymentId);

        $this->selectedPaymentId = $paymentId;
        $this->refundReason = '';
        $this->showRefundModal = true;
    }

    public function closeRefundModal(): void
    {
        $this->showRefundModal = false;
        $this->selectedPaymentId = null;
    }

    public function processRefund(RefundStripePayment $useCase): void
    {
        abort_unless(auth()->user()?->can('payment.refund'), 403);

        $this->validate([
            'refundReason' => ['required', 'string', 'min:10', 'max:500'],
        ]);

        $payment = $this->registration->payments()->findOrFail($this->selectedPaymentId);

        if (! $payment->canBeRefunded()) {
            session()->flash('error', 'Ce paiement ne peut pas être remboursé.');

            return;
        }

        /** @var User $user */
        $user = auth()->user();

        try {
            $useCase->execute($payment, $user, null, $this->refundReason);
        } catch (InvalidArgumentException $e) {
            session()->flash('error', $e->getMessage());

            return;
        } catch (ApiErrorException) {
            session()->flash('error', 'Stripe n’a pas confirmé le remboursement. Vérifiez son état avant de réessayer.');

            return;
        }

        $this->closeRefundModal();
        $this->registration->refresh();

        session()->flash('success', 'Remboursement effectué avec succès.');
    }

    public function cancelPayment(int $paymentId): void
    {
        abort_unless(auth()->user()?->can('payment.manage'), 403);
        $payment = $this->registration->payments()->findOrFail($paymentId);

        if ($payment->status !== PaymentStatus::PENDING) {
            session()->flash('error', 'Seuls les paiements en attente peuvent être annulés.');

            return;
        }

        $payment->update(['status' => PaymentStatus::CANCELLED]);

        $this->registration->refresh();

        session()->flash('success', 'Paiement annulé.');
    }

    public function render(): View
    {
        return view('livewire.staff.registrations.payment-manager')
            ->layout('layouts.app');
    }
}
