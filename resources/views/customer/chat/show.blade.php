<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <a href="{{ route('customer.chat.index') }}" class="text-gray-400 hover:text-gray-600 mr-3">
                    ← Kembali
                </a>
                <div>
                    <h2 class="font-semibold text-lg text-gray-800 leading-tight">
                        💬 {{ $chat->partner->workshop_name ?? 'Chat' }}
                    </h2>
                    <p class="text-xs text-gray-500">Order #{{ $order->code }}</p>
                </div>
            </div>
            <div id="typing-indicator" class="text-xs text-gray-400 hidden">
                <span class="animate-pulse">sedang mengetik...</span>
            </div>
        </div>
    </x-slot>

    <div
        class="max-w-4xl mx-auto py-6 px-4 sm:px-6 lg:px-8"
        x-data="chatApp({{ $chat->id }}, {{ $order->id }}, {{ Auth::id() }})"
    >
        <!-- Chat Messages -->
        <div
            id="chat-messages"
            class="bg-white shadow-sm rounded-lg p-4 mb-4 h-96 overflow-y-auto flex flex-col-reverse"
        >
            <template x-for="msg in messages" :key="msg.id">
                <div
                    class="flex mb-3"
                    :class="msg.sender_id === currentUserId ? 'justify-end' : 'justify-start'"
                >
                    <div
                        class="max-w-xs lg:max-w-md px-4 py-2 rounded-2xl"
                        :class="msg.sender_id === currentUserId
                            ? 'bg-primary-500 text-white rounded-br-md'
                            : 'bg-gray-100 text-gray-800 rounded-bl-md'"
                    >
                        <p class="text-sm" x-text="msg.message"></p>
                        <p
                            class="text-xs mt-1"
                            :class="msg.sender_id === currentUserId ? 'text-primary-100' : 'text-gray-400'"
                            x-text="formatTime(msg.created_at)"
                        ></p>
                    </div>
                </div>
            </template>
        </div>

        <!-- Message Input -->
        @if ($order->status !== 'cancelled' && $order->status !== 'expired' && $order->status !== 'completed')
            <div class="bg-white shadow-sm rounded-lg p-4">
                <form @submit.prevent="sendMessage" class="flex items-end gap-3">
                    <div class="flex-1">
                        <textarea
                            x-model="newMessage"
                            @keydown.enter.prevent="sendMessage"
                            placeholder="Ketik pesan..."
                            rows="1"
                            class="w-full border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 resize-none"
                            x-ref="messageInput"
                        ></textarea>
                    </div>
                    <button
                        type="submit"
                        :disabled="!newMessage.trim()"
                        class="px-4 py-2 bg-primary-500 text-white rounded-lg hover:bg-primary-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                    </button>
                </form>
            </div>
        @else
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 text-center">
                <p class="text-sm text-gray-500">Chat ditutup — order sudah {{ $order->status_label }}</p>
            </div>
        @endif
    </div>

    <script>
        function chatApp(chatId, orderId, currentUserId) {
            return {
                messages: [],
                newMessage: '',
                currentUserId: currentUserId,
                lastMessageId: 0,
                pollInterval: null,

                init() {
                    // Load initial messages
                    this.loadMessages();

                    // Real-time via Echo (WebSocket)
                    if (window.Echo) {
                        window.Echo.private(`chat.${chatId}`)
                            .listen('.chat.message.new', (e) => {
                                this.addMessage({
                                    id: e.id,
                                    sender_id: e.sender_id,
                                    message: e.message,
                                    created_at: e.created_at,
                                });
                            })
                            .listen('.chat.typing', (e) => {
                                if (e.user_id !== currentUserId) {
                                    const indicator = document.getElementById('typing-indicator');
                                    indicator.classList.remove('hidden');
                                    setTimeout(() => indicator.classList.add('hidden'), 3000);
                                }
                            });
                    }

                    // Polling fallback (every 3 seconds)
                    this.pollInterval = setInterval(() => this.pollMessages(), 3000);
                },

                async loadMessages() {
                    try {
                        const res = await fetch(`/customer/orders/${orderId}/chat/poll?last_id=0`);
                        const data = await res.json();
                        this.messages = data.messages || [];
                        if (this.messages.length > 0) {
                            this.lastMessageId = Math.max(...this.messages.map(m => m.id));
                        }
                        this.$nextTick(() => this.scrollToBottom());
                    } catch (e) {
                        console.error('Failed to load messages:', e);
                    }
                },

                async pollMessages() {
                    try {
                        const res = await fetch(`/customer/orders/${orderId}/chat/poll?last_id=${this.lastMessageId}`);
                        const data = await res.json();
                        if (data.messages && data.messages.length > 0) {
                            data.messages.forEach(msg => this.addMessage(msg));
                        }
                    } catch (e) {
                        // Silent fail for polling
                    }
                },

                addMessage(msg) {
                    if (!this.messages.find(m => m.id === msg.id)) {
                        this.messages.push(msg);
                        this.lastMessageId = Math.max(this.lastMessageId, msg.id);
                        this.$nextTick(() => this.scrollToBottom());
                    }
                },

                async sendMessage() {
                    const text = this.newMessage.trim();
                    if (!text) return;

                    this.newMessage = '';

                    try {
                        const res = await fetch(`/customer/orders/${orderId}/chat/send`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ message: text }),
                        });
                        const data = await res.json();
                        if (data.success) {
                            this.addMessage(data.message);
                        }
                    } catch (e) {
                        console.error('Failed to send message:', e);
                        this.newMessage = text;
                    }
                },

                formatTime(dateStr) {
                    if (!dateStr) return '';
                    const d = new Date(dateStr);
                    return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                },

                scrollToBottom() {
                    const container = document.getElementById('chat-messages');
                    if (container) container.scrollTop = 0;
                },

                destroy() {
                    if (this.pollInterval) clearInterval(this.pollInterval);
                },
            };
        }
    </script>
</x-app-layout>
