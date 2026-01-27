<div class="space-y-6">
    {{-- Racing Header --}}
    <div class="relative overflow-hidden rounded-xl bg-racing-gradient-subtle border border-carbon-700/50 p-6">
        <div class="absolute top-0 right-0 w-32 h-32 opacity-5">
            <svg viewBox="0 0 100 100" fill="currentColor" class="text-racing-red-500">
                <path d="M50 5L90 25v50L50 95 10 75V25L50 5z"/>
            </svg>
        </div>
        <div class="relative flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-white flex items-center gap-3">
                    <div class="p-2 bg-checkered-yellow-500/20 rounded-lg">
                        <svg class="w-6 h-6 text-checkered-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    Notifications de Course
                </h1>
                <p class="mt-1 text-carbon-400">
                    {{ $race->name }} - {{ $race->race_date->format('d/m/Y') }}
                </p>
            </div>
            <x-racing.button href="{{ route('admin.races.index') }}" variant="secondary" wire:navigate>
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour aux courses
            </x-racing.button>
        </div>
    </div>

    {{-- Stats --}}
    <x-racing.alert type="info">
        <div class="flex items-center gap-2">
            <svg class="h-5 w-5 text-status-info" fill="currentColor" viewBox="0 0 20 20">
                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
            </svg>
            <span><strong>{{ $registrationsCount }}</strong> pilote(s) inscrit(s) recevront vos notifications</span>
        </div>
    </x-racing.alert>

    {{-- Formulaire d'envoi --}}
    <x-racing.card>
        <x-slot name="header">
            <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-racing-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
                Envoyer une notification
            </h2>
        </x-slot>

        <form wire:submit="sendNotification" class="space-y-6">
            {{-- Type de notification --}}
            <div>
                <label class="block text-sm font-medium text-carbon-300 mb-3">Type de notification</label>
                <div class="flex flex-wrap gap-4">
                    <label class="flex items-center cursor-pointer group">
                        <input type="radio" wire:model="type" value="info" class="sr-only peer">
                        <span class="flex items-center gap-2 px-4 py-2 rounded-lg border border-carbon-600 bg-carbon-800 text-carbon-300 peer-checked:border-status-info peer-checked:bg-status-info/10 peer-checked:text-status-info transition-all group-hover:border-carbon-500">
                            <span class="text-lg">ℹ️</span>
                            Information
                        </span>
                    </label>
                    <label class="flex items-center cursor-pointer group">
                        <input type="radio" wire:model="type" value="warning" class="sr-only peer">
                        <span class="flex items-center gap-2 px-4 py-2 rounded-lg border border-carbon-600 bg-carbon-800 text-carbon-300 peer-checked:border-status-warning peer-checked:bg-status-warning/10 peer-checked:text-status-warning transition-all group-hover:border-carbon-500">
                            <span class="text-lg">⚠️</span>
                            Avertissement
                        </span>
                    </label>
                    <label class="flex items-center cursor-pointer group">
                        <input type="radio" wire:model="type" value="success" class="sr-only peer">
                        <span class="flex items-center gap-2 px-4 py-2 rounded-lg border border-carbon-600 bg-carbon-800 text-carbon-300 peer-checked:border-status-success peer-checked:bg-status-success/10 peer-checked:text-status-success transition-all group-hover:border-carbon-500">
                            <span class="text-lg">✅</span>
                            Succès
                        </span>
                    </label>
                </div>
                @error('type')
                    <p class="mt-1 text-sm text-status-danger">{{ $message }}</p>
                @enderror
            </div>

            {{-- Sujet --}}
            <x-racing.form.input
                label="Sujet"
                wire:model="subject"
                id="subject"
                required
                placeholder="Ex: Lien pour le chronométrage en ligne"
                :error="$errors->first('subject')"
            />

            {{-- Message --}}
            <x-racing.form.textarea
                label="Message"
                wire:model="message"
                id="message"
                rows="6"
                required
                placeholder="Votre message aux pilotes inscrits..."
                :error="$errors->first('message')"
            />

            {{-- Planification --}}
            <div class="p-4 bg-carbon-800/50 rounded-xl border border-carbon-700/50">
                <h3 class="text-sm font-medium text-white mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-checkered-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Planification (optionnel)
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-racing.form.input
                        type="date"
                        label="Date"
                        wire:model="scheduledDate"
                        id="scheduledDate"
                    />
                    <x-racing.form.input
                        type="time"
                        label="Heure"
                        wire:model="scheduledTime"
                        id="scheduledTime"
                    />
                </div>
                <p class="text-xs text-carbon-500 mt-2">
                    Laissez vide pour envoyer immédiatement
                </p>
            </div>

            {{-- Bouton d'envoi --}}
            <div class="flex justify-end">
                <x-racing.button type="submit">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    {{ ($scheduledDate && $scheduledTime) ? 'Planifier l\'envoi' : 'Envoyer maintenant' }}
                </x-racing.button>
            </div>
        </form>
    </x-racing.card>

    {{-- Historique des notifications --}}
    <x-racing.card>
        <x-slot name="header">
            <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-status-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Historique des notifications
            </h2>
        </x-slot>

        @if($notifications->isEmpty())
            <x-racing.empty-state
                icon='<svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>'
                title="Aucune notification"
                description="Commencez par envoyer une notification aux pilotes inscrits."
            />
        @else
            <div class="divide-y divide-carbon-700/50">
                @foreach($notifications as $notification)
                    <div class="py-4 first:pt-0 last:pb-0">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-lg">
                                        @if($notification->type === 'warning') ⚠️
                                        @elseif($notification->type === 'success') ✅
                                        @else ℹ️
                                        @endif
                                    </span>
                                    <h3 class="text-base font-medium text-white">
                                        {{ $notification->subject }}
                                    </h3>
                                </div>

                                <p class="mt-2 text-sm text-carbon-400 whitespace-pre-wrap">
                                    {{ Str::limit($notification->message, 200) }}
                                </p>

                                <div class="mt-3 flex flex-wrap items-center gap-3 text-xs">
                                    <span class="text-carbon-500">Par {{ $notification->creator->name }}</span>
                                    @if($notification->sent_at)
                                        <span class="flex items-center gap-1 text-status-success">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Envoyée le {{ $notification->sent_at->format('d/m/Y à H:i') }}
                                        </span>
                                        <span class="px-2 py-0.5 rounded bg-carbon-700 text-carbon-300">
                                            {{ $notification->sent_count }} destinataire(s)
                                        </span>
                                    @elseif($notification->scheduled_at)
                                        <span class="flex items-center gap-1 text-status-warning">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            Planifiée pour le {{ $notification->scheduled_at->format('d/m/Y à H:i') }}
                                        </span>
                                    @else
                                        <span class="text-carbon-500">En attente d'envoi</span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                @if($notification->sent_at)
                                    <button
                                        wire:click="resendNotification({{ $notification->id }})"
                                        wire:confirm="Renvoyer cette notification à tous les inscrits ?"
                                        class="p-2 text-carbon-400 hover:text-status-info hover:bg-status-info/10 rounded-lg transition-colors"
                                        title="Renvoyer"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                        </svg>
                                    </button>
                                @endif
                                <button
                                    wire:click="deleteNotification({{ $notification->id }})"
                                    wire:confirm="Supprimer cette notification ?"
                                    class="p-2 text-carbon-400 hover:text-status-danger hover:bg-status-danger/10 rounded-lg transition-colors"
                                    title="Supprimer"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 pt-4 border-t border-carbon-700/50">
                {{ $notifications->links() }}
            </div>
        @endif
    </x-racing.card>
</div>
