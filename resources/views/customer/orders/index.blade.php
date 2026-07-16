<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-dark">Riwayat Order</h2>
                <p class="text-sm text-gray-500 mt-0.5">{{ $activeCount }} order aktif</p>
            </div>
            <a href="{{ route('customer.orders.create') }}" class="inline-flex items-center gap-2 bg-primary hover:bg-primary-600 text-white font-semibold px-5 py-2.5 rounded-xl transition-all shadow-lg shadow-primary/25 text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Pesan Baru
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 text-sm flex items-center gap-2 mb-6">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ session('success') }}
                </div>
            @endif

            {{-- Filter Tabs --}}
            <div class="flex gap-2 mb-6 overflow-x-auto pb-2">
                @php
                    $tabs = [
                        '' => 'Semua',
                        'active' => 'Aktif',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ];
                    $currentStatus = request('status', '');
                @endphp
                @foreach($tabs as $value => $label)
                    <a
                        href="{{ route('customer.orders.index', $value ? ['status' => $value] : []) }}"
                        class="px-4 py-2 rounded-xl text-sm font-medium whitespace-nowrap transition-all
                            {{ $currentStatus === $value ? 'bg-primary text-white shadow-lg shadow-primary/25' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200' }}"
                    >
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            {{-- Orders List --}}
            <div class="space-y-4">
                @forelse($orders as $order)
                    <a href="{{ route('customer.orders.show', $order) }}" class="block bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-md hover:border-primary-200 transition-all">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 bg-{{ $order->status_color }}-50 rounded-xl flex items-center justify-center">
                                    @if($order->status === 'completed')
                                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    @elseif($order->status === 'cancelled')
                                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    @elseif(in_array($order->status, ['pending', 'dispatching']))
                                        <svg class="w-5 h-5 text-yellow-500 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    @else
                                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-dark">{{ $order->service_type }}</p>
                                    <p class="text-xs text-gray-400">#{{ $order->code }} · {{ $order->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-{{ $order->status_color }}-100 text-{{ $order->status_color }}-700">
                                {{ $order->status_label }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between text-sm">
                            <div class="flex items-center gap-2 text-gray-500">
                                @if($order->partner)
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    <span class="text-xs">{{ $order->partner->workshop_name }}</span>
                                @else
                                    <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span class="text-xs text-gray-400">Mencari mekanik...</span>
                                @endif
                            </div>
                            <span class="font-bold text-dark">Rp {{ number_format((float) $order->total_amount, 0, ',', '.') }}</span>
                        </div>
                    </a>
                @empty
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
                        <svg class="w-16 h-16 text-gray-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <h3 class="text-lg font-bold text-dark mb-1">Belum ada order</h3>
                        <p class="text-sm text-gray-400 mb-5">Mulai pesan mekanik untuk kendaraan Anda</p>
                        <a href="{{ route('customer.orders.create') }}" class="inline-flex items-center gap-2 bg-primary hover:bg-primary-600 text-white font-semibold px-6 py-3 rounded-xl transition-all shadow-lg shadow-primary/25 text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            Pesan Mekanik
                        </a>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if($orders->hasPages())
                <div class="mt-6">
                    {{ $orders->links() }}
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
