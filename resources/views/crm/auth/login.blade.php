<x-crm-guest-layout>
    <div class="mb-8 text-center">
        <p class="text-sm font-semibold uppercase tracking-[0.24em] text-cyan-600">GSB EV</p>
        <h1 class="mt-2 text-2xl font-bold text-slate-900">GSB EV CRM</h1>
        <p class="mt-2 text-sm text-slate-600">{{ __('auth_ui.subtitle') }}</p>
    </div>

    <!-- Session Status -->
    <x-crm::auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('crm.login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-crm::input-label for="email" :value="__('auth_ui.email')" />
            <x-crm::text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-crm::input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-crm::input-label for="password" :value="__('auth_ui.password')" />

            <x-crm::text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-crm::input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('auth_ui.remember') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('crm.password.request') }}">
                    {{ __('auth_ui.forgot') }}
                </a>
            @endif

            <x-crm::primary-button class="ms-3">
                {{ __('auth_ui.sign_in') }}
            </x-crm::primary-button>
        </div>
    </form>
</x-crm-guest-layout>
