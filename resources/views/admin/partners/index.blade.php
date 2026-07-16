<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Partners Management</h2>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8">
        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
            <form action="{{ route('admin.partners.index') }}" method="GET" class="flex flex-wrap gap-3">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search workshop, owner..."
                    class="flex-1 min-w-[200px] px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-primary focus:border-primary">
                <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-primary focus:border-primary">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-primary hover:bg-primary-600 text-white rounded-lg text-sm font-medium">Filter</button>
                <a href="{{ route('admin.partners.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-300">Reset</a>
            </form>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 text-sm">{{ session('success') }}</div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-5 py-3 text-left font-medium">Workshop</th>
                            <th class="px-5 py-3 text-left font-medium">Owner</th>
                            <th class="px-5 py-3 text-left font-medium">Address</th>
                            <th class="px-5 py-3 text-left font-medium">Rating</th>
                            <th class="px-5 py-3 text-left font-medium">Orders</th>
                            <th class="px-5 py-3 text-left font-medium">Status</th>
                            <th class="px-5 py-3 text-right font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($partners as $partner)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3 font-medium text-dark">{{ $partner->workshop_name }}</td>
                                <td class="px-5 py-3 text-gray-500">{{ $partner->user->name ?? '-' }}</td>
                                <td class="px-5 py-3 text-gray-500 max-w-[200px] truncate">{{ $partner->workshop_address }}</td>
                                <td class="px-5 py-3">
                                    <span class="text-yellow-500">★</span> {{ number_format($partner->rating_avg, 1) }}
                                </td>
                                <td class="px-5 py-3">{{ $partner->total_orders }}</td>
                                <td class="px-5 py-3">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full
                                        {{ $partner->status === 'approved' ? 'bg-green-100 text-green-700' : '' }}
                                        {{ $partner->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                        {{ $partner->status === 'suspended' ? 'bg-red-100 text-red-700' : '' }}
                                        {{ $partner->status === 'rejected' ? 'bg-gray-100 text-gray-500' : '' }}">
                                        {{ ucfirst($partner->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <a href="{{ route('admin.partners.show', $partner) }}" class="px-3 py-1 text-xs font-medium bg-primary-50 text-primary rounded-lg hover:bg-primary-100">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-8 text-center text-gray-400">No partners found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3 border-t border-gray-100">{{ $partners->withQueryString()->links() }}</div>
        </div>
    </div>
</x-app-layout>
