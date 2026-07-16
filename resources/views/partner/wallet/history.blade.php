<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('partner.wallet.index') }}" class="w-9 h-9 bg-gray-100 hover:bg-gray-200 rounded-xl flex items-center justify-center transition-colors">
                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h2 class="text-xl font-bold text-dark">Riwayat Transaksi</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                @forelse($transactions as $tx)
                    <div class="flex items-center justify-between px-5 py-4 {{ !$loop->last ? 'border-b border-gray-50' : '' }}">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center {{ match($tx->type) { 'income' => 'bg-green-50', 'withdrawal' => 'bg-red-50', 'refund' => 'bg-yellow-50', default => 'bg-blue-50' } }}">
                                @if($tx->type === 'income')
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/></svg>
                                @elseif($tx->type === 'withdrawal')
                                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                @elseif($tx->type === 'refund')
                                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                @else
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                @endif
                            </div>
                            <div>
                                <p class="text-sm font-medium text-dark">{{ $tx->description ?? ucfirst($tx->type) }}</p>
                                <p class="text-xs text-gray-400">{{ $tx->created_at->format('d M Y, H:i') }}</p>
                                @if($tx->order)
                                    <p class="text-xs text-gray-400">Order #{{ $tx->order->code }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold {{ in_array($tx->type, ['income', 'refund', 'topup', 'bonus']) ? 'text-green-600' : 'text-red-600' }}">
                                {{ in_array($tx->type, ['income', 'refund', 'topup', 'bonus']) ? '+' : '-' }} Rp {{ number_format((float) $tx->amount, 0, ',', '.') }}
                            </p>
                            <p class="text-xs text-gray-400">Saldo: Rp {{ number_format((float) $tx->balance_after, 0, ',', '.') }}</p>
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center text-gray-400 text-sm">
                        Belum ada transaksi
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
