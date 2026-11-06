<?php

declare(strict_types=1);

namespace App\Livewire\Staff\Registrations;

use App\Application\Payments\UseCases\RecordManualPayment;
use App\Application\Payments\UseCases\RefundStripePayment;
use App\Application\Payments\UseCases\VoidRecordedPayment;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\RaceRegistration;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Stripe\Exception\ApiErrorException;

class PaymentManager extends Component
{
    use AuthorizesRequests;

    public RaceRegistration $registration;

    public bool $showManualPaymentModal = false;

    public bool $showRefundModal = false;

    public bool $showVoidPaymentModal = false;

    public string $manualAmount = '50,00';

    public string $manualMethod = 'cash';

    public string $manualNotes = '';

    public ?int $selectedPaymentId = null;

    public string $refundReason = '';

    public string $voidReason = '';

    #[Locked]
    public array $voidSnapshot = [];

    #[Locked]
    public array $voidSummary = [];

    public function mount(RaceRegistration $registration): void
    {
        abort_unless(auth()->user()?->can('payment.view'), 403);
        $this->registration = $registration->load(['payments', 'pilot', 'car', 'race']);
    }

    #[Computed]
    public function payments(): Collection
    {
        return $this->registration->payments()->with('receipt')->orderByDesc('created_at')->get();
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

        $user = auth()->user();
        abort_unless($user instanceof User, 403);

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
        $this->payment($paymentId);

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

        if ($this->selectedPaymentId === null) {
            session()->flash('error', 'Sélectionnez un paiement à rembourser.');

            return;
        }
        $payment = $this->payment($this->selectedPaymentId);

        if (! $payment->canBeRefunded()) {
            session()->flash('error', 'Ce paiement ne peut pas être remboursé.');

            return;
        }

        $user = auth()->user();
        abort_unless($user instanceof User, 403);

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
        $payment = $this->payment($paymentId);

        if ($payment->status !== PaymentStatus::PENDING) {
            session()->flash('error', 'Seuls les paiements en attente peuvent être annulés.');

            return;
        }

        $payment->update(['status' => PaymentStatus::CANCELLED]);

        $this->registration->refresh();

        session()->flash('success', 'Paiement annulé.');
    }

    public function openVoidPaymentModal(int $paymentId): void
    {
        abort_unless(auth()->user()?->can('payment.void'), 403);

        $payment = $this->payment($paymentId)->load('receipt');
        $registration = RaceRegistration::query()
            ->with(['pilot', 'race'])
            ->findOrFail($this->registration->id);

        if (! $payment->isPaid() || $payment->isStripePayment()
            || $payment->stripe_session_id || $payment->stripe_payment_intent_id
            || $payment->stripe_customer_id || $payment->stripe_event_id
            || $payment->refunded_at || (int) $payment->refund_amount_cents > 0) {
            session()->flash('error', 'Cette action est réservée à un paiement local saisi comme payé par erreur.');

            return;
        }

        if (in_array($payment->receipt?->email_status, ['sending', 'failed'], true)) {
            session()->flash('error', 'L’envoi du reçu doit être vérifié par un responsable avant cette correction.');

            return;
        }

        $this->voidSnapshot = [
            'payment_id' => $payment->id,
            'registration_id' => $registration->id,
            'pilot_id' => $registration->pilot_id,
            'status' => $payment->status->value,
            'method' => $payment->method->value,
            'amount_cents' => $payment->amount_cents,
            'receipt_id' => $payment->receipt?->id,
            'receipt_email_status' => $payment->receipt?->email_status,
            'receipt_email_sent_at' => $payment->receipt?->email_sent_at?->toISOString(),
        ];
        $this->voidSummary = [
            'pilot' => trim($registration->pilot->first_name.' '.$registration->pilot->last_name),
            'race' => $registration->race->name,
            'amount' => $payment->formatted_amount,
            'method' => $payment->method_label,
            'receipt_status' => $payment->receipt?->email_status,
        ];
        $this->voidReason = '';
        $this->resetValidation();
        $this->showVoidPaymentModal = true;
    }

    public function closeVoidPaymentModal(): void
    {
        $this->showVoidPaymentModal = false;
        $this->voidSnapshot = [];
        $this->voidSummary = [];
        $this->voidReason = '';
        $this->resetValidation();
    }

    public function voidRecordedPayment(VoidRecordedPayment $useCase): void
    {
        abort_unless(auth()->user()?->can('payment.void'), 403);

        if (! $this->showVoidPaymentModal || ! isset($this->voidSnapshot['payment_id'])) {
            session()->flash('error', 'Sélectionnez le paiement à corriger.');

            return;
        }

        $this->validate([
            'voidReason' => ['required', 'string', 'min:10', 'max:500'],
        ], [
            'voidReason.required' => 'Indiquez pourquoi ce paiement a été enregistré par erreur.',
            'voidReason.min' => 'Expliquez l’erreur en au moins 10 caractères.',
            'voidReason.max' => 'Le motif ne doit pas dépasser 500 caractères.',
        ]);

        $user = auth()->user();
        abort_unless($user instanceof User, 403);

        try {
            $useCase->execute(
                $this->payment((int) $this->voidSnapshot['payment_id']),
                $user,
                $this->voidReason,
                $this->voidSnapshot,
            );
        } catch (InvalidArgumentException $e) {
            session()->flash('error', $e->getMessage());

            return;
        }

        $this->closeVoidPaymentModal();
        $this->registration->refresh();

        session()->flash('success', 'Paiement annulé dans le dossier ; le règlement reste dû.');
    }

    public function render(): View
    {
        return view('livewire.staff.registrations.payment-manager')
            ->layout('layouts.app');
    }

    private function payment(int $paymentId): Payment
    {
        return Payment::query()
            ->where('race_registration_id', $this->registration->id)
            ->findOrFail($paymentId);
    }
}
