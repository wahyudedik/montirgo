<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-dark">Order Masuk</h2>
                <p class="text-sm text-gray-500 mt-0.5">{{ $pendingCount }} order menunggu</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

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
                        'dispatching' => 'Menunggu',
                        'accepted' => 'Diterima',
                        'on_the_way' => 'Perjalanan',
                        'in_progress' => 'Dikerjakan',
                        'completed' => 'Selesai',
                    ];
                    $currentStatus = request('status', '');
                @endphp
                @foreach($tabs as $value => $label)
                    <a
                        href="{{ route('partner.orders.index', $value ? ['status' => $value] : []) }}"
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
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5
                        {{ $order->status === 'dispatching' ? 'border-l-4 border-l-yellow-400' : '' }}">

                        @if($order->status === 'dispatching')
                            <div class="flex items-center gap-2 mb-3 bg-yellow-50 rounded-xl px-3 py-2">
                                <span class="w-2 h-2 bg-yellow-500 rounded-full animate-pulse"></span>
                                <span class="text-xs font-semibold text-yellow-700">ORDER BARU — 60 detik untuk merespon!</span>
                            </div>
                        @endif

                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 bg-{{ $order->status_color }}-50 rounded-xl flex items-center justify-center">
                                    <span class="text-xs font-bold text-{{ $order->status_color }}-600">#{{ substr($order->code, -4) }}</span>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-dark">{{ $order->service_type }}</p>
                                    <p class="text-xs text-gray-400">{{ $order->user->name }} · {{ $order->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-{{ $order->status_color }}-100 text-{{ $order->status_color }}-700">
                                {{ $order->status_label }}
                            </span>
                        </div>

                        @if($order->problem_description)
                            <p class="text-sm text-gray-600 bg-gray-50 rounded-xl px-3 py-2 mb-3 line-clamp-2">{{ $order->problem_description }}</p>
                        @endif

                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2 text-sm text-gray-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                <span class="text-xs">{{ $order->location_address ?? 'Lokasi GPS' }}</span>
                            </div>

                            <div class="flex gap-2">
                                @if($order->status === 'dispatching')
                                    <form method="POST" action="{{ route('partner.orders.reject', $order) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="bg-red-50 hover:bg-red-100 text-red-600 text-xs font-semibold px-4 py-2 rounded-xl transition-colors">
                                            Tolak
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('partner.orders.accept', $order) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="bg-primary hover:bg-primary-600 text-white text-xs font-semibold px-4 py-2 rounded-xl transition-colors shadow-lg shadow-primary/25">
                                            Terima
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('partner.orders.show', $order) }}" class="text-xs text-primary font-medium hover:text-primary-600 transition-colors">
                                        Lihat Detail →
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
                        <svg class="w-16 h-16 text-gray-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <h3 class="text-lg font-bold text-dark mb-1">Belum ada order</h3>
                        <p class="text-sm text-gray-400">Order akan muncul di sini ketika ada customer yang memesan</p>
                    </div>
                @endforelse
            </div>

            @if($orders->hasPages())
                <div class="mt-6">{{ $orders->links() }}</div>
            @endif

        </div>
    </div>
</x-app-layout>
