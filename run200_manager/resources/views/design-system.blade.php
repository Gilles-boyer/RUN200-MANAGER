{{--
    Racing Design System - Component Showcase
    Route: /design-system (dev only)
--}}

<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RUN200 - Design System Racing Premium</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-carbon-50 dark:bg-carbon-950 text-carbon-900 dark:text-carbon-50 antialiased" x-data="{ darkMode: false }" :class="{ 'dark': darkMode }">

    <div class="min-h-screen">
        {{-- Header --}}
        <header class="sticky top-0 z-50 glass-effect border-b border-carbon-200 dark:border-carbon-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">🏎️</span>
                        <h1 class="text-xl font-bold text-racing-gradient">RUN200 Design System</h1>
                    </div>
                    <button
                        @click="darkMode = !darkMode"
                        class="btn-racing-ghost"
                    >
                        <span x-show="!darkMode">🌙 Dark</span>
                        <span x-show="darkMode">☀️ Light</span>
                    </button>
                </div>
            </div>
        </header>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            {{-- Color Palette --}}
            <section class="mb-12">
                <h2 class="text-2xl font-bold mb-6">🎨 Palette de Couleurs</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {{-- Racing Red --}}
                    <div class="card-racing">
                        <div class="h-20 bg-racing-gradient rounded-t-xl"></div>
                        <div class="p-4">
                            <h3 class="font-semibold mb-2">Racing Red (Primary)</h3>
                            <div class="flex gap-1">
                                @foreach([50, 100, 200, 300, 400, 500, 600, 700, 800, 900] as $shade)
                                    <div class="w-6 h-6 rounded" style="background-color: var(--racing-red-{{ $shade }})"></div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Checkered Yellow --}}
                    <div class="card-racing">
                        <div class="h-20 bg-gradient-to-r from-checkered-yellow-400 to-checkered-yellow-600 rounded-t-xl"></div>
                        <div class="p-4">
                            <h3 class="font-semibold mb-2">Checkered Yellow (Accent)</h3>
                            <div class="flex gap-1">
                                @foreach([50, 100, 200, 300, 400, 500, 600, 700, 800, 900] as $shade)
                                    <div class="w-6 h-6 rounded" style="background-color: var(--checkered-yellow-{{ $shade }})"></div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Carbon --}}
                    <div class="card-racing">
                        <div class="h-20 bg-gradient-to-r from-carbon-700 to-carbon-900 rounded-t-xl"></div>
                        <div class="p-4">
                            <h3 class="font-semibold mb-2">Carbon (Neutral)</h3>
                            <div class="flex gap-1">
                                @foreach([50, 100, 200, 300, 400, 500, 600, 700, 800, 900] as $shade)
                                    <div class="w-6 h-6 rounded border border-carbon-300 dark:border-carbon-600" style="background-color: var(--carbon-{{ $shade }})"></div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Semantic Colors --}}
                <div class="mt-6 flex flex-wrap gap-4">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-status-success"></div>
                        <span class="text-sm">Success</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-status-warning"></div>
                        <span class="text-sm">Warning</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-status-danger"></div>
                        <span class="text-sm">Danger</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-status-info"></div>
                        <span class="text-sm">Info</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-status-pending"></div>
                        <span class="text-sm">Pending</span>
                    </div>
                </div>
            </section>

            {{-- Buttons --}}
            <section class="mb-12">
                <h2 class="text-2xl font-bold mb-6">🔘 Boutons</h2>

                <div class="card-racing p-6">
                    <h3 class="font-semibold mb-4">Variants</h3>
                    <div class="flex flex-wrap gap-4 mb-6">
                        <x-racing.button variant="primary">Primary</x-racing.button>
                        <x-racing.button variant="secondary">Secondary</x-racing.button>
                        <x-racing.button variant="ghost">Ghost</x-racing.button>
                        <x-racing.button variant="danger">Danger</x-racing.button>
                        <x-racing.button variant="success">Success</x-racing.button>
                    </div>

                    <h3 class="font-semibold mb-4">Sizes</h3>
                    <div class="flex flex-wrap items-center gap-4 mb-6">
                        <x-racing.button size="sm">Small</x-racing.button>
                        <x-racing.button size="md">Medium</x-racing.button>
                        <x-racing.button size="lg">Large</x-racing.button>
                    </div>

                    <h3 class="font-semibold mb-4">States</h3>
                    <div class="flex flex-wrap gap-4">
                        <x-racing.button disabled>Disabled</x-racing.button>
                        <x-racing.button loading>Loading</x-racing.button>
                        <x-racing.button href="#">Lien →</x-racing.button>
                    </div>
                </div>
            </section>

            {{-- Badges --}}
            <section class="mb-12">
                <h2 class="text-2xl font-bold mb-6">🏷️ Badges Status</h2>

                <div class="card-racing p-6">
                    <div class="flex flex-wrap gap-4">
                        <x-racing.badge-status status="pending">En attente</x-racing.badge-status>
                        <x-racing.badge-status status="success">Accepté</x-racing.badge-status>
                        <x-racing.badge-status status="danger">Refusé</x-racing.badge-status>
                        <x-racing.badge-status status="warning">Attention</x-racing.badge-status>
                        <x-racing.badge-status status="info">Info</x-racing.badge-status>
                        <x-racing.badge-status status="neutral">Neutre</x-racing.badge-status>
                    </div>

                    <h3 class="font-semibold mt-6 mb-4">Registration Status (Auto-mapped)</h3>
                    <div class="flex flex-wrap gap-4">
                        <x-racing.badge-status status="PENDING_VALIDATION">PENDING_VALIDATION</x-racing.badge-status>
                        <x-racing.badge-status status="ACCEPTED">ACCEPTED</x-racing.badge-status>
                        <x-racing.badge-status status="REFUSED">REFUSED</x-racing.badge-status>
                        <x-racing.badge-status status="CANCELLED">CANCELLED</x-racing.badge-status>
                    </div>
                </div>
            </section>

            {{-- Stat Cards --}}
            <section class="mb-12">
                <h2 class="text-2xl font-bold mb-6">📊 Stat Cards</h2>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <x-racing.stat-card value="2" label="Voitures" icon="🚗" />
                    <x-racing.stat-card value="5" label="Inscriptions" icon="📋" highlight />
                    <x-racing.stat-card value="145" label="Points" icon="🏆" trend="up" trendValue="+12" />
                    <x-racing.stat-card value="4" label="Position" icon="🏁" trend="down" trendValue="-1" href="#" />
                </div>
            </section>

            {{-- Progress & Stepper --}}
            <section class="mb-12">
                <h2 class="text-2xl font-bold mb-6">📈 Progress & Stepper</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="card-racing p-6">
                        <h3 class="font-semibold mb-4">Progress Bars</h3>
                        <div class="space-y-4">
                            <x-racing.progress-bar :value="25" :max="100" showLabel />
                            <x-racing.progress-bar :value="50" :max="100" size="sm" />
                            <x-racing.progress-bar :value="75" :max="100" size="lg" />
                            <x-racing.progress-bar :value="100" :max="100" showLabel />
                        </div>
                    </div>

                    <div class="card-racing p-6">
                        <h3 class="font-semibold mb-4">Stepper</h3>
                        <x-racing.stepper
                            :steps="[
                                ['label' => 'Pilote'],
                                ['label' => 'Voiture'],
                                ['label' => 'Confirmation'],
                            ]"
                            :currentStep="2"
                        />

                        <div class="mt-8">
                            <x-racing.stepper
                                :steps="[[], [], [], []]"
                                :currentStep="3"
                            />
                        </div>
                    </div>
                </div>
            </section>

            {{-- Alerts --}}
            <section class="mb-12">
                <h2 class="text-2xl font-bold mb-6">⚠️ Alertes</h2>

                <div class="space-y-4">
                    <x-racing.alert type="success" title="Inscription confirmée">
                        Votre inscription au Grand Prix de Lyon a été validée avec succès.
                    </x-racing.alert>

                    <x-racing.alert type="warning" title="Profil incomplet">
                        Complétez votre profil pour pouvoir vous inscrire aux courses.
                    </x-racing.alert>

                    <x-racing.alert type="danger" title="Inscription refusée" dismissible>
                        Votre inscription a été refusée. Contactez l'organisation pour plus d'informations.
                    </x-racing.alert>

                    <x-racing.alert type="info">
                        Les inscriptions pour le prochain Grand Prix ouvrent dans 3 jours.
                    </x-racing.alert>
                </div>
            </section>

            {{-- Cards --}}
            <section class="mb-12">
                <h2 class="text-2xl font-bold mb-6">🃏 Cards</h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <x-racing.card>
                        <x-slot:header>
                            <h3 class="font-semibold">Card avec Header</h3>
                        </x-slot:header>
                        <p class="text-carbon-600 dark:text-carbon-400">Contenu de la card avec un header coloré racing red.</p>
                        <x-slot:footer>
                            <x-racing.button size="sm" variant="ghost">Action →</x-racing.button>
                        </x-slot:footer>
                    </x-racing.card>

                    <x-racing.card>
                        <h3 class="font-semibold mb-2">Card Simple</h3>
                        <p class="text-carbon-600 dark:text-carbon-400">Une card sans header ni footer, parfaite pour du contenu simple.</p>
                    </x-racing.card>

                    <x-racing.card :hover="false">
                        <h3 class="font-semibold mb-2">Card Sans Hover</h3>
                        <p class="text-carbon-600 dark:text-carbon-400">Cette card ne réagit pas au survol de la souris.</p>
                    </x-racing.card>
                </div>
            </section>

            {{-- Empty States --}}
            <section class="mb-12">
                <h2 class="text-2xl font-bold mb-6">🕳️ Empty States</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-racing.card>
                        <x-racing.empty-state
                            title="Aucune voiture"
                            description="Ajoutez votre première voiture pour pouvoir vous inscrire aux courses."
                            icon="🚗"
                            actionLabel="Ajouter une voiture"
                            actionHref="#"
                        />
                    </x-racing.card>

                    <x-racing.card>
                        <x-racing.empty-state
                            title="Aucune inscription"
                            description="Vous n'avez pas encore d'inscription en cours."
                            icon="📋"
                        />
                    </x-racing.card>
                </div>
            </section>

            {{-- Loading --}}
            <section class="mb-12">
                <h2 class="text-2xl font-bold mb-6">⏳ Loading States</h2>

                <div class="card-racing p-6">
                    <div class="flex flex-wrap items-end gap-8">
                        <div class="text-center">
                            <x-racing.loading-spinner size="sm" />
                            <p class="text-sm mt-2 text-carbon-500">Small</p>
                        </div>
                        <div class="text-center">
                            <x-racing.loading-spinner size="md" />
                            <p class="text-sm mt-2 text-carbon-500">Medium</p>
                        </div>
                        <div class="text-center">
                            <x-racing.loading-spinner size="lg" />
                            <p class="text-sm mt-2 text-carbon-500">Large</p>
                        </div>
                        <div class="text-center">
                            <x-racing.loading-spinner size="md" label="Chargement..." />
                        </div>
                    </div>

                    <h3 class="font-semibold mt-8 mb-4">Skeleton Loading</h3>
                    <div class="space-y-3">
                        <div class="skeleton h-4 w-3/4"></div>
                        <div class="skeleton h-4 w-1/2"></div>
                        <div class="skeleton h-4 w-2/3"></div>
                    </div>
                </div>
            </section>

            {{-- Podium --}}
            <section class="mb-12">
                <h2 class="text-2xl font-bold mb-6">🏆 Podium</h2>

                <div class="card-racing p-6">
                    <x-racing.podium
                        :first="['name' => 'Pierre Dupont', 'points' => 145, 'avatar' => 'PD']"
                        :second="['name' => 'Marie Martin', 'points' => 128, 'avatar' => 'MM']"
                        :third="['name' => 'Jean Bernard', 'points' => 125, 'avatar' => 'JB']"
                    />
                </div>
            </section>

            {{-- Animations --}}
            <section class="mb-12">
                <h2 class="text-2xl font-bold mb-6">✨ Animations</h2>

                <div class="card-racing p-6">
                    <div class="flex flex-wrap gap-8">
                        <div class="text-center">
                            <div class="w-16 h-16 bg-racing-red-500 rounded-lg animate-glow mx-auto"></div>
                            <p class="text-sm mt-2 text-carbon-500">Glow</p>
                        </div>
                        <div class="text-center">
                            <div class="text-4xl animate-wave-flag">🏁</div>
                            <p class="text-sm mt-2 text-carbon-500">Wave Flag</p>
                        </div>
                        <div class="text-center">
                            <x-racing.badge-status status="pending" pulse>Pulse</x-racing.badge-status>
                        </div>
                        <div class="text-center">
                            <div class="hover-lift bg-racing-red-500 text-white px-4 py-2 rounded-lg cursor-pointer">
                                Hover Lift
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Form Components --}}
            <section class="mb-12">
                <h2 class="text-2xl font-bold mb-6">📝 Formulaires</h2>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Inputs --}}
                    <div class="card-racing p-6">
                        <h3 class="font-semibold mb-4">Inputs</h3>
                        <div class="space-y-4">
                            <x-racing.form.input
                                name="name_demo"
                                label="Nom complet"
                                placeholder="Jean Dupont"
                                icon="user"
                                required
                            />
                            <x-racing.form.input
                                name="email_demo"
                                type="email"
                                label="Email"
                                placeholder="jean@example.com"
                                icon="email"
                                hint="Nous ne partagerons jamais votre email"
                            />
                            <x-racing.form.input
                                name="phone_demo"
                                type="tel"
                                label="Téléphone"
                                prefix="+33"
                                placeholder="6 12 34 56 78"
                            />
                            <x-racing.form.input
                                name="price_demo"
                                type="number"
                                label="Prix"
                                suffix="€"
                                placeholder="0.00"
                            />
                        </div>
                    </div>

                    {{-- Select & Textarea --}}
                    <div class="card-racing p-6">
                        <h3 class="font-semibold mb-4">Select & Textarea</h3>
                        <div class="space-y-4">
                            <x-racing.form.select
                                name="category_demo"
                                label="Catégorie"
                                icon="tag"
                                required
                                :options="[
                                    'gt' => 'GT',
                                    'touring' => 'Touring',
                                    'proto' => 'Proto',
                                ]"
                            />
                            <x-racing.form.textarea
                                name="bio_demo"
                                label="Biographie"
                                placeholder="Parlez-nous de vous..."
                                rows="3"
                                maxlength="200"
                                showCount
                            />
                        </div>
                    </div>
                </div>

                {{-- Checkboxes, Radios & Toggles --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
                    <div class="card-racing p-6">
                        <h3 class="font-semibold mb-4">Checkboxes</h3>
                        <div class="space-y-3">
                            <x-racing.form.checkbox
                                name="accept_rules"
                                label="J'accepte le règlement"
                                description="Vous devez accepter pour participer"
                            />
                            <x-racing.form.checkbox
                                name="newsletter"
                                label="S'abonner à la newsletter"
                                checked
                            />
                            <x-racing.form.checkbox
                                name="disabled_demo"
                                label="Option désactivée"
                                disabled
                            />
                        </div>
                    </div>

                    <div class="card-racing p-6">
                        <h3 class="font-semibold mb-4">Radio Buttons</h3>
                        <x-racing.form.radio-group
                            name="payment_demo"
                            :options="[
                                'card' => 'Carte bancaire',
                                'cash' => 'Espèces',
                                'transfer' => 'Virement',
                            ]"
                            selected="card"
                        />
                    </div>

                    <div class="card-racing p-6">
                        <h3 class="font-semibold mb-4">Toggles</h3>
                        <div class="space-y-4">
                            <x-racing.form.toggle
                                name="notifications"
                                label="Notifications"
                                description="Recevoir les alertes"
                                checked
                            />
                            <x-racing.form.toggle
                                name="dark_mode_demo"
                                label="Mode sombre"
                                size="lg"
                            />
                            <x-racing.form.toggle
                                name="premium_demo"
                                label="Premium"
                                color="success"
                                checked
                            />
                        </div>
                    </div>
                </div>

                {{-- Radio Card Style --}}
                <div class="card-racing p-6 mt-6">
                    <h3 class="font-semibold mb-4">Radio Cards</h3>
                    <x-racing.form.radio-group
                        name="car_type_demo"
                        layout="grid"
                        cardStyle
                        :options="[
                            'gt' => ['label' => 'GT', 'description' => 'Voitures de grand tourisme', 'icon' => '🏎️'],
                            'touring' => ['label' => 'Touring', 'description' => 'Voitures de tourisme', 'icon' => '🚗'],
                            'proto' => ['label' => 'Proto', 'description' => 'Prototypes', 'icon' => '🏁'],
                        ]"
                        selected="gt"
                    />
                </div>

                {{-- File Upload --}}
                <div class="card-racing p-6 mt-6">
                    <h3 class="font-semibold mb-4">Upload de fichiers</h3>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <x-racing.form.file-upload
                            name="photo_demo"
                            label="Photo de profil"
                            accept="image"
                            hint="PNG, JPG jusqu'à 5MB"
                        />
                        <x-racing.form.file-upload
                            name="documents_demo"
                            label="Documents"
                            accept="documents"
                            multiple
                            hint="PDF, DOC, XLS (plusieurs fichiers)"
                        />
                    </div>
                </div>
            </section>

            {{-- Modals --}}
            <section class="mb-12">
                <h2 class="text-2xl font-bold mb-6">💬 Modals & Dialogs</h2>

                <div class="card-racing p-6">
                    <div class="flex flex-wrap gap-4">
                        {{-- Basic Modal --}}
                        <x-racing.modal name="demo-modal" title="Modal de démo">
                            <x-slot:trigger>
                                <x-racing.button>Ouvrir Modal</x-racing.button>
                            </x-slot:trigger>

                            <p class="text-carbon-600 dark:text-carbon-400">
                                Ceci est le contenu de la modal. Vous pouvez y mettre n'importe quel contenu.
                            </p>

                            <x-slot:footer>
                                <x-racing.button variant="secondary" size="sm" x-on:click="open = false">Annuler</x-racing.button>
                                <x-racing.button size="sm">Confirmer</x-racing.button>
                            </x-slot:footer>
                        </x-racing.modal>

                        {{-- Confirm Dialog Danger --}}
                        <x-racing.confirm-dialog
                            name="delete-confirm"
                            title="Supprimer l'élément ?"
                            message="Cette action est irréversible. L'élément sera définitivement supprimé."
                            variant="danger"
                            confirmText="Supprimer"
                        >
                            <x-slot:trigger>
                                <x-racing.button variant="danger">Confirm Danger</x-racing.button>
                            </x-slot:trigger>
                        </x-racing.confirm-dialog>

                        {{-- Confirm Dialog Warning --}}
                        <x-racing.confirm-dialog
                            name="warning-confirm"
                            title="Attention"
                            message="Voulez-vous vraiment continuer cette action ?"
                            variant="warning"
                        >
                            <x-slot:trigger>
                                <x-racing.button variant="secondary">Confirm Warning</x-racing.button>
                            </x-slot:trigger>
                        </x-racing.confirm-dialog>
                    </div>
                </div>
            </section>

            {{-- Navigation Components --}}
            <section class="mb-12">
                <h2 class="text-2xl font-bold mb-6">🧭 Navigation</h2>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Navigation Items --}}
                    <div class="card-racing p-6">
                        <h3 class="font-semibold mb-4">Nav Items</h3>
                        <div class="space-y-2 max-w-xs">
                            <x-racing.navigation.nav-item route="design-system" label="Actif" icon="dashboard" />
                            <x-racing.navigation.nav-item route="pilot.dashboard" label="Inactif" icon="flag" />
                            <x-racing.navigation.nav-item route="pilot.races.index" label="Avec Badge" icon="clipboard" badge="12" />
                        </div>
                    </div>

                    {{-- User Menu --}}
                    <div class="card-racing p-6">
                        <h3 class="font-semibold mb-4">User Menu</h3>
                        <div class="flex gap-4">
                            <x-racing.navigation.user-menu />
                        </div>
                    </div>
                </div>

                {{-- Bottom Navigation Preview --}}
                <div class="mt-6 card-racing p-6">
                    <h3 class="font-semibold mb-4">Bottom Navigation (Mobile)</h3>
                    <p class="text-sm text-carbon-500 mb-4">Aperçu de la navigation mobile - visible en bas de l'écran sur mobile</p>
                    <div class="relative bg-carbon-100 dark:bg-carbon-800 rounded-xl p-4 overflow-hidden">
                        <div class="flex items-center justify-around h-16">
                            @foreach([
                                ['icon' => 'home', 'label' => 'Accueil', 'active' => true],
                                ['icon' => 'flag', 'label' => 'Courses', 'active' => false],
                                ['icon' => 'clipboard', 'label' => 'Inscriptions', 'active' => false],
                                ['icon' => 'car', 'label' => 'Voitures', 'active' => false],
                                ['icon' => 'user', 'label' => 'Profil', 'active' => false],
                            ] as $item)
                                <div @class([
                                    'flex flex-col items-center justify-center flex-1',
                                    'text-racing-red-500' => $item['active'],
                                    'text-carbon-500' => !$item['active'],
                                ])>
                                    <span @class([
                                        'flex items-center justify-center w-12 h-8 rounded-2xl',
                                        'bg-racing-red-100 dark:bg-racing-red-900/30' => $item['active'],
                                    ])>
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            @if($item['icon'] === 'home')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                            @elseif($item['icon'] === 'flag')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
                                            @elseif($item['icon'] === 'clipboard')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                            @elseif($item['icon'] === 'car')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            @elseif($item['icon'] === 'user')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            @endif
                                        </svg>
                                    </span>
                                    <span class="mt-1 text-[10px] font-medium">{{ $item['label'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Layout Links --}}
                <div class="mt-6 card-racing p-6">
                    <h3 class="font-semibold mb-4">Layouts Disponibles</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="p-4 rounded-xl bg-carbon-100 dark:bg-carbon-800">
                            <h4 class="font-medium text-racing-red-500">racing.blade.php</h4>
                            <p class="text-sm text-carbon-500 mt-1">Layout unifié pour pilotes, staff et admin avec sidebar responsive</p>
                        </div>
                        <div class="p-4 rounded-xl bg-carbon-100 dark:bg-carbon-800">
                            <h4 class="font-medium text-racing-red-500">racing-public.blade.php</h4>
                            <p class="text-sm text-carbon-500 mt-1">Layout public avec nav horizontale et footer</p>
                        </div>
                        <div class="p-4 rounded-xl bg-carbon-100 dark:bg-carbon-800">
                            <h4 class="font-medium text-racing-red-500">Components</h4>
                            <p class="text-sm text-carbon-500 mt-1">sidebar, nav-item, bottom-nav, user-menu</p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Utilities --}}
            <section class="mb-12">
                <h2 class="text-2xl font-bold mb-6">🛠️ Utilitaires</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="card-racing p-6">
                        <h3 class="font-semibold mb-4">Gradients</h3>
                        <div class="space-y-3">
                            <div class="bg-racing-gradient text-white p-4 rounded-lg">Racing Gradient</div>
                            <div class="bg-racing-gradient-subtle p-4 rounded-lg">Racing Gradient Subtle</div>
                        </div>
                    </div>

                    <div class="card-racing p-6">
                        <h3 class="font-semibold mb-4">Text</h3>
                        <p class="text-racing-gradient text-3xl font-bold">Text Racing Gradient</p>
                        <p class="text-carbon-500 mt-2">Texte secondaire</p>
                        <p class="text-carbon-400 dark:text-carbon-500 text-sm">Texte muted</p>
                    </div>
                </div>
            </section>

        </main>

        {{-- Footer --}}
        <footer class="border-t border-carbon-200 dark:border-carbon-800 py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-carbon-500">
                <p>RUN200 Racing Design System v1.0</p>
                <p class="text-sm mt-1">Built with TailwindCSS 4.0 + Livewire Flux</p>
            </div>
        </footer>
    </div>

</body>
</html>
