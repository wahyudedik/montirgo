<x-guest-layout>
    {{-- Verify Email Content --}}
    <div class="mb-8 text-center">
        {{-- Email Icon --}}
        <div class="mx-auto mb-4 w-16 h-16 bg-primary-50 rounded-full flex items-center justify-center">
            <svg class="w-8 h-8 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
        </div>

        <h2 class="text-xl font-bold text-dark-800 mb-2">
            {{ __('Verifikasi Email') }}
        </h2>
        <p class="text-sm text-gray-500 leading-relaxed">
            {{ __('Terima kasih telah mendaftar! Sebelum memulai, silakan verifikasi alamat email Anda dengan mengklik tautan yang kami kirimkan ke email Anda.') }}
        </p>
    </div>

    {{-- Status Alert --}}
    @if (session('status') == 'verification-link-sent')
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl flex items-start gap-3">
            <div class="flex-shrink-0 mt-0.5">
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <p class="text-sm text-green-700">
                {{ __('Tautan verifikasi baru telah dikirim ke alamat email yang Anda daftarkan.') }}
            </p>
        </div>
    @endif

    {{-- Resend Verification Email --}}
    <div class="mb-4">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <button
                type="submit"
                class="w-full bg-primary-500 hover:bg-primary-600 text-white font-semibold py-3 px-4 rounded-xl transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 shadow-lg shadow-primary-500/25"
            >
                {{ __('Kirim Ulang Email Verifikasi') }}
            </button>
        </form>
    </div>

    {{-- Logout --}}
    <div class="text-center">
        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button
                type="submit"
                class="text-sm text-gray-500 hover:text-dark-700 font-medium transition-colors duration-200"
            >
                {{ __('Keluar') }}
            </button>
        </form>
    </div>
</x-guest-layout>
