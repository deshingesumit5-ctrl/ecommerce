@extends('layouts.admin')

@section('title', '3KM Area Delivery Fleet Assignment')

@section('content')
<div class="space-y-6">

    <!-- HEADER & OPERATIONS CONSOLE -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-emerald-600 mb-1">
                <i class="fa-solid fa-motorcycle"></i>
                <span>Hyperlocal Fleet & Dispatch Hub</span>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">Delivery Boy Assignment</h1>
            <p class="text-xs text-slate-500 mt-0.5">Assign and reassign orders to delivery boys operating within the 3 KM store coverage radius with real-time app notifications.</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('admin.orders.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center space-x-2">
                <i class="fa-solid fa-arrow-left"></i>
                <span>All Orders</span>
            </a>
            <a href="{{ route('admin.delivery_boys.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition flex items-center space-x-2">
                <i class="fa-solid fa-users-gear text-emerald-400"></i>
                <span>Fleet Master</span>
            </a>
        </div>
    </div>

    <!-- FILTER TABS & SEARCH -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm space-y-3">
        <div class="flex flex-col md:flex-row items-center justify-between gap-3">
            
            <!-- Filter Tabs -->
            <div class="flex items-center space-x-2 w-full md:w-auto overflow-x-auto pb-1 text-xs font-bold">
                <a href="{{ route('admin.orders.assign_view', ['tab' => 'unassigned', 'search' => request('search')]) }}" class="px-3.5 py-2 rounded-xl border transition flex items-center space-x-2 whitespace-nowrap {{ ($filterTab ?? 'unassigned') === 'unassigned' ? 'bg-amber-500 text-white border-amber-500 shadow-sm' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100' }}">
                    <i class="fa-solid fa-clock"></i>
                    <span>Awaiting Assignment</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ ($filterTab ?? 'unassigned') === 'unassigned' ? 'bg-amber-700 text-white' : 'bg-amber-100 text-amber-800' }}">{{ $unassignedCount }}</span>
                </a>

                <a href="{{ route('admin.orders.assign_view', ['tab' => 'assigned', 'search' => request('search')]) }}" class="px-3.5 py-2 rounded-xl border transition flex items-center space-x-2 whitespace-nowrap {{ ($filterTab ?? '') === 'assigned' ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100' }}">
                    <i class="fa-solid fa-person-biking"></i>
                    <span>Assigned & Reassign</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ ($filterTab ?? '') === 'assigned' ? 'bg-indigo-800 text-white' : 'bg-indigo-100 text-indigo-800' }}">{{ $assignedCount }}</span>
                </a>

                <a href="{{ route('admin.orders.assign_view', ['tab' => 'all', 'search' => request('search')]) }}" class="px-3.5 py-2 rounded-xl border transition flex items-center space-x-2 whitespace-nowrap {{ ($filterTab ?? '') === 'all' ? 'bg-slate-900 text-white border-slate-900 shadow-sm' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100' }}">
                    <i class="fa-solid fa-list-check"></i>
                    <span>All Active Orders</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ ($filterTab ?? '') === 'all' ? 'bg-slate-700 text-white' : 'bg-slate-200 text-slate-700' }}">{{ $allUpcomingCount }}</span>
                </a>
            </div>

            <!-- Search Field -->
            <form action="{{ route('admin.orders.assign_view') }}" method="GET" class="w-full md:w-72 flex items-center">
                <input type="hidden" name="tab" value="{{ $filterTab ?? 'unassigned' }}">
                <div class="relative w-full">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-xs">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search Order ID, customer, rider..." class="w-full text-xs rounded-xl border border-slate-200 pl-9 pr-3 py-2 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
            </form>

        </div>
    </div>

    <!-- MAIN DISPATCH & FLEET GRID -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left 2 Cols: Orders List for Assignment / Reassignment -->
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center space-x-2">
                        <i class="fa-solid fa-boxes-packing text-emerald-600"></i>
                        <span>
                            @if(($filterTab ?? 'unassigned') === 'unassigned')
                                Orders Awaiting 3KM Fleet Assignment ({{ $orders->count() }})
                            @elseif(($filterTab ?? '') === 'assigned')
                                Active Assigned Orders & Quick Reassignment ({{ $orders->count() }})
                            @else
                                All Active Orders in Store Coverage ({{ $orders->count() }})
                            @endif
                        </span>
                    </h3>
                    <span class="text-xs text-slate-400 font-medium">Auto-synced with Rider App</span>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse($orders as $order)
                        @php
                            $isAssigned = !empty($order->delivery_boy_id);
                            $isDelivered = ($order->order_status === 'DELIVERED');
                            $currentBoy = $order->deliveryBoy;
                            $storeRadius = $order->branch->radius_km ?? 3.0;
                            // Filter available delivery boys in this store / area
                            $storeBoys = $deliveryBoys->where('branch_id', $order->branch_id);
                            $otherBoys = $deliveryBoys->where('branch_id', '!=', $order->branch_id);
                        @endphp
                        <div class="p-5 hover:bg-slate-50/70 transition space-y-3.5 {{ $isDelivered ? 'border-l-4 border-l-emerald-500' : ($isAssigned ? 'border-l-4 border-l-indigo-500' : 'border-l-4 border-l-amber-500') }}">
                            
                            <!-- Card Header: Order info & Status & Trash Delete Icon -->
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2">
                                <div class="space-y-1">
                                    <div class="flex items-center space-x-2 flex-wrap gap-y-1">
                                        <a href="{{ route('admin.orders.show', $order->id) }}" class="font-mono font-bold text-emerald-600 hover:underline text-xs bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                                            {{ $order->order_number }}
                                        </a>
                                        <span class="font-bold text-slate-900 text-xs">{{ $order->customer->name ?? 'Customer' }}</span>
                                        <span class="text-[11px] text-slate-500"><i class="fa-solid fa-phone text-[9px] mr-0.5 text-slate-400"></i> {{ $order->customer->mobile ?? '' }}</span>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <i class="fa-solid fa-location-crosshairs mr-1"></i> {{ $order->branch->name ?? 'Store' }} &bull; {{ $storeRadius }} KM Area
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-slate-600 flex items-start space-x-1 pt-0.5">
                                        <i class="fa-solid fa-location-dot text-slate-400 mt-0.5 mr-1 flex-shrink-0"></i>
                                        <span>{{ $order->delivery_address ?: 'Customer delivery address' }}</span>
                                    </p>
                                </div>

                                <div class="flex items-start sm:items-center space-x-3 flex-shrink-0">
                                    <div class="text-right">
                                        <span class="font-black text-slate-900 text-sm block">₹{{ number_format($order->total_amount, 2) }}</span>
                                        <div class="flex items-center justify-end space-x-1 mt-0.5">
                                            <span class="px-1.5 py-0.2 rounded text-[10px] font-bold {{ $order->payment_mode === 'COD' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' }}">
                                                {{ $order->payment_mode }}
                                            </span>
                                            <span class="px-2 py-0.2 rounded-full text-[10px] font-bold
                                                {{ $order->order_status === 'PLACED' ? 'bg-amber-100 text-amber-800 border border-amber-200' : '' }}
                                                {{ $order->order_status === 'CONFIRMED' ? 'bg-blue-100 text-blue-800 border border-blue-200' : '' }}
                                                {{ $order->order_status === 'PACKED' ? 'bg-indigo-100 text-indigo-800 border border-indigo-200' : '' }}
                                                {{ $order->order_status === 'ASSIGNED' ? 'bg-sky-100 text-sky-800 border border-sky-200' : '' }}
                                                {{ $order->order_status === 'OUT_FOR_DELIVERY' ? 'bg-purple-100 text-purple-800 border border-purple-200' : '' }}
                                                {{ $order->order_status === 'DELIVERED' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : '' }}
                                                {{ $order->order_status === 'CANCELLED' ? 'bg-rose-100 text-rose-800 border border-rose-200' : '' }}
                                            ">
                                                {{ $order->order_status }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Trash Delete Icon Button -->
                                    <form action="{{ route('admin.orders.destroy', $order->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete Order #{{ $order->order_number }}? This will permanently delete this record.')" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 rounded-xl text-rose-500 hover:text-white hover:bg-rose-600 bg-rose-50 border border-rose-200 transition shadow-sm" title="Delete Order #{{ $order->order_number }}">
                                            <i class="fa-solid fa-trash-can text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Items mini-preview -->
                            <div class="bg-slate-50/80 rounded-xl p-2.5 text-[11px] text-slate-600 flex items-center justify-between border border-slate-100">
                                <div class="truncate max-w-md">
                                    <strong class="text-slate-800">{{ $order->items->count() }} items:</strong>
                                    <span class="text-slate-500">{{ $order->items->pluck('product_name')->implode(', ') }}</span>
                                </div>
                                <span class="text-[10px] text-slate-400 font-mono">{{ $order->placed_at->format('h:i A') }}</span>
                            </div>

                            <!-- Current Assigned Driver Badge (if already assigned) -->
                            @if($isAssigned && $currentBoy)
                                <div class="bg-indigo-50/70 border border-indigo-100 rounded-xl p-2.5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-xs">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-6 h-6 rounded-full {{ $currentBoy->is_online ? 'bg-emerald-500 text-white' : 'bg-slate-400 text-white' }} flex items-center justify-center text-[10px] font-bold">
                                            <i class="fa-solid fa-person-biking"></i>
                                        </div>
                                        <div>
                                            <span class="text-[11px] text-slate-500">Assigned Delivery Partner:</span>
                                            <span class="font-bold text-slate-900 ml-1">{{ $currentBoy->name }}</span>
                                            <span class="text-[10px] text-slate-500">({{ $currentBoy->mobile }})</span>
                                            <span class="ml-1 px-1.5 py-0.2 rounded text-[9px] font-bold {{ $currentBoy->is_online ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' }}">
                                                {{ $currentBoy->is_online ? 'ONLINE' : 'OFFLINE' }}
                                            </span>
                                        </div>
                                    </div>
                                    @if($isDelivered)
                                        <span class="text-[11px] text-emerald-700 font-bold flex items-center">
                                            <i class="fa-solid fa-circle-check mr-1"></i>
                                            Delivered {{ $order->delivered_at ? 'at ' . $order->delivered_at->copy()->timezone('Asia/Kolkata')->format('h:i:s A') : 'Successfully' }}
                                        </span>
                                    @else
                                        <span class="text-[10px] text-amber-700 font-semibold flex items-center">
                                            <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                                            Rider busy or unavailable? Reassign below
                                        </span>
                                    @endif
                                </div>
                            @endif

                            <!-- Assignment & Reassignment Form -->
                            <form action="{{ route('admin.orders.assign_delivery') }}" method="POST" class="pt-2 border-t border-slate-100 flex flex-col sm:flex-row items-center gap-2">
                                @csrf
                                <input type="hidden" name="order_id" value="{{ $order->id }}">
                                
                                <div class="relative flex-1 w-full">
                                    <select name="delivery_boy_id" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 font-medium text-slate-800 bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                                        <option value="">-- Select Delivery Boy for 3KM Area Dispatch --</option>
                                        
                                        @if($storeBoys->count() > 0)
                                            <optgroup label="⚡ In-Store / 3KM Zone Delivery Boys ({{ $order->branch->name ?? 'Store' }})">
                                                @foreach($storeBoys as $boy)
                                                    <option value="{{ $boy->id }}" {{ $order->delivery_boy_id == $boy->id ? 'selected' : '' }}>
                                                        {{ $boy->name }} [{{ $boy->is_online ? '🟢 Online' : '⚪ Offline' }}] &bull; {{ $boy->vehicle_type }} &bull; Mob: {{ $boy->mobile }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endif

                                        @if($otherBoys->count() > 0)
                                            <optgroup label="🌐 Other Available Fleet Boys">
                                                @foreach($otherBoys as $boy)
                                                    <option value="{{ $boy->id }}" {{ $order->delivery_boy_id == $boy->id ? 'selected' : '' }}>
                                                        {{ $boy->name }} ({{ $boy->branch->name ?? 'Other Store' }}) [{{ $boy->is_online ? '🟢 Online' : '⚪ Offline' }}] &bull; Mob: {{ $boy->mobile }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endif
                                    </select>
                                </div>

                                <button type="submit" class="w-full sm:w-auto px-4 py-2 rounded-xl {{ $isAssigned ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-emerald-600 hover:bg-emerald-700' }} text-white text-xs font-bold transition flex items-center justify-center space-x-1.5 flex-shrink-0 shadow-sm">
                                    <i class="fa-solid {{ $isAssigned ? 'fa-arrows-rotate' : 'fa-paper-plane' }} text-xs"></i>
                                    <span>{{ $isAssigned ? 'Reassign Rider' : 'Assign & Dispatch' }}</span>
                                </button>
                            </form>

                        </div>
                    @empty
                        <div class="p-10 text-center space-y-3">
                            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto text-xl">
                                <i class="fa-solid fa-circle-check"></i>
                            </div>
                            <h4 class="text-sm font-bold text-slate-800">All Orders Processed</h4>
                            <p class="text-xs text-slate-500 max-w-sm mx-auto">No orders currently matching this filter. When new upcoming orders are placed in the 3km area, they will appear here ready for fleet dispatch.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right Col: Active 3KM Fleet Status Roster -->
        <div class="space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 flex items-center space-x-2">
                            <i class="fa-solid fa-helmet-safety text-emerald-600"></i>
                            <span>3KM Area Fleet Availability</span>
                        </h3>
                        <p class="text-[11px] text-slate-400 mt-0.5">Real-time status of delivery partners</p>
                    </div>
                    <a href="{{ route('admin.delivery_boys.index') }}" class="text-xs text-emerald-600 font-bold hover:underline">Manage &rarr;</a>
                </div>

                <div class="divide-y divide-slate-100 text-xs">
                    @forelse($deliveryBoys as $db)
                        @php
                            $activeLoadCount = \App\Models\Order::where('delivery_boy_id', $db->id)
                                ->whereIn('order_status', ['ASSIGNED', 'OUT_FOR_DELIVERY'])
                                ->count();
                        @endphp
                        <div class="p-3.5 flex items-center justify-between hover:bg-slate-50 transition">
                            <div class="flex items-center space-x-2.5">
                                <div class="w-8 h-8 rounded-full {{ $db->is_online ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500' }} flex items-center justify-center font-bold text-xs relative">
                                    {{ substr($db->name, 0, 1) }}
                                    <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full {{ $db->is_online ? 'bg-emerald-500 border border-white' : 'bg-slate-400 border border-white' }}"></span>
                                </div>
                                <div>
                                    <span class="font-bold text-slate-800 block">{{ $db->name }}</span>
                                    <span class="text-[11px] text-slate-500">{{ $db->mobile }} &bull; {{ $db->branch->name ?? 'Store' }}</span>
                                    <span class="text-[10px] text-slate-400 block">{{ $db->vehicle_type }} ({{ $db->vehicle_number ?: 'Fleet' }})</span>
                                </div>
                            </div>
                            
                            <div class="text-right">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $db->is_online ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $db->is_online ? 'Online' : 'Offline' }}
                                </span>
                                <span class="text-[10px] text-indigo-600 font-semibold block mt-1">
                                    {{ $activeLoadCount }} Active {{ Str::plural('Order', $activeLoadCount) }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-slate-400 text-xs">
                            No delivery boys registered. <a href="{{ route('admin.delivery_boys.index') }}" class="text-emerald-600 font-bold hover:underline">Add Delivery Boy</a>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Quick Instructions Card -->
            <div class="bg-gradient-to-br from-slate-900 to-slate-800 text-white rounded-2xl p-4 text-xs space-y-2.5 shadow-md">
                <div class="flex items-center space-x-2 text-emerald-400 font-bold">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>Hyperlocal Dispatch Protocol</span>
                </div>
                <ul class="space-y-1.5 text-slate-300 text-[11px] list-disc list-inside">
                    <li>Orders are serviced within each store's <strong>3 KM radius</strong>.</li>
                    <li>Assigning an order instantly sends a push alert to the rider's <strong>delivery_boy_app</strong>.</li>
                    <li>If a rider is busy or offline, admin can <strong>reassign</strong> the order to another online boy anytime.</li>
                </ul>
            </div>
        </div>

    </div>

</div>
@endsection
