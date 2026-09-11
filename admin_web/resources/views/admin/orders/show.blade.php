@extends('layouts.admin')

@section('title', 'Order ' . $order->order_number)

@section('content')
<div class="space-y-6">

    <!-- TOP HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-emerald-600 mb-1">
                <a href="{{ route('admin.orders.index') }}" class="hover:underline flex items-center space-x-1">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>All Orders</span>
                </a>
                <span>&bull;</span>
                <span>Order Details</span>
            </div>
            <div class="flex items-center space-x-3">
                <h1 class="text-2xl font-black tracking-tight text-slate-900 font-mono">{{ $order->order_number }}</h1>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold
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
            </div>
            <p class="text-xs text-slate-500 mt-0.5">Placed on {{ $order->placed_at->format('d M Y, h:i A') }} ({{ $order->placed_at->diffForHumans() }})</p>
        </div>

        <div class="flex items-center space-x-2">
            <a href="{{ route('admin.invoices.show', $order->id) }}" target="_blank" class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition shadow-sm flex items-center space-x-2">
                <i class="fa-solid fa-receipt text-emerald-400"></i>
                <span>View GST Invoice</span>
            </a>
        </div>
    </div>

    <!-- MAIN GRID -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- LEFT 2 COLS: Order Items, Price Summary & Status Transition Form -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- STATUS TRANSITION CONSOLE -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center space-x-2">
                        <i class="fa-solid fa-arrows-spin text-emerald-600"></i>
                        <span>Update Order Lifecycle Status</span>
                    </h3>
                    <span class="text-xs text-slate-400">Current: <strong class="text-slate-800">{{ $order->order_status }}</strong></span>
                </div>

                <form action="{{ route('admin.orders.update_status', $order->id) }}" method="POST" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Target Status <span class="text-red-500">*</span></label>
                            <select name="order_status" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 font-bold text-slate-800 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500">
                                <option value="PLACED" {{ $order->order_status === 'PLACED' ? 'selected' : '' }}>PLACED (Awaiting Store Acceptance)</option>
                                <option value="CONFIRMED" {{ $order->order_status === 'CONFIRMED' ? 'selected' : '' }}>CONFIRMED (Accepted by Store)</option>
                                <option value="PACKED" {{ $order->order_status === 'PACKED' ? 'selected' : '' }}>PACKED (Ready for Delivery Assign)</option>
                                <option value="ASSIGNED" {{ $order->order_status === 'ASSIGNED' ? 'selected' : '' }}>ASSIGNED (Assigned to Fleet)</option>
                                <option value="OUT_FOR_DELIVERY" {{ $order->order_status === 'OUT_FOR_DELIVERY' ? 'selected' : '' }}>OUT_FOR_DELIVERY (On Road)</option>
                                <option value="DELIVERED" {{ $order->order_status === 'DELIVERED' ? 'selected' : '' }}>DELIVERED (Doorstep Handover Done)</option>
                                <option value="CANCELLED" {{ $order->order_status === 'CANCELLED' ? 'selected' : '' }}>CANCELLED (Aborted / Rejected)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Assign Delivery Boy</label>
                            <select name="delivery_boy_id" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 text-slate-800 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500">
                                <option value="">Select Delivery Boy (Optional)</option>
                                @foreach($availableDeliveryBoys as $db)
                                    <option value="{{ $db->id }}" {{ $order->delivery_boy_id == $db->id ? 'selected' : '' }}>{{ $db->name }} ({{ $db->mobile }} &bull; {{ $db->vehicle_type }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Status Transition Remarks / Notes</label>
                        <input type="text" name="remarks" placeholder="e.g. Package packed & sealed by store manager" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div class="flex justify-end pt-1">
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-md shadow-emerald-600/20 flex items-center space-x-2">
                            <i class="fa-solid fa-check"></i>
                            <span>Update Order Status</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- ORDER ITEMS BREAKDOWN -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 bg-slate-50 border-b border-slate-200">
                    <h3 class="text-sm font-bold text-slate-900">Order Items ({{ $order->items->count() }})</h3>
                </div>

                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/50 text-[11px] font-bold uppercase text-slate-500 border-b border-slate-100">
                            <th class="py-3 px-4">Item Name</th>
                            <th class="py-3 px-4">Unit</th>
                            <th class="py-3 px-4">Price</th>
                            <th class="py-3 px-4">Qty</th>
                            <th class="py-3 px-4 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($order->items as $item)
                            <tr>
                                <td class="py-3 px-4 font-bold text-slate-800">{{ $item->product_name }}</td>
                                <td class="py-3 px-4 font-mono text-slate-600">{{ $item->unit }}</td>
                                <td class="py-3 px-4">₹{{ number_format($item->price, 2) }}</td>
                                <td class="py-3 px-4 font-bold text-slate-900">{{ $item->quantity }}</td>
                                <td class="py-3 px-4 font-bold text-slate-900 text-right">₹{{ number_format($item->total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="p-4 bg-slate-50/80 border-t border-slate-200 space-y-1.5 text-xs">
                    <div class="flex justify-between text-slate-600">
                        <span>Items Subtotal:</span>
                        <span>₹{{ number_format($order->subtotal, 2) }}</span>
                    </div>
                    @if($order->discount_amount > 0)
                        <div class="flex justify-between text-emerald-600 font-semibold">
                            <span>Coupon / Promo Discount ({{ $order->coupon->code ?? 'PROMO' }}):</span>
                            <span>- ₹{{ number_format($order->discount_amount, 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-slate-600">
                        <span>Delivery Fee:</span>
                        <span>{{ $order->delivery_charge > 0 ? '₹' . number_format($order->delivery_charge, 2) : 'FREE' }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Taxes & GST (5%):</span>
                        <span>₹{{ number_format($order->tax_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-base font-black text-slate-900 pt-2 border-t border-slate-200">
                        <span>Grand Total:</span>
                        <span class="text-emerald-700">₹{{ number_format($order->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- ORDER STATUS LOGS / TIMELINE -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-slate-900 flex items-center space-x-2 pb-3 border-b border-slate-100">
                    <i class="fa-solid fa-timeline text-emerald-600"></i>
                    <span>Order Transition Audit Trail</span>
                </h3>

                <div class="space-y-3">
                    @foreach($order->statusLogs as $log)
                        <div class="flex items-start space-x-3 text-xs">
                            <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-[10px] font-bold mt-0.5">
                                <i class="fa-solid fa-check"></i>
                            </div>
                            <div class="flex-1 bg-slate-50 p-3 rounded-xl border border-slate-100">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-slate-900">{{ $log->to_status }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $log->created_at->format('d M Y, h:i:s A') }}</span>
                                </div>
                                <p class="text-slate-600 mt-1">{{ $log->remarks ?: 'Status updated.' }}</p>
                                <span class="text-[10px] text-slate-400 mt-0.5 block">Actor: {{ $log->changed_by }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

        <!-- RIGHT COL: Customer Info, Store Info, Fleet Assignment -->
        <div class="space-y-6">
            
            <!-- Customer Card -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-3 text-xs">
                <h3 class="text-sm font-bold text-slate-900 flex items-center space-x-2 pb-2 border-b border-slate-100">
                    <i class="fa-solid fa-user text-emerald-600"></i>
                    <span>Customer Details</span>
                </h3>
                <div>
                    <span class="font-bold text-slate-900 block text-sm">{{ $order->customer->name ?? 'Customer' }}</span>
                    <span class="text-slate-500 font-mono">{{ $order->customer->mobile ?? '' }}</span>
                    <span class="text-slate-500 block">{{ $order->customer->email ?? '' }}</span>
                </div>
                <div class="pt-2 border-t border-slate-100">
                    <span class="font-bold text-slate-700 block mb-1">Delivery Address:</span>
                    <p class="text-slate-600 bg-slate-50 p-2.5 rounded-xl border border-slate-100">{{ $order->delivery_address }}</p>
                </div>
                @if($order->special_notes)
                    <div class="pt-2">
                        <span class="font-bold text-amber-700 block mb-0.5">Customer Delivery Notes:</span>
                        <p class="text-slate-600 italic bg-amber-50 p-2.5 rounded-xl border border-amber-200/60">{{ $order->special_notes }}</p>
                    </div>
                @endif
            </div>

            <!-- Operating Store Card -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-3 text-xs">
                <h3 class="text-sm font-bold text-slate-900 flex items-center space-x-2 pb-2 border-b border-slate-100">
                    <i class="fa-solid fa-store text-emerald-600"></i>
                    <span>Fulfillment Store</span>
                </h3>
                <div>
                    <span class="font-bold text-slate-900 block">{{ $order->branch->name ?? 'Store' }}</span>
                    <span class="text-slate-500 font-mono">{{ $order->branch->code ?? '' }} &bull; {{ $order->branch->contact_phone ?? '' }}</span>
                    <p class="text-slate-500 mt-1">{{ $order->branch->address ?? '' }}</p>
                </div>
            </div>

            <!-- Assigned Fleet Card -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-3 text-xs">
                <h3 class="text-sm font-bold text-slate-900 flex items-center space-x-2 pb-2 border-b border-slate-100">
                    <i class="fa-solid fa-motorcycle text-emerald-600"></i>
                    <span>Delivery Boy</span>
                </h3>
                @if($order->deliveryBoy)
                    <div>
                        <span class="font-bold text-slate-900 block text-sm">{{ $order->deliveryBoy->name }}</span>
                        <span class="text-slate-500 font-mono">{{ $order->deliveryBoy->mobile }}</span>
                        <span class="text-slate-500 block mt-1">{{ $order->deliveryBoy->vehicle_type }} &bull; {{ $order->deliveryBoy->vehicle_number }}</span>
                    </div>
                @else
                    <div class="p-3 bg-slate-50 rounded-xl text-center text-slate-500">
                        <p>No delivery boy assigned yet.</p>
                        <a href="{{ route('admin.orders.assign_view') }}" class="text-emerald-600 font-bold underline mt-1 inline-block">Assign via Dispatch Console</a>
                    </div>
                @endif
            </div>

            <!-- Payment Details Card -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-3 text-xs">
                <h3 class="text-sm font-bold text-slate-900 flex items-center space-x-2 pb-2 border-b border-slate-100">
                    <i class="fa-solid fa-credit-card text-emerald-600"></i>
                    <span>Payment Transaction</span>
                </h3>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Payment Mode:</span>
                    <span class="font-bold text-slate-800">{{ $order->payment_mode }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Payment Status:</span>
                    <span class="font-bold {{ $order->payment_status === 'PAID' ? 'text-emerald-600' : 'text-amber-600' }}">{{ $order->payment_status }}</span>
                </div>
                @if($order->payments->count() > 0)
                    <div class="pt-2 border-t border-slate-100">
                        <span class="text-[10px] text-slate-400 font-mono block">Txn ID: {{ $order->payments->first()->transaction_id }}</span>
                        <span class="text-[10px] text-slate-400 block">COD Verified: {{ $order->payments->first()->cod_verified ? 'Yes' : 'Pending' }}</span>
                    </div>
                @endif
            </div>

        </div>

    </div>

</div>
@endsection
