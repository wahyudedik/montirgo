<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-dark">💎 Subscription</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Current Plan --}}
            @if($currentSubscription)
                <div class="bg-gradient-to-br from-primary to-primary-600 rounded-2xl p-6 text-white shadow-lg shadow-primary/25">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-white/80">Plan Aktif</p>
                            <p class="text-2xl font-bold mt-1">{{ ucfirst($currentSubscription->plan) }}</p>
                            <p class="text-sm text-white/60 mt-1">
                                Berlaku sampai {{ $currentSubscription->expires_at->format('d M Y') }}
                            </p>
                        </div>
                        <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Plans --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($plans as $key => $plan)
                    <div class="bg-white rounded-2xl shadow-sm border-2 p-6 transition hover:shadow-md
                        {{ $currentSubscription && $currentSubscription->plan === $key ? 'border-primary' : 'border-gray-100' }}">

                        @if($currentSubscription && $currentSubscription->plan === $key)
                            <span class="inline-block px-2 py-0.5 text-xs font-medium bg-primary/10 text-primary rounded-full mb-3">Aktif</span>
                        @endif

                        <h3 class="text-lg font-bold text-dark">{{ $plan['name'] }}</h3>

                        <div class="mt-3 mb-5">
                            @if($plan['price'] > 0)
                                <span class="text-3xl font-bold text-dark">Rp {{ number_format($plan['price'], 0, ',', '.') }}</span>
                                <span class="text-sm text-gray-500">{{ $plan['period'] }}</span>
                            @else
                                <span class="text-3xl font-bold text-dark">Gratis</span>
                            @endif
                        </div>

                        <ul class="space-y-2 mb-6">
                            @foreach($plan['features'] as $feature)
                                <li class="flex items-start gap-2 text-sm text-gray-600">
                                    <svg class="w-4 h-4 text-green-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    {{ $feature }}
                                </li>
                            @endforeach
                        </ul>

                        @if($key === 'basic')
                            <button disabled class="w-full py-2.5 px-4 rounded-xl text-sm font-medium bg-gray-100 text-gray-400 cursor-not-allowed">
                                Plan Default
                            </button>
                        @elseif($currentSubscription && $currentSubscription->plan === $key)
                            <form method="POST" action="{{ route('partner.subscription.cancel') }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full py-2.5 px-4 rounded-xl text-sm font-medium border border-red-200 text-red-600 hover:bg-red-50 transition">
                                    Batalkan
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('partner.subscription.subscribe') }}">
                                @csrf
                                <input type="hidden" name="plan" value="{{ $key }}">
                                <button type="submit" class="w-full py-2.5 px-4 rounded-xl text-sm font-medium
                                    {{ $plan['color'] === 'primary' ? 'bg-primary hover:bg-primary-600 text-white shadow-lg shadow-primary/25' : '' }}
                                    {{ $plan['color'] === 'amber' ? 'bg-amber-500 hover:bg-amber-600 text-white shadow-lg shadow-amber-500/25' : '' }}
                                    {{ $plan['color'] === 'gray' ? 'bg-gray-100 text-gray-500' : '' }}
                                    transition">
                                    Upgrade ke {{ $plan['name'] }}
                                </button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
