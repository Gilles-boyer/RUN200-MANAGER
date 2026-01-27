<div>
    {{-- Racing Header --}}
    <div class="relative mb-8 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-8 bg-racing-gradient-subtle overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-racing-red-500/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-checkered-yellow-500/5 rounded-full translate-y-1/2 -translate-x-1/2"></div>

        <div class="relative flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-white flex items-center gap-3">
                    <span>🔧</span> Historique des Contrôles Techniques
                </h1>
                <p class="mt-2 text-gray-400">
                    <span class="text-racing-red-500 font-bold">#{{ $car->race_number }}</span>
                    {{ $car->full_name }} — Pilote: <span class="text-white">{{ $car->pilot->user->name }}</span>
                </p>
            </div>
            <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium text-gray-300 bg-carbon-700/50 border border-carbon-600 hover:bg-carbon-700 hover:text-white transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Retour
            </a>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <x-racing.stat-card
            label="Total Contrôles"
            :value="$stats['total']"
            icon="📋"
        />
        <x-racing.stat-card
            label="Contrôles OK"
            :value="$stats['ok']"
            icon="✅"
        />
        <x-racing.stat-card
            label="Contrôles Échoués"
            :value="$stats['fail']"
            icon="❌"
        />
        <x-racing.stat-card
            label="Dernier Contrôle"
            :value="$stats['last_inspection'] ? $stats['last_inspection']->inspected_at->format('d/m/Y') : 'Aucun'"
            icon="⏱️"
        />
    </div>

    {{-- Filters --}}
    <x-racing.card class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-racing.form.select wire:model.live="statusFilter" label="Statut">
                <option value="">Tous</option>
                <option value="OK">OK</option>
                <option value="FAIL">Échoué</option>
            </x-racing.form.select>

            <x-racing.form.select wire:model.live="inspectorFilter" label="Inspecteur">
                <option value="">Tous</option>
                @foreach($inspectors as $inspector)
                    <option value="{{ $inspector->id }}">{{ $inspector->name }}</option>
                @endforeach
            </x-racing.form.select>

            <x-racing.form.input
                type="date"
                wire:model.live="fromDate"
                label="Date Début"
            />

            <x-racing.form.input
                type="date"
                wire:model.live="toDate"
                label="Date Fin"
            />
        </div>

        <div class="mt-4 flex justify-end">
            <x-racing.button wire:click="resetFilters" variant="secondary" size="sm">
                Réinitialiser les filtres
            </x-racing.button>
        </div>
    </x-racing.card>

    {{-- History Table --}}
    <x-racing.card noPadding>
        @if($history->isEmpty())
            <div class="p-8">
                <x-racing.empty-state
                    icon="🔧"
                    title="Aucun contrôle"
                    description="Aucun contrôle technique n'a été effectué pour cette voiture."
                />
            </div>
        @else
            {{-- Version Desktop --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-carbon-800/50 border-b border-carbon-700/50">
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Course</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Statut</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Inspecteur</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Notes/Annotations</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-carbon-700/50">
                        @foreach($history as $inspection)
                            <tr class="hover:bg-carbon-700/30 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-medium text-white">{{ $inspection->inspected_at->format('d/m/Y') }}</span>
                                    <span class="text-sm text-gray-400 ml-1">{{ $inspection->inspected_at->format('H:i') }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($inspection->registration)
                                        <span class="text-sm text-checkered-yellow-500 font-medium">{{ $inspection->registration->race->name }}</span>
                                    @else
                                        <span class="text-sm text-gray-500 italic">Contrôle hors course</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($inspection->status === 'OK')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-status-success/20 text-status-success border border-status-success/30">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                            OK
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-status-danger/20 text-status-danger border border-status-danger/30">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                            ÉCHOUÉ
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                    {{ $inspection->inspector->name }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    @if($inspection->notes)
                                        <div class="max-w-xs">
                                            <p class="text-gray-300 whitespace-pre-wrap">{{ $inspection->notes }}</p>
                                        </div>
                                    @else
                                        <span class="text-gray-500 italic">Aucune note</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Version Mobile (Cards) --}}
            <div class="md:hidden p-4 space-y-4">
                @foreach($history as $inspection)
                    <div class="bg-carbon-800/50 rounded-xl border border-carbon-700 overflow-hidden">
                        {{-- Header de la carte --}}
                        <div class="p-4 bg-carbon-800 border-b border-carbon-700 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center text-lg
                                    {{ $inspection->status === 'OK' ? 'bg-status-success/20' : 'bg-status-danger/20' }}">
                                    {{ $inspection->status === 'OK' ? '✅' : '❌' }}
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-white">{{ $inspection->inspected_at->format('d/m/Y') }}</div>
                                    <div class="text-xs text-gray-400">{{ $inspection->inspected_at->format('H:i') }}</div>
                                </div>
                            </div>
                            @if($inspection->status === 'OK')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-status-success/20 text-status-success border border-status-success/30">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    OK
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-status-danger/20 text-status-danger border border-status-danger/30">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                    ÉCHOUÉ
                                </span>
                            @endif
                        </div>

                        {{-- Contenu --}}
                        <div class="p-4 space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-carbon-400 uppercase tracking-wider">Course</span>
                                @if($inspection->registration)
                                    <span class="text-sm text-checkered-yellow-500 font-medium">{{ $inspection->registration->race->name }}</span>
                                @else
                                    <span class="text-sm text-gray-500 italic">Hors course</span>
                                @endif
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-carbon-400 uppercase tracking-wider">Inspecteur</span>
                                <span class="text-sm text-white">{{ $inspection->inspector->name }}</span>
                            </div>

                            {{-- Notes --}}
                            @if($inspection->notes)
                                <div class="pt-2 border-t border-carbon-700">
                                    <span class="text-xs text-carbon-400 uppercase tracking-wider block mb-2">Notes</span>
                                    <p class="text-sm text-gray-300 whitespace-pre-wrap">{{ $inspection->notes }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            @if($history->hasPages())
                <div class="px-6 py-4 border-t border-carbon-700/50">
                    {{ $history->links() }}
                </div>
            @endif
        @endif
    </x-racing.card>
</div>
