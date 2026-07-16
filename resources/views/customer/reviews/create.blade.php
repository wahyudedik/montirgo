<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('customer.orders.show', $order) }}" class="text-gray-400 hover:text-dark transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h2 class="text-xl font-bold text-dark">⭐ Tulis Review</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-lg mx-auto sm:px-6 lg:px-8">

            {{-- Order Info --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 bg-primary-50 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-dark">Order #{{ $order->code ?? $order->id }}</p>
                        <p class="text-xs text-gray-500">{{ $order->service_type }} · {{ $order->created_at->format('d M Y') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-600">Partner:</span>
                    <span class="text-sm font-medium text-dark">{{ $order->partner->workshop_name ?? '-' }}</span>
                </div>
            </div>

            {{-- Review Form --}}
            <form method="POST" action="{{ route('customer.reviews.store', $order) }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6" x-data="{ rating: 0, hover: 0 }">
                @csrf

                {{-- Star Rating --}}
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-dark mb-3">Rating</label>
                    <div class="flex items-center gap-1" @click.outside="if(rating === 0) hover = 0">
                        @for($i = 1; $i <= 5; $i++)
                            <button type="button"
                                @click="rating = {{ $i }}"
                                @mouseenter="hover = {{ $i }}"
                                @mouseleave="hover = 0"
                                class="focus:outline-none transition transform hover:scale-110">
                                <svg class="w-10 h-10 transition" :class="(hover || rating) >= {{ $i }} ? 'text-yellow-400' : 'text-gray-200'" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </button>
                        @endfor
                        <input type="hidden" name="rating" :value="rating" x-model="rating">
                        <span class="ml-3 text-sm text-gray-500" x-text="rating > 0 ? ['', 'Sangat Buruk', 'Buruk', 'Cukup', 'Bagus', 'Sangat Bagus'][rating] : 'Pilih rating'"></span>
                    </div>
                    @error('rating')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Comment --}}
                <div class="mb-6">
                    <label for="comment" class="block text-sm font-semibold text-dark mb-2">Komentar (opsional)</label>
                    <textarea id="comment" name="comment" rows="4" maxlength="500"
                        class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm text-dark focus:border-primary focus:ring-1 focus:ring-primary resize-none"
                        placeholder="Ceritakan pengalaman Anda..."></textarea>
                    @error('comment')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Submit --}}
                <button type="submit" class="w-full bg-primary hover:bg-primary-600 text-white font-semibold py-3 px-6 rounded-xl transition shadow-lg shadow-primary/25 disabled:opacity-50"
                    :disabled="rating === 0">
                    Kirim Review
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
