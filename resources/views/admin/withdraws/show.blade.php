<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.withdraws.index') }}" class="w-9 h-9 bg-gray-100 hover:bg-gray-200 rounded-xl flex items-center justify-center transition-colors">
                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Detail Withdraw #{{ $withdrawRequest->id }}</h2>
                <p class="text-sm text-gray-500">{{ $withdrawRequest->created_at->format('d M Y, H:i') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

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

            {{-- Status --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Status</p>
                        <span class="inline-flex px-3 py-1 rounded-full text-sm font-semibold {{ match($withdrawRequest->status) { 'pending' => 'bg-yellow-100 text-yellow-700', 'approved' => 'bg-green-100 text-green-700', 'rejected' => 'bg-red-100 text-red-700', 'processed' => 'bg-blue-100 text-blue-700', default => 'bg-gray-100 text-gray-700' } }}">
                            {{ ucfirst($withdrawRequest->status) }}
                        </span>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500 mb-1">Jumlah</p>
                        <p class="text-2xl font-bold text-dark">Rp {{ number_format((float) $withdrawRequest->amount, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            {{-- Partner Info --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-bold text-dark mb-4">Informasi Partner</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-gray-500">Nama</p>
                        <p class="text-sm font-medium text-dark">{{ $withdrawRequest->user->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Email</p>
                        <p class="text-sm font-medium text-dark">{{ $withdrawRequest->user->email ?? '-' }}</p>
                    </div>
                </div>
            </div>

            {{-- Bank Info --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-bold text-dark mb-4">Informasi Rekening</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-gray-500">Bank</p>
                        <p class="text-sm font-medium text-dark">{{ $withdrawRequest->bank_name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Nomor Rekening</p>
                        <p class="text-sm font-medium text-dark font-mono">{{ $withdrawRequest->bank_account_number }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Nama Pemilik</p>
                        <p class="text-sm font-medium text-dark">{{ $withdrawRequest->bank_account_name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Tanggal Pengajuan</p>
                        <p class="text-sm font-medium text-dark">{{ $withdrawRequest->created_at->format('d M Y, H:i') }}</p>
                    </div>
                </div>
            </div>

            @if($withdrawRequest->rejection_reason)
                <div class="bg-red-50 rounded-2xl border border-red-200 p-6">
                    <h3 class="font-bold text-red-700 mb-2">Alasan Penolakan</h3>
                    <p class="text-sm text-red-600">{{ $withdrawRequest->rejection_reason }}</p>
                </div>
            @endif

            @if($withdrawRequest->admin_note)
                <div class="bg-blue-50 rounded-2xl border border-blue-200 p-6">
                    <h3 class="font-bold text-blue-700 mb-2">Catatan Admin</h3>
                    <p class="text-sm text-blue-600">{{ $withdrawRequest->admin_note }}</p>
                </div>
            @endif

            {{-- Actions --}}
            @if($withdrawRequest->status === 'pending')
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6" x-data="{ showReject: false }">
                    <h3 class="font-bold text-dark mb-4">Aksi</h3>

                    <div class="flex gap-3">
                        <form action="{{ route('admin.withdraws.approve', $withdrawRequest) }}" method="POST" class="flex-1"
                            onsubmit="return confirm('Yakin ingin menyetujui withdraw ini?');">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 rounded-xl text-sm transition-colors">
                                Setujui
                            </button>
                        </form>

                        <button type="button" @click="showReject = !showReject"
                            class="flex-1 bg-red-50 hover:bg-red-100 text-red-600 font-semibold py-3 rounded-xl text-sm transition-colors">
                            Tolak
                        </button>
                    </div>

                    <div x-show="showReject" x-transition class="mt-4">
                        <form action="{{ route('admin.withdraws.reject', $withdrawRequest) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Alasan Penolakan</label>
                            <textarea name="rejection_reason" rows="3" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:ring-primary focus:border-primary"
                                placeholder="Masukkan alasan penolakan..."></textarea>
                            @error('rejection_reason') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            <button type="submit" class="mt-3 w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-3 rounded-xl text-sm transition-colors"
                                onsubmit="return confirm('Yakin ingin menolak withdraw ini?');">
                                Konfirmasi Penolakan
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
