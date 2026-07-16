<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-dark">💰 Biaya Servis</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm flex items-center gap-2">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
                <div class="px-5 py-4 border-b border-gray-100">
                    <p class="text-sm text-gray-500">Input rincian biaya servis untuk order yang sedang berlangsung.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600">
                            <tr>
                                <th class="px-5 py-3 text-left font-medium">Order</th>
                                <th class="px-5 py-3 text-left font-medium">Customer</th>
                                <th class="px-5 py-3 text-left font-medium">Layanan</th>
                                <th class="px-5 py-3 text-right font-medium">Biaya Panggilan</th>
                                <th class="px-5 py-3 text-center font-medium">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($orders as $order)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-5 py-4">
                                        <div>
                                            <p class="font-semibold text-dark">#{{ $order->code }}</p>
                                            <p class="text-xs text-gray-400">{{ $order->created_at->format('d M Y, H:i') }}</p>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <p class="font-medium text-dark">{{ $order->user->name }}</p>
                                        <p class="text-xs text-gray-400">{{ $order->user->phone ?? $order->user->email }}</p>
                                    </td>
                                    <td class="px-5 py-4 text-gray-600">{{ $order->service_type }}</td>
                                    <td class="px-5 py-4 text-right font-medium text-dark">Rp {{ number_format((float) $order->callout_fee, 0, ',', '.') }}</td>
                                    <td class="px-5 py-4 text-center">
                                        <a href="{{ route('partner.service-cost.create', $order) }}" class="inline-flex items-center gap-1 bg-primary hover:bg-primary-600 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition shadow-sm">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            Input Biaya
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center gap-2">
                                            <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            <p>Belum ada order yang perlu input biaya.</p>
                                            <p class="text-xs text-gray-400">Order harus dalam status "Sedang Dikerjakan".</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($orders->hasPages())
                    <div class="px-5 py-4 border-t border-gray-100">
                        {{ $orders->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
