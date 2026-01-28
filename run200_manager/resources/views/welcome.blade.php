{{--
    RUN200 Manager - Welcome Page
    Target: Pilots and future pilots
    Theme: Racing Premium with carbon/racing-red colors
--}}
<x-layouts.racing-public title="RUN200 Manager - Plateforme de Gestion des Courses">

    {{-- Hero Section --}}
    <section class="relative min-h-[90vh] flex items-center justify-center overflow-hidden">
        {{-- Animated Background --}}
        <div class="absolute inset-0 bg-gradient-to-br from-carbon-900 via-carbon-950 to-racing-red-950">
            {{-- Racing Lines Pattern --}}
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-0 left-1/4 w-px h-full bg-gradient-to-b from-transparent via-racing-red-500 to-transparent"></div>
                <div class="absolute top-0 left-1/2 w-px h-full bg-gradient-to-b from-transparent via-racing-red-500 to-transparent"></div>
                <div class="absolute top-0 left-3/4 w-px h-full bg-gradient-to-b from-transparent via-racing-red-500 to-transparent"></div>
            </div>
            {{-- Checkered Pattern Overlay --}}
            <div class="absolute bottom-0 left-0 right-0 h-32 bg-gradient-to-t from-carbon-950/50 to-transparent"></div>
        </div>

        {{-- Hero Content --}}
        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            {{-- Logo Animation --}}
            <div class="mb-8 animate-[fade-in-up_0.8s_ease-out]">
                <img src="{{ asset('images/logorun200.svg') }}" alt="RUN200" class="h-24 md:h-32 mx-auto drop-shadow-2xl" />
            </div>

            {{-- Main Heading --}}
            <h1 class="font-display text-4xl md:text-6xl lg:text-7xl font-bold text-white mb-6 animate-[fade-in-up_0.8s_ease-out_0.2s_both]">
                Votre Passion,
                <span class="block text-racing-red-500">Notre Technologie</span>
            </h1>

            {{-- Subheading --}}
            <p class="text-xl md:text-2xl text-carbon-300 max-w-3xl mx-auto mb-10 animate-[fade-in-up_0.8s_ease-out_0.4s_both]">
                La plateforme tout-en-un pour gérer vos inscriptions aux courses, suivre vos résultats et vivre votre passion du sport automobile.
            </p>

            {{-- CTA Buttons --}}
            <div class="flex flex-col sm:flex-row gap-4 justify-center animate-[fade-in-up_0.8s_ease-out_0.6s_both]">
                @guest
                    <a href="{{ route('register') }}" class="btn-racing-primary btn-lg group">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                        Créer mon compte pilote
                        <svg class="w-5 h-5 ml-2 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                    <a href="{{ route('login') }}" class="btn-racing-secondary btn-lg">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                        Se connecter
                    </a>
                @else
                    @php
                        $dashboardRoute = 'pilot.dashboard';
                        if (auth()->user()->isAdmin()) {
                            $dashboardRoute = 'admin.dashboard';
                        } elseif (auth()->user()->isStaff()) {
                            $dashboardRoute = 'staff.dashboard';
                        }
                    @endphp
                    <a href="{{ route($dashboardRoute) }}" class="btn-racing-primary btn-lg group">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Accéder à mon espace
                        <svg class="w-5 h-5 ml-2 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                @endguest
            </div>

            {{-- Scroll Indicator --}}
            <div class="absolute bottom-8 left-1/2 -translate-x-1/2 animate-bounce">
                <svg class="w-8 h-8 text-carbon-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                </svg>
            </div>
        </div>
    </section>

    {{-- Features Section --}}
    <section class="py-20 lg:py-32 bg-carbon-50 dark:bg-carbon-950">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Section Header --}}
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="inline-flex items-center px-4 py-1.5 rounded-full bg-racing-red-100 dark:bg-racing-red-900/30 text-racing-red-600 dark:text-racing-red-400 text-sm font-semibold mb-4">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                    </svg>
                    Fonctionnalités
                </span>
                <h2 class="font-display text-3xl md:text-4xl lg:text-5xl font-bold text-carbon-900 dark:text-white mb-6">
                    Tout ce dont un pilote a besoin
                </h2>
                <p class="text-lg text-carbon-600 dark:text-carbon-400">
                    Une plateforme conçue par des passionnés, pour des passionnés. Gérez votre carrière de pilote en toute simplicité.
                </p>
            </div>

            {{-- Features Grid --}}
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                {{-- Feature 1: Inscription en ligne --}}
                <div class="racing-card p-8 group hover:scale-[1.02] transition-all duration-300">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-racing-red-500 to-racing-red-600 flex items-center justify-center mb-6 shadow-lg group-hover:shadow-racing transition-shadow">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </div>
                    <h3 class="font-display text-xl font-bold text-carbon-900 dark:text-white mb-3">
                        Inscription simplifiée
                    </h3>
                    <p class="text-carbon-600 dark:text-carbon-400 leading-relaxed">
                        Inscrivez-vous aux courses en quelques clics. Sélectionnez votre véhicule, payez en ligne et recevez votre e-carte d'engagement instantanément.
                    </p>
                </div>

                {{-- Feature 2: QR Code --}}
                <div class="racing-card p-8 group hover:scale-[1.02] transition-all duration-300">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-status-info to-blue-600 flex items-center justify-center mb-6 shadow-lg group-hover:shadow-racing transition-shadow">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                        </svg>
                    </div>
                    <h3 class="font-display text-xl font-bold text-carbon-900 dark:text-white mb-3">
                        Pointages QR Code
                    </h3>
                    <p class="text-carbon-600 dark:text-carbon-400 leading-relaxed">
                        Passez les vérifications administrative et technique rapidement grâce à votre QR code personnel. Plus de files d'attente, plus de paperasse.
                    </p>
                </div>

                {{-- Feature 3: Résultats & Classement --}}
                <div class="racing-card p-8 group hover:scale-[1.02] transition-all duration-300">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-checkered-yellow-500 to-checkered-yellow-600 flex items-center justify-center mb-6 shadow-lg group-hover:shadow-racing transition-shadow">
                        <svg class="w-7 h-7 text-carbon-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <h3 class="font-display text-xl font-bold text-carbon-900 dark:text-white mb-3">
                        Résultats & Classement
                    </h3>
                    <p class="text-carbon-600 dark:text-carbon-400 leading-relaxed">
                        Consultez vos résultats en temps réel et suivez votre progression dans le championnat. Général et par catégorie.
                    </p>
                </div>

                {{-- Feature 4: Profil Pilote --}}
                <div class="racing-card p-8 group hover:scale-[1.02] transition-all duration-300">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-status-success to-green-600 flex items-center justify-center mb-6 shadow-lg group-hover:shadow-racing transition-shadow">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <h3 class="font-display text-xl font-bold text-carbon-900 dark:text-white mb-3">
                        Profil pilote complet
                    </h3>
                    <p class="text-carbon-600 dark:text-carbon-400 leading-relaxed">
                        Gérez votre profil, votre licence et vos informations personnelles. Ajoutez autant de véhicules que vous le souhaitez.
                    </p>
                </div>

                {{-- Feature 5: Paiement sécurisé --}}
                <div class="racing-card p-8 group hover:scale-[1.02] transition-all duration-300">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-status-pending to-purple-600 flex items-center justify-center mb-6 shadow-lg group-hover:shadow-racing transition-shadow">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                    <h3 class="font-display text-xl font-bold text-carbon-900 dark:text-white mb-3">
                        Paiement sécurisé
                    </h3>
                    <p class="text-carbon-600 dark:text-carbon-400 leading-relaxed">
                        Payez vos inscriptions en toute sécurité via Stripe. Cartes bancaires acceptées, transactions cryptées.
                    </p>
                </div>

                {{-- Feature 6: Tableau d'affichage --}}
                <div class="racing-card p-8 group hover:scale-[1.02] transition-all duration-300">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-orange-500 to-orange-600 flex items-center justify-center mb-6 shadow-lg group-hover:shadow-racing transition-shadow">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="font-display text-xl font-bold text-carbon-900 dark:text-white mb-3">
                        Tableau d'affichage numérique
                    </h3>
                    <p class="text-carbon-600 dark:text-carbon-400 leading-relaxed">
                        Accédez aux documents officiels, communiqués et informations importantes directement depuis votre espace pilote.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- How it Works Section --}}
    <section class="py-20 lg:py-32 bg-white dark:bg-carbon-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Section Header --}}
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="inline-flex items-center px-4 py-1.5 rounded-full bg-checkered-yellow-100 dark:bg-checkered-yellow-900/30 text-checkered-yellow-700 dark:text-checkered-yellow-400 text-sm font-semibold mb-4">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                    </svg>
                    Comment ça marche
                </span>
                <h2 class="font-display text-3xl md:text-4xl lg:text-5xl font-bold text-carbon-900 dark:text-white mb-6">
                    En piste en 4 étapes
                </h2>
                <p class="text-lg text-carbon-600 dark:text-carbon-400">
                    De l'inscription à la ligne d'arrivée, on simplifie tout.
                </p>
            </div>

            {{-- Steps --}}
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                {{-- Step 1 --}}
                <div class="relative text-center">
                    <div class="w-16 h-16 rounded-full bg-racing-red-500 text-white font-display text-2xl font-bold flex items-center justify-center mx-auto mb-6 shadow-racing">
                        1
                    </div>
                    <h3 class="font-display text-lg font-bold text-carbon-900 dark:text-white mb-2">Créez votre compte</h3>
                    <p class="text-carbon-600 dark:text-carbon-400 text-sm">
                        Renseignez votre profil pilote et ajoutez votre numéro de licence ASA.
                    </p>
                    {{-- Connector --}}
                    <div class="hidden lg:block absolute top-8 left-[60%] w-[80%] h-0.5 bg-gradient-to-r from-racing-red-500 to-transparent"></div>
                </div>

                {{-- Step 2 --}}
                <div class="relative text-center">
                    <div class="w-16 h-16 rounded-full bg-racing-red-500 text-white font-display text-2xl font-bold flex items-center justify-center mx-auto mb-6 shadow-racing">
                        2
                    </div>
                    <h3 class="font-display text-lg font-bold text-carbon-900 dark:text-white mb-2">Ajoutez vos véhicules</h3>
                    <p class="text-carbon-600 dark:text-carbon-400 text-sm">
                        Enregistrez vos voitures avec leur numéro de course unique.
                    </p>
                    {{-- Connector --}}
                    <div class="hidden lg:block absolute top-8 left-[60%] w-[80%] h-0.5 bg-gradient-to-r from-racing-red-500 to-transparent"></div>
                </div>

                {{-- Step 3 --}}
                <div class="relative text-center">
                    <div class="w-16 h-16 rounded-full bg-racing-red-500 text-white font-display text-2xl font-bold flex items-center justify-center mx-auto mb-6 shadow-racing">
                        3
                    </div>
                    <h3 class="font-display text-lg font-bold text-carbon-900 dark:text-white mb-2">Inscrivez-vous</h3>
                    <p class="text-carbon-600 dark:text-carbon-400 text-sm">
                        Choisissez une course, sélectionnez votre véhicule et payez en ligne.
                    </p>
                    {{-- Connector --}}
                    <div class="hidden lg:block absolute top-8 left-[60%] w-[80%] h-0.5 bg-gradient-to-r from-racing-red-500 to-transparent"></div>
                </div>

                {{-- Step 4 --}}
                <div class="relative text-center">
                    <div class="w-16 h-16 rounded-full bg-checkered-yellow-500 text-carbon-900 font-display text-2xl font-bold flex items-center justify-center mx-auto mb-6 shadow-racing">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 6a3 3 0 013-3h10a1 1 0 01.8 1.6L14.25 8l2.55 3.4A1 1 0 0116 13H6a1 1 0 00-1 1v3a1 1 0 11-2 0V6z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h3 class="font-display text-lg font-bold text-carbon-900 dark:text-white mb-2">En piste !</h3>
                    <p class="text-carbon-600 dark:text-carbon-400 text-sm">
                        Présentez votre QR code aux checkpoints et profitez de la course !
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Stats Section --}}
    <section class="py-16 bg-gradient-to-br from-racing-red-600 via-racing-red-700 to-racing-red-800 relative overflow-hidden">
        {{-- Pattern Background --}}
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%23ffffff&quot; fill-opacity=&quot;0.4&quot;%3E%3Cpath d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="font-display text-4xl md:text-5xl font-bold text-white mb-2">100%</div>
                    <div class="text-racing-red-200 font-medium">Dématérialisé</div>
                </div>
                <div class="text-center">
                    <div class="font-display text-4xl md:text-5xl font-bold text-white mb-2">24/7</div>
                    <div class="text-racing-red-200 font-medium">Accessible</div>
                </div>
                <div class="text-center">
                    <div class="font-display text-4xl md:text-5xl font-bold text-white mb-2">0</div>
                    <div class="text-racing-red-200 font-medium">Paperasse</div>
                </div>
                <div class="text-center">
                    <div class="font-display text-4xl md:text-5xl font-bold text-white mb-2">∞</div>
                    <div class="text-racing-red-200 font-medium">Passion</div>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA Section --}}
    <section class="py-20 lg:py-32 bg-carbon-50 dark:bg-carbon-950">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="racing-card p-12 lg:p-16">
                <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-racing-red-500 to-racing-red-600 flex items-center justify-center mx-auto mb-8 shadow-racing">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <h2 class="font-display text-3xl md:text-4xl font-bold text-carbon-900 dark:text-white mb-6">
                    Prêt à prendre le départ ?
                </h2>
                <p class="text-lg text-carbon-600 dark:text-carbon-400 mb-10 max-w-2xl mx-auto">
                    Rejoignez la communauté des pilotes RUN200 et simplifiez votre expérience sur circuit. Inscription gratuite, rapide et sécurisée.
                </p>
                @guest
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="{{ route('register') }}" class="btn-racing-primary btn-lg">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                            </svg>
                            Créer mon compte gratuitement
                        </a>
                    </div>
                @else
                    <a href="{{ route('pilot.races.index') }}" class="btn-racing-primary btn-lg">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Voir les courses disponibles
                    </a>
                @endguest
            </div>
        </div>
    </section>

</x-layouts.racing-public>
