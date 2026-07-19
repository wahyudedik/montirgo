<nav x-data="{ open: false, notifOpen: false }" class="bg-white/80 backdrop-blur-md border-b border-gray-100 sticky top-0 z-40">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
                        <img src="{{ asset('logo-rm.png') }}" alt="MontirGo" class="h-8 w-auto">
                        <span class="text-lg font-bold text-dark hidden sm:inline">Montir<span class="text-primary">Go</span></span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-1 sm:-my-px sm:ms-8 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    @if(Auth::user()->isCustomer())
                        <x-nav-link :href="route('customer.orders.index')" :active="request()->routeIs('customer.orders.*')">
                            {{ __('Orders') }}
                        </x-nav-link>
                        <x-nav-link :href="route('customer.chat.index')" :active="request()->routeIs('customer.chat.*')">
                            {{ __('Chat') }}
                        </x-nav-link>
                        <x-nav-link :href="route('customer.reviews.index')" :active="request()->routeIs('customer.reviews.*')">
                            {{ __('Reviews') }}
                        </x-nav-link>
                        <x-nav-link :href="route('customer.history.index')" :active="request()->routeIs('customer.history.*')">
                            {{ __('History') }}
                        </x-nav-link>
                        <x-nav-link :href="route('customer.sos.index')" :active="request()->routeIs('customer.sos.*')">
                            <span class="text-red-500 font-semibold">{{ __('SOS') }}</span>
                        </x-nav-link>
                    @endif

                    @if(Auth::user()->isPartner())
                        <x-nav-link :href="route('partner.orders.index')" :active="request()->routeIs('partner.orders.*')">
                            {{ __('Orders') }}
                        </x-nav-link>
                        <x-nav-link :href="route('partner.chat.index')" :active="request()->routeIs('partner.chat.*')">
                            {{ __('Chat') }}
                        </x-nav-link>
                        <x-nav-link :href="route('partner.reviews.index')" :active="request()->routeIs('partner.reviews.*')">
                            {{ __('Reviews') }}
                        </x-nav-link>
                        <x-nav-link :href="route('partner.spareparts.index')" :active="request()->routeIs('partner.spareparts.*')">
                            {{ __('Sparepart') }}
                        </x-nav-link>
                        <x-nav-link :href="route('partner.service-cost.index')" :active="request()->routeIs('partner.service-cost.*')">
                            {{ __('Biaya Servis') }}
                        </x-nav-link>
                    @endif

                    @if(Auth::user()->isAdmin())
                        <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                            {{ __('Users') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.partners.index')" :active="request()->routeIs('admin.partners.*')">
                            {{ __('Partners') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.orders.index')" :active="request()->routeIs('admin.orders.*')">
                            {{ __('Orders') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.advertisements.index')" :active="request()->routeIs('admin.advertisements.*')">
                            {{ __('Iklan') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 gap-3">
                <!-- Role Badge -->
                <span class="px-2.5 py-1 text-xs font-semibold rounded-full
                    {{ Auth::user()->isAdmin() ? 'bg-red-50 text-red-600 border border-red-100' : '' }}
                    {{ Auth::user()->isPartner() ? 'bg-blue-50 text-blue-600 border border-blue-100' : '' }}
                    {{ Auth::user()->isCustomer() ? 'bg-green-50 text-green-600 border border-green-100' : '' }}">
                    {{ ucfirst(Auth::user()->role) }}
                </span>

                <!-- Notification Bell -->
                <div x-data="notificationDropdown()" x-init="init()" class="relative">
                    <button @click="notifOpen = !notifOpen; if(notifOpen) fetchNotifications()" class="relative p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-xl transition-all duration-200 focus:outline-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <span x-show="unreadCount > 0" x-text="unreadCount" x-transition class="absolute -top-0.5 -right-0.5 inline-flex items-center justify-center w-4 h-4 text-xs font-bold text-white bg-red-500 rounded-full"></span>
                    </button>

                    <!-- Notification Dropdown Panel -->
                    <div x-show="notifOpen" @click.away="notifOpen = false" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg border border-gray-100 z-50 overflow-hidden" style="display: none;">
                        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-dark">Notifikasi</h3>
                            <button @click="markAllRead()" x-show="unreadCount > 0" class="text-xs text-primary hover:text-primary-700 font-medium">Tandai semua dibaca</button>
                        </div>
                        <div class="max-h-80 overflow-y-auto">
                            <template x-if="notifications.length === 0">
                                <div class="px-4 py-8 text-center">
                                    <svg class="w-8 h-8 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                    <p class="text-xs text-gray-400">Belum ada notifikasi</p>
                                </div>
                            </template>
                            <template x-for="notif in notifications" :key="notif.id">
                                <div @click="notifOpen = false" class="px-4 py-3 border-b border-gray-50 hover:bg-gray-50 cursor-pointer transition-colors" :class="{ 'bg-primary-50/30': !notif.read_at }">
                                    <div class="flex items-start gap-3">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 mt-0.5"
                                            :class="{
                                                'bg-blue-100': notif.type === 'order_status',
                                                'bg-green-100': notif.type === 'payment' || notif.type === 'wallet',
                                                'bg-purple-100': notif.type === 'chat_message',
                                                'bg-orange-100': notif.type === 'new_order',
                                                'bg-red-100': notif.type === 'sos',
                                                'bg-gray-100': !['order_status','payment','wallet','chat_message','new_order','sos'].includes(notif.type)
                                            }">
                                            <svg class="w-4 h-4" :class="{
                                                'text-blue-600': notif.type === 'order_status',
                                                'text-green-600': notif.type === 'payment' || notif.type === 'wallet',
                                                'text-purple-600': notif.type === 'chat_message',
                                                'text-orange-600': notif.type === 'new_order',
                                                'text-red-600': notif.type === 'sos',
                                                'text-gray-600': !['order_status','payment','wallet','chat_message','new_order','sos'].includes(notif.type)
                                            }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-semibold text-dark" x-text="notif.title"></p>
                                            <p class="text-xs text-gray-500 mt-0.5 line-clamp-2" x-text="notif.body"></p>
                                            <p class="text-[10px] text-gray-400 mt-1" x-text="timeAgo(notif.created_at)"></p>
                                        </div>
                                        <div x-show="!notif.read_at" class="w-2 h-2 bg-primary rounded-full shrink-0 mt-1.5"></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <div class="px-4 py-2 border-t border-gray-100 text-center">
                            <span class="text-xs text-gray-400">Real-time via WebSocket</span>
                        </div>
                    </div>
                </div>

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-2 px-3 py-2 border border-gray-200 text-sm leading-4 font-medium rounded-xl text-gray-600 bg-white hover:bg-gray-50 hover:border-gray-300 focus:outline-none transition-all duration-200">
                            <div class="w-7 h-7 bg-primary-50 rounded-full flex items-center justify-center">
                                <span class="text-xs font-bold text-primary">{{ substr(Auth::user()->name, 0, 1) }}</span>
                            </div>
                            <div class="hidden md:inline">{{ Auth::user()->name }}</div>

                            <div class="ms-0.5">
                                <svg class="fill-current h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-4 py-3 border-b border-gray-100">
                            <p class="text-sm font-semibold text-dark">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-400">{{ Auth::user()->email }}</p>
                        </div>
                        <x-dropdown-link :href="route('profile.edit')">
                            <svg class="w-4 h-4 me-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                <svg class="w-4 h-4 me-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-xl text-gray-400 hover:text-gray-600 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-600 transition-all duration-200">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t border-gray-100 bg-white">
        <div class="pt-2 pb-3 space-y-1 px-4">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            @if(Auth::user()->isCustomer())
                <x-responsive-nav-link :href="route('customer.orders.index')" :active="request()->routeIs('customer.orders.*')">
                    {{ __('Orders') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('customer.chat.index')" :active="request()->routeIs('customer.chat.*')">
                    {{ __('Chat') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('customer.reviews.index')" :active="request()->routeIs('customer.reviews.*')">
                    {{ __('Reviews') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('customer.history.index')" :active="request()->routeIs('customer.history.*')">
                    {{ __('History') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('customer.sos.index')" :active="request()->routeIs('customer.sos.*')">
                    <span class="text-red-500 font-semibold">{{ __('SOS') }}</span>
                </x-responsive-nav-link>
            @endif

            @if(Auth::user()->isPartner())
                <x-responsive-nav-link :href="route('partner.orders.index')" :active="request()->routeIs('partner.orders.*')">
                    {{ __('Orders') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('partner.chat.index')" :active="request()->routeIs('partner.chat.*')">
                    {{ __('Chat') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('partner.reviews.index')" :active="request()->routeIs('partner.reviews.*')">
                    {{ __('Reviews') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('partner.spareparts.index')" :active="request()->routeIs('partner.spareparts.*')">
                    {{ __('Sparepart') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('partner.service-cost.index')" :active="request()->routeIs('partner.service-cost.*')">
                    {{ __('Biaya Servis') }}
                </x-responsive-nav-link>
            @endif

            @if(Auth::user()->isAdmin())
                <x-responsive-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                    {{ __('Users') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.partners.index')" :active="request()->routeIs('admin.partners.*')">
                    {{ __('Partners') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.orders.index')" :active="request()->routeIs('admin.orders.*')">
                    {{ __('Orders') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.advertisements.index')" :active="request()->routeIs('admin.advertisements.*')">
                    {{ __('Iklan') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-100 px-4">
            <div class="flex items-center gap-3 py-2">
                <div class="w-10 h-10 bg-primary-50 rounded-full flex items-center justify-center">
                    <span class="text-sm font-bold text-primary">{{ substr(Auth::user()->name, 0, 1) }}</span>
                </div>
                <div>
                    <div class="font-medium text-sm text-dark">{{ Auth::user()->name }}</div>
                    <div class="text-xs text-gray-400">{{ Auth::user()->email }}</div>
                </div>
            </div>

            <div class="mt-2 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>

<script>
function notificationDropdown() {
    return {
        notifications: [],
        unreadCount: 0,
        notifOpen: false,
        echoChannel: null,

        init() {
            this.fetchNotifications();
            this.setupEcho();
        },

        async fetchNotifications() {
            try {
                const response = await fetch('/notifications', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                });
                if (response.ok) {
                    const data = await response.json();
                    this.notifications = (data.data || data.notifications || []).slice(0, 10);
                    this.unreadCount = data.unread_count || this.notifications.filter(n => !n.read_at).length;
                }
            } catch (e) {
                console.warn('Failed to fetch notifications:', e);
            }
        },

        setupEcho() {
            const userId = {{ Auth::id() }};
            if (window.Echo) {
                this.echoChannel = window.Echo.private(`App.Models.User.${userId}`)
                    .listen('.notification.new', (e) => {
                        this.notifications.unshift({
                            id: Date.now(),
                            title: e.title || 'Notifikasi Baru',
                            body: e.body || '',
                            type: e.type || 'general',
                            data: e.data || {},
                            read_at: null,
                            created_at: new Date().toISOString(),
                        });
                        this.unreadCount++;
                        if (this.notifications.length > 10) {
                            this.notifications.pop();
                        }
                    });
            }
        },

        async markAllRead() {
            try {
                const response = await fetch('/notifications/read-all', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                });
                if (response.ok) {
                    this.notifications = this.notifications.map(n => ({ ...n, read_at: new Date().toISOString() }));
                    this.unreadCount = 0;
                }
            } catch (e) {
                console.warn('Failed to mark all read:', e);
            }
        },

        timeAgo(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const seconds = Math.floor((now - date) / 1000);
            if (seconds < 60) return 'Baru saja';
            const minutes = Math.floor(seconds / 60);
            if (minutes < 60) return `${minutes}m lalu`;
            const hours = Math.floor(minutes / 60);
            if (hours < 24) return `${hours}j lalu`;
            const days = Math.floor(hours / 24);
            return `${days}h lalu`;
        },
    };
}
</script>
