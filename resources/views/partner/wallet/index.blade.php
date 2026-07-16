<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-dark">Dompet Saya</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 text-sm flex items-center gap-2">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm flex items-center gap-2">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ session('error') }}
                </div>
            @endif

            {{-- Balance Card --}}
            <div class="bg-gradient-to-br from-primary to-primary-600 rounded-2xl p-6 text-white shadow-lg">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        </div>
                        <span class="text-sm text-white/80">Saldo Tersedia</span>
                    </div>
                </div>
                <p class="text-3xl font-bold mb-1">Rp {{ number_format((float) $wallet->balance, 0, ',', '.') }}</p>
                <p class="text-sm text-white/60">Klik tombol di bawah untuk menarik dana</p>

                <div class="mt-4 flex gap-3">
                    <a href="{{ route('partner.wallet.withdraw') }}" class="flex-1 bg-white text-primary font-semibold text-center py-2.5 rounded-xl text-sm hover:bg-white/90 transition-colors">
                        Tarik Dana
                    </a>
                    <a href="{{ route('partner.wallet.history') }}" class="flex-1 bg-white/20 text-white font-semibold text-center py-2.5 rounded-xl text-sm hover:bg-white/30 transition-colors">
                        Riwayat
                    </a>
                </div>
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-3 gap-4">
                <div class="bg-white rounded-xl border border-gray-100 p-4 text-center">
                    <p class="text-2xl font-bold text-dark">Rp {{ number_format((float) $wallet->total_income, 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-500 mt-1">Total Pendapatan</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 p-4 text-center">
                    <p class="text-2xl font-bold text-dark">Rp {{ number_format((float) $wallet->frozen, 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-500 mt-1">Dibekukan</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 p-4 text-center">
                    <p class="text-2xl font-bold text-dark">Rp {{ number_format((float) $wallet->total_withdrawn, 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-500 mt-1">Total Ditarik</p>
                </div>
            </div>

            {{-- Recent Transactions --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-dark">Transaksi Terakhir</h3>
                    <a href="{{ route('partner.wallet.history') }}" class="text-sm text-primary hover:underline">Lihat Semua</a>
                </div>

                @forelse($transactions->take(5) as $tx)
                    <div class="flex items-center justify-between py-3 {{ !$loop->last ? 'border-b border-gray-50' : '' }}">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center {{ $tx->type === 'income' ? 'bg-green-50' : 'bg-red-50' }}">
                                @if($tx->type === 'income')
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/></svg>
                                @elseif($tx->type === 'withdrawal')
                                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                @else
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                @endif
                            </div>
                            <div>
                                <p class="text-sm font-medium text-dark">{{ $tx->description ?? ucfirst($tx->type) }}</p>
                                <p class="text-xs text-gray-400">{{ $tx->created_at->format('d M Y, H:i') }}</p>
                            </div>
                        </div>
                        <span class="text-sm font-semibold {{ $tx->type === 'income' ? 'text-green-600' : 'text-red-600' }}">
                            {{ $tx->type === 'income' ? '+' : '-' }} Rp {{ number_format((float) $tx->amount, 0, ',', '.') }}
                        </span>
                    </div>
                @empty
                    <div class="py-8 text-center text-gray-400 text-sm">
                        Belum ada transaksi
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
