<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('partner.service-cost.index') }}" class="w-9 h-9 bg-gray-100 hover:bg-gray-200 rounded-xl flex items-center justify-center transition-colors">
                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h2 class="text-xl font-bold text-dark">Input Biaya Servis</h2>
                <p class="text-sm text-gray-500">Order #{{ $order->code }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6" x-data="serviceCostForm()" x-init="init()">

            @if($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-700">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Order Summary --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-bold text-dark mb-3">Ringkasan Order</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500">Customer</span>
                        <p class="font-medium text-dark">{{ $order->user->name }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500">Layanan</span>
                        <p class="font-medium text-dark">{{ $order->service_type }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500">Kendaraan</span>
                        <p class="font-medium text-dark">{{ $order->vehicle->brand ?? '-' }} {{ $order->vehicle->model ?? '' }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500">Biaya Panggilan</span>
                        <p class="font-bold text-primary">Rp {{ number_format((float) $order->callout_fee, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            {{-- Service Cost Items --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-dark">Rincian Biaya Servis</h3>
                    <button type="button" @click="addItem()" class="inline-flex items-center gap-1 bg-primary hover:bg-primary-600 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Tambah Item
                    </button>
                </div>

                <div class="space-y-3">
                    <template x-for="(item, index) in items" :key="index">
                        <div class="bg-gray-50 rounded-xl p-4 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-medium text-gray-500" x-text="'Item #' + (index + 1)"></span>
                                <button type="button" @click="removeItem(index)" x-show="items.length > 1" class="text-red-500 hover:text-red-700 text-xs font-medium">
                                    Hapus
                                </button>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="col-span-2 sm:col-span-1">
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Nama Item</label>
                                    <input type="text" x-model="item.name" name="items[]" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition" placeholder="Contoh: Ganti Oli" required>
                                    <input type="hidden" :name="'items[' + index + '][name]'" :value="item.name">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Tipe</label>
                                    <select x-model="item.type" :name="'items[' + index + '][type]'" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition" required>
                                        <option value="service">🔧 Jasa Servis</option>
                                        <option value="sparepart">🔩 Sparepart</option>
                                    </select>
                                </div>
                            </div>
                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Harga Satuan (Rp)</label>
                                    <input type="number" x-model.number="item.unit_price" :name="'items[' + index + '][unit_price]'" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition" placeholder="0" min="0" required>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Jumlah</label>
                                    <input type="number" x-model.number="item.quantity" :name="'items[' + index + '][quantity]'" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition" placeholder="1" min="1" required>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Subtotal</label>
                                    <div class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-100 font-medium text-dark">
                                        Rp <span x-text="formatCurrency(item.unit_price * item.quantity)">0</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-show="items.length === 0" class="text-center py-8 text-gray-400 text-sm">
                    Klik "Tambah Item" untuk menambahkan rincian biaya servis.
                </div>
            </div>

            {{-- Total Summary --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-bold text-dark mb-3">Ringkasan Biaya</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Biaya Panggilan</span>
                        <span class="font-medium text-dark">Rp <span x-text="formatCurrency({{ (float) $order->callout_fee }})">{{ number_format((float) $order->callout_fee, 0, ',', '.') }}</span></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Biaya Servis</span>
                        <span class="font-medium text-dark">Rp <span x-text="formatCurrency(totalServiceFee)">{{ number_format((float) $order->service_fee, 0, ',', '.') }}</span></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Komisi Platform (10%)</span>
                        <span class="font-medium text-orange-600">Rp <span x-text="formatCurrency(platformCommission)">{{ number_format((float) $order->platform_commission, 0, ',', '.') }}</span></span>
                    </div>
                    <div class="border-t border-gray-100 pt-2 flex justify-between">
                        <span class="font-bold text-dark">Total Tagihan</span>
                        <span class="font-bold text-primary text-lg">Rp <span x-text="formatCurrency(grandTotal)">{{ number_format((float) $order->total_amount, 0, ',', '.') }}</span></span>
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div class="flex gap-3">
                <a href="{{ route('partner.service-cost.index') }}" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-3 px-6 rounded-xl transition-colors text-center">
                    Batal
                </a>
                <button type="button" @click="submitForm()" :disabled="items.length === 0 || submitting" class="flex-1 bg-primary hover:bg-primary-600 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold py-3 px-6 rounded-xl transition-all shadow-lg shadow-primary/25 flex items-center justify-center gap-2">
                    <template x-if="!submitting">
                        <span class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Simpan Biaya Servis
                        </span>
                    </template>
                    <template x-if="submitting">
                        <span class="flex items-center gap-2">
                            <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            Menyimpan...
                        </span>
                    </template>
                </button>
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
        function serviceCostForm() {
            return {
                items: @js($order->serviceCostItems->map(fn($item) => [
                    'name' => $item->name,
                    'type' => $item->type,
                    'unit_price' => (float) $item->unit_price,
                    'quantity' => (int) $item->quantity,
                ])->toArray() ?: [['name' => '', 'type' => 'service', 'unit_price' => 0, 'quantity' => 1]]),
                submitting: false,

                init() {
                    // Items are pre-filled from existing data or default
                },

                addItem() {
                    this.items.push({ name: '', type: 'service', unit_price: 0, quantity: 1 });
                },

                removeItem(index) {
                    this.items.splice(index, 1);
                },

                get totalServiceFee() {
                    return this.items.reduce((sum, item) => sum + (item.unit_price * item.quantity), 0);
                },

                get platformCommission() {
                    return Math.round(this.totalServiceFee * 0.10);
                },

                get grandTotal() {
                    return {{ (float) $order->callout_fee }} + this.totalServiceFee;
                },

                formatCurrency(value) {
                    return new Intl.NumberFormat('id-ID').format(Math.round(value || 0));
                },

                submitForm() {
                    if (this.items.length === 0) return;
                    this.submitting = true;

                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route("partner.service-cost.store", $order) }}';

                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = '{{ csrf_token() }}';
                    form.appendChild(csrfInput);

                    this.items.forEach((item, index) => {
                        ['name', 'type', 'unit_price', 'quantity'].forEach(field => {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = `items[${index}][${field}]`;
                            input.value = item[field];
                            form.appendChild(input);
                        });
                    });

                    document.body.appendChild(form);
                    form.submit();
                }
            };
        }
    </script>
    @endpush
</x-app-layout>
