<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('partner.orders.index') }}" class="w-9 h-9 bg-gray-100 hover:bg-gray-200 rounded-xl flex items-center justify-center transition-colors">
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

            {{-- Status & Customer Info --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <span class="inline-flex px-3 py-1 rounded-full text-sm font-semibold bg-{{ $order->status_color }}-100 text-{{ $order->status_color }}-700">
                        {{ $order->status_label }}
                    </span>
                    <span class="text-sm text-gray-400">#{{ $order->code }}</span>
                </div>

                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-primary-50 rounded-2xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <div>
                        <p class="font-bold text-dark">{{ $order->user->name }}</p>
                        <p class="text-sm text-gray-500">{{ $order->user->phone ?? $order->user->email }}</p>
                    </div>
                </div>
                {{-- In-App Phone Call --}}
                @if($order->user->phone)
                    <a href="tel:{{ $order->user->phone }}" class="mt-3 flex items-center gap-2 bg-green-50 hover:bg-green-100 text-green-600 text-sm font-medium px-4 py-2.5 rounded-xl transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        Hubungi Customer
                    </a>
                @endif
            </div>

            {{-- Service Detail --}}
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
                            <span class="text-sm text-gray-500 block mb-1">Keluhan Customer</span>
                            <p class="text-sm text-dark bg-gray-50 rounded-xl p-3">{{ $order->problem_description }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Location --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-bold text-dark mb-3">Lokasi Customer</h3>
                <div class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-red-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                    <div>
                        <p class="text-sm font-medium text-dark">{{ $order->location_address ?? 'Alamat tidak tersedia' }}</p>
                        <p class="text-xs text-gray-400 mt-1">Koordinat: {{ $order->location_lat }}, {{ $order->location_lng }}</p>
                    </div>
                </div>
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

            {{-- Biaya --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-bold text-dark mb-3">Biaya</h3>
                <div class="flex justify-between">
                    <span class="text-gray-500 text-sm">Biaya Panggilan</span>
                    <span class="font-bold text-primary">Rp {{ number_format((float) $order->callout_fee, 0, ',', '.') }}</span>
                </div>
                @if($order->service_fee > 0)
                    <div class="flex justify-between mt-2">
                        <span class="text-gray-500 text-sm">Biaya Servis</span>
                        <span class="font-bold text-dark">Rp {{ number_format((float) $order->service_fee, 0, ',', '.') }}</span>
                    </div>
                @endif
                @if($order->partner_earning > 0)
                    <div class="border-t border-gray-100 mt-3 pt-3 flex justify-between">
                        <span class="font-semibold text-dark text-sm">Pendapatan Anda</span>
                        <span class="font-bold text-green-600">Rp {{ number_format((float) $order->partner_earning, 0, ',', '.') }}</span>
                    </div>
                @endif
            </div>

            {{-- Action Buttons --}}
            @if(in_array($order->status, ['accepted', 'on_the_way', 'arrived', 'in_progress']))
                <div class="space-y-3" x-data="{ showServiceFee: false }">
                    @if($order->status === 'accepted')
                        <form method="POST" action="{{ route('partner.orders.update-status', $order) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="on_the_way">
                            <button type="submit" class="w-full bg-primary hover:bg-primary-600 text-white font-bold py-3 px-6 rounded-xl transition-all shadow-lg shadow-primary/25 flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                                Mulai Perjalanan
                            </button>
                        </form>
                    @elseif($order->status === 'on_the_way')
                        <form method="POST" action="{{ route('partner.orders.update-status', $order) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="arrived">
                            <button type="submit" class="w-full bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-3 px-6 rounded-xl transition-all shadow-lg shadow-indigo-500/25 flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                Tiba di Lokasi
                            </button>
                        </form>
                    @elseif($order->status === 'arrived')
                        <form method="POST" action="{{ route('partner.orders.update-status', $order) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="in_progress">
                            <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 px-6 rounded-xl transition-all shadow-lg shadow-orange-500/25 flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                Mulai Kerjakan
                            </button>
                        </form>
                    @elseif($order->status === 'in_progress')
                        {{-- Upload Foto Before/After --}}
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                            <h3 class="text-sm font-bold text-dark mb-4">Upload Foto Servis</h3>

                            {{-- Existing Photos --}}
                            @if($order->photos->count() > 0)
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-4">
                                    @foreach($order->photos as $photo)
                                        <div class="relative group">
                                            <img src="{{ asset('storage/' . $photo->photo_url) }}" alt="{{ $photo->caption }}"
                                                class="w-full h-24 object-cover rounded-xl border border-gray-200">
                                            <span class="absolute bottom-1 left-1 px-2 py-0.5 text-xs font-medium rounded-full
                                                {{ $photo->type === 'before' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700' }}">
                                                {{ ucfirst($photo->type) }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Upload Form --}}
                            <div class="grid grid-cols-2 gap-3">
                                <form method="POST" action="{{ route('partner.orders.upload-photo', $order) }}" enctype="multipart/form-data"
                                    x-data="{ uploading: false }" @submit="uploading = true">
                                    @csrf
                                    <input type="hidden" name="type" value="before">
                                    <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-yellow-300 bg-yellow-50 hover:bg-yellow-100 rounded-xl cursor-pointer transition-colors"
                                        :class="{ 'opacity-50 pointer-events-none': uploading }">
                                        <svg class="w-8 h-8 text-yellow-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        <span class="text-xs font-medium text-yellow-700">Foto Sebelum</span>
                                        <input type="file" name="photo" accept="image/*" class="hidden" required @change="$el.form.submit()">
                                    </label>
                                </form>

                                <form method="POST" action="{{ route('partner.orders.upload-photo', $order) }}" enctype="multipart/form-data"
                                    x-data="{ uploading: false }" @submit="uploading = true">
                                    @csrf
                                    <input type="hidden" name="type" value="after">
                                    <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-green-300 bg-green-50 hover:bg-green-100 rounded-xl cursor-pointer transition-colors"
                                        :class="{ 'opacity-50 pointer-events-none': uploading }">
                                        <svg class="w-8 h-8 text-green-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        <span class="text-xs font-medium text-green-700">Foto Sesudah</span>
                                        <input type="file" name="photo" accept="image/*" class="hidden" required @change="$el.form.submit()">
                                    </label>
                                </form>
                            </div>
                        </div>

                        <button @click="showServiceFee = true" class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-6 rounded-xl transition-all shadow-lg shadow-green-500/25 flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Selesaikan Order
                        </button>

                        {{-- Complete Order Modal --}}
                        <div x-show="showServiceFee" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" style="display: none;">
                            <div @click.away="showServiceFee = false" x-transition class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
                                <h3 class="text-lg font-bold text-dark mb-2">Selesaikan Order</h3>
                                <p class="text-sm text-gray-500 mb-4">Input biaya servis yang telah dikerjakan.</p>
                                <form method="POST" action="{{ route('partner.orders.update-status', $order) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="completed">
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Biaya Servis (Rp)</label>
                                        <input type="number" name="service_fee" class="input-field" placeholder="0" min="0" value="{{ old('service_fee', 0) }}">
                                    </div>
                                    <div class="flex gap-3">
                                        <button type="button" @click="showServiceFee = false" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2.5 px-4 rounded-xl transition-colors">
                                            Batal
                                        </button>
                                        <button type="submit" class="flex-1 bg-green-500 hover:bg-green-600 text-white font-medium py-2.5 px-4 rounded-xl transition-colors">
                                            Konfirmasi
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
