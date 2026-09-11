@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">

    <!-- DASHBOARD TOP BAR: Greeting, Active Store context, Quick Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-emerald-600 mb-1">
                <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                <span>Hyperlocal Operations Live &bull; {{ now()->format('l, d M Y') }}</span>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">Operations Command Center</h1>
            <p class="text-xs text-slate-500 mt-0.5">Real-time overview for <span class="font-bold text-slate-700">{{ $branchId === 'all' ? 'All Operating Stores' : ($branches->firstWhere('id', $branchId)?->name ?? 'Store') }}</span>.</p>
        </div>

        <div class="flex items-center flex-wrap gap-2.5">
            <a href="{{ route('admin.orders.index') }}" class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition shadow-sm flex items-center space-x-2">
                <i class="fa-solid fa-cart-shopping text-emerald-400"></i>
                <span>Live Orders</span>
            </a>
            <a href="{{ route('admin.daily_prices.index') }}" class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition shadow-sm shadow-emerald-600/20 flex items-center space-x-2">
                <i class="fa-solid fa-bolt"></i>
                <span>Daily Price Matrix</span>
            </a>
            <a href="{{ route('admin.branches.radius') }}" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center space-x-2">
                <i class="fa-solid fa-map-location-dot text-slate-500"></i>
                <span>Coverage Map</span>
            </a>
        </div>
    </div>

    <!-- KPI STATS CARDS (Clickable: directly navigate to corresponding master/module) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Card 1: Today's Revenue -->
        <a href="{{ route('admin.payments.index') }}" class="group bg-white p-5 rounded-2xl border border-slate-200 hover:border-emerald-500 hover:shadow-lg transition flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Today's Revenue</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-base group-hover:bg-emerald-600 group-hover:text-white transition">
                    <i class="fa-solid fa-indian-rupee-sign"></i>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-black text-slate-900 tracking-tight">₹{{ number_format($todaySales, 2) }}</span>
                <div class="flex items-center space-x-1.5 text-[11px] font-semibold text-emerald-600 mt-1">
                    <i class="fa-solid fa-arrow-trend-up"></i>
                    <span>Paid & Delivered Today</span>
                </div>
            </div>
        </a>

        <!-- Card 2: Today's Orders -->
        <a href="{{ route('admin.orders.index') }}" class="group bg-white p-5 rounded-2xl border border-slate-200 hover:border-blue-500 hover:shadow-lg transition flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Today's Orders</span>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-base group-hover:bg-blue-600 group-hover:text-white transition">
                    <i class="fa-solid fa-bag-shopping"></i>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-black text-slate-900 tracking-tight">{{ $todayOrdersCount }}</span>
                <div class="flex items-center space-x-1.5 text-[11px] font-semibold text-blue-600 mt-1">
                    <i class="fa-solid fa-clock"></i>
                    <span>{{ $pendingOrdersCount }} Pending Action</span>
                </div>
            </div>
        </a>

        <!-- Card 3: Out for Delivery -->
        <a href="{{ route('admin.orders.index', ['status' => 'OUT_FOR_DELIVERY']) }}" class="group bg-white p-5 rounded-2xl border border-slate-200 hover:border-amber-500 hover:shadow-lg transition flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Out for Delivery</span>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-base group-hover:bg-amber-600 group-hover:text-white transition">
                    <i class="fa-solid fa-motorcycle"></i>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-black text-slate-900 tracking-tight">{{ $outForDeliveryCount }}</span>
                <div class="flex items-center space-x-1.5 text-[11px] font-semibold text-amber-600 mt-1">
                    <i class="fa-solid fa-road"></i>
                    <span>Live on the road</span>
                </div>
            </div>
        </a>

        <!-- Card 4: Active Fleet -->
        <a href="{{ route('admin.delivery_boys.index', ['online' => 'online']) }}" class="group bg-white p-5 rounded-2xl border border-slate-200 hover:border-purple-500 hover:shadow-lg transition flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Delivery Boys</span>
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-base group-hover:bg-purple-600 group-hover:text-white transition">
                    <i class="fa-solid fa-helmet-safety"></i>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-black text-slate-900 tracking-tight">{{ $activeDeliveryBoys }} / {{ $totalDeliveryBoys }}</span>
                <div class="flex items-center space-x-1.5 text-[11px] font-semibold text-purple-600 mt-1">
                    <i class="fa-solid fa-circle-dot text-emerald-500"></i>
                    <span>Online & Ready</span>
                </div>
            </div>
        </a>

    </div>

    <!-- ORDER STATUS PIPELINE (Interactive Filter Bar) -->
    <div class="bg-slate-900 text-white p-4 rounded-2xl shadow-md">
        <div class="flex items-center justify-between mb-3 px-1">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Order Fulfillment Pipeline</span>
            <a href="{{ route('admin.orders.index') }}" class="text-xs text-emerald-400 hover:underline font-semibold flex items-center space-x-1">
                <span>View Full Pipeline</span>
                <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </a>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2">
            
            <a href="{{ route('admin.orders.index', ['status' => 'PLACED']) }}" class="bg-slate-800 hover:bg-slate-700/80 p-3 rounded-xl border border-slate-700/80 transition text-center group">
                <span class="text-[11px] font-semibold text-slate-400 uppercase block">Placed</span>
                <span class="text-lg font-black text-amber-400 mt-0.5 block group-hover:scale-110 transition">{{ $pendingOrdersCount }}</span>
                <span class="text-[10px] text-slate-500">Awaiting Accept</span>
            </a>

            <a href="{{ route('admin.orders.index', ['status' => 'PACKED']) }}" class="bg-slate-800 hover:bg-slate-700/80 p-3 rounded-xl border border-slate-700/80 transition text-center group">
                <span class="text-[11px] font-semibold text-slate-400 uppercase block">Packed</span>
                <span class="text-lg font-black text-blue-400 mt-0.5 block group-hover:scale-110 transition">{{ $packedOrdersCount }}</span>
                <span class="text-[10px] text-slate-500">Ready to Assign</span>
            </a>

            <a href="{{ route('admin.orders.assign_view') }}" class="bg-slate-800 hover:bg-slate-700/80 p-3 rounded-xl border border-slate-700/80 transition text-center group">
                <span class="text-[11px] font-semibold text-slate-400 uppercase block">Dispatch</span>
                <span class="text-lg font-black text-indigo-400 mt-0.5 block group-hover:scale-110 transition">{{ $packedOrdersCount }}</span>
                <span class="text-[10px] text-indigo-400 font-semibold">Assign Fleet &rarr;</span>
            </a>

            <a href="{{ route('admin.orders.index', ['status' => 'OUT_FOR_DELIVERY']) }}" class="bg-slate-800 hover:bg-slate-700/80 p-3 rounded-xl border border-slate-700/80 transition text-center group">
                <span class="text-[11px] font-semibold text-slate-400 uppercase block">Out for Delivery</span>
                <span class="text-lg font-black text-amber-400 mt-0.5 block group-hover:scale-110 transition">{{ $outForDeliveryCount }}</span>
                <span class="text-[10px] text-slate-500">En Route</span>
            </a>

            <a href="{{ route('admin.orders.index', ['status' => 'DELIVERED']) }}" class="bg-slate-800 hover:bg-slate-700/80 p-3 rounded-xl border border-slate-700/80 transition text-center group">
                <span class="text-[11px] font-semibold text-slate-400 uppercase block">Delivered</span>
                <span class="text-lg font-black text-emerald-400 mt-0.5 block group-hover:scale-110 transition">{{ $deliveredCount }}</span>
                <span class="text-[10px] text-slate-500">Completed</span>
            </a>

            <a href="{{ route('admin.orders.index', ['status' => 'CANCELLED']) }}" class="bg-slate-800 hover:bg-slate-700/80 p-3 rounded-xl border border-slate-700/80 transition text-center group">
                <span class="text-[11px] font-semibold text-slate-400 uppercase block">Cancelled</span>
                <span class="text-lg font-black text-rose-400 mt-0.5 block group-hover:scale-110 transition">{{ $cancelledCount }}</span>
                <span class="text-[10px] text-slate-500">Rejected / Aborted</span>
            </a>

        </div>
    </div>

    <!-- MAIN DASHBOARD SPLIT: Interactive Map + Quick Daily Price Matrix -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- HYPERLOCAL COVERAGE MAP (Leaflet.js) -->
        <div class="lg:col-span-2 bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm">
                        <i class="fa-solid fa-map-location-dot"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Hyperlocal Coverage & Live Fleet Map</h2>
                        <p class="text-[11px] text-slate-500">Satara & Koregaon Stores (3 KM Delivery Radius)</p>
                    </div>
                </div>
                <a href="{{ route('admin.branches.radius') }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 hover:underline flex items-center space-x-1">
                    <span>Configure Radius</span>
                    <i class="fa-solid fa-chevron-right text-[10px]"></i>
                </a>
            </div>

            <!-- Leaflet Map Container -->
            <div id="hyperlocalMap" class="w-full h-80 rounded-xl border border-slate-200 overflow-hidden relative z-10 shadow-inner"></div>

            <div class="mt-3 pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between text-xs text-slate-600 gap-2">
                <div class="flex items-center space-x-4">
                    <span class="flex items-center space-x-1.5">
                        <span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span>
                        <span class="font-medium">Satara Store (3 KM)</span>
                    </span>
                    <span class="flex items-center space-x-1.5">
                        <span class="w-3 h-3 rounded-full bg-blue-500 inline-block"></span>
                        <span class="font-medium">Koregaon Store (3 KM)</span>
                    </span>
                </div>
                <span class="text-slate-400 text-[11px]"><i class="fa-solid fa-info-circle mr-1"></i> Click on markers to view details</span>
            </div>
        </div>

        <!-- 1-CLICK QUICK DAILY PRICE UPDATE WIDGET -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-sm">
                        <i class="fa-solid fa-bolt"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Quick Daily Price</h2>
                        <p class="text-[11px] text-slate-500">Fast 1-click market price updater</p>
                    </div>
                </div>
                <a href="{{ route('admin.daily_prices.index') }}" class="text-xs font-bold text-emerald-600 hover:underline">All Prices &rarr;</a>
            </div>

            <form action="{{ route('admin.daily_prices.bulk_update') }}" method="POST" class="flex-1 flex flex-col justify-between">
                @csrf
                <input type="hidden" name="branch_id" value="{{ $branches->first()?->id ?? 1 }}">
                <div class="space-y-2.5 max-h-72 overflow-y-auto pr-1 custom-scrollbar divide-y divide-slate-100">
                    @foreach($quickPriceProducts as $qProd)
                        <div class="pt-2 flex items-center justify-between gap-2">
                            <div class="flex items-center space-x-2 min-w-0">
                                <img src="{{ $qProd->image ?: 'https://images.unsplash.com/photo-1546094096-0df4bcaaa337?w=100' }}" class="w-8 h-8 rounded-lg object-cover border border-slate-200">
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-slate-800 truncate">{{ $qProd->name }}</p>
                                    <span class="text-[10px] text-slate-400">Base: ₹{{ $qProd->base_price }} / {{ $qProd->unit }}</span>
                                </div>
                            </div>
                            <div class="w-24 flex-shrink-0 flex items-center space-x-1">
                                <span class="text-xs font-bold text-slate-500">₹</span>
                                <input type="number" step="0.01" min="0.01" name="prices[{{ $qProd->id }}]" value="{{ $qProd->current_daily_price }}" class="w-full text-xs font-bold text-slate-800 bg-slate-50 border border-slate-200 rounded-lg px-2 py-1 text-right focus:ring-emerald-500 focus:border-emerald-500">
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100">
                    <button type="submit" class="w-full py-2.5 px-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition shadow-sm flex items-center justify-center space-x-2">
                        <i class="fa-solid fa-check text-emerald-400"></i>
                        <span>Save Today's Daily Prices</span>
                    </button>
                </div>
            </form>
        </div>

    </div>

    <!-- RECENT ORDERS TABLE (Clickable Table: Clicking row navigates directly to Order Details / Module) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h2 class="text-base font-bold text-slate-900">Recent Live Orders</h2>
                <p class="text-xs text-slate-500">Click any order row to open detailed lifecycle and dispatch modal.</p>
            </div>
            <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 text-xs font-bold transition">
                <span>View All Orders</span>
                <i class="fa-solid fa-arrow-right text-[11px]"></i>
            </a>
        </div>

        <!-- Desktop View Table -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200">
                        <th class="py-3.5 px-4">Order ID</th>
                        <th class="py-3.5 px-4">Customer & Store</th>
                        <th class="py-3.5 px-4">Amount & Mode</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4">Assigned Fleet</th>
                        <th class="py-3.5 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($recentOrders as $rOrder)
                        <tr onclick="window.location='{{ route('admin.orders.show', $rOrder->id) }}'" class="hover:bg-slate-50/80 cursor-pointer transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-800">
                                <span class="text-emerald-600 hover:underline">{{ $rOrder->order_number }}</span>
                                <span class="block text-[10px] font-normal text-slate-400 mt-0.5">{{ $rOrder->placed_at->diffForHumans() }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-slate-800 block">{{ $rOrder->customer->name ?? 'Customer' }}</span>
                                <span class="text-[11px] text-slate-500 flex items-center space-x-1 mt-0.5">
                                    <i class="fa-solid fa-store text-slate-400 text-[10px]"></i>
                                    <span>{{ $rOrder->branch->name ?? 'Store' }}</span>
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-slate-900 block">₹{{ number_format($rOrder->total_amount, 2) }}</span>
                                <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-semibold {{ $rOrder->payment_mode === 'COD' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ $rOrder->payment_mode }} ({{ $rOrder->payment_status }})
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($rOrder->order_status === 'PLACED')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800">Placed</span>
                                @elseif($rOrder->order_status === 'CONFIRMED')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800">Confirmed</span>
                                @elseif($rOrder->order_status === 'PACKED')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-indigo-100 text-indigo-800">Packed</span>
                                @elseif($rOrder->order_status === 'OUT_FOR_DELIVERY')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-purple-100 text-purple-800 animate-pulse">Out for Delivery</span>
                                @elseif($rOrder->order_status === 'DELIVERED')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">Delivered</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700">{{ $rOrder->order_status }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                @if($rOrder->deliveryBoy)
                                    <span class="font-medium text-slate-800 flex items-center space-x-1">
                                        <i class="fa-solid fa-motorcycle text-emerald-500"></i>
                                        <span>{{ $rOrder->deliveryBoy->name }}</span>
                                    </span>
                                @else
                                    <span class="text-slate-400 italic">Unassigned</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <a href="{{ route('admin.orders.show', $rOrder->id) }}" class="p-1.5 rounded-lg bg-slate-100 hover:bg-emerald-600 hover:text-white text-slate-600 transition inline-block">
                                    <i class="fa-solid fa-chevron-right text-xs"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-empty-state title="No Live Orders Found" message="Orders placed by customers will automatically appear here in real time." actionText="Create Order" actionUrl="{{ route('admin.orders.index') }}" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Stacked Card View for Orders -->
        <div class="md:hidden divide-y divide-slate-100">
            @forelse($recentOrders as $mOrder)
                <div onclick="window.location='{{ route('admin.orders.show', $mOrder->id) }}'" class="p-4 hover:bg-slate-50 active:bg-slate-100 transition cursor-pointer space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-mono font-bold text-emerald-600 text-xs">{{ $mOrder->order_number }}</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $mOrder->order_status === 'DELIVERED' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                            {{ $mOrder->order_status }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-bold text-slate-800">{{ $mOrder->customer->name ?? 'Customer' }}</span>
                        <span class="font-black text-slate-900">₹{{ number_format($mOrder->total_amount, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-[11px] text-slate-500 pt-1">
                        <span><i class="fa-solid fa-store mr-1 text-slate-400"></i> {{ $mOrder->branch->name ?? 'Store' }}</span>
                        <span>{{ $mOrder->placed_at->diffForHumans() }}</span>
                    </div>
                </div>
            @empty
                <div class="p-4">
                    <x-empty-state title="No Live Orders Found" />
                </div>
            @endforelse
        </div>

    </div>

    <!-- BOTTOM SPLIT: Active Delivery Fleet Table + Low Stock / Master Quick Link -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- ACTIVE DELIVERY BOYS (Clickable table navigating to Delivery Boy Master) -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <div class="w-7 h-7 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-motorcycle"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900">Delivery Fleet Status</h3>
                </div>
                <a href="{{ route('admin.delivery_boys.index') }}" class="text-xs font-bold text-emerald-600 hover:underline">Manage Fleet &rarr;</a>
            </div>

            <div class="divide-y divide-slate-100 text-xs">
                @foreach($deliveryFleet as $fleet)
                    <div onclick="window.location='{{ route('admin.delivery_boys.index') }}'" class="p-3.5 hover:bg-slate-50 cursor-pointer flex items-center justify-between transition">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center font-bold text-slate-700">
                                {{ substr($fleet->name, 0, 1) }}
                            </div>
                            <div>
                                <span class="font-bold text-slate-800 block">{{ $fleet->name }}</span>
                                <span class="text-[11px] text-slate-500">{{ $fleet->mobile }} &bull; {{ $fleet->vehicle_type }}</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $fleet->is_online ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                <span class="w-1.5 h-1.5 rounded-full mr-1 {{ $fleet->is_online ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                {{ $fleet->is_online ? 'Online' : 'Offline' }}
                            </span>
                            <span class="block text-[10px] text-slate-400 mt-0.5">{{ $fleet->branch->name ?? 'Store' }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- QUICK ACCESS MASTERS & LOW STOCK ALERTS (Clickable table navigating to Product Master) -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <div class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900">Product & Stock Alerts</h3>
                </div>
                <a href="{{ route('admin.products.index') }}" class="text-xs font-bold text-emerald-600 hover:underline">Product Master &rarr;</a>
            </div>

            <div class="divide-y divide-slate-100 text-xs">
                @forelse($lowStockProducts as $lsProd)
                    <div onclick="window.location='{{ route('admin.products.index') }}'" class="p-3.5 hover:bg-slate-50 cursor-pointer flex items-center justify-between transition">
                        <div class="flex items-center space-x-3 min-w-0">
                            <img src="{{ $lsProd->image ?: 'https://images.unsplash.com/photo-1546094096-0df4bcaaa337?w=100' }}" class="w-8 h-8 rounded-lg object-cover border border-slate-200">
                            <div class="min-w-0">
                                <span class="font-bold text-slate-800 block truncate">{{ $lsProd->name }}</span>
                                <span class="text-[11px] text-slate-500">Base Price: ₹{{ $lsProd->base_price }} / {{ $lsProd->unit }}</span>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ $lsProd->in_stock ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800' }}">
                            {{ $lsProd->in_stock ? 'Inactive' : 'Out of Stock' }}
                        </span>
                    </div>
                @empty
                    <div class="p-6 text-center text-slate-400 text-xs">
                        <i class="fa-solid fa-circle-check text-emerald-500 text-xl mb-1"></i>
                        <p>All active products are in stock</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Leaflet Map centered between Satara & Koregaon
        const map = L.map('hyperlocalMap').setView([17.6890, 74.0900], 11);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        // Satara Store Marker & Radius Circle (3 KM)
        const sataraLat = 17.6805, sataraLng = 74.0183;
        const sataraMarker = L.marker([sataraLat, sataraLng]).addTo(map);
        sataraMarker.bindPopup("<b>Satara Store (SAT-01)</b><br>Powai Naka, Satara<br><span class='text-xs text-emerald-600 font-bold'>Delivery Radius: 3 KM</span><br><a href='{{ route('admin.branches.radius') }}' class='text-xs text-blue-600 underline font-bold'>Configure Radius</a>");

        L.circle([sataraLat, sataraLng], {
            color: '#10b981',
            fillColor: '#10b981',
            fillOpacity: 0.15,
            radius: 3000 // 3 KM in meters
        }).addTo(map);

        // Koregaon Store Marker & Radius Circle (3 KM)
        const koregaonLat = 17.7000, koregaonLng = 74.1700;
        const koregaonMarker = L.marker([koregaonLat, koregaonLng]).addTo(map);
        koregaonMarker.bindPopup("<b>Koregaon Store (KOR-02)</b><br>Station Road, Koregaon<br><span class='text-xs text-blue-600 font-bold'>Delivery Radius: 3 KM</span><br><a href='{{ route('admin.branches.radius') }}' class='text-xs text-blue-600 underline font-bold'>Configure Radius</a>");

        L.circle([koregaonLat, koregaonLng], {
            color: '#3b82f6',
            fillColor: '#3b82f6',
            fillOpacity: 0.15,
            radius: 3000 // 3 KM in meters
        }).addTo(map);

        // Delivery Boys Live Locations
        const deliveryBoys = [
            { name: "Rohan Patil", lat: 17.6840, lng: 74.0150, status: "Out for Delivery (Satara)" },
            { name: "Amit Deshmukh", lat: 17.6790, lng: 74.0210, status: "Online Available (Satara)" },
            { name: "Sagar Jadhav", lat: 17.7010, lng: 74.1720, status: "Online Available (Koregaon)" }
        ];

        deliveryBoys.forEach(db => {
            const dbMarker = L.circleMarker([db.lat, db.lng], {
                radius: 7,
                fillColor: "#e11d48",
                color: "#ffffff",
                weight: 2,
                opacity: 1,
                fillOpacity: 0.9
            }).addTo(map);
            dbMarker.bindPopup(`<b>Fleet: ${db.name}</b><br><span class="text-xs text-emerald-600">${db.status}</span>`);
        });
    });
</script>
@endpush
