<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-dark">
                    {{ $partner->workshop_name ?? 'Partner' }}
                </h2>
                <p class="text-sm text-gray-500 mt-0.5">{{ $user->name }} · {{ $stats['total_orders'] }} order</p>
            </div>
            <div class="flex items-center gap-3">
                {{-- Online Status --}}
                <div class="flex items-center gap-2 bg-white border border-gray-200 rounded-xl px-4 py-2">
                    <div class="w-2.5 h-2.5 rounded-full {{ $partner->is_online ? 'bg-green-500 animate-pulse' : 'bg-gray-300' }}"></div>
                    <span class="text-sm font-medium {{ $partner->is_online ? 'text-green-600' : 'text-gray-500' }}">
                        {{ $partner->is_online ? 'Online' : 'Offline' }}
                    </span>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Quick Stats --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Earnings --}}
                <div class="bg-gradient-to-br from-primary to-primary-600 rounded-2xl p-5 text-white shadow-lg shadow-primary/25">
                    <div class="flex items-center gap-2 mb-3">
                        <svg class="w-5 h-5 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="text-sm text-white/80">Saldo</span>
                    </div>
                    <p class="text-2xl font-bold">Rp {{ number_format($walletBalance, 0, ',', '.') }}</p>
                    <p class="text-xs text-white/60 mt-1">Total: Rp {{ number_format($totalIncome, 0, ',', '.') }}</p>
                </div>

                {{-- Pending Orders --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-9 h-9 bg-yellow-50 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-dark">{{ $pendingOrders }}</p>
                    <p class="text-xs text-gray-500 mt-1">Menunggu Diterima</p>
                </div>

                {{-- Completed --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-9 h-9 bg-green-50 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-dark">{{ $stats['completed_orders'] }}</p>
                    <p class="text-xs text-gray-500 mt-1">Selesai</p>
                </div>

                {{-- Rating --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-9 h-9 bg-yellow-50 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-dark">{{ number_format($stats['rating_avg'], 1) }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $stats['total_reviews'] }} ulasan</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Left Column --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- Pending Orders Alert --}}
                    @if($pendingOrders > 0)
                        <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-5">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-yellow-100 rounded-xl flex items-center justify-center animate-pulse">
                                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-yellow-800">{{ $pendingOrders }} order menunggu diterima!</p>
                                    <p class="text-xs text-yellow-600">Segera respon sebelum timeout 60 detik</p>
                                </div>
                                <a href="#" class="ml-auto bg-yellow-500 hover:bg-yellow-600 text-white text-xs font-semibold px-4 py-2 rounded-xl transition-colors">
                                    Lihat
                                </a>
                            </div>
                        </div>
                    @endif

                    {{-- Recent Orders --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
                        <div class="px-6 py-4 border-b border-gray-100">
                            <h3 class="text-base font-bold text-dark">Order Terakhir</h3>
                        </div>
                        <div class="divide-y divide-gray-50">
                            @forelse($recentOrders as $order)
                                <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-primary-50 rounded-xl flex items-center justify-center text-primary font-bold text-sm">
                                            #{{ substr($order->code, -4) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-dark">{{ $order->service_type }}</p>
                                            <p class="text-xs text-gray-400">{{ $order->user->name }} · {{ $order->created_at->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $order->status_color }}-100 text-{{ $order->status_color }}-700">
                                            {{ $order->status_label }}
                                        </span>
                                        <p class="text-xs text-gray-400 mt-1">Rp {{ number_format((float) $order->partner_earning, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                            @empty
                                <div class="px-6 py-12 text-center">
                                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                    <p class="text-sm text-gray-400">Belum ada order</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Right Column --}}
                <div class="space-y-6">

                    {{-- Services --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-base font-bold text-dark">Layanan Aktif</h3>
                            <button class="text-xs text-primary font-medium hover:text-primary-600 transition-colors">Kelola</button>
                        </div>
                        @forelse($services as $service)
                            <div class="flex items-center justify-between {{ !$loop->last ? 'pb-3 mb-3 border-b border-gray-50' : '' }}">
                                <div>
                                    <p class="text-sm font-medium text-dark">{{ $service->service_name }}</p>
                                    <p class="text-xs text-gray-400">{{ $service->category }}</p>
                                </div>
                                <span class="text-sm font-bold text-primary">Rp {{ number_format($service->base_price, 0, ',', '.') }}</span>
                            </div>
                        @empty
                            <div class="text-center py-4">
                                <p class="text-sm text-gray-400">Belum ada layanan</p>
                                <button class="mt-2 text-xs text-primary font-medium hover:text-primary-600">Tambah Layanan</button>
                            </div>
                        @endforelse
                    </div>

                    {{-- Quick Actions --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-base font-bold text-dark mb-4">Aksi Cepat</h3>
                        <div class="space-y-2">
                            <a href="#" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 transition-colors group">
                                <div class="w-10 h-10 bg-primary-50 rounded-xl flex items-center justify-center group-hover:bg-primary-100 transition-colors">
                                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-dark">Dompet</p>
                                    <p class="text-xs text-gray-400">Saldo & penarikan</p>
                                </div>
                            </a>
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 transition-colors group">
                                <div class="w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center group-hover:bg-gray-200 transition-colors">
                                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-dark">Profil Bengkel</p>
                                    <p class="text-xs text-gray-400">Edit data bengkel</p>
                                </div>
                            </a>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>
