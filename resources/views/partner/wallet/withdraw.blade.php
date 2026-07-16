<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('partner.wallet.index') }}" class="w-9 h-9 bg-gray-100 hover:bg-gray-200 rounded-xl flex items-center justify-center transition-colors">
                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h2 class="text-xl font-bold text-dark">Tarik Dana</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-lg mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm flex items-center gap-2">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ session('error') }}
                </div>
            @endif

            {{-- Saldo Info --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-5 text-center">
                <p class="text-sm text-gray-500 mb-1">Saldo Tersedia</p>
                <p class="text-2xl font-bold text-dark">Rp {{ number_format((float) $wallet->balance, 0, ',', '.') }}</p>
            </div>

            {{-- Form --}}
            <form action="{{ route('partner.wallet.withdraw') }}" method="POST" x-data="{ amount: '' }">
                @csrf

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">

                    {{-- Amount --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Jumlah Penarikan</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">Rp</span>
                            <input type="number" name="amount" x-model="amount" min="10000" max="{{ $wallet->balance }}" step="1000"
                                class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl text-sm focus:ring-primary focus:border-primary"
                                placeholder="0" required>
                        </div>
                        <div class="flex justify-between mt-1.5">
                            <p class="text-xs text-gray-400">Min. Rp 10.000</p>
                            <button type="button" @click="amount = {{ $wallet->balance }}" class="text-xs text-primary hover:underline">Tarik Semua</button>
                        </div>
                        @error('amount') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Bank Name --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Bank</label>
                        <select name="bank_name" class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:ring-primary focus:border-primary" required>
                            <option value="">Pilih Bank</option>
                            @foreach(['BCA', 'Mandiri', 'BRI', 'BNI', 'CIMB Niaga', 'Danamon', 'Permata', 'BSI', 'Bank Lainnya'] as $bank)
                                <option value="{{ $bank }}" {{ old('bank_name') === $bank ? 'selected' : '' }}>{{ $bank }}</option>
                            @endforeach
                        </select>
                        @error('bank_name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Account Number --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Nomor Rekening</label>
                        <input type="text" name="bank_account_number" value="{{ old('bank_account_number') }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:ring-primary focus:border-primary"
                            placeholder="Masukkan nomor rekening" required>
                        @error('bank_account_number') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Account Name --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Pemilik Rekening</label>
                        <input type="text" name="bank_account_name" value="{{ old('bank_account_name') }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:ring-primary focus:border-primary"
                            placeholder="Sesuai nama di rekening" required>
                        @error('bank_account_name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <button type="submit"
                    class="w-full mt-4 bg-primary hover:bg-primary-600 text-white font-semibold py-3 rounded-xl text-sm transition-colors"
                    x-bind:disabled="!amount || amount < 10000 || amount > {{ $wallet->balance }}"
                    x-bind:class="(amount && amount >= 10000 && amount <= {{ $wallet->balance }}) ? 'opacity-100 cursor-pointer' : 'opacity-50 cursor-not-allowed'">
                    Ajukan Penarikan
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
