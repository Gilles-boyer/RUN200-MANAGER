<x-layouts::auth>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Créer un compte pilote')" :description="__('Remplissez les informations ci-dessous pour créer votre compte')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <!-- Info box -->
        <div class="rounded-lg bg-blue-50 dark:bg-blue-900/20 p-4 text-sm text-blue-700 dark:text-blue-300">
            <div class="flex items-start gap-3">
                <svg class="h-5 w-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
                <div>
                    <p class="font-medium">Inscription pilote</p>
                    <p class="mt-1">Après votre inscription, vous devrez compléter votre profil à 100% et enregistrer au moins une voiture pour pouvoir vous inscrire aux courses.</p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-6">
            @csrf

            <div class="space-y-1">
                <h3 class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Informations personnelles</h3>
                <div class="border-t border-zinc-200 dark:border-zinc-700"></div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- First Name -->
                <flux:input
                    name="first_name"
                    :label="__('Prénom')"
                    :value="old('first_name')"
                    type="text"
                    required
                    autofocus
                    autocomplete="given-name"
                    :placeholder="__('Votre prénom')"
                />

                <!-- Last Name -->
                <flux:input
                    name="last_name"
                    :label="__('Nom')"
                    :value="old('last_name')"
                    type="text"
                    required
                    autocomplete="family-name"
                    :placeholder="__('Votre nom')"
                />
            </div>

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Adresse email')"
                :value="old('email')"
                type="email"
                required
                autocomplete="email"
                placeholder="email@example.com"
            />

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Phone -->
                <flux:input
                    name="phone"
                    :label="__('Téléphone')"
                    :value="old('phone')"
                    type="tel"
                    required
                    autocomplete="tel"
                    placeholder="+33 6 12 34 56 78"
                />

                <!-- License Number -->
                <flux:input
                    name="license_number"
                    :label="__('Numéro de licence FFSA')"
                    :value="old('license_number')"
                    type="text"
                    required
                    maxlength="6"
                    pattern="[0-9]+"
                    :placeholder="__('123456')"
                    description="Numéro à 6 chiffres maximum"
                />
            </div>

            <div class="space-y-1 mt-2">
                <h3 class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Sécurité du compte</h3>
                <div class="border-t border-zinc-200 dark:border-zinc-700"></div>
            </div>

            <!-- Password -->
            <flux:input
                name="password"
                :label="__('Mot de passe')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Mot de passe')"
                viewable
            />

            <!-- Confirm Password -->
            <flux:input
                name="password_confirmation"
                :label="__('Confirmer le mot de passe')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Confirmer le mot de passe')"
                viewable
            />

            <!-- Terms acceptance (optional) -->
            <div class="flex items-start gap-2">
                <input type="checkbox" name="terms" id="terms" required class="mt-1 rounded border-zinc-300 text-blue-600 shadow-sm focus:ring-blue-500">
                <label for="terms" class="text-sm text-zinc-600 dark:text-zinc-400">
                    J'accepte les <a href="#" class="text-blue-600 hover:underline">conditions générales d'utilisation</a> et la <a href="#" class="text-blue-600 hover:underline">politique de confidentialité</a>
                </label>
            </div>

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full" data-test="register-user-button">
                    {{ __('Créer mon compte pilote') }}
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>{{ __('Vous avez déjà un compte ?') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('Se connecter') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
