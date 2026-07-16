<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-dark">📋 Riwayat Layanan</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Stats Cards --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 text-center">
                    <p class="text-2xl font-bold text-dark">{{ $stats['total_orders'] }}</p>
                    <p class="text-xs text-gray-500 mt-1">Total Order</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 text-center">
                    <p class="text-2xl font-bold text-green-600">{{ $stats['completed_orders'] }}</p>
                    <p class="text-xs text-gray-500 mt-1">Selesai</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 text-center">
                    <p class="text-2xl font-bold text-primary">Rp {{ number_format($stats['total_spent'], 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-500 mt-1">Total Pengeluaran</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 text-center">
                    <p class="text-lg font-bold text-dark">{{ $stats['favorite_service'] }}</p>
                    <p class="text-xs text-gray-500 mt-1">Layanan Favorit</p>
                </div>
            </div>

            {{-- Orders List --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-dark">Riwayat Order</h3>
                </div>

                <div class="divide-y divide-gray-100">
                    @forelse($orders as $order)
                        <a href="{{ route('customer.orders.show', $order) }}" class="block px-6 py-4 hover:bg-gray-50 transition">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    {{-- Status Icon --}}
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center
                                        {{ $order->status === 'completed' ? 'bg-green-50' : '' }}
                                        {{ $order->status === 'cancelled' ? 'bg-red-50' : '' }}
                                        {{ in_array($order->status, ['pending','dispatching']) ? 'bg-yellow-50' : '' }}
                                        {{ in_array($order->status, ['accepted','on_the_way','arrived','in_progress']) ? 'bg-blue-50' : '' }}">
                                        @if($order->status === 'completed')
                                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        @elseif($order->status === 'cancelled')
                                            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        @else
                                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        @endif
                                    </div>

                                    <div>
                                        <div class="flex items-center gap-2">
                                            <p class="text-sm font-semibold text-dark">#{{ $order->code ?? $order->id }}</p>
                                            <span class="px-2 py-0.5 text-xs font-medium rounded-full
                                                {{ $order->status === 'completed' ? 'bg-green-100 text-green-700' : '' }}
                                                {{ $order->status === 'cancelled' ? 'bg-red-100 text-red-700' : '' }}
                                                {{ in_array($order->status, ['pending','dispatching']) ? 'bg-yellow-100 text-yellow-700' : '' }}
                                                {{ in_array($order->status, ['accepted','on_the_way','arrived','in_progress']) ? 'bg-blue-100 text-blue-700' : '' }}">
                                                {{ $order->status_label }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            {{ $order->service_type }} · {{ $order->partner->workshop_name ?? 'Menunggu partner' }}
                                        </p>
                                    </div>
                                </div>

                                <div class="text-right">
                                    <p class="text-sm font-semibold text-dark">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</p>
                                    <p class="text-xs text-gray-400">{{ $order->created_at->format('d M Y') }}</p>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="px-6 py-12 text-center">
                            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            <p class="text-gray-500">Belum ada riwayat layanan.</p>
                            <a href="{{ route('customer.orders.create') }}" class="inline-block mt-3 text-sm text-primary hover:text-primary-600 font-medium">Pesan Sekarang →</a>
                        </div>
                    @endforelse
                </div>

                @if($orders->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100">
                        {{ $orders->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
