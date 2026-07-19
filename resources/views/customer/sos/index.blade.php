<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard') }}" class="w-9 h-9 bg-gray-100 hover:bg-gray-200 rounded-xl flex items-center justify-center transition-colors">
                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h2 class="text-xl font-bold text-red-600 flex items-center gap-2"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg> SOS Darurat</h2>
                <p class="text-sm text-gray-500">Kirim bantuan mekanik segera</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            {{-- Alert Aktif SOS --}}
            @if($activeSos)
                <div class="bg-red-50 border border-red-200 rounded-2xl p-6 mb-6" x-data>
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center animate-pulse">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-base font-bold text-red-800">SOS Aktif — #{{ $activeSos->code }}</h3>
                            <p class="text-sm text-red-600">{{ $activeSos->status_label }} · {{ $activeSos->service_type }}</p>
                        </div>
                        <a href="{{ route('customer.orders.show', $activeSos) }}" class="px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded-xl hover:bg-red-700 transition-colors">
                            Lacak
                        </a>
                    </div>
                </div>
            @endif

            {{-- Info Box --}}
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 mb-6">
                <div class="flex gap-3">
                    <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-amber-800">Kapan harus pakai SOS?</h3>
                        <p class="text-xs text-amber-700 mt-1">Gunakan tombol SOS ketika kendaraan Anda mengalami kendala mendesak di jalan yang membutuhkan penanganan segera. Biaya panggilan GRATIS untuk layanan SOS.</p>
                    </div>
                </div>
            </div>

            {{-- Form SOS --}}
            <form method="POST" action="{{ route('customer.sos.send') }}" x-data="sosForm()">
                @csrf

                {{-- Step 1: Pilih Kategori Darurat --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center text-white text-sm font-bold">1</div>
                        <h3 class="text-base font-bold text-dark">Jenis Darurat</h3>
                    </div>

                    @error('sos_type')
                        <p class="text-sm text-red-600 mb-3">{{ $message }}</p>
                    @enderror

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($categories as $key => $category)
                            <label class="relative cursor-pointer">
                                <input type="radio" name="sos_type" value="{{ $key }}" class="peer sr-only" wire:model="selectedCategory">
                                <div class="border-2 border-gray-200 peer-checked:border-red-500 peer-checked:bg-red-50 rounded-xl p-4 transition-all hover:border-gray-300 cursor-pointer">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 bg-gray-100 peer-checked:bg-red-100 rounded-xl flex items-center justify-center text-2xl">
                                            {{ $category['icon'] }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-dark">{{ $category['label'] }}</p>
                                            <p class="text-xs text-gray-400">{{ $category['description'] }}</p>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Step 2: Pilih Kendaraan (opsional) --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center text-white text-sm font-bold">2</div>
                        <h3 class="text-base font-bold text-dark">Kendaraan <span class="text-gray-400 font-normal text-sm">(opsional)</span></h3>
                    </div>

                    @if($vehicles->isEmpty())
                        <div class="text-center py-4 bg-gray-50 rounded-xl">
                            <p class="text-sm text-gray-500">Anda bisa melanjutkan tanpa memilih kendaraan</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($vehicles as $vehicle)
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="vehicle_id" value="{{ $vehicle->id }}" class="peer sr-only" {{ $vehicle->is_default ? 'checked' : '' }}>
                                    <div class="border-2 border-gray-200 peer-checked:border-red-500 peer-checked:bg-red-50 rounded-xl p-3 transition-all hover:border-gray-300 cursor-pointer">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center">
                                                @if($vehicle->type === 'motorcycle')
                                                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                                @else
                                                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 17h8M8 17v-4h8v4M8 17H5a1 1 0 01-1-1v-3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-1 1h-3"/></svg>
                                                @endif
                                            </div>
                                            <div>
                                                <p class="text-sm font-semibold text-dark">{{ $vehicle->brand }} {{ $vehicle->model }}</p>
                                                <p class="text-xs text-gray-400">{{ $vehicle->license_plate }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Step 3: Lokasi --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center text-white text-sm font-bold">3</div>
                        <h3 class="text-base font-bold text-dark">Lokasi Saat Ini</h3>
                    </div>

                    <input type="hidden" name="location_lat" :value="latitude">
                    <input type="hidden" name="location_lng" :value="longitude">

                    @error('location_lat')
                        <p class="text-sm text-red-600 mb-3">{{ $message }}</p>
                    @enderror

                    <div class="text-center py-6 bg-gray-50 rounded-xl" x-show="!locationFound">
                        <div class="animate-spin w-8 h-8 border-4 border-red-500 border-t-transparent rounded-full mx-auto mb-3"></div>
                        <p class="text-sm text-gray-500">Mendeteksi lokasi Anda...</p>
                        <p class="text-xs text-gray-400 mt-1">Aktifkan GPS untuk lokasi otomatis</p>
                    </div>

                    <div class="text-center py-6 bg-green-50 rounded-xl" x-show="locationFound">
                        <svg class="w-10 h-10 text-green-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <p class="text-sm font-semibold text-green-700">Lokasi terdeteksi!</p>
                        <p class="text-xs text-green-600 mt-1" x-text="latitude + ', ' + longitude"></p>
                    </div>

                    <div class="mt-3">
                        <input type="text" name="location_address" placeholder="Alamat / patokan (opsional, contoh: depan Indomaret)" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </div>
                </div>

                {{-- Tombol SOS --}}
                <div class="text-center" x-show="locationFound">
                    <button type="submit" class="relative w-full max-w-md mx-auto px-8 py-5 bg-red-600 hover:bg-red-700 text-white text-lg font-bold rounded-2xl transition-all shadow-lg hover:shadow-xl active:scale-95" onclick="return confirm('Kirim SOS sekarang? Mekanik terdekat akan segera dikirim.')">
                        <span class="flex items-center justify-center gap-3">
                            <svg class="w-7 h-7 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            KIRIM SOS SEKARANG
                        </span>
                    </button>
                    <p class="text-xs text-gray-400 mt-3">Biaya panggilan GRATIS untuk layanan SOS</p>
                </div>

                {{-- Hidden --}}
                <input type="hidden" name="sos_type" :value="selectedCategory">
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function sosForm() {
            return {
                latitude: null,
                longitude: null,
                locationFound: false,
                selectedCategory: null,

                init() {
                    this.getLocation();
                },

                getLocation() {
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(
                            (position) => {
                                this.latitude = position.coords.latitude.toFixed(7);
                                this.longitude = position.coords.longitude.toFixed(7);
                                this.locationFound = true;
                            },
                            (error) => {
                                console.error('Geolocation error:', error);
                                // Fallback: Jakarta center
                                this.latitude = '-6.2088';
                                this.longitude = '106.8456';
                                this.locationFound = true;
                            },
                            { enableHighAccuracy: true, timeout: 10000 }
                        );
                    } else {
                        // Fallback
                        this.latitude = '-6.2088';
                        this.longitude = '106.8456';
                        this.locationFound = true;
                    }
                }
            };
        }
    </script>
    @endpush
</x-app-layout>
