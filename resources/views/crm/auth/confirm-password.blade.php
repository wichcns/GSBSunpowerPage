<x-crm-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('auth_ui.confirm_instructions') }}
    </div>

    <form method="POST" action="{{ route('crm.password.confirm') }}">
        @csrf

        <!-- Password -->
        <div>
            <x-crm::input-label for="password" :value="__('auth_ui.password')" />

            <x-crm::text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-crm::input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex justify-end mt-4">
            <x-crm::primary-button>
                {{ __('auth_ui.confirm') }}
            </x-crm::primary-button>
        </div>
    </form>
</x-crm-guest-layout>
