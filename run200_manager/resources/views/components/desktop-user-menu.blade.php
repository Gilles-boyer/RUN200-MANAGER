<flux:dropdown position="top" align="start">
    <flux:sidebar.profile
        {{ $attributes->only('name') }}
        :initials="auth()->user()->initials()"
        icon:trailing="chevrons-up-down"
        data-test="sidebar-menu-button"
    />

    <flux:menu>
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
        <flux:menu.separator />
        <flux:menu.radio.group>
            <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                {{ __('Settings') }}
            </flux:menu.item>
            {{-- Theme Toggle - Disabled until light mode is properly supported --}}
            {{-- Dark mode is forced for optimal readability with Racing Design System --}}
            <flux:menu.item
                as="button"
                type="button"
                x-data="{ isDark: true }"
                @click.prevent="window.toggleDarkMode()"
                class="w-full cursor-not-allowed opacity-50"
                title="{{ __('Le mode sombre est requis pour une lisibilité optimale') }}"
            >
                <span class="flex items-center gap-2">
                    <flux:icon.moon class="size-4" />
                    {{ __('Mode sombre (requis)') }}
                </span>
            </flux:menu.item>
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
        </flux:menu.radio.group>
    </flux:menu>
</flux:dropdown>
