<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Withdraw Management</h2>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8">

        {{-- Stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <p class="text-xs text-gray-500 mb-1">Menunggu</p>
                <p class="text-2xl font-bold text-yellow-600">{{ $stats['pending'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <p class="text-xs text-gray-500 mb-1">Disetujui</p>
                <p class="text-2xl font-bold text-green-600">{{ $stats['approved'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <p class="text-xs text-gray-500 mb-1">Ditolak</p>
                <p class="text-2xl font-bold text-red-600">{{ $stats['rejected'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <p class="text-xs text-gray-500 mb-1">Total Pending</p>
                <p class="text-2xl font-bold text-dark">Rp {{ number_format($stats['total_pending_amount'], 0, ',', '.') }}</p>
            </div>
        </div>

        {{-- Filter --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
            <form action="{{ route('admin.withdraws.index') }}" method="GET" class="flex flex-wrap gap-3">
                <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-primary focus:border-primary">
                    <option value="">Semua Status</option>
                    @foreach(['pending', 'approved', 'rejected', 'processed'] as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2 bg-primary hover:bg-primary-600 text-white rounded-lg text-sm font-medium">Filter</button>
                <a href="{{ route('admin.withdraws.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-300">Reset</a>
            </form>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-5 py-3 text-left font-medium">Partner</th>
                            <th class="px-5 py-3 text-left font-medium">Jumlah</th>
                            <th class="px-5 py-3 text-left font-medium">Bank</th>
                            <th class="px-5 py-3 text-left font-medium">Rekening</th>
                            <th class="px-5 py-3 text-left font-medium">Status</th>
                            <th class="px-5 py-3 text-left font-medium">Tanggal</th>
                            <th class="px-5 py-3 text-right font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($requests as $req)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3">{{ $req->user->name ?? '-' }}</td>
                                <td class="px-5 py-3 font-semibold">Rp {{ number_format((float) $req->amount, 0, ',', '.') }}</td>
                                <td class="px-5 py-3">{{ $req->bank_name }}</td>
                                <td class="px-5 py-3 font-mono text-xs">{{ $req->bank_account_number }}</td>
                                <td class="px-5 py-3">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ match($req->status) { 'pending' => 'bg-yellow-100 text-yellow-700', 'approved' => 'bg-green-100 text-green-700', 'rejected' => 'bg-red-100 text-red-700', 'processed' => 'bg-blue-100 text-blue-700', default => 'bg-gray-100 text-gray-700' } }}">
                                        {{ ucfirst($req->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-gray-500">{{ $req->created_at->format('d M Y') }}</td>
                                <td class="px-5 py-3 text-right">
                                    <a href="{{ route('admin.withdraws.show', $req) }}" class="text-primary hover:underline text-xs font-medium">Detail</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-8 text-center text-gray-400">Tidak ada data withdraw</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-5 py-3 border-t border-gray-100">
                {{ $requests->withQueryString()->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
