@extends('layouts.admin')

@section('title', 'Order Management')

@section('content')
<div class="space-y-6">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-emerald-600 mb-1">
                <i class="fa-solid fa-cart-shopping"></i>
                <span>Operations & Dispatch Control</span>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">Order Management</h1>
            <p class="text-xs text-slate-500 mt-0.5">Track and manage hyperlocal order fulfillment lifecycle from placement to doorstep delivery.</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('admin.orders.assign_view') }}" class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition shadow-sm flex items-center space-x-2">
                <i class="fa-solid fa-dolly text-emerald-400"></i>
                <span>Dispatch Console &rarr;</span>
            </a>
            <a href="{{ route('admin.orders.history') }}" class="px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center space-x-2">
                <i class="fa-solid fa-timeline text-slate-500"></i>
                <span>Audit Trail</span>
            </a>
        </div>
    </div>

    <!-- ORDER STATUS PIPELINE TABS -->
    <div class="flex items-center space-x-2 overflow-x-auto pb-1 custom-scrollbar text-xs font-bold">
        <a href="{{ route('admin.orders.index') }}" class="px-3.5 py-2 rounded-xl border transition whitespace-nowrap {{ !request('status') ? 'bg-slate-900 text-white border-slate-900 shadow-sm' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
            All Orders
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'PLACED']) }}" class="px-3.5 py-2 rounded-xl border transition whitespace-nowrap {{ request('status') === 'PLACED' ? 'bg-amber-500 text-white border-amber-500 shadow-sm' : 'bg-white text-amber-700 border-slate-200 hover:bg-amber-50' }}">
            Placed (Pending)
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'CONFIRMED']) }}" class="px-3.5 py-2 rounded-xl border transition whitespace-nowrap {{ request('status') === 'CONFIRMED' ? 'bg-blue-600 text-white border-blue-600 shadow-sm' : 'bg-white text-blue-700 border-slate-200 hover:bg-blue-50' }}">
            Confirmed
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'PACKED']) }}" class="px-3.5 py-2 rounded-xl border transition whitespace-nowrap {{ request('status') === 'PACKED' ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm' : 'bg-white text-indigo-700 border-slate-200 hover:bg-indigo-50' }}">
            Packed
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'ASSIGNED']) }}" class="px-3.5 py-2 rounded-xl border transition whitespace-nowrap {{ request('status') === 'ASSIGNED' ? 'bg-sky-600 text-white border-sky-600 shadow-sm' : 'bg-white text-sky-700 border-slate-200 hover:bg-sky-50' }}">
            Assigned
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'OUT_FOR_DELIVERY']) }}" class="px-3.5 py-2 rounded-xl border transition whitespace-nowrap {{ request('status') === 'OUT_FOR_DELIVERY' ? 'bg-purple-600 text-white border-purple-600 shadow-sm' : 'bg-white text-purple-700 border-slate-200 hover:bg-purple-50' }}">
            Out for Delivery
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'DELIVERED']) }}" class="px-3.5 py-2 rounded-xl border transition whitespace-nowrap {{ request('status') === 'DELIVERED' ? 'bg-emerald-600 text-white border-emerald-600 shadow-sm' : 'bg-white text-emerald-700 border-slate-200 hover:bg-emerald-50' }}">
            Delivered
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'CANCELLED']) }}" class="px-3.5 py-2 rounded-xl border transition whitespace-nowrap {{ request('status') === 'CANCELLED' ? 'bg-rose-600 text-white border-rose-600 shadow-sm' : 'bg-white text-rose-700 border-slate-200 hover:bg-rose-50' }}">
            Cancelled
        </a>
    </div>

    <!-- FILTER BAR -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row items-center justify-between gap-3">
        <form action="{{ route('admin.orders.index') }}" method="GET" class="w-full flex flex-col md:flex-row items-center gap-3">
            <input type="hidden" name="status" value="{{ request('status') }}">
            <div class="relative flex-1 w-full">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-xs">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search Order ID, customer name, mobile..." class="w-full text-xs rounded-xl border border-slate-200 pl-9 pr-3 py-2 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>

            <div class="flex items-center gap-2 w-full md:w-auto flex-wrap">
                <select name="payment_mode" class="text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <option value="all">All Payment Modes</option>
                    <option value="COD" {{ request('payment_mode') === 'COD' ? 'selected' : '' }}>COD</option>
                    <option value="ONLINE" {{ request('payment_mode') === 'ONLINE' ? 'selected' : '' }}>Online</option>
                </select>

                <select name="payment_status" class="text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <option value="all">All Payment Status</option>
                    <option value="PAID" {{ request('payment_status') === 'PAID' ? 'selected' : '' }}>PAID</option>
                    <option value="PENDING" {{ request('payment_status') === 'PENDING' ? 'selected' : '' }}>PENDING</option>
                </select>

                <input type="date" name="date" value="{{ request('date') }}" class="text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none">

                <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition">Filter</button>
            </div>
        </form>
    </div>

    <!-- ORDERS TABLE -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200">
                        <th class="py-3.5 px-4">Order ID & Date</th>
                        <th class="py-3.5 px-4">Customer & Store</th>
                        <th class="py-3.5 px-4">Items Summary</th>
                        <th class="py-3.5 px-4">Total Amount</th>
                        <th class="py-3.5 px-4">Payment</th>
                        <th class="py-3.5 px-4">Fulfillment Status</th>
                        <th class="py-3.5 px-4">Assigned Fleet</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($orders as $order)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4">
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="font-mono font-bold text-emerald-600 hover:underline block text-xs">
                                    {{ $order->order_number }}
                                </a>
                                <span class="text-[10px] text-slate-400 block mt-0.5">{{ $order->placed_at->format('d M, h:i A') }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-slate-900 block">{{ $order->customer->name ?? 'Customer' }}</span>
                                <span class="text-[11px] text-slate-500"><i class="fa-solid fa-phone text-[9px] mr-1 text-slate-400"></i> {{ $order->customer->mobile ?? '' }}</span>
                                <span class="text-[10px] text-slate-400 block"><i class="fa-solid fa-store text-[9px] mr-1 text-slate-400"></i> {{ $order->branch->name ?? 'Store' }}</span>
                            </td>
                            <td class="py-3.5 px-4 max-w-xs">
                                <span class="font-semibold text-slate-700 block">{{ $order->items->count() }} items</span>
                                <span class="text-[11px] text-slate-500 truncate block">
                                    {{ $order->items->pluck('product_name')->implode(', ') }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-black text-slate-900 text-sm block">₹{{ number_format($order->total_amount, 2) }}</span>
                                @if($order->discount_amount > 0)
                                    <span class="text-[10px] text-emerald-600 block">Saved ₹{{ $order->discount_amount }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-bold {{ $order->payment_mode === 'COD' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ $order->payment_mode }}
                                </span>
                                <span class="block text-[10px] font-bold mt-0.5 {{ $order->payment_status === 'PAID' ? 'text-emerald-600' : 'text-amber-600' }}">
                                    {{ $order->payment_status }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold
                                    {{ $order->order_status === 'PLACED' ? 'bg-amber-100 text-amber-800' : '' }}
                                    {{ $order->order_status === 'CONFIRMED' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $order->order_status === 'PACKED' ? 'bg-indigo-100 text-indigo-800' : '' }}
                                    {{ $order->order_status === 'ASSIGNED' ? 'bg-sky-100 text-sky-800' : '' }}
                                    {{ $order->order_status === 'OUT_FOR_DELIVERY' ? 'bg-purple-100 text-purple-800 animate-pulse' : '' }}
                                    {{ $order->order_status === 'DELIVERED' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                    {{ $order->order_status === 'CANCELLED' ? 'bg-rose-100 text-rose-800' : '' }}
                                ">
                                    {{ $order->order_status }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($order->deliveryBoy)
                                    <span class="font-medium text-slate-800 flex items-center space-x-1">
                                        <i class="fa-solid fa-motorcycle text-emerald-500 text-xs"></i>
                                        <span>{{ $order->deliveryBoy->name }}</span>
                                    </span>
                                @else
                                    <a href="{{ route('admin.orders.assign_view') }}" class="px-2 py-0.5 rounded bg-slate-100 hover:bg-slate-200 text-slate-600 text-[10px] font-semibold transition">Assign Fleet</a>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-1">
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="p-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-600 hover:text-white text-emerald-700 transition inline-block" title="Manage Order">
                                    <i class="fa-solid fa-sliders"></i>
                                </a>
                                <a href="{{ route('admin.invoices.show', $order->id) }}" target="_blank" class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition inline-block" title="Print Invoice">
                                    <i class="fa-solid fa-receipt"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <x-empty-state title="No Orders Found" message="Orders placed by customers will be visible here." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards -->
        <div class="md:hidden divide-y divide-slate-100">
            @forelse($orders as $mOrder)
                <div class="p-4 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <a href="{{ route('admin.orders.show', $mOrder->id) }}" class="font-mono font-bold text-emerald-600 text-xs">{{ $mOrder->order_number }}</a>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-800">{{ $mOrder->order_status }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-bold text-slate-800">{{ $mOrder->customer->name ?? 'Customer' }}</span>
                        <span class="font-black text-slate-900">₹{{ number_format($mOrder->total_amount, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-[11px] text-slate-400 pt-1 border-t border-slate-100">
                        <span>{{ $mOrder->branch->name ?? 'Store' }} &bull; {{ $mOrder->payment_mode }}</span>
                        <a href="{{ route('admin.orders.show', $mOrder->id) }}" class="px-3 py-1 rounded-lg bg-emerald-600 text-white font-bold text-xs">Manage &rarr;</a>
                    </div>
                </div>
            @empty
                <div class="p-4"><x-empty-state title="No Orders" /></div>
            @endforelse
        </div>

        @if($orders->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50">{{ $orders->links() }}</div>
        @endif

    </div>

</div>
@endsection
