<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('partner.spareparts.index') }}" class="text-gray-400 hover:text-dark transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h2 class="text-xl font-bold text-dark">🔧 Tambah Sparepart</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-lg mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('partner.spareparts.store') }}" enctype="multipart/form-data" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-semibold text-dark mb-1.5">Nama Item *</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:border-primary focus:ring-1 focus:ring-primary"
                        placeholder="Contoh: Kampas Rem Depan">
                    @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-semibold text-dark mb-1.5">Deskripsi</label>
                    <textarea id="description" name="description" rows="2"
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:border-primary focus:ring-1 focus:ring-primary resize-none"
                        placeholder="Deskripsi singkat sparepart...">{{ old('description') }}</textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="category" class="block text-sm font-semibold text-dark mb-1.5">Kategori *</label>
                        <select id="category" name="category" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:border-primary focus:ring-1 focus:ring-primary">
                            <option value="">Pilih</option>
                            <option value="Rem" {{ old('category') === 'Rem' ? 'selected' : '' }}>Rem</option>
                            <option value="Mesin" {{ old('category') === 'Mesin' ? 'selected' : '' }}>Mesin</option>
                            <option value="Kelistrikan" {{ old('category') === 'Kelistrikan' ? 'selected' : '' }}>Kelistrikan</option>
                            <option value="Oli & Pelumas" {{ old('category') === 'Oli & Pelumas' ? 'selected' : '' }}>Oli & Pelumas</option>
                            <option value="Ban & Velg" {{ old('category') === 'Ban & Velg' ? 'selected' : '' }}>Ban & Velg</option>
                            <option value="Aksesoris" {{ old('category') === 'Aksesoris' ? 'selected' : '' }}>Aksesoris</option>
                            <option value="Lainnya" {{ old('category') === 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                        @error('category') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="stock" class="block text-sm font-semibold text-dark mb-1.5">Stok *</label>
                        <input type="number" id="stock" name="stock" value="{{ old('stock', 0) }}" min="0" required
                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:border-primary focus:ring-1 focus:ring-primary">
                    </div>
                </div>

                <div>
                    <label for="price" class="block text-sm font-semibold text-dark mb-1.5">Harga (Rp) *</label>
                    <input type="number" id="price" name="price" value="{{ old('price') }}" min="0" required
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:border-primary focus:ring-1 focus:ring-primary"
                        placeholder="0">
                    @error('price') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="photo" class="block text-sm font-semibold text-dark mb-1.5">Foto</label>
                    <input type="file" id="photo" name="photo" accept="image/*"
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:border-primary focus:ring-1 focus:ring-primary file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:bg-primary file:text-white file:cursor-pointer">
                    @error('photo') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="flex-1 bg-primary hover:bg-primary-600 text-white font-semibold py-2.5 px-6 rounded-xl transition shadow-lg shadow-primary/25">
                        Simpan
                    </button>
                    <a href="{{ route('partner.spareparts.index') }}" class="px-6 py-2.5 text-sm font-medium text-gray-500 hover:text-dark border border-gray-200 rounded-xl transition">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
