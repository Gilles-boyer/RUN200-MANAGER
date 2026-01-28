<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        @php
            $user = auth()->user();

            $homeUrl = route('dashboard');
            if ($user?->isAdmin()) {
                $homeUrl = route('admin.dashboard');
            } elseif ($user?->isStaff()) {
                $homeUrl = route('staff.dashboard');
            } elseif ($user?->isPilot()) {
                $homeUrl = route('pilot.dashboard');
            }
        @endphp

        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ $homeUrl }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Platform')" class="grid">
                    @if ($user?->isPilot())
                        <flux:sidebar.item icon="home" :href="route('pilot.dashboard')" :current="request()->routeIs('pilot.dashboard')" wire:navigate>
                            {{ __('Dashboard') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="user" :href="route('pilot.profile.show')" :current="request()->routeIs('pilot.profile.*')" wire:navigate>
                            {{ __('Mon Profil') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="truck" :href="route('pilot.cars.index')" :current="request()->routeIs('pilot.cars.*')" wire:navigate>
                            {{ __('Mes Voitures') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="flag" :href="route('pilot.races.index')" :current="request()->routeIs('pilot.races.*')" wire:navigate>
                            {{ __('Courses') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="clipboard-document-list" :href="route('pilot.registrations.index')" :current="request()->routeIs('pilot.registrations.*')" wire:navigate>
                            {{ __('Mes Inscriptions') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="trophy" :href="route('pilot.championship')" :current="request()->routeIs('pilot.championship*')" wire:navigate>
                            {{ __('Championnat') }}
                        </flux:sidebar.item>

                    @elseif ($user?->isAdmin())
                        <flux:sidebar.item icon="home" :href="route('admin.dashboard')" :current="request()->routeIs('admin.dashboard')" wire:navigate>
                            {{ __('Dashboard') }}
                        </flux:sidebar.item>

                        <flux:sidebar.group :heading="__('Gestion')" expandable>
                            <flux:sidebar.item icon="calendar" :href="route('admin.seasons.index')" :current="request()->routeIs('admin.seasons.*')" wire:navigate>
                                {{ __('Saisons') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="flag" :href="route('admin.races.index')" :current="request()->routeIs('admin.races.*')" wire:navigate>
                                {{ __('Courses') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="users" :href="route('admin.users.index')" :current="request()->routeIs('admin.users.*')" wire:navigate>
                                {{ __('Utilisateurs') }}
                            </flux:sidebar.item>
                        </flux:sidebar.group>

                        <flux:sidebar.group :heading="__('Configuration')" expandable>
                            <flux:sidebar.item icon="tag" :href="route('admin.car-categories.index')" :current="request()->routeIs('admin.car-categories.*')" wire:navigate>
                                {{ __('Catégories Voitures') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="clipboard-document-check" :href="route('admin.checkpoints.index')" :current="request()->routeIs('admin.checkpoints.*')" wire:navigate>
                                {{ __('Checkpoints') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="map-pin" :href="route('admin.paddock-spots.index')" :current="request()->routeIs('admin.paddock-spots.*')" wire:navigate>
                                {{ __('Emplacements Paddock') }}
                            </flux:sidebar.item>
                        </flux:sidebar.group>

                        <flux:sidebar.group :heading="__('Inscriptions')" expandable>
                            <flux:sidebar.item icon="clipboard-document-list" :href="route('staff.registrations.index')" :current="request()->routeIs('staff.registrations.*')" wire:navigate>
                                {{ __('Toutes les inscriptions') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="document-text" :href="route('staff.races.index')" :current="request()->routeIs('staff.races.*')" wire:navigate>
                                {{ __('Courses & PDF') }}
                            </flux:sidebar.item>
                        </flux:sidebar.group>

                        <flux:sidebar.group :heading="__('Communications')" expandable>
                            <flux:sidebar.item icon="bell" :href="route('admin.races.index')" :current="request()->routeIs('admin.races.notifications')" wire:navigate>
                                {{ __('Notifications par Course') }}
                            </flux:sidebar.item>
                        </flux:sidebar.group>

                        <flux:sidebar.group :heading="__('Contrôles Techniques')" expandable>
                            <flux:sidebar.item icon="truck" :href="route('staff.cars.index')" :current="request()->routeIs('staff.cars.*')" wire:navigate>
                                {{ __('Voitures & Historique') }}
                            </flux:sidebar.item>
                        </flux:sidebar.group>

                        <flux:sidebar.group :heading="__('Paddock')" expandable>
                            <flux:sidebar.item icon="map" :href="route('staff.paddock.manage')" :current="request()->routeIs('staff.paddock.*')" wire:navigate>
                                {{ __('Gestion des Emplacements') }}
                            </flux:sidebar.item>
                        </flux:sidebar.group>

                        <flux:sidebar.group :heading="__('Terrain / Scans')" expandable>
                            <flux:sidebar.item icon="qr-code" :href="route('staff.scan.admin')" :current="request()->routeIs('staff.scan.admin')" wire:navigate>
                                {{ __('Scan Admin') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="wrench-screwdriver" :href="route('staff.scan.tech')" :current="request()->routeIs('staff.scan.tech')" wire:navigate>
                                {{ __('Scan Technique') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="arrow-right-circle" :href="route('staff.scan.entry')" :current="request()->routeIs('staff.scan.entry')" wire:navigate>
                                {{ __('Scan Entrée') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="identification" :href="route('staff.scan.bracelet')" :current="request()->routeIs('staff.scan.bracelet')" wire:navigate>
                                {{ __('Scan Bracelet') }}
                            </flux:sidebar.item>
                        </flux:sidebar.group>

                        <flux:sidebar.group :heading="__('Résultats')" expandable>
                            <flux:sidebar.item icon="arrow-up-tray" :href="route('staff.races.index')" :current="request()->routeIs('staff.races.results')" wire:navigate>
                                {{ __('Import CSV') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="trophy" :href="route('admin.championship', ['season' => \App\Models\Season::active()->first()?->id ?? 1])" :current="request()->routeIs('admin.championship*')" wire:navigate>
                                {{ __('Championnat') }}
                            </flux:sidebar.item>
                        </flux:sidebar.group>

                    @elseif ($user?->isStaff())
                        <flux:sidebar.item icon="home" :href="route('staff.dashboard')" :current="request()->routeIs('staff.dashboard')" wire:navigate>
                            {{ __('Dashboard') }}
                        </flux:sidebar.item>

                        <flux:sidebar.group :heading="__('Inscriptions')" expandable>
                            <flux:sidebar.item icon="clipboard-document-list" :href="route('staff.registrations.index')" :current="request()->routeIs('staff.registrations.*')" wire:navigate>
                                {{ __('Inscriptions') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="document-text" :href="route('staff.races.index')" :current="request()->routeIs('staff.races.*')" wire:navigate>
                                {{ __('Courses & PDF') }}
                            </flux:sidebar.item>
                        </flux:sidebar.group>

                        @can('tech_inspection.manage')
                        <flux:sidebar.group :heading="__('Contrôles Techniques')" expandable>
                            <flux:sidebar.item icon="truck" :href="route('staff.cars.index')" :current="request()->routeIs('staff.cars.*')" wire:navigate>
                                {{ __('Voitures & Historique') }}
                            </flux:sidebar.item>
                        </flux:sidebar.group>
                        @endcan

                        @can('registration.manage')
                        <flux:sidebar.group :heading="__('Paddock')" expandable>
                            <flux:sidebar.item icon="map" :href="route('staff.paddock.manage')" :current="request()->routeIs('staff.paddock.*')" wire:navigate>
                                {{ __('Gestion des Emplacements') }}
                            </flux:sidebar.item>
                        </flux:sidebar.group>
                        @endcan

                        @can('checkpoint.scan.admin_check')
                        <flux:sidebar.group :heading="__('Scanners')" expandable>
                            @can('checkpoint.scan.admin_check')
                            <flux:sidebar.item icon="qr-code" :href="route('staff.scan.admin')" :current="request()->routeIs('staff.scan.admin')" wire:navigate>
                                {{ __('Scan Admin') }}
                            </flux:sidebar.item>
                            @endcan
                            @can('checkpoint.scan.tech_check')
                            <flux:sidebar.item icon="wrench-screwdriver" :href="route('staff.scan.tech')" :current="request()->routeIs('staff.scan.tech')" wire:navigate>
                                {{ __('Scan Technique') }}
                            </flux:sidebar.item>
                            @endcan
                            @can('checkpoint.scan.entry')
                            <flux:sidebar.item icon="arrow-right-circle" :href="route('staff.scan.entry')" :current="request()->routeIs('staff.scan.entry')" wire:navigate>
                                {{ __('Scan Entrée') }}
                            </flux:sidebar.item>
                            @endcan
                            @can('checkpoint.scan.bracelet')
                            <flux:sidebar.item icon="identification" :href="route('staff.scan.bracelet')" :current="request()->routeIs('staff.scan.bracelet')" wire:navigate>
                                {{ __('Scan Bracelet') }}
                            </flux:sidebar.item>
                            @endcan
                        </flux:sidebar.group>
                        @endcan

                    @else
                        <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                            {{ __('Dashboard') }}
                        </flux:sidebar.item>
                    @endif
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>


        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
