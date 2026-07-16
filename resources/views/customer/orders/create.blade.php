<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('customer.orders.index') }}" class="w-9 h-9 bg-gray-100 hover:bg-gray-200 rounded-xl flex items-center justify-center transition-colors">
                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h2 class="text-xl font-bold text-dark">Pesan Mekanik</h2>
                <p class="text-sm text-gray-500">Isi detail kendala kendaraan Anda</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            <form method="POST" action="{{ route('customer.orders.store') }}" id="orderForm" x-data="orderForm()" x-init="init()">
                @csrf

                {{-- Step 1: Pilih Kendaraan --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-8 h-8 bg-primary rounded-full flex items-center justify-center text-white text-sm font-bold">1</div>
                        <h3 class="text-base font-bold text-dark">Pilih Kendaraan</h3>
                    </div>

                    @if($vehicles->isEmpty())
                        <div class="text-center py-6 bg-gray-50 rounded-xl">
                            <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 17h8M8 17v-4h8v4M8 17H5a1 1 0 01-1-1v-3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-1 1h-3"/></svg>
                            <p class="text-sm text-gray-500">Belum ada kendaraan terdaftar</p>
                            <p class="text-xs text-gray-400 mt-1">Anda bisa melanjutkan tanpa memilih kendaraan</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($vehicles as $vehicle)
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="vehicle_id" value="{{ $vehicle->id }}" class="peer sr-only" {{ $vehicle->is_default ? 'checked' : '' }}>
                                    <div class="border-2 border-gray-200 peer-checked:border-primary peer-checked:bg-primary-50 rounded-xl p-4 transition-all hover:border-gray-300">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-dark-50 rounded-xl flex items-center justify-center peer-checked:bg-primary-100">
                                                @if($vehicle->type === 'motorcycle')
                                                    <svg class="w-5 h-5 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                                @else
                                                    <svg class="w-5 h-5 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 17h8M8 17v-4h8v4M8 17H5a1 1 0 01-1-1v-3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-1 1h-3"/></svg>
                                                @endif
                                            </div>
                                            <div>
                                                <p class="text-sm font-semibold text-dark">{{ $vehicle->brand }} {{ $vehicle->model }}</p>
                                                <p class="text-xs text-gray-400">{{ $vehicle->license_plate }} · {{ ucfirst($vehicle->type) }}</p>
                                            </div>
                                        </div>
                                        @if($vehicle->is_default)
                                            <span class="absolute top-2 right-2 text-[10px] bg-primary text-white px-2 py-0.5 rounded-full font-medium">Default</span>
                                        @endif
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    @endif
                    <input type="hidden" name="vehicle_id" value="{{ $vehicles->firstWhere('is_default', true)?->id ?? '' }}">
                </div>

                {{-- Step 2: Pilih Layanan --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-8 h-8 bg-primary rounded-full flex items-center justify-center text-white text-sm font-bold">2</div>
                        <h3 class="text-base font-bold text-dark">Pilih Layanan</h3>
                    </div>

                    @error('service_category')
                        <p class="text-sm text-red-500 mb-3">{{ $message }}</p>
                    @enderror
                    @error('service_type')
                        <p class="text-sm text-red-500 mb-3">{{ $message }}</p>
                    @enderror

                    <div class="space-y-4">
                        @foreach($serviceTypes as $category => $services)
                            <div>
                                <p class="text-sm font-semibold text-dark mb-2">{{ $category }}</p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    @foreach($services as $service)
                                        <label class="cursor-pointer" @click="selectedService = '{{ $category }} - {{ $service }}'">
                                            <input type="radio" name="service_type" value="{{ $service }}" class="peer sr-only" required>
                                            <input type="hidden" name="service_category" value="{{ $category }}" x-show="selectedService === '{{ $category }} - {{ $service }}'">
                                            <div class="border border-gray-200 peer-checked:border-primary peer-checked:bg-primary-50 rounded-xl px-4 py-2.5 text-sm text-gray-700 peer-checked:text-primary-700 peer-checked:font-medium transition-all hover:border-gray-300 text-center">
                                                {{ $service }}
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Step 3: Detail Keluhan --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-8 h-8 bg-primary rounded-full flex items-center justify-center text-white text-sm font-bold">3</div>
                        <h3 class="text-base font-bold text-dark">Detail Keluhan</h3>
                    </div>

                    <div>
                        <label for="problem_description" class="block text-sm font-medium text-gray-700 mb-1.5">Deskripsikan kendala Anda</label>
                        <textarea
                            id="problem_description"
                            name="problem_description"
                            rows="4"
                            class="input-field"
                            placeholder="Contoh: Motor mogok di jalan, mesin tidak menyala saat distarter..."
                            maxlength="1000"
                        >{{ old('problem_description') }}</textarea>
                        <p class="text-xs text-gray-400 mt-1">Maksimal 1000 karakter</p>
                    </div>
                </div>

                {{-- Step 4: Lokasi --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-8 h-8 bg-primary rounded-full flex items-center justify-center text-white text-sm font-bold">4</div>
                        <h3 class="text-base font-bold text-dark">Lokasi Anda</h3>
                    </div>

                    <div x-show="!locationDetected" class="text-center py-6 bg-gray-50 rounded-xl">
                        <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <p class="text-sm text-gray-500 mb-3">Mendeteksi lokasi Anda...</p>
                        <button type="button" @click="detectLocation()" class="text-sm text-primary font-medium hover:text-primary-600">
                            Klik untuk deteksi manual
                        </button>
                    </div>

                    <div x-show="locationDetected" class="space-y-3">
                        <div class="flex items-center gap-2 text-green-600 bg-green-50 rounded-xl px-4 py-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span class="text-sm font-medium">Lokasi terdeteksi</span>
                        </div>
                        <div>
                            <label for="location_address" class="block text-sm font-medium text-gray-700 mb-1.5">Alamat (opsional)</label>
                            <input
                                type="text"
                                id="location_address"
                                name="location_address"
                                class="input-field"
                                placeholder="Contoh: Jl. Mojopahit No. 45, Mojokerto"
                                value="{{ old('location_address') }}"
                            >
                        </div>
                        <p class="text-xs text-gray-400">Koordinat: <span x-text="lat"></span>, <span x-text="lng"></span></p>
                    </div>

                    <input type="hidden" name="location_lat" x-model="lat" required>
                    <input type="hidden" name="location_lng" x-model="lng" required>

                    @error('location_lat')
                        <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Step 5: Pembayaran --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-8 h-8 bg-primary rounded-full flex items-center justify-center text-white text-sm font-bold">5</div>
                        <h3 class="text-base font-bold text-dark">Metode Pembayaran</h3>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        @php
                            $paymentMethods = [
                                'cash' => ['label' => 'Tunai', 'icon' => 'banknote'],
                                'wallet' => ['label' => 'Dompet', 'icon' => 'wallet'],
                                'qris' => ['label' => 'QRIS', 'icon' => 'qr'],
                                'card' => ['label' => 'Kartu', 'icon' => 'card'],
                            ];
                        @endphp
                        @foreach($paymentMethods as $value => $method)
                            <label class="cursor-pointer">
                                <input type="radio" name="payment_method" value="{{ $value }}" class="peer sr-only" {{ $value === 'cash' ? 'checked' : '' }} required>
                                <div class="border-2 border-gray-200 peer-checked:border-primary peer-checked:bg-primary-50 rounded-xl p-3 text-center transition-all hover:border-gray-300">
                                    @if($method['icon'] === 'banknote')
                                        <svg class="w-6 h-6 text-gray-400 peer-checked:text-primary mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    @elseif($method['icon'] === 'wallet')
                                        <svg class="w-6 h-6 text-gray-400 peer-checked:text-primary mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                    @elseif($method['icon'] === 'qr')
                                        <svg class="w-6 h-6 text-gray-400 peer-checked:text-primary mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                    @else
                                        <svg class="w-6 h-6 text-gray-400 peer-checked:text-primary mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                    @endif
                                    <span class="text-xs font-medium text-gray-600 peer-checked:text-primary-700">{{ $method['label'] }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Ringkasan & Submit --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
                    <h3 class="text-base font-bold text-dark mb-4">Ringkasan Biaya</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Biaya Panggilan</span>
                            <span class="font-medium text-dark">Rp {{ number_format($calloutFee, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Biaya Servis</span>
                            <span class="text-gray-400 italic">Dihitung setelah mekanik tiba</span>
                        </div>
                        <div class="border-t border-gray-100 pt-3 flex justify-between">
                            <span class="font-semibold text-dark">Total Saat Ini</span>
                            <span class="font-bold text-primary text-lg">Rp {{ number_format($calloutFee, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="mt-5 p-3 bg-blue-50 rounded-xl">
                        <div class="flex gap-2">
                            <svg class="w-4 h-4 text-blue-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="text-xs text-blue-700">Biaya servis dan sparepart akan diinput oleh mekanik setelah diagnosis di lokasi. Anda akan mendapat estimasi sebelum pekerjaan dimulai.</p>
                        </div>
                    </div>

                    <button
                        type="submit"
                        class="w-full mt-5 bg-primary hover:bg-primary-600 text-white font-bold py-3.5 px-6 rounded-xl transition-all shadow-lg shadow-primary/25 flex items-center justify-center gap-2"
                        :disabled="!locationDetected"
                        :class="{ 'opacity-50 cursor-not-allowed': !locationDetected }"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        Pesan Mekanik Sekarang
                    </button>
                </div>
            </form>

        </div>
    </div>

    @push('scripts')
    <script>
        function orderForm() {
            return {
                lat: {{ old('location_lat', 'null') }},
                lng: {{ old('location_lng', 'null') }},
                locationDetected: {{ old('location_lat') ? 'true' : 'false' }},
                selectedService: '{{ old('service_category', '') }}{{ old('service_category') && old('service_type') ? ' - ' : '' }}{{ old('service_type', '') }}',

                init() {
                    if (!this.locationDetected) {
                        this.detectLocation();
                    }
                },

                detectLocation() {
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(
                            (position) => {
                                this.lat = position.coords.latitude.toFixed(7);
                                this.lng = position.coords.longitude.toFixed(7);
                                this.locationDetected = true;
                            },
                            (error) => {
                                console.error('Geolocation error:', error);
                                // Default ke Mojokerto
                                this.lat = '-7.4704';
                                this.lng = '112.4391';
                                this.locationDetected = true;
                            },
                            { enableHighAccuracy: true, timeout: 10000 }
                        );
                    } else {
                        // Default ke Mojokerto
                        this.lat = '-7.4704';
                        this.lng = '112.4391';
                        this.locationDetected = true;
                    }
                }
            };
        }
    </script>
    @endpush
</x-app-layout>
