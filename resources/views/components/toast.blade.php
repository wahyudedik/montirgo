{{-- ============================================================
     Toast Notification Component — Alpine.js + Tailwind CSS
     Supports: success, error, warning, info
     Usage: @include('components.toast')
     Manual trigger: Alpine.store('toast').show('success', 'Pesan')
     ============================================================ --}}

<div
    x-data="toastManager()"
    x-on:toast-show.window="show($event.detail.type, $event.detail.message)"
    class="fixed top-4 right-4 z-[9999] flex flex-col gap-3 pointer-events-none"
    style="max-width: 420px;"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-show="toast.visible"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-8 scale-95"
            x-transition:enter-end="opacity-100 translate-x-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-x-0 scale-100"
            x-transition:leave-end="opacity-0 translate-x-8 scale-95"
            :class="{
                'bg-emerald-50 border-emerald-200': toast.type === 'success',
                'bg-red-50 border-red-200': toast.type === 'error',
                'bg-amber-50 border-amber-200': toast.type === 'warning',
                'bg-blue-50 border-blue-200': toast.type === 'info',
            }"
            class="pointer-events-auto relative flex items-start gap-3 p-4 rounded-xl border shadow-lg backdrop-blur-sm overflow-hidden"
        >
            {{-- Success Icon --}}
            <svg x-show="toast.type === 'success'" class="w-5 h-5 shrink-0 mt-0.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>

            {{-- Error Icon --}}
            <svg x-show="toast.type === 'error'" class="w-5 h-5 shrink-0 mt-0.5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>

            {{-- Warning Icon --}}
            <svg x-show="toast.type === 'warning'" class="w-5 h-5 shrink-0 mt-0.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>

            {{-- Info Icon --}}
            <svg x-show="toast.type === 'info'" class="w-5 h-5 shrink-0 mt-0.5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>

            {{-- Message --}}
            <p
                class="flex-1 text-sm font-medium"
                :class="{
                    'text-emerald-800': toast.type === 'success',
                    'text-red-800': toast.type === 'error',
                    'text-amber-800': toast.type === 'warning',
                    'text-blue-800': toast.type === 'info',
                }"
                x-text="toast.message"
            ></p>

            {{-- Close Button --}}
            <button
                @click="dismiss(toast.id)"
                :class="{
                    'text-emerald-400 hover:text-emerald-600': toast.type === 'success',
                    'text-red-400 hover:text-red-600': toast.type === 'error',
                    'text-amber-400 hover:text-amber-600': toast.type === 'warning',
                    'text-blue-400 hover:text-blue-600': toast.type === 'info',
                }"
                class="shrink-0 ml-1 p-0.5 rounded-lg hover:bg-black/5 transition-colors"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            {{-- Auto-dismiss Progress Bar --}}
            <div class="absolute bottom-0 left-0 right-0 h-1 rounded-b-xl overflow-hidden">
                <div
                    class="h-full animate-shrink"
                    :class="{
                        'bg-emerald-400': toast.type === 'success',
                        'bg-red-400': toast.type === 'error',
                        'bg-amber-400': toast.type === 'warning',
                        'bg-blue-400': toast.type === 'info',
                    }"
                ></div>
            </div>
        </div>
    </template>
</div>

<style>
    @keyframes shrink {
        from { width: 100%; }
        to { width: 0%; }
    }
    .animate-shrink {
        animation: shrink 5s linear forwards;
    }
</style>

<script>
    function toastManager() {
        return {
            toasts: [],
            init() {
                // Read Laravel session flash messages
                @if(session('success'))
                    this.show('success', @json(session('success')));
                @endif
                @if(session('error'))
                    this.show('error', @json(session('error')));
                @endif
                @if(session('warning'))
                    this.show('warning', @json(session('warning')));
                @endif
                @if(session('info'))
                    this.show('info', @json(session('info')));
                @endif

                // Register global store for AJAX triggers
                Alpine.store('toast', {
                    show: (type, message) => {
                        this.show(type, message);
                    },
                });
            },
            show(type, message) {
                const id = Date.now() + Math.random();
                this.toasts.push({ id, type, message, visible: true });
                setTimeout(() => this.dismiss(id), 5000);
            },
            dismiss(id) {
                const toast = this.toasts.find(t => t.id === id);
                if (toast) {
                    toast.visible = false;
                    setTimeout(() => {
                        this.toasts = this.toasts.filter(t => t.id !== id);
                    }, 300);
                }
            },
        };
    }
</script>
