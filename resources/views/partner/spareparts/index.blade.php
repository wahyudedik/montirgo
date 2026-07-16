<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-dark">🔧 Sparepart</h2>
            <a href="{{ route('partner.spareparts.create') }}" class="inline-flex items-center gap-2 bg-primary hover:bg-primary-600 text-white text-sm font-medium px-4 py-2 rounded-xl transition shadow-lg shadow-primary/25">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600">
                            <tr>
                                <th class="px-5 py-3 text-left font-medium">Item</th>
                                <th class="px-5 py-3 text-left font-medium">Kategori</th>
                                <th class="px-5 py-3 text-right font-medium">Harga</th>
                                <th class="px-5 py-3 text-right font-medium">Stok</th>
                                <th class="px-5 py-3 text-center font-medium">Status</th>
                                <th class="px-5 py-3 text-right font-medium">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($spareparts as $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            @if($item->photo_url)
                                                <img src="{{ asset('storage/'.$item->photo_url) }}" alt="{{ $item->name }}" class="w-10 h-10 rounded-lg object-cover">
                                            @else
                                                <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                                </div>
                                            @endif
                                            <div>
                                                <p class="font-semibold text-dark">{{ $item->name }}</p>
                                                @if($item->description)
                                                    <p class="text-xs text-gray-500 truncate max-w-[200px]">{{ $item->description }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-gray-600">{{ $item->category }}</td>
                                    <td class="px-5 py-4 text-right font-medium text-dark">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                    <td class="px-5 py-4 text-right">
                                        <span class="{{ $item->stock <= 0 ? 'text-red-600' : ($item->stock <= 5 ? 'text-yellow-600' : 'text-gray-600') }}">
                                            {{ $item->stock }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $item->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                            {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('partner.spareparts.edit', $item) }}" class="text-xs text-primary hover:text-primary-600 font-medium">Edit</a>
                                            <form method="POST" action="{{ route('partner.spareparts.destroy', $item) }}" onsubmit="return confirm('Hapus sparepart ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-medium">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        Belum ada sparepart.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($spareparts->hasPages())
                    <div class="px-5 py-4 border-t border-gray-100">
                        {{ $spareparts->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
