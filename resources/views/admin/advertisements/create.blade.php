<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.advertisements.index') }}" class="text-gray-500 hover:text-gray-700">← Kembali</a>
            <h2 class="text-xl font-semibold text-gray-800">Tambah Iklan Baru</h2>
        </div>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl">
            <form action="{{ route('admin.advertisements.store') }}" method="POST" enctype="multipart/form-data"
                class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-5">
                @csrf

                {{-- Title --}}
                <div>
                    <x-input-label for="title" value="Judul Iklan" />
                    <x-text-input id="title" name="title" :value="old('title')" class="mt-1 block w-full" required />
                    <x-input-error :messages="$errors->get('title')" class="mt-2" />
                </div>

                {{-- Image --}}
                <div>
                    <x-input-label for="image" value="Banner Image (JPG/PNG/WebP, maks 5MB)" />
                    <input type="file" id="image" name="image" accept="image/*" required
                        class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary file:text-white hover:file:bg-primary-600">
                    <x-input-error :messages="$errors->get('image')" class="mt-2" />
                </div>

                {{-- Target URL --}}
                <div>
                    <x-input-label for="target_url" value="URL Target (opsional)" />
                    <x-text-input id="target_url" name="target_url" :value="old('target_url')" type="url" class="mt-1 block w-full" placeholder="https://..." />
                    <x-input-error :messages="$errors->get('target_url')" class="mt-2" />
                </div>

                {{-- Position --}}
                <div>
                    <x-input-label for="position" value="Posisi Tampilan" />
                    <select id="position" name="position" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring-primary text-sm" required>
                        @foreach(['banner' => 'Banner (Atas)', 'sidebar' => 'Sidebar', 'feed' => 'Feed', 'popup' => 'Popup'] as $val => $label)
                            <option value="{{ $val }}" {{ old('position') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('position')" class="mt-2" />
                </div>

                {{-- Date Range --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="start_date" value="Tanggal Mulai" />
                        <x-text-input id="start_date" name="start_date" :value="old('start_date', date('Y-m-d'))" type="date" class="mt-1 block w-full" required />
                        <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="end_date" value="Tanggal Selesai" />
                        <x-text-input id="end_date" name="end_date" :value="old('end_date', date('Y-m-d', strtotime('+30 days')))" type="date" class="mt-1 block w-full" required />
                        <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
                    </div>
                </div>

                {{-- Submit --}}
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                    <a href="{{ route('admin.advertisements.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-300">Batal</a>
                    <button type="submit" class="px-4 py-2 bg-primary hover:bg-primary-600 text-white rounded-lg text-sm font-medium">Simpan Iklan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
