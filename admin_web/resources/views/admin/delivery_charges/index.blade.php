@extends('layouts.admin')

@section('title', 'Delivery Charge Configuration')

@section('content')
<div class="space-y-6" x-data="deliveryChargeManager()">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-emerald-600 mb-1">
                <i class="fa-solid fa-truck-ramp-box"></i>
                <span>Pricing Rules Engine</span>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">Delivery Charge Configuration</h1>
            <p class="text-xs text-slate-500 mt-0.5">Build free delivery order threshold rules, standard hyperlocal fees and express charges.</p>
        </div>
        <button type="button" @click="openCreateModal()" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-md shadow-emerald-600/20 flex items-center space-x-2">
            <i class="fa-solid fa-plus"></i>
            <span>Add Delivery Rule</span>
        </button>
    </div>

    <!-- RULE EXPLANATION BANNER -->
    <div class="p-4 rounded-2xl bg-emerald-950 text-emerald-200 border border-emerald-800/80 shadow-md flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-lg">
                <i class="fa-solid fa-calculator"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-white">Active Hyperlocal Rule Logic</h3>
                <p class="text-xs text-emerald-300/80 mt-0.5">Orders &ge; <strong class="text-white">₹500.00</strong> &rarr; <span class="text-emerald-400 font-bold uppercase">Free Delivery</span> &bull; Orders &lt; <strong class="text-white">₹500.00</strong> &rarr; <span class="text-white font-bold">₹40.00 Standard Delivery Charge</span></p>
            </div>
        </div>
    </div>

    <!-- TABLE -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200">
                        <th class="py-3.5 px-4">#</th>
                        <th class="py-3.5 px-4">Applicable Store / Branch</th>
                        <th class="py-3.5 px-4">Free Delivery Min Order</th>
                        <th class="py-3.5 px-4">Standard Delivery Charge</th>
                        <th class="py-3.5 px-4">Express Priority Charge</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($configs as $index => $cfg)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4 text-slate-400">{{ $index + 1 }}</td>
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-slate-900 block">
                                    {{ $cfg->branch ? $cfg->branch->name : 'All Hyperlocal Stores (Global Rule)' }}
                                </span>
                                @if($cfg->branch)
                                    <span class="text-[10px] font-mono text-slate-400">{{ $cfg->branch->code }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-black text-emerald-600">
                                ₹{{ number_format($cfg->min_order_free_delivery, 2) }}
                            </td>
                            <td class="py-3.5 px-4 font-bold text-slate-800">
                                ₹{{ number_format($cfg->standard_charge, 2) }}
                            </td>
                            <td class="py-3.5 px-4 font-bold text-indigo-600">
                                ₹{{ number_format($cfg->express_charge, 2) }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $cfg->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                    {{ ucfirst($cfg->status) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-1">
                                <button type="button" @click="openEditModal({{ json_encode($cfg) }})" class="p-1.5 rounded-lg bg-slate-100 hover:bg-emerald-600 hover:text-white text-slate-600 transition">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <form action="{{ route('admin.delivery_charges.destroy', $cfg->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete delivery rule?')">
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
                                <x-empty-state title="No Delivery Rules" actionText="Add Rule" actionClick="openCreateModal()" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL -->
    <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm" @click="modalOpen = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full p-6 border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900 flex items-center space-x-2">
                        <i class="fa-solid fa-truck-ramp-box text-emerald-600"></i>
                        <span x-text="isEdit ? 'Edit Delivery Charge Rule' : 'Configure Delivery Charge Rule'"></span>
                    </h3>
                    <button @click="modalOpen = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
                </div>

                <form :action="formAction" method="POST" class="mt-4 space-y-4">
                    @csrf
                    <template x-if="isEdit"><input type="hidden" name="_method" value="PUT"></template>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Select Store</label>
                        <select name="branch_id" x-model="formData.branch_id" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            <option value="">All Stores (Store-wide)</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Free Delivery Minimum Order Amount (₹) <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" min="0" name="min_order_free_delivery" x-model="formData.min_order_free_delivery" required placeholder="500.00" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 font-bold text-emerald-600 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <span class="text-[10px] text-slate-400 mt-0.5 block">Orders above or equal to this amount get Free Delivery.</span>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Standard Charge (₹) <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" min="0" name="standard_charge" x-model="formData.standard_charge" required placeholder="40.00" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Express Charge (₹) <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" min="0" name="express_charge" x-model="formData.express_charge" required placeholder="70.00" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Status <span class="text-red-500">*</span></label>
                        <select name="status" x-model="formData.status" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <div class="flex justify-end space-x-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="modalOpen = false" class="px-4 py-2 rounded-xl bg-slate-100 text-xs font-semibold text-slate-700">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-xs font-bold text-white shadow-md shadow-emerald-600/20">Save Rule</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
    function deliveryChargeManager() {
        return {
            modalOpen: false,
            isEdit: false,
            formAction: "{{ route('admin.delivery_charges.store') }}",
            formData: {
                branch_id: '',
                min_order_free_delivery: 500,
                standard_charge: 40,
                express_charge: 70,
                status: 'active'
            },
            openCreateModal() {
                this.isEdit = false;
                this.formAction = "{{ route('admin.delivery_charges.store') }}";
                this.formData = {
                    branch_id: '',
                    min_order_free_delivery: 500,
                    standard_charge: 40,
                    express_charge: 70,
                    status: 'active'
                };
                this.modalOpen = true;
            },
            openEditModal(cfg) {
                this.isEdit = true;
                this.formAction = `/admin/delivery-charges/${cfg.id}`;
                this.formData = {
                    branch_id: cfg.branch_id || '',
                    min_order_free_delivery: cfg.min_order_free_delivery,
                    standard_charge: cfg.standard_charge,
                    express_charge: cfg.express_charge,
                    status: cfg.status
                };
                this.modalOpen = true;
            }
        };
    }
</script>
@endsection
