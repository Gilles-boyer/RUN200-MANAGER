<?php

declare(strict_types=1);

namespace App\Livewire\Staff\Registrations;

use App\Application\Payments\UseCases\RecordManualPayment;
use App\Application\Payments\UseCases\RefundStripePayment;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\RaceRegistration;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Computed;
use Livewire\Component;

class PaymentManager extends Component
{
    use AuthorizesRequests;

    public RaceRegistration $registration;

    public bool $showManualPaymentModal = false;

    public bool $showRefundModal = false;

    public int $manualAmount = 5000;

    public string $manualNotes = '';

    public ?int $selectedPaymentId = null;

    public string $refundReason = '';

    public function mount(RaceRegistration $registration): void
    {
        $this->registration = $registration->load(['payments', 'pilot', 'car', 'race']);
    }

    #[Computed]
    public function payments(): \Illuminate\Database\Eloquent\Collection
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
        $this->manualAmount = (int) config('stripe.default_registration_fee', 5000);
        $this->manualNotes = '';
        $this->showManualPaymentModal = true;
    }

    public function closeManualPaymentModal(): void
    {
        $this->showManualPaymentModal = false;
    }

    public function recordManualPayment(RecordManualPayment $useCase): void
    {
        $this->validate([
            'manualAmount' => ['required', 'integer', 'min:100'],
            'manualNotes' => ['nullable', 'string', 'max:1000'],
        ]);

        /** @var \App\Models\User $user */
        $user = auth()->user();

        $useCase->execute(
            $this->registration,
            $user,
            (float) ($this->manualAmount / 100),
            'EUR',
            $this->manualNotes ?: null
        );

        $this->closeManualPaymentModal();
        $this->registration->refresh();

        session()->flash('success', 'Paiement manuel enregistré avec succès.');
    }

    public function openRefundModal(int $paymentId): void
    {
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
        $this->validate([
            'refundReason' => ['required', 'string', 'min:10', 'max:500'],
        ]);

        $payment = Payment::findOrFail($this->selectedPaymentId);

        if (! $payment->canBeRefunded()) {
            session()->flash('error', 'Ce paiement ne peut pas être remboursé.');

            return;
        }

        /** @var \App\Models\User $user */
        $user = auth()->user();

        $useCase->execute($payment, $user, null, $this->refundReason);

        $this->closeRefundModal();
        $this->registration->refresh();

        session()->flash('success', 'Remboursement effectué avec succès.');
    }

    public function cancelPayment(int $paymentId): void
    {
        $payment = Payment::findOrFail($paymentId);

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
