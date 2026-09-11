@extends('layouts.admin')

@section('title', 'Product Master')

@section('content')
<div class="space-y-6" x-data="productMaster()">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-emerald-600 mb-1">
                <i class="fa-solid fa-apple-whole"></i>
                <span>Inventory & Catalog Master</span>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">Product Master</h1>
            <p class="text-xs text-slate-500 mt-0.5">Manage products, base selling prices, units and stock availability.</p>
        </div>
        <button type="button" @click="openCreateModal()" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-md shadow-emerald-600/20 flex items-center space-x-2">
            <i class="fa-solid fa-plus"></i>
            <span>Add New Product</span>
        </button>
    </div>

    <!-- FILTER BAR -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row items-center justify-between gap-3">
        <form action="{{ route('admin.products.index') }}" method="GET" class="w-full flex flex-col md:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-xs">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search product name, unit, sku..." class="w-full text-xs rounded-xl border border-slate-200 pl-9 pr-3 py-2 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>

            <div class="flex items-center gap-2 w-full md:w-auto flex-wrap">
                <select name="category_id" class="text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <option value="all">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ (string)request('category_id') === (string)$cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>

                <select name="stock" class="text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <option value="all">All Stock</option>
                    <option value="in_stock" {{ request('stock') === 'in_stock' ? 'selected' : '' }}>In Stock</option>
                    <option value="out_of_stock" {{ request('stock') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
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

    <!-- PRODUCTS TABLE -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200">
                        <th class="py-3.5 px-4">#</th>
                        <th class="py-3.5 px-4">Thumbnail</th>
                        <th class="py-3.5 px-4">Product Name & Hierarchy</th>
                        <th class="py-3.5 px-4">Unit</th>
                        <th class="py-3.5 px-4">Base Price</th>
                        <th class="py-3.5 px-4">Today's Daily Price</th>
                        <th class="py-3.5 px-4">Stock</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($products as $index => $prod)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4 text-slate-400">{{ $products->firstItem() + $index }}</td>
                            <td class="py-3.5 px-4">
                                <img src="{{ $prod->image ?: 'https://images.unsplash.com/photo-1546094096-0df4bcaaa337?w=100' }}" class="w-10 h-10 rounded-xl object-cover border border-slate-200 shadow-sm">
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-slate-900 block">{{ $prod->name }}</span>
                                <span class="text-[10px] text-slate-400">
                                    {{ $prod->category->name ?? 'Category' }} &bull; {{ $prod->subCategory->name ?? 'Subcategory' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-mono font-semibold text-slate-700">
                                {{ $prod->unit }}
                            </td>
                            <td class="py-3.5 px-4 font-medium text-slate-500">
                                ₹{{ number_format($prod->base_price, 2) }}
                            </td>
                            <td class="py-3.5 px-4 font-bold text-emerald-600">
                                ₹{{ number_format($prod->current_daily_price, 2) }}
                            </td>
                            <td class="py-3.5 px-4">
                                <form action="{{ route('admin.products.toggle_stock', $prod->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $prod->in_stock ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                        {{ $prod->in_stock ? 'In Stock' : 'Out of Stock' }}
                                    </button>
                                </form>
                            </td>
                            <td class="py-3.5 px-4">
                                <form action="{{ route('admin.products.toggle', $prod->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $prod->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                        {{ ucfirst($prod->status) }}
                                    </button>
                                </form>
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-1">
                                <button type="button" @click="openEditModal({{ json_encode($prod) }})" class="p-1.5 rounded-lg bg-slate-100 hover:bg-emerald-600 hover:text-white text-slate-600 transition">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <form action="{{ route('admin.products.destroy', $prod->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete product?')">
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
                                <x-empty-state title="No Products Found" message="Add products to your catalog with units and daily pricing." actionText="Add Product" actionClick="openCreateModal()" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

                    <!-- Mobile Cards -->
        <div class="md:hidden divide-y divide-slate-100">
            @forelse($products as $mProd)
                <div class="p-4 flex items-center justify-between gap-3">
                    <div class="flex items-center space-x-3 min-w-0">
                        <img src="{{ $mProd->image ?: 'https://images.unsplash.com/photo-1546094096-0df4bcaaa337?w=100' }}" class="w-12 h-12 rounded-xl object-cover border border-slate-200 flex-shrink-0">
                        <div class="min-w-0">
                            <span class="font-bold text-slate-900 text-xs block truncate">{{ $mProd->name }}</span>
                            <span class="text-[11px] font-bold text-emerald-600">₹{{ number_format($mProd->current_daily_price, 2) }} / {{ $mProd->unit }}</span>
                            <span class="block text-[10px] text-slate-400">{{ $mProd->in_stock ? 'In Stock' : 'Out of Stock' }}</span>
                        </div>
                    </div>
                    <div class="flex items-center space-x-1">
                        <button type="button" @click="openEditModal({{ json_encode($mProd) }})" class="p-2 rounded-lg bg-slate-100 hover:bg-emerald-600 hover:text-white text-slate-600 transition">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                        <form action="{{ route('admin.products.destroy', $mProd->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete product?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 rounded-lg bg-slate-100 hover:bg-red-600 hover:text-white text-slate-600 transition">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="p-4"><x-empty-state title="No Products" actionText="Add Product" actionClick="openCreateModal()" /></div>
            @endforelse
        </div>

        @if($products->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50">{{ $products->links() }}</div>
        @endif

    </div>

    <!-- ADD / EDIT MODAL -->
    <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm" @click="modalOpen = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full p-6 border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900 flex items-center space-x-2">
                        <i class="fa-solid fa-apple-whole text-emerald-600"></i>
                        <span x-text="isEdit ? 'Edit Product' : 'Add New Product'"></span>
                    </h3>
                    <button @click="modalOpen = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
                </div>

                <form :action="formAction" method="POST" enctype="multipart/form-data" class="mt-4 space-y-4">
                    @csrf
                    <template x-if="isEdit"><input type="hidden" name="_method" value="PUT"></template>
                    <input type="hidden" name="remove_image" :value="removeImage">

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Product Title / Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" x-model="formData.name" required placeholder="e.g. Farm Fresh Tomatoes" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Category <span class="text-red-500">*</span></label>
                            <select name="category_id" x-model="formData.category_id" @change="fetchSubcategories()" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Sub-Category</label>
                            <select name="sub_category_id" x-model="formData.sub_category_id" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                                <option value="">Select Sub-Category</option>
                                <template x-for="sub in dynamicSubcategories" :key="sub.id">
                                    <option :value="sub.id" x-text="sub.name" :selected="sub.id == formData.sub_category_id"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Unit Measure <span class="text-red-500">*</span></label>
                            <select name="unit" x-model="formData.unit" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                                <option value="kg">kg (Kilogram)</option>
                                <option value="gm">gm (Grams)</option>
                                <option value="liter">liter (Liter)</option>
                                <option value="pcs">pcs (Pieces)</option>
                                <option value="bunch">bunch</option>
                                <option value="dozen">dozen</option>
                                <option value="pack">pack</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Base Price (₹) <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" min="0.01" name="base_price" x-model="formData.base_price" required placeholder="45.00" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Daily Price (₹) <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" min="0.01" name="current_daily_price" x-model="formData.current_daily_price" required placeholder="38.00" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 font-bold text-emerald-600 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>
                    </div>

                    <!-- IMAGE UPLOAD & PREVIEW SECTION -->
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-semibold text-slate-700">Product Image</label>
                            <button type="button" @click="$refs.imageFileInput.click()" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition flex items-center space-x-1.5 shadow-sm">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                                <span>Upload Image</span>
                            </button>
                        </div>

                        <!-- Hidden File Input -->
                        <input type="file" name="image_file" x-ref="imageFileInput" accept="image/*" @change="handleFileSelect($event)" class="hidden">

                        <!-- Preview Card with Pencil Edit and Trash Delete Icons -->
                        <div x-show="imagePreview" class="relative mt-2 p-2 bg-white rounded-xl border border-slate-200 flex items-center justify-between shadow-sm">
                            <div class="flex items-center space-x-3 overflow-hidden">
                                <img :src="imagePreview" alt="Product Preview" class="w-14 h-14 rounded-lg object-cover border border-slate-200 shadow-sm flex-shrink-0">
                                <div class="min-w-0">
                                    <span class="text-xs font-bold text-slate-800 block truncate" x-text="fileName || 'Product Image'"></span>
                                    <span class="text-[10px] text-emerald-600 font-medium">Ready to save</span>
                                </div>
                            </div>
                            <!-- Pencil Edit and Trash Delete Icons that both work -->
                            <div class="flex items-center space-x-1.5 flex-shrink-0">
                                <button type="button" @click="$refs.imageFileInput.click()" title="Edit Image" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-emerald-600 hover:text-white text-slate-600 transition flex items-center justify-center">
                                    <i class="fa-solid fa-pen text-xs"></i>
                                </button>
                                <button type="button" @click="deleteImage()" title="Delete Image" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-rose-600 hover:text-white text-slate-600 transition flex items-center justify-center">
                                    <i class="fa-solid fa-trash text-xs"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Fallback URL Input -->
                        <div class="pt-1">
                            <div class="text-[10px] text-slate-400 mb-1 flex items-center space-x-1">
                                <span>Or paste external image URL:</span>
                            </div>
                            <input type="url" name="image" x-model="formData.image" @input="onUrlInput()" placeholder="https://images.unsplash.com/..." class="w-full text-xs rounded-xl border border-slate-200 px-3 py-1.5 bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Description</label>
                        <textarea name="description" x-model="formData.description" rows="2" placeholder="Fresh produce highlights..." class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-3 pt-1">
                        <label class="flex items-center space-x-2 text-xs text-slate-700 font-semibold cursor-pointer">
                            <input type="checkbox" name="in_stock" value="1" x-model="formData.in_stock" class="rounded text-emerald-600 focus:ring-emerald-500">
                            <span>Available in Stock</span>
                        </label>
                        <div>
                            <select name="status" x-model="formData.status" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="modalOpen = false" class="px-4 py-2 rounded-xl bg-slate-100 text-xs font-semibold text-slate-700">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-xs font-bold text-white shadow-md shadow-emerald-600/20">Save Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
    function productMaster() {
        return {
            modalOpen: false,
            isEdit: false,
            formAction: "{{ route('admin.products.store') }}",
            dynamicSubcategories: [],
            imagePreview: '',
            fileName: '',
            removeImage: '0',
            formData: {
                name: '',
                category_id: '{{ $categories->first()?->id ?? 1 }}',
                sub_category_id: '',
                unit: 'kg',
                base_price: 50.00,
                current_daily_price: 45.00,
                image: '',
                description: '',
                in_stock: true,
                status: 'active'
            },
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
            onUrlInput() {
                if (this.formData.image) {
                    this.imagePreview = this.formData.image;
                    this.fileName = 'External URL Image';
                    this.removeImage = '0';
                }
            },
            async fetchSubcategories() {
                try {
                    const res = await fetch(`/admin/categories/${this.formData.category_id}/subcategories-json`);
                    this.dynamicSubcategories = await res.json();
                } catch(e) {
                    this.dynamicSubcategories = [];
                }
            },
            openCreateModal() {
                this.isEdit = false;
                this.formAction = "{{ route('admin.products.store') }}";
                this.imagePreview = '';
                this.fileName = '';
                this.removeImage = '0';
                if (this.$refs.imageFileInput) {
                    this.$refs.imageFileInput.value = '';
                }
                this.formData = {
                    name: '',
                    category_id: '{{ $categories->first()?->id ?? 1 }}',
                    sub_category_id: '',
                    unit: 'kg',
                    base_price: 50.00,
                    current_daily_price: 45.00,
                    image: '',
                    description: '',
                    in_stock: true,
                    status: 'active'
                };
                this.fetchSubcategories();
                this.modalOpen = true;
            },
            openEditModal(prod) {
                this.isEdit = true;
                this.formAction = `/admin/products/${prod.id}`;
                this.imagePreview = prod.image || '';
                this.fileName = prod.image ? 'Current Image' : '';
                this.removeImage = '0';
                if (this.$refs.imageFileInput) {
                    this.$refs.imageFileInput.value = '';
                }
                this.formData = {
                    name: prod.name,
                    category_id: prod.category_id,
                    sub_category_id: prod.sub_category_id || '',
                    unit: prod.unit,
                    base_price: prod.base_price,
                    current_daily_price: prod.current_daily_price,
                    image: prod.image || '',
                    description: prod.description || '',
                    in_stock: Boolean(prod.in_stock),
                    status: prod.status
                };
                this.fetchSubcategories();
                this.modalOpen = true;
            }
        };
    }
</script>
@endsection
