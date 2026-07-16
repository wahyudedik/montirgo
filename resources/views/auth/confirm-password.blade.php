<x-guest-layout>
    {{-- Confirm Password Content --}}
    <div class="mb-8 text-center">
        {{-- Shield Icon --}}
        <div class="mx-auto mb-4 w-16 h-16 bg-primary-50 rounded-full flex items-center justify-center">
            <svg class="w-8 h-8 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
        </div>

        <h2 class="text-xl font-bold text-dark-800 mb-2">
            {{ __('Konfirmasi Password') }}
        </h2>
        <p class="text-sm text-gray-500 leading-relaxed">
            {{ __('Ini adalah area aman aplikasi. Silakan konfirmasi password Anda sebelum melanjutkan.') }}
        </p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        {{-- Password --}}
        <div class="mb-6">
            <x-input-label for="password" :value="__('Password')" class="text-dark-700 font-medium text-sm mb-1.5" />

            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <x-text-input
                    id="password"
                    class="block w-full pl-11 pr-4 py-3 border-gray-300 rounded-xl focus:border-primary-500 focus:ring-primary-500 shadow-sm"
                    type="password"
                    name="password"
                    placeholder="Masukkan password Anda"
                    required
                    autocomplete="current-password"
                />
            </div>

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        {{-- Tombol Konfirmasi --}}
        <button
            type="submit"
            class="w-full bg-primary-500 hover:bg-primary-600 text-white font-semibold py-3 px-4 rounded-xl transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 shadow-lg shadow-primary-500/25"
        >
            {{ __('Konfirmasi') }}
        </button>
    </form>
</x-guest-layout>
