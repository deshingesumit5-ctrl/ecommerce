@extends('layouts.admin')

@section('title', 'Coupon Management')

@section('content')
<div class="space-y-6" x-data="couponManager()">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-emerald-600 mb-1">
                <i class="fa-solid fa-ticket"></i>
                <span>Promotions & Loyalty</span>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">Coupon Management</h1>
            <p class="text-xs text-slate-500 mt-0.5">Generate coupon codes, configure % or flat ₹ discounts.</p>
        </div>
        <button type="button" @click="openCreateModal()" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-md shadow-emerald-600/20 flex items-center space-x-2">
            <i class="fa-solid fa-plus"></i>
            <span>Create New Coupon</span>
        </button>
    </div>

    <!-- FILTER BAR -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row items-center justify-between gap-3">
        <form action="{{ route('admin.coupons.index') }}" method="GET" class="w-full flex flex-col md:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-xs">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search coupon code (e.g. WELCOME50)..." class="w-full text-xs rounded-xl border border-slate-200 pl-9 pr-3 py-2 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 uppercase font-mono focus:outline-none">
            </div>

            <div class="flex items-center gap-2 w-full md:w-auto">
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
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200">
                        <th class="py-3.5 px-4">#</th>
                        <th class="py-3.5 px-4">Coupon Code</th>
                        <th class="py-3.5 px-4">Discount Value</th>
                        <th class="py-3.5 px-4">Min Order Amount</th>
                        <th class="py-3.5 px-4">Max Cap</th>
                        <th class="py-3.5 px-4">Usage (Used / Limit)</th>
                        <th class="py-3.5 px-4">Validity</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($coupons as $index => $c)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4 text-slate-400">{{ $coupons->firstItem() + $index }}</td>
                            <td class="py-3.5 px-4">
                                <span class="font-mono font-extrabold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-1 rounded-lg text-xs">{{ $c->code }}</span>
                                <span class="block text-[11px] text-slate-500 mt-1 truncate max-w-xs">{{ $c->description }}</span>
                            </td>
                            <td class="py-3.5 px-4 font-bold text-slate-900">
                                {{ $c->discount_type === 'percentage' ? $c->discount_value . '%' : '₹' . number_format($c->discount_value, 2) }}
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-700">
                                ₹{{ number_format($c->min_order_amount, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                {{ $c->max_discount_amount ? '₹' . number_format($c->max_discount_amount, 2) : 'No Cap' }}
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-700">
                                {{ $c->times_used }} / {{ $c->usage_limit }}
                            </td>
                            <td class="py-3.5 px-4 text-[11px] text-slate-500">
                                <span>{{ $c->start_date ? $c->start_date->format('d M') : 'Any' }} - {{ $c->end_date ? $c->end_date->format('d M Y') : 'Ongoing' }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <form action="{{ route('admin.coupons.toggle', $c->id) }}" method="POST">
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
                                <form action="{{ route('admin.coupons.destroy', $c->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete coupon?')">
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
                                <x-empty-state title="No Coupons Found" message="Create coupons to incentivize checkout discounts." actionText="Create Coupon" actionClick="openCreateModal()" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards -->
        <div class="md:hidden divide-y divide-slate-100">
            @forelse($coupons as $mC)
                <div class="p-4 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-mono font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded text-xs">{{ $mC->code }}</span>
                        <span class="font-bold text-slate-900 text-xs">{{ $mC->discount_type === 'percentage' ? $mC->discount_value . '%' : '₹' . $mC->discount_value }} OFF</span>
                    </div>
                    <p class="text-xs text-slate-500">{{ $mC->description }}</p>
                    <div class="flex items-center justify-between text-[11px] text-slate-400 pt-1">
                        <span>Min Order: ₹{{ $mC->min_order_amount }}</span>
                        <button type="button" @click="openEditModal({{ json_encode($mC) }})" class="px-3 py-1 rounded-lg bg-slate-100 text-slate-700 font-semibold">Edit</button>
                    </div>
                </div>
            @empty
                <div class="p-4"><x-empty-state title="No Coupons" actionText="Create Coupon" actionClick="openCreateModal()" /></div>
            @endforelse
        </div>

        @if($coupons->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50">{{ $coupons->links() }}</div>
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
                        <i class="fa-solid fa-ticket text-emerald-600"></i>
                        <span x-text="isEdit ? 'Edit Coupon' : 'Create Coupon'"></span>
                    </h3>
                    <button @click="modalOpen = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
                </div>

                <form :action="formAction" method="POST" class="mt-4 space-y-4">
                    @csrf
                    <template x-if="isEdit"><input type="hidden" name="_method" value="PUT"></template>

                    <div class="flex items-center space-x-2">
                        <div class="flex-1">
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Coupon Code <span class="text-red-500">*</span></label>
                            <input type="text" name="code" x-model="formData.code" required placeholder="WELCOME50" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 font-mono uppercase font-bold text-emerald-700 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>
                        <button type="button" @click="generateRandomCode()" class="mt-5 px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center space-x-1">
                            <i class="fa-solid fa-wand-magic-sparkles text-emerald-600"></i>
                            <span>Generate</span>
                        </button>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Coupon Description</label>
                        <input type="text" name="description" x-model="formData.description" placeholder="e.g. 50% discount up to ₹100 on first purchase" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Discount Type <span class="text-red-500">*</span></label>
                            <select name="discount_type" x-model="formData.discount_type" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                                <option value="percentage">Percentage (%)</option>
                                <option value="fixed">Fixed Amount (₹)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Discount Value <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" min="0.01" name="discount_value" x-model="formData.discount_value" required placeholder="50.00" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 font-bold text-emerald-600 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Min Order Amount (₹) <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" min="0" name="min_order_amount" x-model="formData.min_order_amount" required placeholder="200.00" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Max Discount Cap (₹)</label>
                            <input type="number" step="0.01" min="0" name="max_discount_amount" x-model="formData.max_discount_amount" placeholder="100.00 (Optional for %)" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Usage Limit <span class="text-red-500">*</span></label>
                            <input type="number" min="1" name="usage_limit" x-model="formData.usage_limit" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Start Date</label>
                            <input type="date" name="start_date" x-model="formData.start_date" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">End Date</label>
                            <input type="date" name="end_date" x-model="formData.end_date" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
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
                        <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-xs font-bold text-white shadow-md shadow-emerald-600/20">Save Coupon</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
    function couponManager() {
        return {
            modalOpen: false,
            isEdit: false,
            formAction: "{{ route('admin.coupons.store') }}",
            formData: {
                code: '',
                description: '',
                discount_type: 'percentage',
                discount_value: 50,
                min_order_amount: 200,
                max_discount_amount: 100,
                usage_limit: 100,
                start_date: '',
                end_date: '',
                status: 'active'
            },
            generateRandomCode() {
                const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
                let code = 'SAVE';
                for (let i = 0; i < 4; i++) {
                    code += chars.charAt(Math.floor(Math.random() * chars.length));
                }
                this.formData.code = code;
            },
            openCreateModal() {
                this.isEdit = false;
                this.formAction = "{{ route('admin.coupons.store') }}";
                this.formData = {
                    code: 'HYPER' + Math.floor(Math.random() * 90 + 10),
                    description: '',
                    discount_type: 'percentage',
                    discount_value: 20,
                    min_order_amount: 300,
                    max_discount_amount: 100,
                    usage_limit: 200,
                    start_date: '',
                    end_date: '',
                    status: 'active'
                };
                this.modalOpen = true;
            },
            openEditModal(c) {
                this.isEdit = true;
                this.formAction = `/admin/coupons/${c.id}`;
                this.formData = {
                    code: c.code,
                    description: c.description || '',
                    discount_type: c.discount_type,
                    discount_value: c.discount_value,
                    min_order_amount: c.min_order_amount,
                    max_discount_amount: c.max_discount_amount || '',
                    usage_limit: c.usage_limit,
                    start_date: c.start_date ? c.start_date.substring(0, 10) : '',
                    end_date: c.end_date ? c.end_date.substring(0, 10) : '',
                    status: c.status
                };
                this.modalOpen = true;
            }
        };
    }
</script>
@endsection
