<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-dark">
                    Halo, {{ $user->name }}
                </h2>
                <p class="text-sm text-gray-500 mt-0.5">Selamat datang di MontirGo</p>
            </div>
            <a href="{{ route('customer.orders.create') }}" class="inline-flex items-center gap-2 bg-primary hover:bg-primary-600 text-white font-semibold px-5 py-2.5 rounded-xl transition-all shadow-lg shadow-primary/25 text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Pesan Sekarang
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Quick Stats --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 bg-primary-50 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-dark">{{ $stats['total_orders'] }}</p>
                            <p class="text-xs text-gray-500">Total Order</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 bg-green-50 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-dark">{{ $stats['completed_orders'] }}</p>
                            <p class="text-xs text-gray-500">Selesai</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 bg-blue-50 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-dark">{{ $stats['active_orders'] }}</p>
                            <p class="text-xs text-gray-500">Sedang Proses</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Left Column --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- Recent Orders --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
                        <div class="px-6 py-4 border-b border-gray-100">
                            <h3 class="text-base font-bold text-dark">Order Terakhir</h3>
                        </div>
                        <div class="divide-y divide-gray-50">
                            @forelse($recentOrders as $order)
                                    <a href="{{ route('customer.orders.show', $order) }}" class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition-colors">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-primary-50 rounded-xl flex items-center justify-center text-primary font-bold text-sm">
                                                #{{ substr($order->code, -4) }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-dark">{{ $order->service_type }}</p>
                                                <p class="text-xs text-gray-400">{{ $order->partner->workshop_name ?? 'Belum ada partner' }} · {{ $order->created_at->diffForHumans() }}</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $order->status_color }}-100 text-{{ $order->status_color }}-700">
                                                {{ $order->status_label }}
                                            </span>
                                            <p class="text-xs text-gray-400 mt-1">{{ $order->total_display }}</p>
                                        </div>
                                    </a>
                            @empty
                                <div class="px-6 py-12 text-center">
                                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                    <p class="text-sm text-gray-400">Belum ada order</p>
                                    <p class="text-xs text-gray-300 mt-1">Pesan mekanik sekarang!</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Right Column --}}
                <div class="space-y-6">

                    {{-- Wallet --}}
                    <div class="bg-gradient-to-br from-primary to-primary-600 rounded-2xl p-6 text-white shadow-lg shadow-primary/25">
                        <div class="flex items-center gap-2 mb-4">
                            <svg class="w-5 h-5 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            <span class="text-sm font-medium text-white/80">Saldo Dompet</span>
                        </div>
                        <p class="text-3xl font-bold">Rp {{ number_format($walletBalance, 0, ',', '.') }}</p>
                        <div class="mt-4 flex gap-2">
                            <button class="flex-1 bg-white/20 hover:bg-white/30 text-white text-xs font-medium py-2 rounded-xl transition-colors">
                                Top Up
                            </button>
                            <button class="flex-1 bg-white/20 hover:bg-white/30 text-white text-xs font-medium py-2 rounded-xl transition-colors">
                                Riwayat
                            </button>
                        </div>
                    </div>

                    {{-- My Vehicles --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-base font-bold text-dark">Kendaraan Saya</h3>
                            <button class="text-xs text-primary font-medium hover:text-primary-600 transition-colors">+ Tambah</button>
                        </div>
                        @forelse($vehicles as $vehicle)
                            <div class="flex items-center gap-3 {{ !$loop->last ? 'pb-3 mb-3 border-b border-gray-50' : '' }}">
                                <div class="w-10 h-10 bg-dark-50 rounded-xl flex items-center justify-center">
                                    @if($vehicle->type === 'motorcycle')
                                        <svg class="w-5 h-5 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    @else
                                        <svg class="w-5 h-5 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 17h8M8 17v-4h8v4M8 17H5a1 1 0 01-1-1v-3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-1 1h-3"/></svg>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-dark">{{ $vehicle->brand }} {{ $vehicle->model }}</p>
                                    <p class="text-xs text-gray-400">{{ $vehicle->license_plate }} · {{ ucfirst($vehicle->type) }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4">
                                <p class="text-sm text-gray-400">Belum ada kendaraan</p>
                                <button class="mt-2 text-xs text-primary font-medium hover:text-primary-600">Tambah Kendaraan</button>
                            </div>
                        @endforelse
                    </div>

                    {{-- Quick Actions --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-base font-bold text-dark mb-4">Aksi Cepat</h3>
                        <div class="space-y-2">
                            <a href="{{ route('customer.sos.index') }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-red-50 transition-colors group">
                                <div class="w-10 h-10 bg-red-50 rounded-xl flex items-center justify-center group-hover:bg-red-100 transition-colors">
                                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-red-600">SOS Darurat</p>
                                    <p class="text-xs text-gray-400">Butuh bantuan mendesak?</p>
                                </div>
                            </a>
                            <a href="{{ route('customer.orders.index') }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 transition-colors group">
                                <div class="w-10 h-10 bg-primary-50 rounded-xl flex items-center justify-center group-hover:bg-primary-100 transition-colors">
                                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-dark">Riwayat Servis</p>
                                    <p class="text-xs text-gray-400">Lihat semua order selesai</p>
                                </div>
                            </a>
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 transition-colors group">
                                <div class="w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center group-hover:bg-gray-200 transition-colors">
                                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-dark">Profil Saya</p>
                                    <p class="text-xs text-gray-400">Edit data diri</p>
                                </div>
                            </a>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>
