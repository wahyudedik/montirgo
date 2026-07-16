<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'MontirGo') }} — Mekanik Darurat Ke Lokasi Anda</title>
        <meta name="description" content="MontirGo menghubungkan pengendara dengan bengkel/mekanik terdekat secara real-time.">
        @fonts
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
        <style>[x-cloak]{display:none!important}</style>
    </head>
    <body class="bg-white text-dark antialiased" x-data="{ mobileMenu: false }">

        {{-- NAVBAR --}}
        <nav class="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-lg border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16 lg:h-20">
                    <a href="/" class="flex items-center gap-2.5">
                        <img src="{{ asset('logo-rm.png') }}" alt="MontirGo" class="h-10 lg:h-12">
                    </a>
                    <div class="hidden lg:flex items-center gap-8">
                        <a href="#layanan" class="text-sm font-medium text-gray-600 hover:text-primary transition-colors">Layanan</a>
                        <a href="#cara-kerja" class="text-sm font-medium text-gray-600 hover:text-primary transition-colors">Cara Kerja</a>
                        <a href="#sos" class="text-sm font-medium text-gray-600 hover:text-primary transition-colors">SOS Darurat</a>
                        <a href="#mitra" class="text-sm font-medium text-gray-600 hover:text-primary transition-colors">Jadi Mitra</a>
                    </div>
                    <div class="hidden lg:flex items-center gap-3">
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}" class="text-sm font-medium text-gray-600 hover:text-primary px-4 py-2 transition-colors">Masuk</a>
                        @endif
                        <a href="#download" class="inline-flex items-center gap-2 bg-primary hover:bg-primary-600 text-white text-sm font-semibold px-5 py-2.5 rounded-full transition-all shadow-lg shadow-primary/25 hover:shadow-xl hover:shadow-primary/30">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            Download App
                        </a>
                    </div>
                    <button @click="mobileMenu = !mobileMenu" class="lg:hidden p-2 rounded-lg hover:bg-gray-100 transition-colors">
                        <svg x-show="!mobileMenu" class="w-6 h-6 text-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        <svg x-show="mobileMenu" x-cloak class="w-6 h-6 text-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div x-show="mobileMenu" x-cloak x-transition class="lg:hidden pb-4 border-t border-gray-100 mt-2">
                    <div class="flex flex-col gap-1 pt-3">
                        <a href="#layanan" @click="mobileMenu=false" class="px-3 py-2.5 text-sm font-medium text-gray-600 hover:text-primary hover:bg-primary-50 rounded-lg transition-colors">Layanan</a>
                        <a href="#cara-kerja" @click="mobileMenu=false" class="px-3 py-2.5 text-sm font-medium text-gray-600 hover:text-primary hover:bg-primary-50 rounded-lg transition-colors">Cara Kerja</a>
                        <a href="#sos" @click="mobileMenu=false" class="px-3 py-2.5 text-sm font-medium text-gray-600 hover:text-primary hover:bg-primary-50 rounded-lg transition-colors">SOS Darurat</a>
                        <a href="#mitra" @click="mobileMenu=false" class="px-3 py-2.5 text-sm font-medium text-gray-600 hover:text-primary hover:bg-primary-50 rounded-lg transition-colors">Jadi Mitra</a>
                        <div class="border-t border-gray-100 mt-2 pt-2 flex flex-col gap-1">
                            @if (Route::has('login'))
                                <a href="{{ route('login') }}" class="px-3 py-2.5 text-sm font-medium text-gray-600 hover:text-primary hover:bg-primary-50 rounded-lg transition-colors">Masuk</a>
                            @endif
                            <a href="#download" class="mx-3 mt-1 text-center bg-primary hover:bg-primary-600 text-white text-sm font-semibold px-5 py-2.5 rounded-full transition-all">Download App</a>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        {{-- HERO --}}
        <section class="relative pt-20 lg:pt-28 pb-16 lg:pb-24 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-primary-50 via-white to-orange-50/50"></div>
            <div class="absolute top-20 right-0 w-[600px] h-[600px] bg-primary/5 rounded-full blur-3xl -translate-y-1/2 translate-x-1/3"></div>
            <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-primary/5 rounded-full blur-3xl translate-y-1/3 -translate-x-1/4"></div>
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
                    <div class="text-center lg:text-left">
                        <div class="inline-flex items-center gap-2 bg-primary-100 text-primary-700 text-xs font-semibold px-4 py-1.5 rounded-full mb-6">
                            <span class="relative flex h-2 w-2"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-75"></span><span class="relative inline-flex rounded-full h-2 w-2 bg-primary"></span></span>
                            Tersedia di Mojokerto & Sekitarnya
                        </div>
                        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-dark leading-tight">
                            Kendaraan Mogok?<br><span class="text-primary">Mekanik Datang</span><br>Ke Lokasi Anda
                        </h1>
                        <p class="mt-6 text-lg text-gray-500 max-w-xl mx-auto lg:mx-0 leading-relaxed">
                            Solusi cepat dan andal saat kendaraan Anda bermasalah di jalan. Cukup satu ketukan, mekanik terdekat akan segera menuju lokasi Anda.
                        </p>
                        <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                            <a href="#download" class="inline-flex items-center justify-center gap-2.5 bg-primary hover:bg-primary-600 text-white font-semibold px-8 py-4 rounded-full transition-all shadow-lg shadow-primary/25 hover:shadow-xl hover:shadow-primary/30 text-base">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                Download Gratis
                            </a>
                            <a href="#cara-kerja" class="inline-flex items-center justify-center gap-2 bg-white hover:bg-gray-50 text-dark font-semibold px-8 py-4 rounded-full border border-gray-200 hover:border-gray-300 transition-all text-base">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Lihat Cara Kerja
                            </a>
                        </div>
                        <div class="mt-12 grid grid-cols-3 gap-6 max-w-md mx-auto lg:mx-0">
                            <div><div class="text-2xl lg:text-3xl font-bold text-dark">200+</div><div class="text-xs text-gray-400 mt-1">Mitra Bengkel</div></div>
                            <div><div class="text-2xl lg:text-3xl font-bold text-dark">15K+</div><div class="text-xs text-gray-400 mt-1">Order Selesai</div></div>
                            <div><div class="text-2xl lg:text-3xl font-bold text-dark">4.9</div><div class="text-xs text-gray-400 mt-1">Rating Pengguna</div></div>
                        </div>
                    </div>
                    <div class="relative flex justify-center lg:justify-end">
                        <div class="relative">
                            <div class="absolute inset-0 flex items-center justify-center"><div class="w-72 h-72 lg:w-96 lg:h-96 border-2 border-dashed border-primary/20 rounded-full animate-spin" style="animation-duration:30s"></div></div>
                            <div class="absolute inset-0 flex items-center justify-center"><div class="w-56 h-56 lg:w-72 lg:h-72 border border-primary/10 rounded-full animate-spin" style="animation-duration:20s;animation-direction:reverse"></div></div>
                            <div class="relative z-10 flex items-center justify-center w-72 h-72 lg:w-96 lg:h-96">
                                <img src="{{ asset('logo-rm.png') }}" alt="MontirGo Logo" class="w-48 h-48 lg:w-64 lg:h-64 drop-shadow-2xl">
                            </div>
                            <div class="absolute -top-2 -right-4 lg:top-4 lg:right-0 bg-white rounded-2xl shadow-xl shadow-gray-200/50 p-4 flex items-center gap-3 border border-gray-100 animate-bounce" style="animation-duration:3s">
                                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center"><svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></div>
                                <div><div class="text-xs font-semibold text-dark">Mekanik Diterima</div><div class="text-[11px] text-gray-400">3 menit lagi tiba</div></div>
                            </div>
                            <div class="absolute -bottom-2 -left-4 lg:bottom-8 lg:-left-8 bg-white rounded-2xl shadow-xl shadow-gray-200/50 p-4 border border-gray-100">
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="flex -space-x-1">
                                        <div class="w-6 h-6 bg-primary rounded-full border-2 border-white flex items-center justify-center text-[8px] text-white font-bold">B</div>
                                        <div class="w-6 h-6 bg-dark rounded-full border-2 border-white flex items-center justify-center text-[8px] text-white font-bold">T</div>
                                        <div class="w-6 h-6 bg-primary-200 rounded-full border-2 border-white flex items-center justify-center text-[8px] text-white font-bold">R</div>
                                    </div>
                                    <span class="text-[11px] text-gray-400">Mekanik Aktif</span>
                                </div>
                                <div class="text-xs font-semibold text-dark">50+ Mekanik Online</div>
                            </div>
                            <div class="absolute top-1/2 -right-8 lg:-right-12 transform -translate-y-1/2 bg-white rounded-2xl shadow-xl shadow-gray-200/50 p-3 border border-gray-100">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 bg-red-50 rounded-full flex items-center justify-center"><svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg></div>
                                    <div><div class="text-[11px] font-semibold text-dark">Radius 5 km</div><div class="text-[10px] text-gray-400">Cari terdekat</div></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- LAYANAN --}}
        <section id="layanan" class="py-16 lg:py-24 bg-gray-50/50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-2xl mx-auto mb-12 lg:mb-16">
                    <span class="inline-block text-primary text-sm font-semibold tracking-wide uppercase mb-3">Layanan Kami</span>
                    <h2 class="text-3xl lg:text-4xl font-bold text-dark">Solusi Lengkap untuk Kendala Kendaraan Anda</h2>
                    <p class="mt-4 text-gray-500 leading-relaxed">Dari servis ringan hingga derek darurat, MontirGo siap membantu Anda kapan saja dan di mana saja.</p>
                </div>
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="group bg-white rounded-2xl p-6 border border-gray-100 hover:border-primary/20 hover:shadow-xl hover:shadow-primary/5 transition-all duration-300">
                        <div class="w-14 h-14 bg-primary-50 group-hover:bg-primary rounded-xl flex items-center justify-center transition-all duration-300 mb-5">
                            <svg class="w-7 h-7 text-primary group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <h3 class="text-lg font-bold text-dark mb-2">Servis Berkala</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">Ganti oli, tune-up, dan servis rutin untuk motor maupun mobil langsung di lokasi Anda.</p>
                    </div>
                    <div class="group bg-white rounded-2xl p-6 border border-gray-100 hover:border-primary/20 hover:shadow-xl hover:shadow-primary/5 transition-all duration-300">
                        <div class="w-14 h-14 bg-primary-50 group-hover:bg-primary rounded-xl flex items-center justify-center transition-all duration-300 mb-5">
                            <svg class="w-7 h-7 text-primary group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        </div>
                        <h3 class="text-lg font-bold text-dark mb-2">Tambal & Ganti Ban</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">Ban bocor atau pecah? Mekanik kami siap menambal atau mengganti ban di tempat.</p>
                    </div>
                    <div class="group bg-white rounded-2xl p-6 border border-gray-100 hover:border-primary/20 hover:shadow-xl hover:shadow-primary/5 transition-all duration-300">
                        <div class="w-14 h-14 bg-primary-50 group-hover:bg-primary rounded-xl flex items-center justify-center transition-all duration-300 mb-5">
                            <svg class="w-7 h-7 text-primary group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        </div>
                        <h3 class="text-lg font-bold text-dark mb-2">Derek Mobil</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">Layanan towing untuk kendaraan mogok berat yang tidak bisa diperbaiki di lokasi.</p>
                    </div>
                    <div class="group bg-white rounded-2xl p-6 border border-gray-100 hover:border-primary/20 hover:shadow-xl hover:shadow-primary/5 transition-all duration-300">
                        <div class="w-14 h-14 bg-primary-50 group-hover:bg-primary rounded-xl flex items-center justify-center transition-all duration-300 mb-5">
                            <svg class="w-7 h-7 text-primary group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <h3 class="text-lg font-bold text-dark mb-2">Servis AC Mobil</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">Tune-up dan perbaikan AC mobil langsung di lokasi tanpa perlu ke bengkel.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- CARA KERJA --}}
        <section id="cara-kerja" class="py-16 lg:py-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-2xl mx-auto mb-12 lg:mb-16">
                    <span class="inline-block text-primary text-sm font-semibold tracking-wide uppercase mb-3">Cara Kerja</span>
                    <h2 class="text-3xl lg:text-4xl font-bold text-dark">Hanya 4 Langkah, Mekanik Siap Membantu</h2>
                    <p class="mt-4 text-gray-500 leading-relaxed">Proses yang cepat dan transparan dari pemesanan hingga perbaikan selesai.</p>
                </div>
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8 relative">
                    <div class="hidden lg:block absolute top-16 left-[12.5%] right-[12.5%] h-0.5 bg-gradient-to-r from-primary/20 via-primary/40 to-primary/20"></div>
                    <div class="relative text-center">
                        <div class="relative z-10 w-16 h-16 bg-primary rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-primary/25"><span class="text-white font-bold text-xl">1</span></div>
                        <h3 class="text-lg font-bold text-dark mb-2">Buka Aplikasi</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">Buka MontirGo dan izinkan akses GPS untuk mendeteksi lokasi Anda secara otomatis.</p>
                    </div>
                    <div class="relative text-center">
                        <div class="relative z-10 w-16 h-16 bg-primary rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-primary/25"><span class="text-white font-bold text-xl">2</span></div>
                        <h3 class="text-lg font-bold text-dark mb-2">Pilih Layanan</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">Pilih jenis kendaraan, keluhan, dan upload foto untuk membantu mekanik memahami masalah.</p>
                    </div>
                    <div class="relative text-center">
                        <div class="relative z-10 w-16 h-16 bg-primary rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-primary/25"><span class="text-white font-bold text-xl">3</span></div>
                        <h3 class="text-lg font-bold text-dark mb-2">Mekanik Datang</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">Sistem kami mencari bengkel terdekat dan melacak perjalanan mekanik secara real-time.</p>
                    </div>
                    <div class="relative text-center">
                        <div class="relative z-10 w-16 h-16 bg-primary rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-primary/25"><span class="text-white font-bold text-xl">4</span></div>
                        <h3 class="text-lg font-bold text-dark mb-2">Selesai & Bayar</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">Perbaikan selesai, masukkan biaya servis, dan bayar dengan metode pilihan Anda.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- SOS --}}
        <section id="sos" class="py-16 lg:py-24 bg-dark relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-dark via-dark-600 to-dark-700/50"></div>
            <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-primary/5 rounded-full blur-3xl translate-x-1/3 -translate-y-1/3"></div>
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
                    <div>
                        <div class="inline-flex items-center gap-2 bg-red-500/10 text-red-400 text-xs font-semibold px-4 py-1.5 rounded-full mb-6 border border-red-500/20">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                            Fitur Darurat
                        </div>
                        <h2 class="text-3xl lg:text-4xl font-bold text-white mb-6">Tombol SOS — Bantuan Darurat Satu Klik</h2>
                        <p class="text-gray-400 leading-relaxed mb-8">Dalam situasi darurat, Anda tidak perlu mengisi form yang rumit. Cukup tekan tombol SOS, pilih kategori masalah, dan mekanik terdekat akan segera dikirim ke lokasi Anda.</p>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="flex items-center gap-3 bg-white/5 rounded-xl p-4 border border-white/5"><span class="text-2xl">🛞</span><div><div class="text-sm font-semibold text-white">Ban Bocor</div><div class="text-xs text-white">Tambal & ganti</div></div></div>
                            <div class="flex items-center gap-3 bg-white/5 rounded-xl p-4 border border-white/5"><span class="text-2xl">🔋</span><div><div class="text-sm font-semibold text-white">Aki Soak</div><div class="text-xs text-white">Jumper & ganti</div></div></div>
                            <div class="flex items-center gap-3 bg-white/5 rounded-xl p-4 border border-white/5"><span class="text-2xl">⛽</span><div><div class="text-sm font-semibold text-white">Kehabisan Bensin</div><div class="text-xs text-white">Pengisian darurat</div></div></div>
                            <div class="flex items-center gap-3 bg-white/5 rounded-xl p-4 border border-white/5"><span class="text-2xl">🌡️</span><div><div class="text-sm font-semibold text-white">Mesin Overheat</div><div class="text-xs text-white">Mogok total</div></div></div>
                        </div>
                    </div>
                    <div class="flex justify-center">
                        <div class="relative">
                            <div class="absolute inset-0 bg-red-500/10 rounded-full blur-3xl animate-pulse" style="animation-duration:2s"></div>
                            <div class="relative bg-dark-600 rounded-3xl p-8 border border-white/10 shadow-2xl max-w-sm w-full">
                                <div class="text-center mb-6">
                                    <div class="w-20 h-20 bg-red-500 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg shadow-red-500/30 animate-pulse">
                                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                                    </div>
                                    <h3 class="text-xl font-bold text-white mb-1">Butuh Bantuan Darurat?</h3>
                                    <p class="text-sm text-gray-400">Tekan tombol untuk mengirim sinyal darurat ke mekanik terdekat</p>
                                </div>
                                <button class="w-full bg-red-500 hover:bg-red-600 text-white font-bold py-4 rounded-2xl transition-all shadow-lg shadow-red-500/25 hover:shadow-xl hover:shadow-red-500/30 flex items-center justify-center gap-3 text-lg">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    SOS — Minta Bantuan
                                </button>
                                <div class="mt-4 flex items-center justify-center gap-2 text-xs text-white">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    Mencari mekanik dalam radius 5 km
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- AUTO-DISPATCH --}}
        <section class="py-16 lg:py-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-2xl mx-auto mb-12 lg:mb-16">
                    <span class="inline-block text-primary text-sm font-semibold tracking-wide uppercase mb-3">Teknologi Canggih</span>
                    <h2 class="text-3xl lg:text-4xl font-bold text-dark">Sistem Auto-Dispatch Pintar</h2>
                    <p class="mt-4 text-gray-500 leading-relaxed">Algoritma cerdas kami memastikan Anda selalu mendapatkan mekanik terdekat secepat mungkin.</p>
                </div>
                <div class="grid md:grid-cols-3 gap-8">
                    <div class="bg-white rounded-2xl p-8 border border-gray-100 shadow-sm text-center">
                        <div class="w-16 h-16 bg-primary-50 rounded-2xl flex items-center justify-center mx-auto mb-5"><svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg></div>
                        <h3 class="text-lg font-bold text-dark mb-2">Radius Escalation</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">Dimulai dari 5 km, radius otomatis membesar hingga 30 km jika belum ada yang menerima.</p>
                    </div>
                    <div class="bg-white rounded-2xl p-8 border border-gray-100 shadow-sm text-center">
                        <div class="w-16 h-16 bg-primary-50 rounded-2xl flex items-center justify-center mx-auto mb-5"><svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                        <h3 class="text-lg font-bold text-dark mb-2">60 Detik Timeout</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">Setiap mekanik memiliki 60 detik untuk menerima. Jika tidak, order langsung ke bengkel berikutnya.</p>
                    </div>
                    <div class="bg-white rounded-2xl p-8 border border-gray-100 shadow-sm text-center">
                        <div class="w-16 h-16 bg-primary-50 rounded-2xl flex items-center justify-center mx-auto mb-5"><svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg></div>
                        <h3 class="text-lg font-bold text-dark mb-2">Real-time Tracking</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">Pantau perjalanan mekanik menuju lokasi Anda secara langsung di peta.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- JADI MITRA --}}
        <section id="mitra" class="py-16 lg:py-24 bg-primary-50/50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
                    <div class="bg-white rounded-3xl p-8 lg:p-10 shadow-xl shadow-gray-200/50 border border-gray-100">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-12 h-12 bg-primary rounded-xl flex items-center justify-center"><svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg></div>
                            <div><h3 class="text-xl font-bold text-dark">Bengkel Mitra</h3><p class="text-sm text-gray-500">Daftarkan bengkel Anda</p></div>
                        </div>
                        <form class="space-y-4" x-data="{ submitting: false }">
                            <div><label class="block text-sm font-medium text-dark mb-1.5">Nama Bengkel</label><input type="text" placeholder="Contoh: Bengkel Jaya Motor" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"></div>
                            <div class="grid grid-cols-2 gap-4">
                                <div><label class="block text-sm font-medium text-dark mb-1.5">Nama Pemilik</label><input type="text" placeholder="Nama lengkap" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"></div>
                                <div><label class="block text-sm font-medium text-dark mb-1.5">No. WhatsApp</label><input type="tel" placeholder="08xxxxxxxxxx" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"></div>
                            </div>
                            <div><label class="block text-sm font-medium text-dark mb-1.5">Alamat Bengkel</label><input type="text" placeholder="Alamat lengkap bengkel" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"></div>
                            <button type="submit" @click.prevent="submitting=true" class="w-full bg-primary hover:bg-primary-600 text-white font-semibold py-3.5 rounded-xl transition-all shadow-lg shadow-primary/25 hover:shadow-xl hover:shadow-primary/30 flex items-center justify-center gap-2">
                                <template x-if="!submitting"><span class="flex items-center gap-2"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>Daftar Sekarang — Gratis!</span></template>
                                <template x-if="submitting"><span class="flex items-center gap-2"><svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Mengirim...</span></template>
                            </button>
                        </form>
                    </div>
                    <div>
                        <span class="inline-block text-primary text-sm font-semibold tracking-wide uppercase mb-3">Jadi Mitra Kami</span>
                        <h2 class="text-3xl lg:text-4xl font-bold text-dark mb-6">Penghasilan Lebih, Waktu Lebih Fleksibel</h2>
                        <p class="text-gray-500 leading-relaxed mb-8">Bergabung sebagai mitra MontirGo dan dapatkan akses ke ribuan pengguna yang membutuhkan jasa perbaikan kendaraan.</p>
                        <div class="space-y-5">
                            <div class="flex items-start gap-4"><div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center shrink-0 mt-0.5"><svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></div><div><h4 class="font-semibold text-dark text-sm">80% Biaya Panggilan Milik Anda</h4><p class="text-sm text-gray-500 mt-0.5">Dari Rp25.000 biaya panggilan, Rp20.000 masuk ke kantong Anda.</p></div></div>
                            <div class="flex items-start gap-4"><div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center shrink-0 mt-0.5"><svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></div><div><h4 class="font-semibold text-dark text-sm">Jadwal Fleksibel</h4><p class="text-sm text-gray-500 mt-0.5">Tentukan sendiri kapan Anda online dan menerima order.</p></div></div>
                            <div class="flex items-start gap-4"><div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center shrink-0 mt-0.5"><svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></div><div><h4 class="font-semibold text-dark text-sm">Dompet Digital</h4><p class="text-sm text-gray-500 mt-0.5">Saldo bisa diwithdraw kapan saja ke rekening bank Anda.</p></div></div>
                            <div class="flex items-start gap-4"><div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center shrink-0 mt-0.5"><svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></div><div><h4 class="font-semibold text-dark text-sm">Navigasi Terintegrasi</h4><p class="text-sm text-gray-500 mt-0.5">Panduan langsung ke lokasi pengguna via Google Maps/Waze.</p></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- DOWNLOAD --}}
        <section id="download" class="py-16 lg:py-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="relative bg-gradient-to-br from-primary via-primary-700 to-primary-600 overflow-hidden rounded-3xl">
                    <div class="absolute top-0 right-0 w-96 h-96 bg-white/5 rounded-full blur-3xl -translate-y-1/2 translate-x-1/3"></div>
                    <div class="absolute bottom-0 left-0 w-64 h-64 bg-white/5 rounded-full blur-3xl translate-y-1/3 -translate-x-1/4"></div>
                    <div class="relative px-8 py-12 lg:px-16 lg:py-16 text-center">
                        <h2 class="text-3xl lg:text-4xl font-bold text-white mb-4">Download MontirGo Sekarang</h2>
                        <p class="text-white/70 max-w-xl mx-auto mb-8 leading-relaxed">Tersedia di App Store dan Google Play. Siapkan MontirGo di ponsel Anda sebelum keadaan darurat terjadi.</p>
                        <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            <a href="#" class="inline-flex items-center justify-center gap-3 bg-dark hover:bg-dark-600 text-white px-8 py-4 rounded-2xl transition-all shadow-xl">
                                <svg class="w-8 h-8" viewBox="0 0 24 24" fill="currentColor"><path d="M17.05 20.28c-.98.95-2.05.8-3.08.35-1.09-.46-2.09-.48-3.24 0-1.44.62-2.2.44-3.06-.35C2.79 15.25 3.51 7.59 9.05 7.31c1.35.07 2.29.74 3.08.8 1.18-.24 2.31-.93 3.57-.84 1.51.12 2.65.72 3.4 1.8-3.12 1.87-2.38 5.98.48 7.13-.57 1.5-1.31 2.99-2.54 4.09zM12.03 7.25c-.15-2.23 1.66-4.07 3.74-4.25.29 2.58-2.34 4.5-3.74 4.25z"/></svg>
                                <div class="text-left"><div class="text-[10px] text-gray-300 leading-none">Download on the</div><div class="text-sm font-semibold leading-tight">App Store</div></div>
                            </a>
                            <a href="#" class="inline-flex items-center justify-center gap-3 bg-dark hover:bg-dark-600 text-white px-8 py-4 rounded-2xl transition-all shadow-xl">
                                <svg class="w-8 h-8" viewBox="0 0 24 24" fill="currentColor"><path d="M3.609 1.814L13.792 12 3.61 22.186a.996.996 0 01-.61-.92V2.734a1 1 0 01.609-.92zm10.89 10.893l2.302 2.302-10.937 6.333 8.635-8.635zm3.199-3.199l2.807 1.626a1 1 0 010 1.732l-2.807 1.626L15.206 12l2.492-2.492zM5.864 2.658L16.8 8.99l-2.302 2.302-8.634-8.634z"/></svg>
                                <div class="text-left"><div class="text-[10px] text-gray-300 leading-none">GET IT ON</div><div class="text-sm font-semibold leading-tight">Google Play</div></div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- FOOTER --}}
        <footer class="bg-dark pt-16 pb-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-10 mb-12">
                    <div class="lg:col-span-1">
                        <a href="/" class="flex items-center gap-2.5 mb-4"><img src="{{ asset('logo-rm.png') }}" alt="MontirGo" class="h-10 brightness-0 invert"></a>
                        <p class="text-sm text-gray-400 leading-relaxed mb-4">Solusi cepat dan andal untuk kendala kendaraan di jalan. Mekanik datang ke lokasi Anda.</p>
                        <div class="flex gap-3">
                            <a href="#" class="w-9 h-9 bg-white/10 hover:bg-primary rounded-lg flex items-center justify-center transition-colors"><svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg></a>
                            <a href="#" class="w-9 h-9 bg-white/10 hover:bg-primary rounded-lg flex items-center justify-center transition-colors"><svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg></a>
                            <a href="#" class="w-9 h-9 bg-white/10 hover:bg-primary rounded-lg flex items-center justify-center transition-colors"><svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z"/></svg></a>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-white font-semibold text-sm mb-4">Layanan</h4>
                        <ul class="space-y-2.5">
                            <li><a href="#" class="text-sm text-gray-400 hover:text-primary transition-colors">Servis Berkala</a></li>
                            <li><a href="#" class="text-sm text-gray-400 hover:text-primary transition-colors">Tambal Ban</a></li>
                            <li><a href="#" class="text-sm text-gray-400 hover:text-primary transition-colors">Derek Mobil</a></li>
                            <li><a href="#" class="text-sm text-gray-400 hover:text-primary transition-colors">Servis AC</a></li>
                            <li><a href="#" class="text-sm text-gray-400 hover:text-primary transition-colors">SOS Darurat</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-white font-semibold text-sm mb-4">Perusahaan</h4>
                        <ul class="space-y-2.5">
                            <li><a href="#" class="text-sm text-gray-400 hover:text-primary transition-colors">Tentang Kami</a></li>
                            <li><a href="#" class="text-sm text-gray-400 hover:text-primary transition-colors">Karir</a></li>
                            <li><a href="#" class="text-sm text-gray-400 hover:text-primary transition-colors">Blog</a></li>
                            <li><a href="#" class="text-sm text-gray-400 hover:text-primary transition-colors">Press Kit</a></li>
                            <li><a href="#mitra" class="text-sm text-gray-400 hover:text-primary transition-colors">Jadi Mitra</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-white font-semibold text-sm mb-4">Dukungan</h4>
                        <ul class="space-y-2.5">
                            <li><a href="#" class="text-sm text-gray-400 hover:text-primary transition-colors">Help Center</a></li>
                            <li><a href="#" class="text-sm text-gray-400 hover:text-primary transition-colors">Hubungi Kami</a></li>
                            <li><a href="#" class="text-sm text-gray-400 hover:text-primary transition-colors">Syarat & Ketentuan</a></li>
                            <li><a href="#" class="text-sm text-gray-400 hover:text-primary transition-colors">Kebijakan Privasi</a></li>
                            <li><a href="#" class="text-sm text-gray-400 hover:text-primary transition-colors">FAQ</a></li>
                        </ul>
                    </div>
                </div>
                <div class="border-t border-white/10 pt-8 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <p class="text-xs text-gray-500">&copy; {{ date('Y') }} MontirGo. Hak cipta dilindungi.</p>
                    <p class="text-xs text-gray-500">Made with 🧡 di Mojokerto, Indonesia</p>
                </div>
            </div>
        </footer>

    </body>
</html>
