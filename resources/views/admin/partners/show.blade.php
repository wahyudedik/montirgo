<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.partners.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            </a>
            <h2 class="text-xl font-semibold text-gray-800">{{ $partner->workshop_name }}</h2>
        </div>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8">
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 text-sm">{{ session('success') }}</div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Info Card -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-dark mb-4">Workshop Information</h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div><span class="text-gray-500">Owner:</span> <span class="font-medium">{{ $partner->user->name }}</span></div>
                        <div><span class="text-gray-500">Email:</span> <span class="font-medium">{{ $partner->user->email }}</span></div>
                        <div><span class="text-gray-500">Phone:</span> <span class="font-medium">{{ $partner->user->phone ?? '-' }}</span></div>
                        <div><span class="text-gray-500">Status:</span>
                            <span class="px-2 py-1 text-xs font-medium rounded-full
                                {{ $partner->status === 'approved' ? 'bg-green-100 text-green-700' : '' }}
                                {{ $partner->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                {{ $partner->status === 'suspended' ? 'bg-red-100 text-red-700' : '' }}">
                                {{ ucfirst($partner->status) }}
                            </span>
                        </div>
                        <div class="col-span-2"><span class="text-gray-500">Address:</span> <span class="font-medium">{{ $partner->workshop_address }}</span></div>
                        <div><span class="text-gray-500">Rating:</span> <span class="font-medium text-yellow-500">★ {{ number_format($partner->rating_avg, 1) }}</span></div>
                        <div><span class="text-gray-500">Total Orders:</span> <span class="font-medium">{{ $partner->total_orders }}</span></div>
                    </div>
                </div>

                <!-- Services -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-dark mb-4">Services</h3>
                    @if($partner->services->count())
                        <div class="space-y-2">
                            @foreach($partner->services as $service)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="font-medium text-sm">{{ $service->service_name }}</p>
                                        <p class="text-xs text-gray-500">{{ ucfirst($service->category) }}</p>
                                    </div>
                                    <span class="text-sm font-semibold text-primary">Rp {{ number_format($service->base_price, 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-400 text-sm">No services added yet.</p>
                    @endif
                </div>

                <!-- Recent Reviews -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-dark mb-4">Recent Reviews</h3>
                    @if($partner->reviews->count())
                        <div class="space-y-3">
                            @foreach($partner->reviews as $review)
                                <div class="p-3 bg-gray-50 rounded-lg">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="font-medium text-sm">{{ $review->user->name }}</span>
                                        <span class="text-yellow-500 text-sm">★ {{ $review->rating }}</span>
                                    </div>
                                    <p class="text-sm text-gray-600">{{ $review->comment }}</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-400 text-sm">No reviews yet.</p>
                    @endif
                </div>
            </div>

            <!-- Actions Sidebar -->
            <div class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-dark mb-4">Actions</h3>
                    <div class="space-y-3">
                        @if($partner->status === 'pending')
                            <form action="{{ route('admin.partners.approve', $partner) }}" method="POST">
                                @csrf @method('PATCH')
                                <button class="w-full px-4 py-2 bg-green-500 text-white rounded-lg text-sm font-medium hover:bg-green-600">✓ Approve Partner</button>
                            </form>
                            <form action="{{ route('admin.partners.reject', $partner) }}" method="POST">
                                @csrf @method('PATCH')
                                <input type="text" name="rejection_reason" placeholder="Rejection reason..." required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm mb-2">
                                <button class="w-full px-4 py-2 bg-red-500 text-white rounded-lg text-sm font-medium hover:bg-red-600">✗ Reject Partner</button>
                            </form>
                        @endif

                        @if($partner->status === 'approved')
                            <form action="{{ route('admin.partners.suspend', $partner) }}" method="POST">
                                @csrf @method('PATCH')
                                <button class="w-full px-4 py-2 bg-orange-500 text-white rounded-lg text-sm font-medium hover:bg-orange-600">⚠ Suspend Partner</button>
                            </form>
                        @endif

                        <form action="{{ route('admin.partners.destroy', $partner) }}" method="POST" onsubmit="return confirm('Delete this partner permanently?')">
                            @csrf @method('DELETE')
                            <button class="w-full px-4 py-2 bg-gray-200 text-red-600 rounded-lg text-sm font-medium hover:bg-red-100">Delete Partner</button>
                        </form>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-dark mb-3">Quick Stats</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between"><span class="text-gray-500">Online:</span> <span class="{{ $partner->is_online ? 'text-green-600' : 'text-gray-400' }}">{{ $partner->is_online ? 'Yes' : 'No' }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Available:</span> <span class="{{ $partner->is_available ? 'text-green-600' : 'text-gray-400' }}">{{ $partner->is_available ? 'Yes' : 'No' }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Approved:</span> <span class="font-medium">{{ $partner->approved_at?->format('d M Y') ?? '-' }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Registered:</span> <span class="font-medium">{{ $partner->created_at->format('d M Y') }}</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
