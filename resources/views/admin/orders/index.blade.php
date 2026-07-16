<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Orders Management</h2>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
            <form action="{{ route('admin.orders.index') }}" method="GET" class="flex flex-wrap gap-3">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search service, customer, partner..."
                    class="flex-1 min-w-[200px] px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-primary focus:border-primary">
                <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-primary focus:border-primary">
                    <option value="">All Status</option>
                    @foreach(['pending','dispatching','accepted','on_the_way','in_progress','completed','cancelled'] as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2 bg-primary hover:bg-primary-600 text-white rounded-lg text-sm font-medium">Filter</button>
                <a href="{{ route('admin.orders.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-300">Reset</a>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-5 py-3 text-left font-medium">ID</th>
                            <th class="px-5 py-3 text-left font-medium">Customer</th>
                            <th class="px-5 py-3 text-left font-medium">Partner</th>
                            <th class="px-5 py-3 text-left font-medium">Service</th>
                            <th class="px-5 py-3 text-left font-medium">Total</th>
                            <th class="px-5 py-3 text-left font-medium">Payment</th>
                            <th class="px-5 py-3 text-left font-medium">Status</th>
                            <th class="px-5 py-3 text-left font-medium">Date</th>
                            <th class="px-5 py-3 text-right font-medium">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($orders as $order)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3 font-mono text-xs">#{{ $order->id }}</td>
                                <td class="px-5 py-3">{{ $order->user->name ?? '-' }}</td>
                                <td class="px-5 py-3">{{ $order->partner->workshop_name ?? '-' }}</td>
                                <td class="px-5 py-3">{{ $order->service_type }}</td>
                                <td class="px-5 py-3">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                                <td class="px-5 py-3">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $order->payment_status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                        {{ ucfirst($order->payment_status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full
                                        {{ $order->status === 'completed' ? 'bg-green-100 text-green-700' : '' }}
                                        {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                        {{ $order->status === 'cancelled' ? 'bg-red-100 text-red-700' : '' }}
                                        {{ in_array($order->status, ['dispatching','accepted','on_the_way','in_progress']) ? 'bg-blue-100 text-blue-700' : '' }}">
                                        {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-gray-500">{{ $order->created_at->format('d M H:i') }}</td>
                                <td class="px-5 py-3 text-right">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="px-3 py-1 text-xs font-medium bg-primary-50 text-primary rounded-lg hover:bg-primary-100">Detail</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-5 py-8 text-center text-gray-400">No orders found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3 border-t border-gray-100">{{ $orders->withQueryString()->links() }}</div>
        </div>
    </div>
</x-app-layout>
