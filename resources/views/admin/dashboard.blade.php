<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Admin Dashboard</h2>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8">
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <!-- Total Users -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Users</p>
                        <p class="text-2xl font-bold text-dark">{{ number_format($stats['total_users']) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-primary-50 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Partners -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Partners</p>
                        <p class="text-2xl font-bold text-dark">{{ number_format($stats['total_partners']) }}</p>
                        <p class="text-xs text-orange-600 mt-1">{{ $stats['pending_partners'] }} pending</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Orders -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Orders</p>
                        <p class="text-2xl font-bold text-dark">{{ number_format($stats['total_orders']) }}</p>
                        <p class="text-xs text-green-600 mt-1">{{ $stats['active_orders'] }} active</p>
                    </div>
                    <div class="w-12 h-12 bg-green-50 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Revenue -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Platform Revenue</p>
                        <p class="text-2xl font-bold text-dark">Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}</p>
                        <p class="text-xs text-gray-500 mt-1">Today: Rp {{ number_format($stats['today_revenue'], 0, ',', '.') }}</p>
                    </div>
                    <div class="w-12 h-12 bg-primary-50 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-8">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-dark">Recent Orders</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-5 py-3 text-left font-medium">ID</th>
                            <th class="px-5 py-3 text-left font-medium">Customer</th>
                            <th class="px-5 py-3 text-left font-medium">Partner</th>
                            <th class="px-5 py-3 text-left font-medium">Service</th>
                            <th class="px-5 py-3 text-left font-medium">Amount</th>
                            <th class="px-5 py-3 text-left font-medium">Status</th>
                            <th class="px-5 py-3 text-left font-medium">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($recentOrders as $order)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3 font-mono text-xs">#{{ $order->id }}</td>
                                <td class="px-5 py-3">{{ $order->user->name ?? '-' }}</td>
                                <td class="px-5 py-3">{{ $order->partner->workshop_name ?? '-' }}</td>
                                <td class="px-5 py-3">{{ $order->service_type }}</td>
                                <td class="px-5 py-3">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                                <td class="px-5 py-3">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full
                                        {{ $order->status === 'completed' ? 'bg-green-100 text-green-700' : '' }}
                                        {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                        {{ $order->status === 'cancelled' ? 'bg-red-100 text-red-700' : '' }}
                                        {{ in_array($order->status, ['dispatching', 'accepted', 'on_the_way', 'in_progress']) ? 'bg-blue-100 text-blue-700' : '' }}">
                                        {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-gray-500">{{ $order->created_at->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-8 text-center text-gray-400">No orders yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pending Partners -->
        @if($recentPartners->count())
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-dark">Pending Partner Approvals</h3>
                </div>
                <div class="divide-y divide-gray-100">
                    @foreach($recentPartners as $partner)
                        <div class="px-5 py-4 flex items-center justify-between">
                            <div>
                                <p class="font-medium text-dark">{{ $partner->workshop_name }}</p>
                                <p class="text-sm text-gray-500">{{ $partner->user->name }} · {{ $partner->workshop_address }}</p>
                            </div>
                            <div class="flex gap-2">
                                <form action="{{ route('admin.partners.approve', $partner) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button class="px-3 py-1 text-xs font-medium bg-green-500 text-white rounded-lg hover:bg-green-600">Approve</button>
                                </form>
                                <a href="{{ route('admin.partners.show', $partner) }}" class="px-3 py-1 text-xs font-medium bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Review</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
