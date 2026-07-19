<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('customer.orders.index') }}" class="w-9 h-9 bg-gray-100 hover:bg-gray-200 rounded-xl flex items-center justify-center transition-colors">
                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h2 class="text-xl font-bold text-dark">Order #{{ $order->code }}</h2>
                <p class="text-sm text-gray-500">{{ $order->created_at->format('d M Y, H:i') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 text-sm flex items-center gap-2">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm flex items-center gap-2">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ session('error') }}
                </div>
            @endif

            {{-- Status Badge --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        @php
                            $statusColor = match($order->status) {
                                'pending' => 'yellow',
                                'dispatching' => 'blue',
                                'accepted' => 'indigo',
                                'on_the_way' => 'purple',
                                'arrived' => 'cyan',
                                'in_progress' => 'orange',
                                'completed' => 'green',
                                'cancelled', 'rejected', 'expired' => 'red',
                                default => 'gray',
                            };
                        @endphp
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-semibold bg-{{ $statusColor }}-100 text-{{ $statusColor }}-700">
                            @if(in_array($order->status, ['dispatching']))
                                <span class="w-2 h-2 bg-{{ $statusColor }}-500 rounded-full animate-pulse"></span>
                            @endif
                            {{ $order->status_label }}
                        </span>
                    </div>
                    <span class="text-sm text-gray-400">#{{ $order->code }}</span>
                </div>

                {{-- Progress Steps --}}
                @if(!in_array($order->status, ['cancelled', 'expired']))
                    @php
                        $stepKeys = ['pending', 'dispatching', 'accepted', 'on_the_way', 'arrived', 'in_progress', 'completed'];
                        $currentIndex = array_search($order->status, $stepKeys);
                        if ($currentIndex === false) $currentIndex = 0;
                    @endphp
                    <div class="flex items-center justify-between mt-4 px-2">
                        @foreach(['pending', 'accepted', 'on_the_way', 'in_progress', 'completed'] as $i => $step)
                            <div class="flex flex-col items-center flex-1">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold
                                    {{ $currentIndex >= array_search($step, $stepKeys) ? 'bg-primary text-white' : 'bg-gray-100 text-gray-400' }}">
                                    @if($currentIndex > array_search($step, $stepKeys))
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    @else
                                        {{ $i + 1 }}
                                    @endif
                                </div>
                                <p class="text-[10px] text-gray-400 mt-1 text-center leading-tight">{{ match($step) {
                                    'pending' => 'Dibuat',
                                    'accepted' => 'Diterima',
                                    'on_the_way' => 'Perjalanan',
                                    'in_progress' => 'Dikerjakan',
                                    'completed' => 'Selesai',
                                    default => $step,
                                } }}</p>
                            </div>
                            @if(!$loop->last)
                                <div class="flex-1 h-0.5 {{ $currentIndex >= array_search($stepKeys[array_search($step, $stepKeys) + 1] ?? '', $stepKeys) ? 'bg-primary' : 'bg-gray-200' }} mx-1 mt-[-12px]"></div>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Order Map --}}
            @if(in_array($order->status, ['dispatching', 'accepted', 'on_the_way', 'arrived', 'in_progress']))
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-sm font-bold text-dark mb-3 flex items-center gap-1"><svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg> Lokasi</h3>
                    @php
                        $mapMarkers = [
                            ['lat' => (float) $order->location_lat, 'lng' => (float) $order->location_lng, 'label' => 'Lokasi Anda', 'color' => '#3B82F6'],
                        ];
                        if ($order->partner && $order->partner->workshop_lat && $order->partner->workshop_lng) {
                            $mapMarkers[] = ['lat' => (float) $order->partner->workshop_lat, 'lng' => (float) $order->partner->workshop_lng, 'label' => $order->partner->workshop_name, 'color' => '#10B981'];
                        }
                    @endphp
                    <x-map
                        id="tracking-map"
                        :lat="(float) $order->location_lat"
                        :lng="(float) $order->location_lng"
                        :zoom="13"
                        :readOnly="true"
                        height="250px"
                        :markers="$mapMarkers"
                    />
                    @if($order->partner && $order->partner->workshop_lat)
                        @php
                            $geoService = app(\App\Services\GeolocationService::class);
                            $distance = $geoService->calculateDistance(
                                $order->location_lat, $order->location_lng,
                                $order->partner->workshop_lat, $order->partner->workshop_lng
                            );
                            $eta = $geoService->estimateArrival(
                                $order->partner->workshop_lat, $order->partner->workshop_lng,
                                $order->location_lat, $order->location_lng
                            );
                        @endphp
                        <div class="flex items-center justify-between mt-3 text-sm">
                            <span class="text-gray-500">Jarak: <strong class="text-dark">{{ $geoService->formatDistance($distance) }}</strong></span>
                            <span class="text-gray-500">Estimasi tiba: <strong class="text-primary">{{ $eta['formatted'] }}</strong></span>
                        </div>
                    @endif
                    {{-- Deep Link Navigation --}}
                    <div class="flex gap-2 mt-3">
                        <a href="https://www.google.com/maps/dir/?api=1&destination={{ $order->location_lat }},{{ $order->location_lng }}" target="_blank" class="flex-1 inline-flex items-center justify-center gap-2 bg-blue-50 hover:bg-blue-100 text-blue-600 text-sm font-medium px-4 py-2.5 rounded-xl transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                            Google Maps
                        </a>
                        <a href="https://waze.com/ul?ll={{ $order->location_lat }},{{ $order->location_lng }}&navigate=yes" target="_blank" class="flex-1 inline-flex items-center justify-center gap-2 bg-purple-50 hover:bg-purple-100 text-purple-600 text-sm font-medium px-4 py-2.5 rounded-xl transition-colors">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                            Waze
                        </a>
                    </div>
                </div>
            @endif

            {{-- Partner Info (if accepted) --}}
            @if($order->partner)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-sm font-bold text-dark mb-3">Mekanik</h3>
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 bg-primary-50 rounded-2xl flex items-center justify-center">
                            @if($order->partner->user?->avatar)
                                <img src="{{ asset('storage/' . $order->partner->user->avatar) }}" alt="" class="w-14 h-14 rounded-2xl object-cover">
                            @else
                                <svg class="w-7 h-7 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            @endif
                        </div>
                        <div class="flex-1">
                            <p class="font-bold text-dark">{{ $order->partner->workshop_name }}</p>
                            <p class="text-sm text-gray-500">{{ $order->partner->workshop_address ?? 'Alamat belum diatur' }}</p>
                            <div class="flex items-center gap-2 mt-1">
                                        <div class="flex items-center gap-0.5">
                                            <svg class="w-3.5 h-3.5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                            <span class="text-xs font-medium text-gray-600">{{ number_format($order->partner->rating_avg, 1) }}</span>
                                        </div>
                                        <span class="text-gray-300">·</span>
                                        <span class="text-xs text-gray-400">{{ $order->partner->total_orders }} order</span>
                                    </div>
                                </div>
                                {{-- In-App Phone Call --}}
                                @if($order->partner->user->phone)
                                    <a href="tel:{{ $order->partner->user->phone }}" class="mt-3 flex items-center gap-2 bg-green-50 hover:bg-green-100 text-green-600 text-sm font-medium px-4 py-2.5 rounded-xl transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                        Hubungi Mekanik
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif

            {{-- Detail Order --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-bold text-dark mb-4">Detail Layanan</h3>
                <div class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Jenis Layanan</span>
                        <span class="font-medium text-dark">{{ $order->service_type }}</span>
                    </div>
                    @if($order->vehicle)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Kendaraan</span>
                            <span class="font-medium text-dark">{{ $order->vehicle->brand }} {{ $order->vehicle->model }} ({{ $order->vehicle->license_plate }})</span>
                        </div>
                    @endif
                    @if($order->problem_description)
                        <div>
                            <span class="text-sm text-gray-500 block mb-1">Keluhan</span>
                            <p class="text-sm text-dark bg-gray-50 rounded-xl p-3">{{ $order->problem_description }}</p>
                        </div>
                    @endif
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Lokasi</span>
                        <span class="font-medium text-dark text-right max-w-[200px]">{{ $order->location_address ?? $order->location_lat . ', ' . $order->location_lng }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Metode Bayar</span>
                        <span class="font-medium text-dark">{{ match($order->payment_method) {
                            'cash' => 'Tunai',
                            'wallet' => 'Dompet Digital',
                            'qris' => 'QRIS',
                            'card' => 'Kartu',
                            default => $order->payment_method,
                        } }}</span>
                    </div>
                </div>
            </div>

            {{-- Biaya --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-bold text-dark mb-4">Rincian Biaya</h3>
                <div class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Biaya Panggilan</span>
                        <span class="font-medium text-dark">Rp {{ number_format((float) $order->callout_fee, 0, ',', '.') }}</span>
                    </div>
                    @if($order->service_fee > 0)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Biaya Servis</span>
                            <span class="font-medium text-dark">Rp {{ number_format((float) $order->service_fee, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    <div class="border-t border-gray-100 pt-3 flex justify-between">
                        <span class="font-semibold text-dark">Total</span>
                        <span class="font-bold text-primary text-lg">Rp {{ number_format((float) $order->total_amount, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            {{-- Cancel Button (only for pending/dispatching) --}}
            @if(in_array($order->status, ['pending', 'dispatching']))
                <div x-data="{ showCancel: false }">
                    <button
                        @click="showCancel = true"
                        class="w-full bg-white border-2 border-red-200 text-red-600 font-semibold py-3 px-6 rounded-xl transition-all hover:bg-red-50 hover:border-red-300"
                    >
                        Batalkan Order
                    </button>

                    {{-- Cancel Modal --}}
                    <div x-show="showCancel" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" style="display: none;">
                        <div @click.away="showCancel = false" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
                            <h3 class="text-lg font-bold text-dark mb-2">Batalkan Order?</h3>
                            <p class="text-sm text-gray-500 mb-4">Anda yakin ingin membatalkan order #{{ $order->code }}? Tindakan ini tidak dapat dibatalkan.</p>
                            <form method="POST" action="{{ route('customer.orders.cancel', $order) }}">
                                @csrf
                                @method('PATCH')
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Alasan Pembatalan</label>
                                    <select name="cancel_reason" class="input-field" required>
                                        <option value="">Pilih alasan...</option>
                                        <option value="Berubah pikiran">Berubah pikiran</option>
                                        <option value="Sudah ada yang memperbaiki">Sudah ada yang memperbaiki</option>
                                        <option value="Masalah lokasi">Masalah lokasi</option>
                                        <option value="Biaya terlalu mahal">Biaya terlalu mahal</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                </div>
                                <div class="flex gap-3">
                                    <button type="button" @click="showCancel = false" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2.5 px-4 rounded-xl transition-colors">
                                        Kembali
                                    </button>
                                    <button type="submit" class="flex-1 bg-red-500 hover:bg-red-600 text-white font-medium py-2.5 px-4 rounded-xl transition-colors">
                                        Ya, Batalkan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Review (if completed and no review yet) --}}
            @if($order->status === 'completed' && !$order->review)
                <div class="bg-gradient-to-br from-primary to-primary-600 rounded-2xl p-6 text-white shadow-lg shadow-primary/25">
                    <div class="flex items-center gap-2 mb-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                        <h3 class="font-bold">Beri Rating</h3>
                    </div>
                    <p class="text-sm text-white/80 mb-4">Bagaimana pengalaman Anda dengan mekanik ini?</p>
                    <div class="flex gap-2">
                        @foreach([1,2,3,4,5] as $star)
                            <button class="w-10 h-10 bg-white/20 hover:bg-white/30 rounded-xl flex items-center justify-center transition-colors">
                                <svg class="w-6 h-6 text-yellow-300" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
