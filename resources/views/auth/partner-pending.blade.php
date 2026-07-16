<x-guest-layout>
    <!-- Header -->
    <div class="text-center mb-6">
        <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-dark">Menunggu Verifikasi</h1>
        <p class="text-gray-500 text-sm mt-1">Akun partner Anda sedang dalam proses verifikasi</p>
    </div>

    <!-- Status Card -->
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-500 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <div class="text-sm text-amber-700">
                <p class="font-semibold">Akun Anda Belum Aktif</p>
                <p class="mt-1">Tim MontirGo akan memverifikasi data bengkel Anda dalam 1-2 hari kerja. Anda akan menerima notifikasi email setelah akun disetujui.</p>
            </div>
        </div>
    </div>

    <!-- Info Steps -->
    <div class="space-y-3 mb-6">
        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl">
            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
            </div>
            <span class="text-sm text-gray-700">Registrasi berhasil</span>
        </div>
        <div class="flex items-center gap-3 p-3 bg-amber-50 rounded-xl">
            <div class="w-8 h-8 bg-amber-100 rounded-full flex items-center justify-center shrink-0">
                <div class="w-2 h-2 bg-amber-500 rounded-full animate-pulse"></div>
            </div>
            <span class="text-sm text-amber-700 font-medium">Menunggu verifikasi admin</span>
        </div>
        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl opacity-50">
            <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center shrink-0">
                <span class="text-xs text-gray-500">3</span>
            </div>
            <span class="text-sm text-gray-500">Mulai menerima order</span>
        </div>
    </div>

    <!-- Logout -->
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="w-full py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-xl transition">
            Keluar
        </button>
    </form>
</x-guest-layout>
