<x-layouts::auth>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Connexion')" :description="__('Entrez vos identifiants pour accéder à votre compte')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-5">
            @csrf

            <!-- Email Address -->
            <div>
                <label for="email" class="block text-sm font-medium text-carbon-700 dark:text-carbon-300 mb-1.5">
                    {{ __('Adresse email') }}
                </label>
                <input
                    type="email"
                    name="email"
                    id="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="email"
                    placeholder="email@example.com"
                    class="w-full px-4 py-2.5 rounded-xl border-2 border-carbon-200 dark:border-carbon-700 bg-white dark:bg-carbon-800 text-carbon-900 dark:text-white placeholder-carbon-400 dark:placeholder-carbon-500 focus:border-racing-red-500 focus:ring-2 focus:ring-racing-red-500/20 transition-all duration-200"
                />
                @error('email')
                    <p class="mt-1.5 text-sm text-status-danger">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="password" class="block text-sm font-medium text-carbon-700 dark:text-carbon-300">
                        {{ __('Mot de passe') }}
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-sm text-racing-red-500 hover:text-racing-red-600 dark:text-racing-red-400 dark:hover:text-racing-red-300 font-medium transition-colors" wire:navigate>
                            {{ __('Mot de passe oublié ?') }}
                        </a>
                    @endif
                </div>
                <input
                    type="password"
                    name="password"
                    id="password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                    class="w-full px-4 py-2.5 rounded-xl border-2 border-carbon-200 dark:border-carbon-700 bg-white dark:bg-carbon-800 text-carbon-900 dark:text-white placeholder-carbon-400 dark:placeholder-carbon-500 focus:border-racing-red-500 focus:ring-2 focus:ring-racing-red-500/20 transition-all duration-200"
                />
                @error('password')
                    <p class="mt-1.5 text-sm text-status-danger">{{ $message }}</p>
                @enderror
            </div>

            <!-- Remember Me -->
            <label class="flex items-center gap-3 cursor-pointer group">
                <div class="relative">
                    <input type="checkbox" name="remember" class="peer sr-only" {{ old('remember') ? 'checked' : '' }}>
                    <div class="w-5 h-5 rounded-md border-2 border-carbon-300 dark:border-carbon-600 bg-white dark:bg-carbon-800 peer-checked:bg-racing-red-500 peer-checked:border-racing-red-500 peer-focus:ring-2 peer-focus:ring-racing-red-500/20 transition-all duration-200 flex items-center justify-center group-hover:border-racing-red-400">
                        <svg class="w-3 h-3 text-white opacity-0 peer-checked:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </div>
                <span class="text-sm text-carbon-700 dark:text-carbon-300">{{ __('Se souvenir de moi') }}</span>
            </label>

            <button
                type="submit"
                data-test="login-button"
                class="w-full py-3 px-4 bg-racing-red-500 hover:bg-racing-red-600 text-white font-semibold rounded-xl shadow-lg shadow-racing-red-500/30 hover:shadow-racing-red-500/50 focus:outline-none focus:ring-2 focus:ring-racing-red-500 focus:ring-offset-2 dark:focus:ring-offset-carbon-900 transition-all duration-200 transform hover:-translate-y-0.5 active:translate-y-0"
            >
                {{ __('Se connecter') }}
            </button>
        </form>

        @if (Route::has('register'))
            <div class="text-center pt-4 border-t border-carbon-200 dark:border-carbon-700">
                <p class="text-sm text-carbon-600 dark:text-carbon-400">
                    {{ __('Pas encore de compte ?') }}
                    <a href="{{ route('register') }}" class="font-semibold text-racing-red-500 hover:text-racing-red-600 dark:text-racing-red-400 dark:hover:text-racing-red-300 transition-colors" wire:navigate>
                        {{ __('Créer un compte') }}
                    </a>
                </p>
            </div>
        @endif
    </div>
</x-layouts::auth>
