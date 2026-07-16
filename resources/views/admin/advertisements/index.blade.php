<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800">Iklan Otomotif</h2>
            <a href="{{ route('admin.advertisements.create') }}" class="px-4 py-2 bg-primary hover:bg-primary-600 text-white rounded-lg text-sm font-medium">
                + Tambah Iklan
            </a>
        </div>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8">

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- Filter --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
            <form action="{{ route('admin.advertisements.index') }}" method="GET" class="flex flex-wrap gap-3">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari iklan..."
                    class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-primary focus:border-primary">
                <select name="position" class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-primary focus:border-primary">
                    <option value="">Semua Posisi</option>
                    @foreach(['banner', 'sidebar', 'feed', 'popup'] as $pos)
                        <option value="{{ $pos }}" {{ request('position') === $pos ? 'selected' : '' }}>{{ ucfirst($pos) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2 bg-primary hover:bg-primary-600 text-white rounded-lg text-sm font-medium">Filter</button>
            </form>
        </div>

        {{-- Advertisements Table --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Iklan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Posisi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jadwal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stats</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($advertisements as $ad)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ asset('storage/' . $ad->image_path) }}" alt="{{ $ad->title }}"
                                            class="w-16 h-10 object-cover rounded-lg border border-gray-200">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $ad->title }}</p>
                                            @if($ad->target_url)
                                                <p class="text-xs text-blue-600 truncate max-w-[200px]">{{ $ad->target_url }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full
                                        {{ match($ad->position) {
                                            'banner' => 'bg-blue-100 text-blue-700',
                                            'sidebar' => 'bg-purple-100 text-purple-700',
                                            'feed' => 'bg-green-100 text-green-700',
                                            'popup' => 'bg-yellow-100 text-yellow-700',
                                            default => 'bg-gray-100 text-gray-700',
                                        } }}">
                                        {{ ucfirst($ad->position) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <p>{{ $ad->start_date->format('d M Y') }}</p>
                                    <p class="text-xs text-gray-400">s/d {{ $ad->end_date->format('d M Y') }}</p>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <p class="text-gray-600">{{ number_format($ad->impressions) }} views</p>
                                    <p class="text-gray-600">{{ number_format($ad->clicks) }} clicks</p>
                                    <p class="text-xs text-gray-400">CTR: {{ $ad->ctr }}%</p>
                                </td>
                                <td class="px-6 py-4">
                                    @if($ad->isActive())
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">Aktif</span>
                                    @elseif($ad->is_active)
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-700">Terjadwal</span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-500">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <a href="{{ route('admin.advertisements.edit', $ad) }}" class="text-primary hover:text-primary-600 text-sm font-medium">Edit</a>
                                    <form action="{{ route('admin.advertisements.destroy', $ad) }}" method="POST" class="inline"
                                        onsubmit="return confirm('Hapus iklan ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 text-sm font-medium">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    Belum ada iklan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $advertisements->withQueryString()->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
