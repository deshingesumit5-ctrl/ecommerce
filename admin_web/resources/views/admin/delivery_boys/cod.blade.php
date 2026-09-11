@extends('layouts.admin')

@section('title', 'COD Collections')

@section('content')
<div class="space-y-6">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-emerald-600 mb-1">
                <i class="fa-solid fa-sack-dollar"></i>
                <span>Cash on Delivery Financials</span>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">COD Collections & Reconciliation</h1>
            <p class="text-xs text-slate-500 mt-0.5">Track cash collected by delivery boys at customer doorsteps and reconcile store deposits.</p>
        </div>
        <a href="{{ route('admin.delivery_boys.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center space-x-2">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Fleet Master</span>
        </a>
    </div>

    <!-- METRICS BANNER -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-emerald-950 p-5 rounded-2xl text-emerald-200 border border-emerald-800/80 shadow-md flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-emerald-400 uppercase tracking-wider block">Total COD Collected</span>
                <span class="text-2xl font-black text-white mt-1 block">₹{{ number_format($totalCodCollected, 2) }}</span>
                <span class="text-[11px] text-emerald-300/80">Successfully received & verified</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-xl font-bold">
                <i class="fa-solid fa-circle-check"></i>
            </div>
        </div>

        <div class="bg-amber-950 p-5 rounded-2xl text-amber-200 border border-amber-800/80 shadow-md flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-amber-400 uppercase tracking-wider block">Pending Reconciliation</span>
                <span class="text-2xl font-black text-white mt-1 block">₹{{ number_format($pendingVerification, 2) }}</span>
                <span class="text-[11px] text-amber-300/80">With delivery boys awaiting store deposit</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-600 text-white flex items-center justify-center text-xl font-bold">
                <i class="fa-solid fa-hourglass-half"></i>
            </div>
        </div>
    </div>

    <!-- FILTER BAR -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row items-center justify-between gap-3">
        <form action="{{ route('admin.delivery_boys.cod') }}" method="GET" class="w-full flex flex-col md:flex-row items-center gap-3">
            
            <div class="flex items-center gap-2 w-full md:w-auto">
                <select name="delivery_boy_id" class="text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 focus:ring-2 focus:ring-emerald-500">
                    <option value="all">All Delivery Boys</option>
                    @foreach($deliveryBoys as $db)
                        <option value="{{ $db->id }}" {{ (string)request('delivery_boy_id') === (string)$db->id ? 'selected' : '' }}>{{ $db->name }} ({{ $db->mobile }})</option>
                    @endforeach
                </select>

                <select name="reconciled" class="text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 focus:ring-2 focus:ring-emerald-500">
                    <option value="all">All Reconciliations</option>
                    <option value="verified" {{ request('reconciled') === 'verified' ? 'selected' : '' }}>Reconciled & Verified</option>
                    <option value="pending" {{ request('reconciled') === 'pending' ? 'selected' : '' }}>Pending Deposit</option>
                </select>

                <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition">Filter</button>
            </div>
        </form>
    </div>

    <!-- TABLE -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200">
                        <th class="py-3.5 px-4">Order ID & Date</th>
                        <th class="py-3.5 px-4">Delivery Boy</th>
                        <th class="py-3.5 px-4">Customer & Store</th>
                        <th class="py-3.5 px-4">COD Amount</th>
                        <th class="py-3.5 px-4">Delivery Status</th>
                        <th class="py-3.5 px-4">COD Verification</th>
                        <th class="py-3.5 px-4 text-right">Reconciliation Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($orders as $order)
                        @php
                            $payment = $order->payments->firstWhere('payment_mode', 'COD');
                            $isVerified = $payment ? $payment->cod_verified : false;
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4">
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="font-mono font-bold text-emerald-600 hover:underline">
                                    {{ $order->order_number }}
                                </a>
                                <span class="block text-[10px] text-slate-400 mt-0.5">{{ $order->placed_at->format('d M, h:i A') }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($order->deliveryBoy)
                                    <span class="font-bold text-slate-900 block">{{ $order->deliveryBoy->name }}</span>
                                    <span class="text-[11px] font-mono text-slate-500">{{ $order->deliveryBoy->mobile }}</span>
                                @else
                                    <span class="text-slate-400 italic">Unassigned</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-slate-800 block">{{ $order->customer->name ?? 'Customer' }}</span>
                                <span class="text-[11px] text-slate-500">{{ $order->branch->name ?? 'Store' }}</span>
                            </td>
                            <td class="py-3.5 px-4 font-black text-slate-900 text-sm">
                                ₹{{ number_format($order->total_amount, 2) }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $order->order_status === 'DELIVERED' ? 'bg-emerald-100 text-emerald-800' : 'bg-purple-100 text-purple-800' }}">
                                    {{ $order->order_status }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($isVerified)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        <i class="fa-solid fa-check mr-1 text-emerald-600"></i> Verified Deposit
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 animate-pulse">
                                        <i class="fa-solid fa-clock mr-1 text-amber-600"></i> Pending Deposit
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                @if(!$isVerified)
                                    <form action="{{ route('admin.orders.verify_cod', $order->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition shadow-sm">
                                            Reconcile & Verify Deposit
                                        </button>
                                    </form>
                                @else
                                    <span class="text-[11px] text-slate-400 font-semibold">Settled</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-empty-state title="No COD Orders Found" message="Cash on delivery orders will appear here for reconciliation." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50">{{ $orders->links() }}</div>
        @endif
    </div>

</div>
@endsection
