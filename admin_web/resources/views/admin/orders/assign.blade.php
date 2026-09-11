@extends('layouts.admin')

@section('title', 'Delivery Assignment')

@section('content')
<div class="space-y-6">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-emerald-600 mb-1">
                <i class="fa-solid fa-dolly"></i>
                <span>Dispatch & Logistics Console</span>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">Delivery Assignment</h1>
            <p class="text-xs text-slate-500 mt-0.5">Assign pending and packed orders to available online delivery boys.</p>
        </div>
        <a href="{{ route('admin.orders.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center space-x-2">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back to All Orders</span>
        </a>
    </div>

    <!-- MAIN DISPATCH GRID -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left 2 Cols: Orders Ready for Dispatch -->
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center space-x-2">
                        <i class="fa-solid fa-boxes-packing text-emerald-600"></i>
                        <span>Orders Ready for Dispatch ({{ $orders->count() }})</span>
                    </h3>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse($orders as $order)
                        <div class="p-4 hover:bg-slate-50 transition space-y-3">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                <div>
                                    <span class="font-mono font-bold text-emerald-600 text-xs">{{ $order->order_number }}</span>
                                    <span class="font-bold text-slate-900 text-xs ml-2">{{ $order->customer->name ?? 'Customer' }}</span>
                                    <span class="text-[11px] text-slate-500 block mt-0.5">
                                        <i class="fa-solid fa-location-dot mr-1 text-slate-400"></i> {{ $order->delivery_address }}
                                    </span>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <span class="font-black text-slate-900 text-xs block">₹{{ number_format($order->total_amount, 2) }}</span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-800">
                                        {{ $order->order_status }}
                                    </span>
                                </div>
                            </div>

                            <!-- Assignment Form for this order -->
                            <form action="{{ route('admin.orders.assign_delivery') }}" method="POST" class="pt-2 border-t border-slate-100 flex items-center gap-2">
                                @csrf
                                <input type="hidden" name="order_id" value="{{ $order->id }}">
                                <select name="delivery_boy_id" required class="flex-1 text-xs rounded-xl border border-slate-200 px-3 py-1.5 focus:ring-2 focus:ring-emerald-500">
                                    <option value="">Select Available Delivery Boy...</option>
                                    @foreach($deliveryBoys->where('branch_id', $order->branch_id) as $boy)
                                        <option value="{{ $boy->id }}" {{ $order->delivery_boy_id == $boy->id ? 'selected' : '' }}>
                                            {{ $boy->name }} ({{ $boy->is_online ? 'Online' : 'Offline' }} &bull; {{ $boy->mobile }})
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition flex items-center space-x-1 flex-shrink-0">
                                    <i class="fa-solid fa-paper-plane text-emerald-400"></i>
                                    <span>Assign & Dispatch</span>
                                </button>
                            </form>
                        </div>
                    @empty
                        <div class="p-8">
                            <x-empty-state title="No Orders Awaiting Dispatch" message="Orders in confirmed or packed status will automatically appear here for fleet assignment." />
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right Col: Active Delivery Boys Status -->
        <div class="space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center space-x-2">
                        <i class="fa-solid fa-helmet-safety text-emerald-600"></i>
                        <span>Online Fleet Availability</span>
                    </h3>
                    <a href="{{ route('admin.delivery_boys.index') }}" class="text-xs text-emerald-600 font-bold hover:underline">Manage &rarr;</a>
                </div>

                <div class="divide-y divide-slate-100 text-xs">
                    @foreach($deliveryBoys as $db)
                        <div class="p-3.5 flex items-center justify-between">
                            <div class="flex items-center space-x-2.5">
                                <div class="w-8 h-8 rounded-full {{ $db->is_online ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500' }} flex items-center justify-center font-bold">
                                    {{ substr($db->name, 0, 1) }}
                                </div>
                                <div>
                                    <span class="font-bold text-slate-800 block">{{ $db->name }}</span>
                                    <span class="text-[11px] text-slate-500">{{ $db->mobile }} &bull; {{ $db->branch->name ?? 'Store' }}</span>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $db->is_online ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                {{ $db->is_online ? 'Online' : 'Offline' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
