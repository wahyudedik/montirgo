<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.orders.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            </a>
            <h2 class="text-xl font-semibold text-gray-800">Order #{{ $order->id }}</h2>
            <span class="px-2 py-1 text-xs font-medium rounded-full
                {{ $order->status === 'completed' ? 'bg-green-100 text-green-700' : '' }}
                {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                {{ $order->status === 'cancelled' ? 'bg-red-100 text-red-700' : '' }}
                {{ in_array($order->status, ['dispatching','accepted','on_the_way','in_progress']) ? 'bg-blue-100 text-blue-700' : '' }}">
                {{ ucfirst(str_replace('_', ' ', $order->status)) }}
            </span>
        </div>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <!-- Order Map -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-dark mb-4 flex items-center gap-2"><svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg> Lokasi Order</h3>
                    @php
                        $mapMarkers = [
                            ['lat' => (float) $order->location_lat, 'lng' => (float) $order->location_lng, 'label' => 'Lokasi Customer', 'color' => '#3B82F6'],
                        ];
                        if ($order->partner && $order->partner->workshop_lat && $order->partner->workshop_lng) {
                            $mapMarkers[] = ['lat' => (float) $order->partner->workshop_lat, 'lng' => (float) $order->partner->workshop_lng, 'label' => $order->partner->workshop_name, 'color' => '#10B981'];
                        }
                    @endphp
                    <x-map
                        id="order-map"
                        :lat="(float) $order->location_lat"
                        :lng="(float) $order->location_lng"
                        :zoom="14"
                        :readOnly="true"
                        height="300px"
                        :markers="$mapMarkers"
                    />
                    @if($order->location_address)
                        <p class="text-sm text-gray-500 mt-3 flex items-center gap-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg> {{ $order->location_address }}</p>
                    @endif
                </div>

                <!-- Order Details -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-dark mb-4">Order Details</h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div><span class="text-gray-500">Service:</span> <span class="font-medium">{{ $order->service_type }}</span></div>
                        <div><span class="text-gray-500">Payment Method:</span> <span class="font-medium">{{ ucfirst($order->payment_method) }}</span></div>
                        <div class="col-span-2"><span class="text-gray-500">Problem:</span> <span class="font-medium">{{ $order->problem_description ?? '-' }}</span></div>
                        <div><span class="text-gray-500">Location:</span> <span class="font-medium">{{ $order->location_address ?? '-' }}</span></div>
                        <div><span class="text-gray-500">Escalation:</span> <span class="font-medium">Level {{ $order->dispatch_escalation }}</span></div>
                        @if($order->is_sos)
                            <div class="col-span-2 bg-red-50 border border-red-200 rounded-lg p-2">
                                <span class="text-red-600 text-sm font-semibold flex items-center gap-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg> SOS Emergency — {{ $order->sos_type }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Customer & Partner -->
                <div class="grid grid-cols-2 gap-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-lg font-semibold text-dark mb-3">Customer</h3>
                        <div class="text-sm space-y-1">
                            <p class="font-medium">{{ $order->user->name ?? '-' }}</p>
                            <p class="text-gray-500">{{ $order->user->email ?? '-' }}</p>
                            <p class="text-gray-500">{{ $order->user->phone ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-lg font-semibold text-dark mb-3">Partner</h3>
                        <div class="text-sm space-y-1">
                            <p class="font-medium">{{ $order->partner->workshop_name ?? '-' }}</p>
                            <p class="text-gray-500">{{ $order->partner->user->name ?? '-' }}</p>
                            <p class="text-gray-500">{{ $order->partner->workshop_address ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Vehicle -->
                @if($order->vehicle)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-lg font-semibold text-dark mb-3">Vehicle</h3>
                        <div class="text-sm">
                            <p class="font-medium">{{ $order->vehicle->brand }} {{ $order->vehicle->model }} ({{ $order->vehicle->year }})</p>
                            <p class="text-gray-500">{{ $order->vehicle->license_plate }} · {{ ucfirst($order->vehicle->type) }}</p>
                        </div>
                    </div>
                @endif

                <!-- Review -->
                @if($order->review)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-lg font-semibold text-dark mb-3">Customer Review</h3>
                        <div class="text-yellow-500 mb-2"><svg class="inline w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg> {{ $order->review->rating }}/5</div>
                        <p class="text-sm text-gray-600">{{ $order->review->comment }}</p>
                    </div>
                @endif
            </div>

            <!-- Payment Sidebar -->
            <div class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-dark mb-4">Payment</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between"><span class="text-gray-500">Callout Fee:</span><span>Rp {{ number_format($order->callout_fee, 0, ',', '.') }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Service Fee:</span><span>Rp {{ number_format($order->service_fee, 0, ',', '.') }}</span></div>
                        <hr>
                        <div class="flex justify-between font-semibold"><span>Total:</span><span>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span></div>
                        <hr>
                        <div class="flex justify-between text-green-600"><span>Partner Earning:</span><span>Rp {{ number_format($order->partner_earning, 0, ',', '.') }}</span></div>
                        <div class="flex justify-between text-primary"><span>Platform Commission:</span><span>Rp {{ number_format($order->platform_commission, 0, ',', '.') }}</span></div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-dark mb-3">Timeline</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between"><span class="text-gray-500">Created:</span><span>{{ $order->created_at->format('d M H:i') }}</span></div>
                        @if($order->dispatch_started_at)
                            <div class="flex justify-between"><span class="text-gray-500">Dispatch:</span><span>{{ $order->dispatch_started_at->format('d M H:i') }}</span></div>
                        @endif
                        @if($order->started_at)
                            <div class="flex justify-between"><span class="text-gray-500">Started:</span><span>{{ $order->started_at->format('d M H:i') }}</span></div>
                        @endif
                        @if($order->completed_at)
                            <div class="flex justify-between"><span class="text-gray-500">Completed:</span><span>{{ $order->completed_at->format('d M H:i') }}</span></div>
                        @endif
                        @if($order->cancelled_at)
                            <div class="flex justify-between text-red-600"><span>Cancelled:</span><span>{{ $order->cancelled_at->format('d M H:i') }}</span></div>
                            <div><span class="text-gray-500">Reason:</span> <span class="text-red-600">{{ $order->cancel_reason }}</span></div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
