@extends('layouts.admin')

@section('title', 'Daily Price Management')

@section('content')
<div class="space-y-6">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-emerald-600 mb-1">
                <i class="fa-solid fa-chart-line"></i>
                <span>Hyperlocal Market Pricing Console</span>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">Daily Price Management</h1>
            <p class="text-xs text-slate-500 mt-0.5">Adjust today's market selling prices per store with single-click bulk update.</p>
        </div>
        <div class="flex items-center space-x-2">
            <span class="text-xs font-bold text-slate-600 bg-slate-100 px-3 py-2 rounded-xl">
                <i class="fa-solid fa-calendar-day text-emerald-600 mr-1.5"></i> Today: {{ now()->format('d M Y') }}
            </span>
        </div>
    </div>

    <!-- FILTER & STORE SELECTOR -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row items-center justify-between gap-3">
        <form action="{{ route('admin.daily_prices.index') }}" method="GET" class="w-full flex flex-col md:flex-row items-center gap-3">
            
            <div class="flex items-center space-x-2 w-full md:w-auto">
                <label class="text-xs font-bold text-slate-700 whitespace-nowrap"><i class="fa-solid fa-store mr-1 text-slate-400"></i> Store:</label>
                <select name="branch_id" onchange="this.form.submit()" class="text-xs font-bold text-emerald-700 rounded-xl border border-slate-200 bg-emerald-50 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ (string)$selectedBranchId === (string)$b->id ? 'selected' : '' }}>{{ $b->name }} ({{ $b->code }})</option>
                    @endforeach
                </select>
            </div>

            <div class="relative flex-1 w-full">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-xs">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search product..." class="w-full text-xs rounded-xl border border-slate-200 pl-9 pr-3 py-2 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>

            <div class="flex items-center gap-2 w-full md:w-auto">
                <select name="category_id" class="text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <option value="all">All Categories</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}" {{ (string)$selectedCategoryId === (string)$c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>

                <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition">Filter</button>
            </div>

        </form>
    </div>

    <!-- DAILY PRICING MATRIX FORM -->
    <form action="{{ route('admin.daily_prices.bulk_update') }}" method="POST">
        @csrf
        <input type="hidden" name="branch_id" value="{{ $selectedBranchId }}">

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            
            <div class="p-4 bg-slate-50/80 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Today's Price Matrix for {{ $branches->firstWhere('id', $selectedBranchId)?->name ?? 'Store' }}</h3>
                    <p class="text-xs text-slate-500">Edit any prices and click "1-Click Save All Prices" or save individually.</p>
                </div>
                <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-md shadow-emerald-600/20 flex items-center space-x-2">
                    <i class="fa-solid fa-check-double"></i>
                    <span>1-Click Save All Daily Prices</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200">
                            <th class="py-3.5 px-4">#</th>
                            <th class="py-3.5 px-4">Product</th>
                            <th class="py-3.5 px-4">Category</th>
                            <th class="py-3.5 px-4">Unit Measure</th>
                            <th class="py-3.5 px-4">Catalog Base Price</th>
                            <th class="py-3.5 px-4">Today's Selling Price (₹)</th>
                            <th class="py-3.5 px-4 text-right">Instant Save</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse($products as $index => $prod)
                            @php
                                $branchDailyPrice = $prod->dailyPrices->firstWhere('branch_id', $selectedBranchId)?->price ?? $prod->current_daily_price;
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-4 text-slate-400">{{ $products->firstItem() + $index }}</td>
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center space-x-3">
                                        <img src="{{ $prod->image ?: 'https://images.unsplash.com/photo-1546094096-0df4bcaaa337?w=100' }}" class="w-9 h-9 rounded-xl object-cover border border-slate-200 shadow-sm">
                                        <div>
                                            <span class="font-bold text-slate-900 block">{{ $prod->name }}</span>
                                            <span class="text-[10px] font-mono text-slate-400">{{ $prod->slug }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2 py-0.5 rounded-lg bg-slate-100 font-semibold text-slate-700 text-[11px]">
                                        {{ $prod->category->name ?? 'Category' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 font-mono font-bold text-slate-700">
                                    {{ $prod->unit }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-500 font-medium">
                                    ₹{{ number_format($prod->base_price, 2) }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center space-x-1 max-w-[140px]">
                                        <span class="font-bold text-slate-500 text-xs">₹</span>
                                        <input type="number" step="0.01" min="0.01" name="prices[{{ $prod->id }}]" id="price_input_{{ $prod->id }}" value="{{ $branchDailyPrice }}" class="w-full text-xs font-bold text-slate-900 bg-emerald-50/40 border border-emerald-300 rounded-lg px-2.5 py-1.5 focus:ring-2 focus:ring-emerald-500 focus:bg-white">
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <button type="button" onclick="saveSinglePrice({{ $prod->id }}, {{ $selectedBranchId }})" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-emerald-600 hover:text-white text-slate-700 text-xs font-bold transition shadow-sm">
                                        <i class="fa-solid fa-check mr-1 text-emerald-500"></i> Save
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <x-empty-state title="No Active Products for Pricing" message="Add active products to manage daily market prices." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($products->count() > 0)
                <div class="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
                    <div>{{ $products->links() }}</div>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-md shadow-emerald-600/20 flex items-center space-x-2">
                        <i class="fa-solid fa-bolt"></i>
                        <span>1-Click Save All Daily Prices</span>
                    </button>
                </div>
            @endif

        </div>
    </form>

</div>

<script>
    async function saveSinglePrice(productId, branchId) {
        const input = document.getElementById(`price_input_${productId}`);
        const price = input.value;
        if (!price || price <= 0) {
            alert('Please enter a valid positive price.');
            return;
        }

        try {
            const res = await fetch("{{ route('admin.daily_prices.update_single') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    product_id: productId,
                    branch_id: branchId,
                    price: price
                })
            });
            const data = await res.json();
            if (data.success) {
                input.classList.add('bg-emerald-100');
                setTimeout(() => input.classList.remove('bg-emerald-100'), 1200);
            }
        } catch (e) {
            alert('Failed to update daily price.');
        }
    }
</script>
@endsection
