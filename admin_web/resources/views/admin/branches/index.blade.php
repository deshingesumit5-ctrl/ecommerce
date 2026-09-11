@extends('layouts.admin')

@section('title', 'Store Master')

@section('content')
<div class="space-y-6" x-data="storeMaster()">

    <!-- TOP HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-emerald-600 mb-1">
                <i class="fa-solid fa-store"></i>
                <span>Business Master Module</span>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">Store Master</h1>
            <p class="text-xs text-slate-500 mt-0.5">Manage hyperlocal operating stores, store locations and base radius.</p>
        </div>
        <button type="button" @click="openCreateModal()" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-md shadow-emerald-600/20 flex items-center space-x-2">
            <i class="fa-solid fa-plus"></i>
            <span>Add New Store</span>
        </button>
    </div>

    <!-- FILTER & SEARCH BAR -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row items-center justify-between gap-3">
        <form action="{{ route('admin.branches.index') }}" method="GET" class="w-full flex flex-col md:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-xs">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search store name, store code, phone..." class="w-full text-xs rounded-xl border border-slate-200 pl-9 pr-3 py-2 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>

            <div class="flex items-center gap-2 w-full md:w-auto">
                <select name="status" class="text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Stores</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive Stores</option>
                </select>

                <select name="per_page" class="text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10 / page</option>
                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 / page</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 / page</option>
                </select>

                <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('admin.branches.index') }}" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold transition">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- STORES DATA TABLE & MOBILE CARDS -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        
        <!-- Desktop Table -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200">
                        <th class="py-3.5 px-4">#</th>
                        <th class="py-3.5 px-4">Store Name & Code</th>
                        <th class="py-3.5 px-4">Contact Phone</th>
                        <th class="py-3.5 px-4">Address & GPS Coordinates</th>
                        <th class="py-3.5 px-4">Radius (KM)</th>
                        <th class="py-3.5 px-4">Fleet / Orders</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($branches as $index => $branch)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4 text-slate-400 font-medium">{{ $branches->firstItem() + $index }}</td>
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-slate-900 block">{{ $branch->name }}</span>
                                <span class="inline-block px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 text-[10px] font-mono mt-0.5">{{ $branch->code }}</span>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-700">
                                <i class="fa-solid fa-phone text-slate-400 text-[10px] mr-1"></i>
                                <span>{{ $branch->contact_phone }}</span>
                            </td>
                            <td class="py-3.5 px-4 max-w-xs">
                                <span class="text-slate-600 truncate block">{{ $branch->address }}</span>
                                <span class="text-[10px] text-slate-400 font-mono block mt-0.5">Lat: {{ $branch->latitude }}, Lng: {{ $branch->longitude }}</span>
                            </td>
                            <td class="py-3.5 px-4 font-bold text-emerald-600">
                                {{ $branch->radius_km }} KM
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="text-[11px] text-slate-600 block">{{ $branch->delivery_boys_count }} Delivery Boys</span>
                                <span class="text-[10px] text-slate-400">{{ $branch->orders_count }} Orders Total</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <form action="{{ route('admin.branches.toggle', $branch->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $branch->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                        <span class="w-1.5 h-1.5 rounded-full mr-1 {{ $branch->status === 'active' ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                        {{ ucfirst($branch->status) }}
                                    </button>
                                </form>
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-1">
                                <button type="button" @click="openEditModal({{ json_encode($branch) }})" class="p-1.5 rounded-lg bg-slate-100 hover:bg-emerald-600 hover:text-white text-slate-600 transition" title="Edit Store">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <a href="{{ route('admin.branches.radius') }}" class="p-1.5 rounded-lg bg-slate-100 hover:bg-blue-600 hover:text-white text-slate-600 transition inline-block" title="Radius Map">
                                    <i class="fa-solid fa-map-location-dot"></i>
                                </a>
                                <form action="{{ route('admin.branches.destroy', $branch->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this store?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg bg-slate-100 hover:bg-red-600 hover:text-white text-slate-600 transition" title="Delete Store">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <x-empty-state title="No Stores Found" message="Add your first store location to start managing hyperlocal delivery operations." actionText="Add New Store" actionClick="openCreateModal()" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Stacked Card View -->
        <div class="md:hidden divide-y divide-slate-100">
            @forelse($branches as $mBranch)
                <div class="p-4 space-y-2">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="font-bold text-slate-900 text-sm block">{{ $mBranch->name }}</span>
                            <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-slate-100 text-slate-600">{{ $mBranch->code }}</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $mBranch->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                            {{ ucfirst($mBranch->status) }}
                        </span>
                    </div>
                    <div class="text-xs text-slate-600 space-y-1">
                        <p><i class="fa-solid fa-phone text-slate-400 mr-1"></i> {{ $mBranch->contact_phone }}</p>
                        <p class="text-[11px] text-slate-500"><i class="fa-solid fa-location-dot text-slate-400 mr-1"></i> {{ $mBranch->address }}</p>
                        <p class="text-emerald-600 font-semibold"><i class="fa-solid fa-crosshairs mr-1"></i> Radius: {{ $mBranch->radius_km }} KM</p>
                    </div>
                    <div class="flex items-center justify-end space-x-2 pt-2 border-t border-slate-100">
                        <button type="button" @click="openEditModal({{ json_encode($mBranch) }})" class="px-3 py-1.5 rounded-lg bg-slate-100 text-xs font-semibold text-slate-700">Edit</button>
                        <a href="{{ route('admin.branches.radius') }}" class="px-3 py-1.5 rounded-lg bg-blue-50 text-xs font-semibold text-blue-700">Radius Map</a>
                    </div>
                </div>
            @empty
                <div class="p-4">
                    <x-empty-state title="No Stores Found" actionText="Add New Store" actionClick="openCreateModal()" />
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($branches->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50">
                {{ $branches->links() }}
            </div>
        @endif

    </div>

    <!-- ADD / EDIT STORE MODAL -->
    <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm transition-opacity" @click="modalOpen = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200 p-6">
                
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900 flex items-center space-x-2">
                        <i class="fa-solid fa-store text-emerald-600"></i>
                        <span x-text="isEdit ? 'Edit Store Master' : 'Add New Store'"></span>
                    </h3>
                    <button @click="modalOpen = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
                </div>

                <form :action="formAction" method="POST" class="mt-4 space-y-4">
                    @csrf
                    <template x-if="isEdit">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Store Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" x-model="formData.name" required placeholder="e.g. Satara Store" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Store Code <span class="text-red-500">*</span></label>
                            <input type="text" name="code" x-model="formData.code" required placeholder="e.g. SAT-01" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 uppercase font-mono focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Mobile Number <span class="text-red-500">*</span></label>
                            <input type="text" name="contact_phone" x-model="formData.contact_phone" required maxlength="10" pattern="[0-9]{10}" placeholder="10-digit number" class="strict-mobile w-full text-xs rounded-xl border border-slate-200 px-3 py-2 font-mono focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Address <span class="text-red-500">*</span></label>
                        <textarea name="address" x-model="formData.address" rows="2" required placeholder="Full address..." class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Latitude <span class="text-red-500">*</span></label>
                            <input type="number" step="0.000001" name="latitude" x-model="formData.latitude" required placeholder="17.6805" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 font-mono focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Longitude <span class="text-red-500">*</span></label>
                            <input type="number" step="0.000001" name="longitude" x-model="formData.longitude" required placeholder="74.0183" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 font-mono focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Radius (KM) <span class="text-red-500">*</span></label>
                            <input type="number" step="0.5" min="0.5" max="100" name="radius_km" x-model="formData.radius_km" required placeholder="3.00" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 font-bold text-emerald-600 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Store Status <span class="text-red-500">*</span></label>
                        <select name="status" x-model="formData.status" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <div class="flex justify-end space-x-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="modalOpen = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-xs font-semibold text-slate-700">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-xs font-bold text-white shadow-md shadow-emerald-600/20">
                            <span x-text="isEdit ? 'Update Store' : 'Create Store'"></span>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

</div>

<script>
    function storeMaster() {
        return {
            modalOpen: false,
            isEdit: false,
            formAction: "{{ route('admin.branches.store') }}",
            formData: {
                name: '',
                code: '',
                contact_phone: '',
                address: '',
                latitude: 17.6805,
                longitude: 74.0183,
                radius_km: 3.00,
                status: 'active',
            },
            openCreateModal() {
                this.isEdit = false;
                this.formAction = "{{ route('admin.branches.store') }}";
                this.formData = {
                    name: '',
                    code: '',
                    contact_phone: '',
                    address: '',
                    latitude: 17.6805,
                    longitude: 74.0183,
                    radius_km: 3.00,
                    status: 'active',
                };
                this.modalOpen = true;
            },
            openEditModal(branch) {
                this.isEdit = true;
                this.formAction = `/admin/branches/${branch.id}`;
                this.formData = {
                    name: branch.name,
                    code: branch.code,
                    contact_phone: branch.contact_phone,
                    address: branch.address,
                    latitude: branch.latitude,
                    longitude: branch.longitude,
                    radius_km: branch.radius_km,
                    status: branch.status,
                };
                this.modalOpen = true;
            }
        };
    }
</script>
@endsection
