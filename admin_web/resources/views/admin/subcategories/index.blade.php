@extends('layouts.admin')

@section('title', 'Sub-Category Master')

@section('content')
<div class="space-y-6" x-data="subcategoryMaster()">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-emerald-600 mb-1">
                <i class="fa-solid fa-sitemap"></i>
                <span>Catalog Master</span>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">Sub-Category Master</h1>
            <p class="text-xs text-slate-500 mt-0.5">Organize sub-categories linked dynamically to parent category trees.</p>
        </div>
        <button type="button" @click="openCreateModal()" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-md shadow-emerald-600/20 flex items-center space-x-2">
            <i class="fa-solid fa-plus"></i>
            <span>Add Sub-Category</span>
        </button>
    </div>

    <!-- FILTER BAR -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row items-center justify-between gap-3">
        <form action="{{ route('admin.subcategories.index') }}" method="GET" class="w-full flex flex-col md:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-xs">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search sub-category name..." class="w-full text-xs rounded-xl border border-slate-200 pl-9 pr-3 py-2 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>

            <div class="flex items-center gap-2 w-full md:w-auto">
                <select name="category_id" class="text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <option value="all">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ (string)request('category_id') === (string)$cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
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
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200">
                        <th class="py-3.5 px-4">#</th>
                        <th class="py-3.5 px-4">Thumbnail</th>
                        <th class="py-3.5 px-4">Sub-Category Name</th>
                        <th class="py-3.5 px-4">Parent Category</th>
                        <th class="py-3.5 px-4">Products</th>
                        <th class="py-3.5 px-4">Order</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($subCategories as $index => $sub)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4 text-slate-400">{{ $subCategories->firstItem() + $index }}</td>
                            <td class="py-3.5 px-4">
                                <img src="{{ $sub->image ?: 'https://images.unsplash.com/photo-1576045057995-568f588f82fb?w=100' }}" class="w-10 h-10 rounded-xl object-cover border border-slate-200 shadow-sm">
                            </td>
                            <td class="py-3.5 px-4 font-bold text-slate-900">{{ $sub->name }}</td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded-lg bg-slate-100 font-bold text-slate-700 text-[11px]">{{ $sub->category->name ?? 'None' }}</span>
                            </td>
                            <td class="py-3.5 px-4 font-bold text-emerald-600">{{ $sub->products_count }} Products</td>
                            <td class="py-3.5 px-4 font-bold text-slate-700">{{ $sub->display_order }}</td>
                            <td class="py-3.5 px-4">
                                <form action="{{ route('admin.subcategories.toggle', $sub->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $sub->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                        {{ ucfirst($sub->status) }}
                                    </button>
                                </form>
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-1">
                                <button type="button" @click="openEditModal({{ json_encode($sub) }})" class="p-1.5 rounded-lg bg-slate-100 hover:bg-emerald-600 hover:text-white text-slate-600 transition">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <form action="{{ route('admin.subcategories.destroy', $sub->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete sub-category?')">
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
                            <td colspan="8">
                                <x-empty-state title="No Sub-Categories Found" message="Create sub-categories linked to your parent categories." actionText="Add Sub-Category" actionClick="openCreateModal()" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards -->
        <div class="md:hidden divide-y divide-slate-100">
            @forelse($subCategories as $mSub)
                <div class="p-4 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <span class="font-bold text-slate-900 text-xs block truncate">{{ $mSub->name }}</span>
                        <span class="text-[10px] text-emerald-600 font-bold">{{ $mSub->category->name ?? 'Category' }}</span>
                    </div>
                    <button type="button" @click="openEditModal({{ json_encode($mSub) }})" class="px-3 py-1.5 rounded-lg bg-slate-100 text-xs font-semibold text-slate-700">Edit</button>
                </div>
            @empty
                <div class="p-4"><x-empty-state title="No Sub-Categories" actionText="Add Sub-Category" actionClick="openCreateModal()" /></div>
            @endforelse
        </div>

        @if($subCategories->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50">{{ $subCategories->links() }}</div>
        @endif

    </div>

    <!-- MODAL -->
    <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm" @click="modalOpen = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full p-6 border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900 flex items-center space-x-2">
                        <i class="fa-solid fa-sitemap text-emerald-600"></i>
                        <span x-text="isEdit ? 'Edit Sub-Category' : 'Add New Sub-Category'"></span>
                    </h3>
                    <button @click="modalOpen = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
                </div>

                <form :action="formAction" method="POST" enctype="multipart/form-data" class="mt-4 space-y-4">
                    @csrf
                    <template x-if="isEdit"><input type="hidden" name="_method" value="PUT"></template>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Parent Category <span class="text-red-500">*</span></label>
                        <select name="category_id" x-model="formData.category_id" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Sub-Category Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" x-model="formData.name" required placeholder="e.g. Leafy Greens & Herbs" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>

                    <!-- IMAGE UPLOAD & PREVIEW SECTION (REPLACED URL FIELD) -->
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-semibold text-slate-700">Sub-Category Photo</label>
                            <button type="button" @click="$refs.imageFileInput.click()" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition flex items-center space-x-1.5 shadow-sm">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                                <span>Upload Photo</span>
                            </button>
                        </div>

                        <!-- Hidden File & State Inputs -->
                        <input type="file" name="image_file" x-ref="imageFileInput" accept="image/*" @change="handleFileSelect($event)" class="hidden">
                        <input type="hidden" name="image" :value="formData.image">
                        <input type="hidden" name="remove_image" :value="removeImage">

                        <!-- Preview Card with Pencil Edit and Trash Delete Icons -->
                        <div x-show="imagePreview" class="relative mt-2 p-2 bg-white rounded-xl border border-slate-200 flex items-center justify-between shadow-sm">
                            <div class="flex items-center space-x-3 overflow-hidden">
                                <img :src="imagePreview" alt="Sub-Category Preview" class="w-14 h-14 rounded-lg object-cover border border-slate-200 shadow-sm flex-shrink-0">
                                <div class="min-w-0">
                                    <span class="text-xs font-bold text-slate-800 block truncate" x-text="fileName || 'Sub-Category Image'"></span>
                                    <span class="text-[10px] text-emerald-600 font-medium">Ready to save</span>
                                </div>
                            </div>
                            <!-- Pencil Edit and Trash Delete Icons -->
                            <div class="flex items-center space-x-1.5 flex-shrink-0">
                                <button type="button" @click="$refs.imageFileInput.click()" title="Edit Image" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-emerald-600 hover:text-white text-slate-600 transition flex items-center justify-center">
                                    <i class="fa-solid fa-pen text-xs"></i>
                                </button>
                                <button type="button" @click="deleteImage()" title="Delete Image" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-rose-600 hover:text-white text-slate-600 transition flex items-center justify-center">
                                    <i class="fa-solid fa-trash text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Display Order <span class="text-red-500">*</span></label>
                            <input type="number" min="0" name="display_order" x-model="formData.display_order" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Status <span class="text-red-500">*</span></label>
                            <select name="status" x-model="formData.status" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="modalOpen = false" class="px-4 py-2 rounded-xl bg-slate-100 text-xs font-semibold text-slate-700">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-xs font-bold text-white shadow-md shadow-emerald-600/20">Save Sub-Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
    function subcategoryMaster() {
        return {
            modalOpen: false,
            isEdit: false,
            formAction: "{{ route('admin.subcategories.store') }}",
            imagePreview: '',
            fileName: '',
            removeImage: '0',
            formData: { category_id: '{{ $categories->first()?->id ?? 1 }}', name: '', image: '', display_order: 1, status: 'active' },
            handleFileSelect(e) {
                const file = e.target.files[0];
                if (file) {
                    this.fileName = file.name;
                    this.removeImage = '0';
                    const reader = new FileReader();
                    reader.onload = (event) => {
                        this.imagePreview = event.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            },
            deleteImage() {
                this.imagePreview = '';
                this.fileName = '';
                this.formData.image = '';
                this.removeImage = '1';
                if (this.$refs.imageFileInput) {
                    this.$refs.imageFileInput.value = '';
                }
            },
            openCreateModal() {
                this.isEdit = false;
                this.formAction = "{{ route('admin.subcategories.store') }}";
                this.imagePreview = '';
                this.fileName = '';
                this.removeImage = '0';
                if (this.$refs.imageFileInput) {
                    this.$refs.imageFileInput.value = '';
                }
                this.formData = { category_id: '{{ $categories->first()?->id ?? 1 }}', name: '', image: '', display_order: 1, status: 'active' };
                this.modalOpen = true;
            },
            openEditModal(sub) {
                this.isEdit = true;
                this.formAction = `/admin/subcategories/${sub.id}`;
                this.imagePreview = sub.image || '';
                this.fileName = sub.image ? 'Current Sub-Category Image' : '';
                this.removeImage = '0';
                if (this.$refs.imageFileInput) {
                    this.$refs.imageFileInput.value = '';
                }
                this.formData = { category_id: sub.category_id, name: sub.name, image: sub.image || '', display_order: sub.display_order, status: sub.status };
                this.modalOpen = true;
            }
        };
    }
</script>
@endsection
