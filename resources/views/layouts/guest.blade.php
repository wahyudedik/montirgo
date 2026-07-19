<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'MontirGo') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-white">
        <div class="min-h-screen flex">
            <!-- Left Branding Panel -->
            <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-dark-900 via-dark-700 to-primary-500 relative overflow-hidden">
                <!-- Decorative Elements -->
                <div class="absolute inset-0">
                    <div class="absolute top-20 left-20 w-72 h-72 bg-primary/10 rounded-full blur-3xl"></div>
                    <div class="absolute bottom-20 right-20 w-96 h-96 bg-primary/5 rounded-full blur-3xl"></div>
                    <div class="absolute top-1/2 left-1/3 w-48 h-48 bg-primary/10 rounded-full blur-2xl"></div>
                </div>

                <div class="relative z-10 flex flex-col items-center justify-center w-full px-12">
                    <!-- Logo -->
                    <a href="/" class="flex items-center gap-3 mb-8">
                        <img src="{{ asset('logo-rm.png') }}" alt="MontirGo" class="h-16 w-auto">
                        <span class="text-3xl font-bold text-white">Montir<span class="text-primary">Go</span></span>
                    </a>

                    <!-- Tagline -->
                    <h1 class="text-3xl font-bold text-white text-center mb-4">
                        Mekanik Darurat<br>
                        <span class="text-primary">Ke Lokasi Anda</span>
                    </h1>
                    <p class="text-gray-300 text-center text-lg max-w-md mb-12">
                        Butuh perbaikan kendaraan mendadak? MontirGo menghubungkan Anda dengan bengkel/mekanik terdekat secara real-time.
                    </p>

                    <!-- Features -->
                    <div class="space-y-4 w-full max-w-sm">
                        <div class="flex items-center gap-3 text-white/80">
                            <div class="w-10 h-10 bg-primary/20 rounded-lg flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            </div>
                            <span class="text-sm">Mekanik terdekat dalam radius 5-30 km</span>
                        </div>
                        <div class="flex items-center gap-3 text-white/80">
                            <div class="w-10 h-10 bg-primary/20 rounded-lg flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <span class="text-sm">Respon cepat, auto-dispatch 60 detik</span>
                        </div>
                        <div class="flex items-center gap-3 text-white/80">
                            <div class="w-10 h-10 bg-primary/20 rounded-lg flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                            </div>
                            <span class="text-sm">Tombol SOS darurat 24/7</span>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="absolute bottom-6 left-0 right-0 text-center text-white/40 text-xs">
                    &copy; {{ date('Y') }} MontirGo. All rights reserved.
                </div>
            </div>

            <!-- Right Form Panel -->
            <div class="w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-12">
                <div class="w-full max-w-md">
                    <!-- Mobile Logo -->
                    <div class="lg:hidden flex items-center justify-center gap-2 mb-8">
                        <a href="/" class="flex items-center gap-2">
                            <img src="{{ asset('logo-rm.png') }}" alt="MontirGo" class="h-10 w-auto">
                            <span class="text-xl font-bold text-dark">Montir<span class="text-primary">Go</span></span>
                        </a>
                    </div>

                    <!-- Form Card -->
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
                        {{ $slot }}
                    </div>

                    <!-- Footer Links -->
                    <div class="mt-6 text-center">
                        <a href="/" class="text-sm text-gray-500 hover:text-primary transition">
                            &larr; Kembali ke beranda
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
