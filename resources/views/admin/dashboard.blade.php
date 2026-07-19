<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-dark flex items-center gap-2"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg> Admin Dashboard</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- KPI Cards --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-gradient-to-br from-primary to-primary-600 rounded-2xl p-5 text-white shadow-lg shadow-primary/25">
                    <div class="flex items-center gap-2 mb-2">
                        <svg class="w-5 h-5 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span class="text-sm text-white/80">Total Users</span>
                    </div>
                    <p class="text-2xl font-bold">{{ number_format($stats['total_users']) }}</p>
                    <p class="text-xs text-white/60 mt-1">{{ $stats['total_customers'] }} customers · {{ $stats['total_partners'] }} partners</p>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-dark">{{ number_format($stats['total_orders']) }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $stats['orders_today'] }} hari ini · {{ $stats['active_orders'] }} aktif</p>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-8 h-8 bg-green-50 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-dark">Rp {{ number_format($stats['revenue_this_month'], 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-500 mt-1">Revenue bulan ini</p>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-8 h-8 bg-yellow-50 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-dark">{{ $stats['avg_rating'] }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $stats['completion_rate'] }}% completion</p>
                </div>
            </div>

            {{-- Charts Row --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- Revenue Chart --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-dark mb-4 flex items-center gap-2"><svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg> Revenue (12 Bulan)</h3>
                    <div class="space-y-2">
                        @foreach($revenueChart as $i => $month)
                            @php
                                $maxRevenue = collect($revenueChart)->max('revenue') ?: 1;
                                $width = round(($month['revenue'] / $maxRevenue) * 100, 1);
                            @endphp
                            <div class="flex items-center gap-3">
                                <span class="text-xs text-gray-500 w-16 shrink-0">{{ substr($month['month'], 0, 7) }}</span>
                                <div class="flex-1 bg-gray-100 rounded-full h-4">
                                    <div class="bg-gradient-to-r from-primary to-primary-600 h-4 rounded-full transition-all duration-500" style="width: {{ $width }}%"></div>
                                </div>
                                <span class="text-xs font-medium text-dark w-20 text-right">Rp {{ number_format($month['revenue'], 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Order Status Distribution --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-dark mb-4 flex items-center gap-2"><svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg> Status Order</h3>
                    <div class="space-y-3">
                        @php
                            $totalOrders = array_sum($statusDistribution) ?: 1;
                            $statusColors = [
                                'completed' => 'bg-green-500',
                                'pending' => 'bg-yellow-500',
                                'cancelled' => 'bg-red-500',
                                'dispatching' => 'bg-blue-400',
                                'accepted' => 'bg-blue-500',
                                'on_the_way' => 'bg-indigo-500',
                                'arrived' => 'bg-purple-500',
                                'in_progress' => 'bg-orange-500',
                            ];
                            $statusLabels = [
                                'completed' => 'Selesai',
                                'pending' => 'Menunggu',
                                'cancelled' => 'Dibatalkan',
                                'dispatching' => 'Dispatch',
                                'accepted' => 'Diterima',
                                'on_the_way' => 'Menuju Lokasi',
                                'arrived' => 'Tiba',
                                'in_progress' => 'Dikerjakan',
                            ];
                        @endphp
                        @forelse($statusDistribution as $status => $count)
                            @php $percent = round(($count / $totalOrders) * 100, 1); @endphp
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm text-gray-600">{{ $statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status)) }}</span>
                                    <span class="text-sm font-medium text-dark">{{ $count }} ({{ $percent }}%)</span>
                                </div>
                                <div class="bg-gray-100 rounded-full h-2">
                                    <div class="{{ $statusColors[$status] ?? 'bg-gray-400' }} h-2 rounded-full" style="width: {{ $percent }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-400">Belum ada data.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Bottom Row --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Peak Hours --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-dark mb-4 flex items-center gap-2"><svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Jam Puncak</h3>
                    <div class="space-y-1.5">
                        @php
                            $maxPeak = collect($peakHours)->max() ?: 1;
                        @endphp
                        @for($h = 6; $h <= 22; $h++)
                            @php $count = $peakHours[$h] ?? 0; @endphp
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-gray-500 w-8">{{ sprintf('%02d', $h) }}:00</span>
                                <div class="flex-1 bg-gray-100 rounded-full h-2.5">
                                    <div class="bg-amber-400 h-2.5 rounded-full" style="width: {{ ($count / $maxPeak) * 100 }}%"></div>
                                </div>
                                <span class="text-xs text-gray-400 w-6 text-right">{{ $count }}</span>
                            </div>
                        @endfor
                    </div>
                </div>

                {{-- Top Partners --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-dark mb-4 flex items-center gap-2"><svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg> Top Partners</h3>
                    <div class="space-y-3">
                        @forelse($topPartners as $i => $partner)
                            <div class="flex items-center gap-3">
                                <span class="w-6 h-6 rounded-full bg-primary/10 text-primary text-xs font-bold flex items-center justify-center">{{ $i + 1 }}</span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-dark truncate">{{ $partner['workshop_name'] ?? 'Partner' }}</p>
                                    <p class="text-xs text-gray-500">{{ $partner['order_count'] ?? 0 }} order selesai</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-400">Belum ada data.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Recent Orders --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-dark mb-4 flex items-center gap-2"><svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg> Recent Orders</h3>
                    <div class="space-y-3">
                        @forelse($recentOrders->take(5) as $order)
                            <a href="{{ route('admin.orders.show', $order) }}" class="flex items-center justify-between hover:bg-gray-50 rounded-lg p-2 -mx-2 transition">
                                <div>
                                    <p class="text-sm font-medium text-dark">#{{ $order->code ?? $order->id }}</p>
                                    <p class="text-xs text-gray-500">{{ $order->user->name ?? '-' }} · {{ $order->service_type }}</p>
                                </div>
                                <span class="px-2 py-0.5 text-xs font-medium rounded-full
                                    {{ $order->status === 'completed' ? 'bg-green-100 text-green-700' : '' }}
                                    {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                    {{ $order->status === 'cancelled' ? 'bg-red-100 text-red-700' : '' }}
                                    {{ in_array($order->status, ['dispatching', 'accepted', 'on_the_way', 'in_progress', 'arrived']) ? 'bg-blue-100 text-blue-700' : '' }}">
                                    {{ $order->status_label }}
                                </span>
                            </a>
                        @empty
                            <p class="text-sm text-gray-400">Belum ada order.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
