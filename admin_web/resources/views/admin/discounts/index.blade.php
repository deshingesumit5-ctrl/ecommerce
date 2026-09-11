@extends('layouts.admin')

@section('title', 'Discount Management')

@section('content')
<div class="space-y-6" x-data="discountManager()">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-emerald-600 mb-1">
                <i class="fa-solid fa-percent"></i>
                <span>Promotions & Campaigns</span>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">Discount Management</h1>
            <p class="text-xs text-slate-500 mt-0.5">Schedule category-wide, festival and seasonal product promotional discounts.</p>
        </div>
        <button type="button" @click="openCreateModal()" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-md shadow-emerald-600/20 flex items-center space-x-2">
            <i class="fa-solid fa-plus"></i>
            <span>Add Promotional Discount</span>
        </button>
    </div>

    <!-- FILTER BAR -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row items-center justify-between gap-3">
        <form action="{{ route('admin.discounts.index') }}" method="GET" class="w-full flex flex-col md:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-xs">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search campaign name..." class="w-full text-xs rounded-xl border border-slate-200 pl-9 pr-3 py-2 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
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
                        <th class="py-3.5 px-4">Campaign Title</th>
                        <th class="py-3.5 px-4">Category</th>
                        <th class="py-3.5 px-4">Discount</th>
                        <th class="py-3.5 px-4">Schedule (Start - End)</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($discounts as $index => $d)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4 text-slate-400">{{ $discounts->firstItem() + $index }}</td>
                            <td class="py-3.5 px-4 font-bold text-slate-900">{{ $d->title }}</td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded-lg bg-slate-100 font-semibold text-slate-700 text-[11px]">
                                    {{ $d->category ? $d->category->name : 'All Categories (Store-wide)' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-bold text-emerald-600">
                                {{ $d->discount_type === 'percentage' ? $d->discount_value . '%' : '₹' . number_format($d->discount_value, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                {{ $d->start_date->format('d M Y') }} &rarr; {{ $d->end_date->format('d M Y') }}
                            </td>
                            <td class="py-3.5 px-4">
                                <form action="{{ route('admin.discounts.toggle', $d->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $d->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                        {{ ucfirst($d->status) }}
                                    </button>
                                </form>
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-1">
                                <button type="button" @click="openEditModal({{ json_encode($d) }})" class="p-1.5 rounded-lg bg-slate-100 hover:bg-emerald-600 hover:text-white text-slate-600 transition">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <form action="{{ route('admin.discounts.destroy', $d->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete discount promotion?')">
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
                                <x-empty-state title="No Discounts Configured" message="Schedule category or store-wide promotional discounts." actionText="Add Discount" actionClick="openCreateModal()" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="md:hidden divide-y divide-slate-100">
            @forelse($discounts as $mD)
                <div class="p-4 flex items-center justify-between gap-3">
                    <div>
                        <span class="font-bold text-slate-900 text-xs block">{{ $mD->title }}</span>
                        <span class="text-[11px] text-emerald-600 font-bold">{{ $mD->discount_value }}% OFF</span>
                    </div>
                    <button type="button" @click="openEditModal({{ json_encode($mD) }})" class="px-3 py-1.5 rounded-lg bg-slate-100 text-xs font-semibold text-slate-700">Edit</button>
                </div>
            @empty
                <div class="p-4"><x-empty-state title="No Discounts" actionText="Add Discount" actionClick="openCreateModal()" /></div>
            @endforelse
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
                        <i class="fa-solid fa-percent text-emerald-600"></i>
                        <span x-text="isEdit ? 'Edit Discount' : 'Create Discount Campaign'"></span>
                    </h3>
                    <button @click="modalOpen = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
                </div>

                <form :action="formAction" method="POST" class="mt-4 space-y-4">
                    @csrf
                    <template x-if="isEdit"><input type="hidden" name="_method" value="PUT"></template>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Campaign Title <span class="text-red-500">*</span></label>
                        <input type="text" name="title" x-model="formData.title" required placeholder="e.g. Monsoon Fresh Veggie Fest" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Target Category</label>
                        <select name="category_id" x-model="formData.category_id" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            <option value="">Store-wide (All Categories)</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
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
                            <input type="number" step="0.01" min="0.01" name="discount_value" x-model="formData.discount_value" required placeholder="10.00" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 font-bold text-emerald-600 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Start Date <span class="text-red-500">*</span></label>
                            <input type="date" name="start_date" x-model="formData.start_date" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">End Date <span class="text-red-500">*</span></label>
                            <input type="date" name="end_date" x-model="formData.end_date" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
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
                        <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-xs font-bold text-white shadow-md shadow-emerald-600/20">Save Campaign</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
    function discountManager() {
        return {
            modalOpen: false,
            isEdit: false,
            formAction: "{{ route('admin.discounts.store') }}",
            formData: {
                title: '',
                category_id: '',
                discount_type: 'percentage',
                discount_value: 10,
                start_date: '{{ now()->toDateString() }}',
                end_date: '{{ now()->addDays(15)->toDateString() }}',
                status: 'active'
            },
            openCreateModal() {
                this.isEdit = false;
                this.formAction = "{{ route('admin.discounts.store') }}";
                this.formData = {
                    title: '',
                    category_id: '',
                    discount_type: 'percentage',
                    discount_value: 10,
                    start_date: '{{ now()->toDateString() }}',
                    end_date: '{{ now()->addDays(15)->toDateString() }}',
                    status: 'active'
                };
                this.modalOpen = true;
            },
            openEditModal(d) {
                this.isEdit = true;
                this.formAction = `/admin/discounts/${d.id}`;
                this.formData = {
                    title: d.title,
                    category_id: d.category_id || '',
                    discount_type: d.discount_type,
                    discount_value: d.discount_value,
                    start_date: d.start_date ? d.start_date.substring(0, 10) : '',
                    end_date: d.end_date ? d.end_date.substring(0, 10) : '',
                    status: d.status
                };
                this.modalOpen = true;
            }
        };
    }
</script>
@endsection
