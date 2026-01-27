<?php

namespace App\Livewire\Admin\Races;

use App\Mail\CustomRaceNotification;
use App\Models\Race;
use App\Models\RaceNotification;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Notifications extends Component
{
    use WithPagination;

    public Race $race;

    public string $subject = '';

    public string $message = '';

    public string $type = 'info';

    public ?string $scheduledDate = null;

    public ?string $scheduledTime = null;

    public bool $sendToAll = true;

    public function mount(Race $race): void
    {
        $this->race = $race->load('season');
    }

    public function sendNotification(): void
    {
        $this->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'type' => 'required|in:info,warning,success',
        ]);

        $scheduledAt = null;
        if ($this->scheduledDate && $this->scheduledTime) {
            $scheduledAt = \Carbon\Carbon::parse($this->scheduledDate.' '.$this->scheduledTime);
        }

        $notification = RaceNotification::create([
            'race_id' => $this->race->id,
            'created_by' => auth()->id(),
            'subject' => $this->subject,
            'message' => $this->message,
            'type' => $this->type,
            'recipients' => $this->sendToAll ? null : [],
            'scheduled_at' => $scheduledAt,
        ]);

        // Si pas de planification, envoyer immédiatement
        if (! $scheduledAt) {
            $this->sendNotificationNow($notification);
        }

        $this->reset(['subject', 'message', 'scheduledDate', 'scheduledTime']);
        $this->dispatch('notification-sent');

        session()->flash('success', 'Notification '.($scheduledAt ? 'planifiée' : 'envoyée').' avec succès !');
    }

    private function sendNotificationNow(RaceNotification $notification): void
    {
        $registrations = $this->race->registrations()
            ->with(['pilot.user'])
            ->whereIn('status', ['ACCEPTED', 'PENDING_VALIDATION', 'TECH_CHECKED_OK', 'RACE_READY'])
            ->get();

        // Collecter toutes les adresses email valides
        $bccEmails = [];
        foreach ($registrations as $registration) {
            if ($registration->pilot && $registration->pilot->user) {
                $email = trim($registration->pilot->user->email);

                // Valider l'email avant de l'ajouter
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $bccEmails[] = $email;
                } else {
                    \Log::warning("Email invalide ignoré: {$email} pour {$registration->pilot->user->name}");
                }
            }
        }

        // Envoyer UN SEUL email avec tous les destinataires en BCC
        if (! empty($bccEmails)) {
            try {
                Mail::to(auth()->user()->email) // Destinataire principal = admin qui envoie
                    ->bcc($bccEmails) // Tous les pilotes en copie cachée
                    ->send(new CustomRaceNotification($notification, 'Pilotes inscrits'));

                $sent = count($bccEmails);
                \Log::info("Notification envoyée à {$sent} destinataire(s) en BCC");
            } catch (\Exception $e) {
                \Log::error("Erreur envoi notification groupée: {$e->getMessage()}");
                $sent = 0;
            }
        } else {
            $sent = 0;
            \Log::warning('Aucun email valide à envoyer');
        }

        $notification->update([
            'sent_at' => now(),
            'sent_count' => $sent,
        ]);
    }

    public function resendNotification(int $notificationId): void
    {
        $notification = RaceNotification::findOrFail($notificationId);

        if ($notification->race_id !== $this->race->id) {
            abort(403);
        }

        $this->sendNotificationNow($notification);

        session()->flash('success', 'Notification renvoyée avec succès !');
    }

    public function deleteNotification(int $notificationId): void
    {
        $notification = RaceNotification::findOrFail($notificationId);

        if ($notification->race_id !== $this->race->id) {
            abort(403);
        }

        $notification->delete();

        session()->flash('success', 'Notification supprimée avec succès !');
    }

    public function render()
    {
        $notifications = RaceNotification::where('race_id', $this->race->id)
            ->with('creator')
            ->latest()
            ->paginate(10);

        $registrationsCount = $this->race->registrations()
            ->whereIn('status', ['ACCEPTED', 'PENDING_VALIDATION', 'TECH_CHECKED_OK', 'RACE_READY'])
            ->count();

        return view('livewire.admin.races.notifications', [
            'notifications' => $notifications,
            'registrationsCount' => $registrationsCount,
        ]);
    }
}
