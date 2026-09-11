@extends('layouts.admin')

@section('title', 'Delivery Boy Master')

@section('content')
<div class="space-y-6" x-data="deliveryBoyMaster()">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-emerald-600 mb-1">
                <i class="fa-solid fa-motorcycle"></i>
                <span>Fleet & Logistics Management</span>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">Delivery Boy Master</h1>
            <p class="text-xs text-slate-500 mt-0.5">Register delivery boys, track online availability and store allocations.</p>
        </div>
        <div class="flex items-center space-x-2">
            <button type="button" @click="openCreateModal()" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-md shadow-emerald-600/20 flex items-center space-x-2">
                <i class="fa-solid fa-plus"></i>
                <span>Add Delivery Boy</span>
            </button>
            <a href="{{ route('admin.delivery_boys.cod') }}" class="px-3.5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition flex items-center space-x-2">
                <i class="fa-solid fa-sack-dollar text-emerald-400"></i>
                <span>COD Collections</span>
            </a>
        </div>
    </div>

    <!-- FILTER BAR -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row items-center justify-between gap-3">
        <form action="{{ route('admin.delivery_boys.index') }}" method="GET" class="w-full flex flex-col md:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-xs">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search delivery boy name, mobile, vehicle number..." class="w-full text-xs rounded-xl border border-slate-200 pl-9 pr-3 py-2 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>

            <div class="flex items-center gap-2 w-full md:w-auto">
                <select name="online" class="text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <option value="all">All Availability</option>
                    <option value="online" {{ request('online') === 'online' ? 'selected' : '' }}>Online</option>
                    <option value="offline" {{ request('online') === 'offline' ? 'selected' : '' }}>Offline</option>
                </select>

                <select name="status" class="text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <option value="all">All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>

                <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition">Filter</button>
            </div>
        </form>
    </div>

    <!-- TABLE -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200">
                        <th class="py-3.5 px-4">#</th>
                        <th class="py-3.5 px-4">Personal Name</th>
                        <th class="py-3.5 px-4">Mobile Number</th>
                        <th class="py-3.5 px-4">Store Allocated</th>
                        <th class="py-3.5 px-4">Vehicle Details</th>
                        <th class="py-3.5 px-4">Availability</th>
                        <th class="py-3.5 px-4">Delivered Orders</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($deliveryBoys as $index => $boy)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4 text-slate-400">{{ $deliveryBoys->firstItem() + $index }}</td>
                            <td class="py-3.5 px-4">
                                <div class="flex items-center space-x-2.5">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center font-bold text-slate-700">
                                        {{ substr($boy->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <span class="font-bold text-slate-900 block">{{ $boy->name }}</span>
                                        <span class="text-[11px] text-emerald-600 font-mono font-medium">@<span>{{ $boy->username }}</span></span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-700">
                                {{ $boy->mobile }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded-lg bg-slate-100 font-semibold text-slate-700 text-[11px]">
                                    {{ $boy->branch->name ?? 'Store' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                <span class="font-bold text-slate-800 block">{{ $boy->vehicle_type }}</span>
                                <span class="text-[10px] text-slate-400 font-mono">{{ $boy->vehicle_number ?: 'No Plate' }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <form action="{{ route('admin.delivery_boys.toggle_online', $boy->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $boy->is_online ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                        <span class="w-1.5 h-1.5 rounded-full mr-1 {{ $boy->is_online ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                        {{ $boy->is_online ? 'Online' : 'Offline' }}
                                    </button>
                                </form>
                            </td>
                            <td class="py-3.5 px-4 font-bold text-slate-800">
                                {{ $boy->orders_count }} Delivered
                            </td>
                            <td class="py-3.5 px-4">
                                <form action="{{ route('admin.delivery_boys.toggle', $boy->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $boy->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                        {{ ucfirst($boy->status) }}
                                    </button>
                                </form>
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-1">
                                <button type="button" @click="openEditModal({{ json_encode($boy) }})" class="p-1.5 rounded-lg bg-slate-100 hover:bg-emerald-600 hover:text-white text-slate-600 transition">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <form action="{{ route('admin.delivery_boys.destroy', $boy->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete delivery personnel record?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg bg-slate-100 hover:bg-red-600 hover:text-white text-slate-600 transition">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <x-empty-state title="No Delivery Boys Found" message="Register delivery fleet personnel to handle order dispatches." actionText="Add Delivery Boy" actionClick="openCreateModal()" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards -->
        <div class="md:hidden divide-y divide-slate-100">
            @forelse($deliveryBoys as $mBoy)
                <div class="p-4 space-y-2">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="font-bold text-slate-900 text-xs block">{{ $mBoy->name }}</span>
                            <span class="text-[10px] text-emerald-600 font-mono">@<span>{{ $mBoy->username }}</span></span>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $mBoy->is_online ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                            {{ $mBoy->is_online ? 'Online' : 'Offline' }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 font-mono"><i class="fa-solid fa-phone text-[10px] mr-1"></i> {{ $mBoy->mobile }} &bull; {{ $mBoy->vehicle_type }}</p>
                    <div class="flex items-center justify-between text-[11px] text-slate-400 pt-1 border-t border-slate-100">
                        <span>{{ $mBoy->branch->name ?? 'Store' }}</span>
                        <button type="button" @click="openEditModal({{ json_encode($mBoy) }})" class="px-3 py-1 rounded-lg bg-slate-100 text-slate-700 font-semibold">Edit</button>
                    </div>
                </div>
            @empty
                <div class="p-4"><x-empty-state title="No Delivery Boys" actionText="Add Delivery Boy" actionClick="openCreateModal()" /></div>
            @endforelse
        </div>

        @if($deliveryBoys->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50">{{ $deliveryBoys->links() }}</div>
        @endif

    </div>

    <!-- MODAL -->
    <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm" @click="modalOpen = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full p-6 border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900 flex items-center space-x-2">
                        <i class="fa-solid fa-motorcycle text-emerald-600"></i>
                        <span x-text="isEdit ? 'Edit Delivery Boy' : 'Register New Delivery Boy'"></span>
                    </h3>
                    <button @click="modalOpen = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
                </div>

                <form :action="formAction" method="POST" class="mt-4 space-y-4">
                    @csrf
                    <template x-if="isEdit"><input type="hidden" name="_method" value="PUT"></template>

                    <!-- Sequence Row 1: Full Name | Mobile -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" x-model="formData.name" required placeholder="e.g. Rohan Patil" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Mobile <span class="text-red-500">*</span></label>
                            <input type="text" name="mobile" x-model="formData.mobile" required maxlength="10" pattern="[0-9]{10}" placeholder="10-digits number" class="strict-mobile w-full text-xs rounded-xl border border-slate-200 px-3 py-2 font-mono focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>
                    </div>

                    <!-- Sequence Row 2: Assigned Store | Vehicle Type -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Assigned Store <span class="text-red-500">*</span></label>
                            <select name="branch_id" x-model="formData.branch_id" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Vehicle Type <span class="text-red-500">*</span></label>
                            <input type="text" name="vehicle_type" x-model="formData.vehicle_type" required placeholder="e.g. Bike (Hero Splendor)" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>
                    </div>

                    <!-- Sequence Row 3: Vehicle Registration Number | Driving License no -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Vehicle Registration Number</label>
                            <input type="text" name="vehicle_number" x-model="formData.vehicle_number" placeholder="MH-11-AB-1234" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 uppercase font-mono focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Driving License no</label>
                            <input type="text" name="license_number" x-model="formData.license_number" placeholder="DL-MH11-2023-009" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 uppercase font-mono focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>
                    </div>

                    <!-- Sequence Row 4: Username | Password (mandatory and add red astric sign) -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Username <span class="text-red-500">*</span></label>
                            <input type="text" name="username" x-model="formData.username" required placeholder="e.g. rohan123" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Password <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input :type="showPassword ? 'text' : 'password'" name="password" x-model="formData.password" required placeholder="Enter password" autocomplete="new-password" class="w-full text-xs rounded-xl border border-slate-200 pl-3 pr-9 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                                <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none">
                                    <i class="fa-regular text-xs" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Row 5: Online Availability & Status -->
                    <div class="grid grid-cols-2 gap-3 pt-1">
                        <label class="flex items-center space-x-2 text-xs text-slate-700 font-semibold cursor-pointer">
                            <input type="checkbox" name="is_online" value="1" x-model="formData.is_online" class="rounded text-emerald-600 focus:ring-emerald-500">
                            <span>Online & Available</span>
                        </label>
                        <div>
                            <select name="status" x-model="formData.status" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="suspended">Suspended</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="modalOpen = false" class="px-4 py-2 rounded-xl bg-slate-100 text-xs font-semibold text-slate-700">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-xs font-bold text-white shadow-md shadow-emerald-600/20">Save Delivery Boy</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
    function deliveryBoyMaster() {
        return {
            modalOpen: false,
            isEdit: false,
            showPassword: false,
            formAction: "{{ route('admin.delivery_boys.store') }}",
            formData: {
                name: '',
                mobile: '',
                branch_id: '{{ $branches->first()?->id ?? 1 }}',
                vehicle_type: 'Bike (Hero Splendor)',
                vehicle_number: '',
                license_number: '',
                username: '',
                password: '',
                is_online: true,
                status: 'active'
            },
            openCreateModal() {
                this.isEdit = false;
                this.showPassword = false;
                this.formAction = "{{ route('admin.delivery_boys.store') }}";
                this.formData = {
                    name: '',
                    mobile: '',
                    branch_id: '{{ $branches->first()?->id ?? 1 }}',
                    vehicle_type: 'Bike (Hero Splendor)',
                    vehicle_number: '',
                    license_number: '',
                    username: '',
                    password: '',
                    is_online: true,
                    status: 'active'
                };
                this.modalOpen = true;
            },
            openEditModal(db) {
                this.isEdit = true;
                this.showPassword = false;
                this.formAction = `/admin/delivery-boys/${db.id}`;
                this.formData = {
                    name: db.name,
                    mobile: db.mobile,
                    branch_id: db.branch_id,
                    vehicle_type: db.vehicle_type,
                    vehicle_number: db.vehicle_number || '',
                    license_number: db.license_number || '',
                    username: db.username || '',
                    password: db.plain_password || '',
                    is_online: Boolean(db.is_online),
                    status: db.status
                };
                this.modalOpen = true;
            }
        };
    }
</script>
@endsection
