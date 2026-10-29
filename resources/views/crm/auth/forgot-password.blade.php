<x-crm-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
    </div>

    <!-- Session Status -->
    <x-crm::auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('crm.password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-crm::input-label for="email" :value="__('Email')" />
            <x-crm::text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-crm::input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-crm::primary-button>
                {{ __('Email Password Reset Link') }}
            </x-crm::primary-button>
        </div>
    </form>
</x-crm-guest-layout>
