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
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
                <select name="workshop_category" class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-primary focus:border-primary">
                    <option value="">All Categories</option>
                    <option value="motorcycle" {{ request('workshop_category') === 'motorcycle' ? 'selected' : '' }}>Motor</option>
                    <option value="car" {{ request('workshop_category') === 'car' ? 'selected' : '' }}>Mobil</option>
                    <option value="both" {{ request('workshop_category') === 'both' ? 'selected' : '' }}>Motor & Mobil</option>
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
                            <th class="px-5 py-3 text-left font-medium">Kategori</th>
                            <th class="px-5 py-3 text-left font-medium">Owner</th>
                            <th class="px-5 py-3 text-left font-medium">Address</th>
                            <th class="px-5 py-3 text-left font-medium">Rating</th>
                            <th class="px-5 py-3 text-left font-medium">Orders</th>
                            <th class="px-5 py-3 text-left font-medium">Status</th>
                            <th class="px-5 py-3 text-left font-medium">Last Seen</th>
                            <th class="px-5 py-3 text-right font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($partners as $partner)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3 font-medium text-dark">{{ $partner->workshop_name }}</td>
                                <td class="px-5 py-3 text-gray-500">
                                    @php
                                        $categoryLabels = ['motorcycle' => 'Motor', 'car' => 'Mobil', 'both' => 'Motor & Mobil'];
                                        $categoryColors = ['motorcycle' => 'bg-blue-100 text-blue-700', 'car' => 'bg-purple-100 text-purple-700', 'both' => 'bg-teal-100 text-teal-700'];
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $categoryColors[$partner->workshop_category] ?? 'bg-gray-100 text-gray-500' }}">
                                        {{ $categoryLabels[$partner->workshop_category] ?? ucfirst($partner->workshop_category) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-gray-500">{{ $partner->user->name ?? '-' }}</td>
                                <td class="px-5 py-3 text-gray-500 max-w-[200px] truncate">{{ $partner->workshop_address }}</td>
                                <td class="px-5 py-3">
                                    <span class="text-yellow-500"><svg class="inline w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg></span> {{ number_format($partner->rating_avg, 1) }}
                                </td>
                                <td class="px-5 py-3">{{ $partner->total_orders }}</td>
                                <td class="px-5 py-3">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full
                                        {{ $partner->status === 'approved' ? 'bg-green-100 text-green-700' : '' }}
                                        {{ $partner->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                        {{ $partner->status === 'draft' ? 'bg-blue-100 text-blue-600' : '' }}
                                        {{ $partner->status === 'suspended' ? 'bg-red-100 text-red-700' : '' }}
                                        {{ $partner->status === 'rejected' ? 'bg-gray-100 text-gray-500' : '' }}">
                                        {{ ucfirst($partner->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3">
                                    @if($partner->last_active_at)
                                        @php
                                            $minutesAgo = $partner->last_active_at->diffInMinutes(now());
                                            $isRecent = $minutesAgo < 30;
                                        @endphp
                                        <span class="text-xs {{ $isRecent ? 'text-green-600' : 'text-gray-500' }}" title="{{ $partner->last_active_at->format('d M Y H:i:s') }}">
                                            {{ $partner->last_active_at->diffForHumans() }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400">Never</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <a href="{{ route('admin.partners.show', $partner) }}" class="px-3 py-1 text-xs font-medium bg-primary-50 text-primary rounded-lg hover:bg-primary-100">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-5 py-8 text-center text-gray-400">No partners found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3 border-t border-gray-100">{{ $partners->withQueryString()->links() }}</div>
        </div>
    </div>
</x-app-layout>
