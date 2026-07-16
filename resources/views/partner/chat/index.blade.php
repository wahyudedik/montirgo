<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            💬 Chat
        </h2>
    </x-slot>

    <div class="max-w-4xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        @if ($chats->isEmpty())
            <div class="text-center py-12">
                <div class="text-6xl mb-4">💬</div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada chat</h3>
                <p class="text-gray-500">Chat akan muncul setelah kamu menerima order.</p>
            </div>
        @else
            <div class="bg-white shadow-sm rounded-lg divide-y">
                @foreach ($chats as $chat)
                    <a
                        href="{{ route('partner.chat.show', $chat->order_id) }}"
                        class="flex items-center p-4 hover:bg-gray-50 transition-colors"
                    >
                        <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                            <span class="text-lg">👤</span>
                        </div>
                        <div class="ml-4 flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-semibold text-gray-900 truncate">
                                    {{ $chat->user->name ?? 'Customer' }}
                                </p>
                                <span class="text-xs text-gray-400">
                                    {{ $chat->last_message_at?->diffForHumans() ?? '' }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-500 truncate mt-1">
                                Order #{{ $chat->order->code ?? '-' }}
                            </p>
                        </div>
                        @if ($chat->is_active)
                            <span class="ml-2 w-3 h-3 bg-green-400 rounded-full flex-shrink-0"></span>
                        @endif
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
