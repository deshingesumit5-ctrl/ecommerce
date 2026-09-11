@extends('layouts.admin')

@section('title', 'Customer Management')

@section('content')
<div class="space-y-6" x-data="customerManager()">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-emerald-600 mb-1">
                <i class="fa-solid fa-users"></i>
                <span>User & Consumer Management</span>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">Customer Management</h1>
            <p class="text-xs text-slate-500 mt-0.5">View customer directory, saved delivery addresses, phone numbers and order history.</p>
        </div>
        <button type="button" @click="openCreateModal()" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-md shadow-emerald-600/20 flex items-center space-x-2">
            <i class="fa-solid fa-user-plus"></i>
            <span>Register New Customer</span>
        </button>
    </div>

    <!-- FILTER BAR -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row items-center justify-between gap-3">
        <form action="{{ route('admin.customers.index') }}" method="GET" class="w-full flex flex-col md:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-xs">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search customer name, 10-digit mobile, email, city..." class="w-full text-xs rounded-xl border border-slate-200 pl-9 pr-3 py-2 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>

            <div class="flex items-center gap-2 w-full md:w-auto">
                <select name="status" class="text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 focus:ring-2 focus:ring-emerald-500">
                    <option value="all">All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>Blocked</option>
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
                        <th class="py-3.5 px-4">Customer Details</th>
                        <th class="py-3.5 px-4">Mobile Number</th>
                        <th class="py-3.5 px-4">Delivery Address & City</th>
                        <th class="py-3.5 px-4">Lifetime Orders</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($customers as $index => $c)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4 text-slate-400">{{ $customers->firstItem() + $index }}</td>
                            <td class="py-3.5 px-4">
                                <div class="flex items-center space-x-2.5">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center font-bold text-slate-700">
                                        {{ substr($c->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <span class="font-bold text-slate-900 block">{{ $c->name }}</span>
                                        <span class="text-[11px] text-slate-400">{{ $c->email ?: 'No email' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-700">
                                {{ $c->mobile }}
                            </td>
                            <td class="py-3.5 px-4 max-w-xs">
                                <span class="text-slate-600 block truncate">{{ $c->address }}</span>
                                <span class="text-[10px] text-slate-400 font-bold block mt-0.5">{{ $c->city }} {{ $c->pincode ? ' - ' . $c->pincode : '' }}</span>
                            </td>
                            <td class="py-3.5 px-4 font-bold text-emerald-700">
                                {{ $c->orders_count }} Orders
                            </td>
                            <td class="py-3.5 px-4">
                                <form action="{{ route('admin.customers.toggle', $c->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $c->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                        {{ ucfirst($c->status) }}
                                    </button>
                                </form>
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-1">
                                <button type="button" @click="openEditModal({{ json_encode($c) }})" class="p-1.5 rounded-lg bg-slate-100 hover:bg-emerald-600 hover:text-white text-slate-600 transition">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <form action="{{ route('admin.customers.destroy', $c->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete customer?')">
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
                            <td colspan="7">
                                <x-empty-state title="No Customers Found" message="Register customer accounts or customers will be added as they place orders." actionText="Add Customer" actionClick="openCreateModal()" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="md:hidden divide-y divide-slate-100">
            @forelse($customers as $mC)
                <div class="p-4 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-slate-900 text-xs">{{ $mC->name }}</span>
                        <span class="text-[10px] font-bold text-emerald-600">{{ $mC->orders_count }} Orders</span>
                    </div>
                    <p class="text-xs text-slate-500 font-mono">{{ $mC->mobile }} &bull; {{ $mC->city }}</p>
                    <p class="text-[11px] text-slate-400 truncate">{{ $mC->address }}</p>
                    <div class="text-right pt-1 border-t border-slate-100">
                        <button type="button" @click="openEditModal({{ json_encode($mC) }})" class="px-3 py-1 rounded-lg bg-slate-100 text-slate-700 font-semibold text-xs">Edit</button>
                    </div>
                </div>
            @empty
                <div class="p-4"><x-empty-state title="No Customers" actionText="Add Customer" actionClick="openCreateModal()" /></div>
            @endforelse
        </div>

        @if($customers->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50">{{ $customers->links() }}</div>
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
                        <i class="fa-solid fa-user text-emerald-600"></i>
                        <span x-text="isEdit ? 'Edit Customer' : 'Register Customer'"></span>
                    </h3>
                    <button @click="modalOpen = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
                </div>

                <form :action="formAction" method="POST" class="mt-4 space-y-4">
                    @csrf
                    <template x-if="isEdit"><input type="hidden" name="_method" value="PUT"></template>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Customer Full Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" x-model="formData.name" required placeholder="Enter name" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Mobile Number <span class="text-red-500">*</span></label>
                            <input type="text" name="mobile" x-model="formData.mobile" required maxlength="10" pattern="[0-9]{10}" placeholder="10-digits number" class="strict-mobile w-full text-xs rounded-xl border border-slate-200 px-3 py-2 font-mono focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Email Address</label>
                            <input type="email" name="email" x-model="formData.email" placeholder="customer@example.com" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Saved Delivery Address <span class="text-red-500">*</span></label>
                        <textarea name="address" x-model="formData.address" rows="2" required placeholder="Flat, Building, Street, Landmark..." class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">City <span class="text-red-500">*</span></label>
                            <input type="text" name="city" x-model="formData.city" required placeholder="Satara" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Pincode</label>
                            <input type="text" name="pincode" x-model="formData.pincode" placeholder="415001" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 font-mono focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Status <span class="text-red-500">*</span></label>
                        <select name="status" x-model="formData.status" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="blocked">Blocked</option>
                        </select>
                    </div>

                    <div class="flex justify-end space-x-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="modalOpen = false" class="px-4 py-2 rounded-xl bg-slate-100 text-xs font-semibold text-slate-700">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-xs font-bold text-white shadow-md shadow-emerald-600/20">Save Customer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
    function customerManager() {
        return {
            modalOpen: false,
            isEdit: false,
            formAction: "{{ route('admin.customers.store') }}",
            formData: {
                name: '',
                mobile: '',
                email: '',
                address: '',
                city: 'Satara',
                pincode: '415001',
                status: 'active'
            },
            openCreateModal() {
                this.isEdit = false;
                this.formAction = "{{ route('admin.customers.store') }}";
                this.formData = {
                    name: '',
                    mobile: '',
                    email: '',
                    address: '',
                    city: 'Satara',
                    pincode: '415001',
                    status: 'active'
                };
                this.modalOpen = true;
            },
            openEditModal(c) {
                this.isEdit = true;
                this.formAction = `/admin/customers/${c.id}`;
                this.formData = {
                    name: c.name,
                    mobile: c.mobile,
                    email: c.email || '',
                    address: c.address,
                    city: c.city,
                    pincode: c.pincode || '',
                    status: c.status
                };
                this.modalOpen = true;
            }
        };
    }
</script>
@endsection
