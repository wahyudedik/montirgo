<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-dark">⭐ Reviews</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Rating Summary --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center gap-8">
                    <div class="text-center">
                        <p class="text-4xl font-bold text-dark">{{ $avgRating }}</p>
                        <div class="flex items-center gap-0.5 mt-1">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="w-4 h-4 {{ $i <= round($avgRating) ? 'text-yellow-400' : 'text-gray-200' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            @endfor
                        </div>
                        <p class="text-xs text-gray-500 mt-1">{{ $totalReviews }} review</p>
                    </div>

                    <div class="flex-1 space-y-1.5">
                        @for($star = 5; $star >= 1; $star--)
                            @php $count = $ratingDistribution[$star] ?? 0; @endphp
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-gray-500 w-3">{{ $star }}</span>
                                <div class="flex-1 bg-gray-100 rounded-full h-2">
                                    <div class="bg-yellow-400 h-2 rounded-full transition-all" style="width: {{ $totalReviews > 0 ? ($count / $totalReviews * 100) : 0 }}%"></div>
                                </div>
                                <span class="text-xs text-gray-500 w-6 text-right">{{ $count }}</span>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>

            {{-- Reviews List --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-dark">Semua Review</h3>
                </div>

                <div class="divide-y divide-gray-100">
                    @forelse($reviews as $review)
                        <div class="px-6 py-5">
                            <div class="flex items-start justify-between mb-2">
                                <div>
                                    <p class="text-sm font-semibold text-dark">{{ $review->user->name ?? 'Customer' }}</p>
                                    <div class="flex items-center gap-0.5 mt-0.5">
                                        @for($i = 1; $i <= 5; $i++)
                                            <svg class="w-3.5 h-3.5 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-200' }}" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        @endfor
                                    </div>
                                </div>
                                <span class="text-xs text-gray-400">{{ $review->created_at->diffForHumans() }}</span>
                            </div>

                            @if($review->comment)
                                <p class="text-sm text-gray-600 mb-3">{{ $review->comment }}</p>
                            @endif

                            @if($review->order)
                                <p class="text-xs text-gray-400 mb-3">Order #{{ $review->order->code ?? $review->order_id }}</p>
                            @endif

                            {{-- Partner Reply --}}
                            @if($review->partner_reply)
                                <div class="p-3 bg-blue-50 rounded-xl">
                                    <p class="text-xs font-semibold text-blue-700 mb-1">💬 Balasan Anda:</p>
                                    <p class="text-sm text-blue-800">{{ $review->partner_reply }}</p>
                                </div>
                            @else
                                {{-- Reply Form --}}
                                <div x-data="{ showReply: false }">
                                    <button @click="showReply = !showReply" class="text-xs text-primary hover:text-primary-600 font-medium">
                                        💬 Balas review
                                    </button>
                                    <form x-show="showReply" x-transition method="POST" action="{{ route('partner.reviews.reply', $review) }}" class="mt-3">
                                        @csrf
                                        <textarea name="partner_reply" rows="2" required maxlength="500"
                                            class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary resize-none"
                                            placeholder="Tulis balasan..."></textarea>
                                        <div class="flex justify-end gap-2 mt-2">
                                            <button type="button" @click="showReply = false" class="text-xs text-gray-500 hover:text-dark">Batal</button>
                                            <button type="submit" class="text-xs bg-primary hover:bg-primary-600 text-white px-4 py-1.5 rounded-lg font-medium">Kirim</button>
                                        </div>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="px-6 py-12 text-center">
                            <p class="text-gray-500">Belum ada review.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
