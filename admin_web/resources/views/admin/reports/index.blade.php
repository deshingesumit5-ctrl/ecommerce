@extends('layouts.admin')

@section('title', 'Analytics & Financial Reports')

@section('content')
<div class="space-y-6">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-emerald-600 mb-1">
                <i class="fa-solid fa-chart-pie"></i>
                <span>Business Intelligence & Analytics</span>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">Reports & Analytics</h1>
            <p class="text-xs text-slate-500 mt-0.5">Comprehensive reports across date ranges, store branches, payments and products.</p>
        </div>
        <button onclick="window.print()" class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition shadow-sm flex items-center space-x-2">
            <i class="fa-solid fa-download text-emerald-400"></i>
            <span>Export Analytics Report</span>
        </button>
    </div>

    <!-- DATE RANGE & STORE FILTER -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row items-center justify-between gap-3">
        <form action="{{ route('admin.reports.index') }}" method="GET" class="w-full flex flex-col md:flex-row items-center gap-3">
            
            <div class="flex items-center gap-2 w-full md:w-auto">
                <label class="text-xs font-bold text-slate-700 whitespace-nowrap">From:</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 focus:ring-2 focus:ring-emerald-500">
            </div>

            <div class="flex items-center gap-2 w-full md:w-auto">
                <label class="text-xs font-bold text-slate-700 whitespace-nowrap">To:</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 focus:ring-2 focus:ring-emerald-500">
            </div>

            <div class="flex items-center gap-2 w-full md:w-auto">
                <select name="branch_id" class="text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 focus:ring-2 focus:ring-emerald-500">
                    <option value="all">All Store Branches</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ (string)$branchId === (string)$b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition">Apply Range</button>
            </div>
        </form>
    </div>

    <!-- SUMMARY METRICS -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Total Sales Revenue</span>
            <span class="text-2xl font-black text-slate-900 mt-1 block">₹{{ number_format($totalRevenue, 2) }}</span>
            <span class="text-[11px] text-emerald-600 font-semibold">Paid settlements</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Total Orders</span>
            <span class="text-2xl font-black text-slate-900 mt-1 block">{{ $totalOrders }}</span>
            <span class="text-[11px] text-blue-600 font-semibold">Across selected dates</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Delivered Orders</span>
            <span class="text-2xl font-black text-emerald-600 mt-1 block">{{ $deliveredOrders }}</span>
            <span class="text-[11px] text-emerald-600 font-semibold">{{ $totalOrders > 0 ? round(($deliveredOrders / $totalOrders) * 100, 1) : 0 }}% Fulfillment rate</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Cancelled Orders</span>
            <span class="text-2xl font-black text-rose-600 mt-1 block">{{ $cancelledOrders }}</span>
            <span class="text-[11px] text-slate-400">Rejected / Aborted</span>
        </div>
    </div>

    <!-- SPLIT: Store Summary + Payment Mode & Top Products -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Store Branch Breakdown -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-slate-900 flex items-center space-x-2 pb-2 border-b border-slate-100">
                <i class="fa-solid fa-store text-emerald-600"></i>
                <span>Store / Branch Performance</span>
            </h3>

            <div class="space-y-3">
                @foreach($branchStats as $bs)
                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-slate-900 text-xs block">{{ $bs->name }}</span>
                            <span class="text-[10px] font-mono text-slate-400">{{ $bs->code }} &bull; Radius: {{ $bs->radius_km }} KM</span>
                        </div>
                        <div class="text-right">
                            <span class="font-black text-slate-900 text-xs block">{{ $bs->orders_count }} Orders</span>
                            <span class="text-[10px] text-emerald-600 font-semibold">{{ $bs->status }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Payment Mode Breakdown -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-slate-900 flex items-center space-x-2 pb-2 border-b border-slate-100">
                <i class="fa-solid fa-credit-card text-emerald-600"></i>
                <span>Payment Mode Breakdown</span>
            </h3>

            <div class="space-y-3">
                @forelse($paymentBreakdown as $pb)
                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100 flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $pb->payment_mode === 'COD' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' }}">
                                {{ $pb->payment_mode }}
                            </span>
                            <span class="text-xs font-bold text-slate-700">{{ $pb->count }} transactions</span>
                        </div>
                        <span class="font-black text-slate-900 text-xs">₹{{ number_format($pb->total, 2) }}</span>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 text-center py-4">No payment data in this range.</p>
                @endforelse
            </div>
        </div>

    </div>

    <!-- TOP SELLING PRODUCTS -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 bg-slate-50 border-b border-slate-200">
            <h3 class="text-sm font-bold text-slate-900 flex items-center space-x-2">
                <i class="fa-solid fa-trophy text-amber-500"></i>
                <span>Top Selling Products in Selected Range</span>
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/50 text-[11px] font-bold uppercase text-slate-500 border-b border-slate-100">
                        <th class="py-3 px-4">#</th>
                        <th class="py-3 px-4">Product Name</th>
                        <th class="py-3 px-4 text-center">Units Sold</th>
                        <th class="py-3 px-4 text-right">Total Sales Generated</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($topProducts as $index => $tp)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3 px-4 text-slate-400 font-bold">{{ $index + 1 }}</td>
                            <td class="py-3 px-4 font-bold text-slate-900">{{ $tp->product_name }}</td>
                            <td class="py-3 px-4 text-center font-bold text-slate-700">{{ $tp->total_qty }}</td>
                            <td class="py-3 px-4 text-right font-black text-emerald-700">₹{{ number_format($tp->total_sales, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <x-empty-state title="No Product Sales in Range" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
