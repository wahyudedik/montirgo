<x-guest-layout>
    <!-- Header -->
    <div class="text-center mb-6">
        <h1 class="text-2xl font-bold text-dark">Daftar Sebagai Partner</h1>
        <p class="text-gray-500 text-sm mt-1">Bergabung menjadi mitra bengkel MontirGo</p>
    </div>

    <form method="POST" action="{{ route('partner.register') }}" class="space-y-4">
        @csrf

        <!-- Name -->
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
            <input
                id="name"
                type="text"
                name="name"
                value="{{ old('name') }}"
                required
                autofocus
                autocomplete="name"
                placeholder="Masukkan nama lengkap"
                class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary focus:border-primary transition"
            >
            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Email -->
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autocomplete="username"
                placeholder="nama@email.com"
                class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary focus:border-primary transition"
            >
            @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Phone -->
        <div>
            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon</label>
            <input
                id="phone"
                type="tel"
                name="phone"
                value="{{ old('phone') }}"
                required
                autocomplete="tel"
                placeholder="08xxxxxxxxxx"
                class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary focus:border-primary transition"
            >
            @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Workshop Name -->
        <div>
            <label for="workshop_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Bengkel</label>
            <input
                id="workshop_name"
                type="text"
                name="workshop_name"
                value="{{ old('workshop_name') }}"
                required
                placeholder="Contoh: Bengkel Jaya Motor"
                class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary focus:border-primary transition"
            >
            @error('workshop_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Workshop Address -->
        <div>
            <label for="workshop_address" class="block text-sm font-medium text-gray-700 mb-1">Alamat Bengkel</label>
            <textarea
                id="workshop_address"
                name="workshop_address"
                rows="3"
                required
                placeholder="Alamat lengkap bengkel Anda"
                class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary focus:border-primary transition"
            >{{ old('workshop_address') }}</textarea>
            @error('workshop_address') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="new-password"
                placeholder="Minimal 8 karakter"
                class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary focus:border-primary transition"
            >
            @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
            <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
                placeholder="Ulangi password"
                class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary focus:border-primary transition"
            >
            @error('password_confirmation') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Info Box -->
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-3">
            <div class="flex items-start gap-2">
                <svg class="w-5 h-5 text-amber-500 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <div class="text-xs text-amber-700">
                    <p class="font-semibold">Proses Verifikasi</p>
                    <p class="mt-1">Setelah mendaftar, akun Anda akan diverifikasi oleh admin MontirGo. Anda akan menerima notifikasi setelah akun disetujui.</p>
                </div>
            </div>
        </div>

        <!-- Terms -->
        <p class="text-xs text-gray-500">
            Dengan mendaftar, Anda menyetujui
            <a href="#" class="text-primary hover:underline">Syarat & Ketentuan</a>
            dan
            <a href="#" class="text-primary hover:underline">Kebijakan Privasi</a>
            MontirGo.
        </p>

        <!-- Submit -->
        <button type="submit" class="w-full py-2.5 bg-primary hover:bg-primary-600 text-white font-semibold rounded-xl focus:ring-4 focus:ring-primary/30 transition">
            Daftar Sebagai Partner
        </button>
    </form>

    <!-- Links -->
    <div class="mt-6 text-center space-y-2">
        <p class="text-sm text-gray-500">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="text-primary font-semibold hover:text-primary-600 transition">
                Masuk
            </a>
        </p>
        <p class="text-sm text-gray-500">
            Ingin daftar sebagai customer?
            <a href="{{ route('register') }}" class="text-primary font-semibold hover:text-primary-600 transition">
                Daftar Customer
            </a>
        </p>
    </div>
</x-guest-layout>
