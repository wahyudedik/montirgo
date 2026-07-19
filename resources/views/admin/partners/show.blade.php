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
                        <div><span class="text-gray-500">Owner:</span> <span class="font-medium">{{ $partner->owner_name ?? $partner->user->name }}</span></div>
                        <div><span class="text-gray-500">Email:</span> <span class="font-medium">{{ $partner->user->email }}</span></div>
                        <div><span class="text-gray-500">Phone:</span> <span class="font-medium">{{ $partner->user->phone ?? '-' }}</span></div>
                        <div><span class="text-gray-500">Owner Phone:</span> <span class="font-medium">{{ $partner->owner_phone ?? '-' }}</span></div>
                        @php
                            $categoryLabels = ['motorcycle' => 'Motor', 'car' => 'Mobil', 'both' => 'Motor & Mobil'];
                            $categoryColors = ['motorcycle' => 'bg-blue-100 text-blue-700', 'car' => 'bg-purple-100 text-purple-700', 'both' => 'bg-teal-100 text-teal-700'];
                        @endphp
                        <div><span class="text-gray-500">Kategori:</span>
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $categoryColors[$partner->workshop_category] ?? 'bg-gray-100 text-gray-500' }}">
                                {{ $categoryLabels[$partner->workshop_category] ?? ucfirst($partner->workshop_category) }}
                            </span>
                        </div>
                        <div><span class="text-gray-500">Service Radius:</span> <span class="font-medium">{{ $partner->service_radius }} km</span></div>
                        <div><span class="text-gray-500">Workshop GPS:</span> <span class="font-medium">{{ $partner->workshop_lat ?? '-' }}, {{ $partner->workshop_lng ?? '-' }}</span></div>
                        <div><span class="text-gray-500">Last Seen:</span>
                            <span class="font-medium {{ $partner->last_active_at && $partner->last_active_at->diffInMinutes(now()) < 30 ? 'text-green-600' : 'text-gray-500' }}">
                                @if($partner->last_active_at)
                                    {{ $partner->last_active_at->diffForHumans() }}
                                @else
                                    Tidak diketahui
                                @endif
                            </span>
                        </div>
                        <div><span class="text-gray-500">Status:</span>
                            @php
                                $statusColors = ['approved' => 'bg-green-100 text-green-700', 'pending' => 'bg-yellow-100 text-yellow-700', 'draft' => 'bg-blue-100 text-blue-600', 'suspended' => 'bg-red-100 text-red-700', 'rejected' => 'bg-gray-100 text-gray-500'];
                            @endphp
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusColors[$partner->status] ?? 'bg-gray-100 text-gray-500' }}">
                                {{ ucfirst($partner->status) }}
                            </span>
                        </div>
                        @if($partner->partner_status)
                        <div><span class="text-gray-500">Partner Status:</span>
                            @php
                                $partnerStatusColors = ['online' => 'bg-green-100 text-green-700', 'offline' => 'bg-gray-100 text-gray-500', 'resting' => 'bg-yellow-100 text-yellow-700', 'closed' => 'bg-red-100 text-red-700', 'on_the_way' => 'bg-blue-100 text-blue-700', 'in_progress' => 'bg-indigo-100 text-indigo-700'];
                            @endphp
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $partnerStatusColors[$partner->partner_status] ?? 'bg-gray-100 text-gray-500' }}">
                                {{ ucfirst(str_replace('_', ' ', $partner->partner_status)) }}
                            </span>
                        </div>
                        @endif
                        @if($partner->operational_schedule && is_array($partner->operational_schedule))
                        <div class="col-span-2 pt-2 border-t border-gray-100">
                            <span class="text-gray-500 font-medium text-xs uppercase tracking-wide">Jam Operasional:</span>
                            <div class="mt-2 grid grid-cols-7 gap-1 text-xs">
                                @php
                                    $dayLabels = ['mon' => 'Sen', 'tue' => 'Sel', 'wed' => 'Rab', 'thu' => 'Kam', 'fri' => 'Jum', 'sat' => 'Sab', 'sun' => 'Min'];
                                    $todayKey = now('Asia/Jakarta')->format('D');
                                    $dayMap = ['Mon' => 'mon', 'Tue' => 'tue', 'Wed' => 'wed', 'Thu' => 'thu', 'Fri' => 'fri', 'Sat' => 'sat', 'Sun' => 'sun'];
                                    $today = $dayMap[$todayKey] ?? '';
                                @endphp
                                @foreach($dayLabels as $key => $label)
                                    @php $schedule = $partner->operational_schedule[$key] ?? null; @endphp
                                    <div class="p-1 rounded {{ $today === $key ? 'bg-primary-50 ring-1 ring-primary-200' : 'bg-gray-50' }}">
                                        <div class="font-medium {{ $today === $key ? 'text-primary' : 'text-gray-600' }}">{{ $label }}</div>
                                        @if($schedule && isset($schedule['open']) && isset($schedule['close']))
                                            <div class="text-gray-700">{{ $schedule['open'] }}-{{ $schedule['close'] }}</div>
                                        @else
                                            <div class="text-red-400">Tutup</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-1 text-xs">
                                <span class="{{ $partner->isCurrentlyOperating() ? 'text-green-600' : 'text-red-500' }}">
                                    {{ $partner->isCurrentlyOperating() ? 'Sedang Buka' : 'Sedang Tutup' }}
                                </span>
                            </div>
                        </div>
                        @endif
                        <div class="col-span-2"><span class="text-gray-500">Address:</span> <span class="font-medium">{{ $partner->workshop_address }}</span></div>
                        <div><span class="text-gray-500">Rating:</span> <span class="font-medium text-yellow-500"><svg class="inline w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg> {{ number_format($partner->rating_avg, 1) }}</span></div>
                        <div><span class="text-gray-500">Total Orders:</span> <span class="font-medium">{{ $partner->total_orders }}</span></div>
                        <div><span class="text-gray-500">Total Reviews:</span> <span class="font-medium">{{ $partner->total_reviews }}</span></div>
                        <div><span class="text-gray-500">Profile Completion:</span> <span class="font-medium {{ $partner->getProfileCompletionPercentage() >= 100 ? 'text-green-600' : 'text-yellow-600' }}">{{ $partner->getProfileCompletionPercentage() }}%</span></div>
                        @if($partner->description)
                        <div class="col-span-2"><span class="text-gray-500">Deskripsi:</span> <span class="font-medium">{{ $partner->description }}</span></div>
                        @endif
                    </div>
                </div>

                <!-- Documents -->
                @php
                    $documents = collect([
                        ['label' => 'KTP Number', 'value' => $partner->ktp_number, 'type' => 'text'],
                        ['label' => 'KTP Photo', 'value' => $partner->ktp_photo, 'type' => 'photo'],
                        ['label' => 'Selfie + KTP', 'value' => $partner->selfie_with_ktp, 'type' => 'photo'],
                        ['label' => 'Workshop Photo', 'value' => $partner->workshop_photo, 'type' => 'photo'],
                        ['label' => 'Front Workshop', 'value' => $partner->front_workshop_photo, 'type' => 'photo'],
                        ['label' => 'Inside Workshop', 'value' => $partner->inside_workshop_photo, 'type' => 'photo'],
                        ['label' => 'Business License', 'value' => $partner->business_license, 'type' => 'photo'],
                        ['label' => 'NPWP', 'value' => $partner->npwp, 'type' => 'text'],
                        ['label' => 'NIB', 'value' => $partner->nib, 'type' => 'text'],
                    ])->filter(fn ($doc) => filled($doc['value']));
                @endphp
                @if($documents->count())
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-dark mb-4">Documents</h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        @foreach($documents as $doc)
                            @if($doc['type'] === 'photo')
                                <div>
                                    <span class="text-gray-500">{{ $doc['label'] }}:</span>
                                    <a href="{{ Storage::url($doc['value']) }}" target="_blank" class="text-primary hover:underline ml-1">View Photo</a>
                                </div>
                            @else
                                <div><span class="text-gray-500">{{ $doc['label'] }}:</span> <span class="font-medium">{{ $doc['value'] }}</span></div>
                            @endif
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Mechanics -->
                @if($partner->mechanics->count())
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-dark mb-4">Mechanics ({{ $partner->mechanics->count() }})</h3>
                    <div class="space-y-2">
                        @foreach($partner->mechanics as $mechanic)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <p class="font-medium text-sm">{{ $mechanic->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $mechanic->expertise }}</p>
                                    @if($mechanic->phone)
                                    <p class="text-xs text-gray-400">{{ $mechanic->phone }}</p>
                                    @endif
                                </div>
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $mechanic->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $mechanic->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

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
                                        <span class="text-yellow-500 text-sm"><svg class="inline w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg> {{ $review->rating }}</span>
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
                        @if(in_array($partner->status, ['pending', 'draft']))
                            <form action="{{ route('admin.partners.approve', $partner) }}" method="POST">
                                @csrf @method('PATCH')
                                <button class="w-full px-4 py-2 bg-green-500 text-white rounded-lg text-sm font-medium hover:bg-green-600 flex items-center justify-center gap-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Approve Partner</button>
                            </form>
                            <form action="{{ route('admin.partners.reject', $partner) }}" method="POST">
                                @csrf @method('PATCH')
                                <input type="text" name="rejection_reason" placeholder="Rejection reason..." required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm mb-2">
                                <button class="w-full px-4 py-2 bg-red-500 text-white rounded-lg text-sm font-medium hover:bg-red-600 flex items-center justify-center gap-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg> Reject Partner</button>
                            </form>
                        @endif

                        @if($partner->status === 'approved')
                            <form action="{{ route('admin.partners.suspend', $partner) }}" method="POST">
                                @csrf @method('PATCH')
                                <button class="w-full px-4 py-2 bg-orange-500 text-white rounded-lg text-sm font-medium hover:bg-orange-600 flex items-center justify-center gap-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg> Suspend Partner</button>
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
                        <div class="flex justify-between"><span class="text-gray-500">Last Active:</span>
                            <span class="font-medium {{ $partner->last_active_at ? ($partner->last_active_at->diffInMinutes(now()) < 30 ? 'text-green-600' : 'text-yellow-600') : 'text-gray-400' }}">
                                {{ $partner->last_active_at ? $partner->last_active_at->diffForHumans() : 'Never' }}
                            </span>
                        </div>
                        <div class="flex justify-between"><span class="text-gray-500">Currently Operating:</span>
                            <span class="{{ $partner->isCurrentlyOperating() ? 'text-green-600' : 'text-red-500' }}">
                                {{ $partner->isCurrentlyOperating() ? 'Open' : 'Closed' }}
                            </span>
                        </div>
                        <div class="flex justify-between"><span class="text-gray-500">Mechanics:</span> <span class="font-medium">{{ $partner->mechanics->count() }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Services:</span> <span class="font-medium">{{ $partner->services->count() }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Approved:</span> <span class="font-medium">{{ $partner->approved_at?->format('d M Y') ?? '-' }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Registered:</span> <span class="font-medium">{{ $partner->created_at->format('d M Y') }}</span></div>
                        @if($partner->rejection_reason)
                        <div class="pt-2 border-t border-gray-100">
                            <span class="text-gray-500 text-xs">Rejection Reason:</span>
                            <p class="text-red-600 text-sm mt-1">{{ $partner->rejection_reason }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
