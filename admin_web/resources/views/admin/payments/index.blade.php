@extends('layouts.admin')

@section('title', 'Payment Management')

@section('content')
<div class="space-y-6">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-emerald-600 mb-1">
                <i class="fa-solid fa-credit-card"></i>
                <span>Financial Transactions</span>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">Payment Management</h1>
            <p class="text-xs text-slate-500 mt-0.5">Track all transaction modes.</p>
        </div>
    </div>

    <!-- METRICS -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Total Payments Collected</span>
            <span class="text-2xl font-black text-slate-900 mt-1 block">₹{{ number_format($totalCollected, 2) }}</span>
            <span class="text-[11px] text-emerald-600 font-semibold"><i class="fa-solid fa-circle-check mr-1"></i> Successful transactions</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Online Gateway Payments</span>
            <span class="text-2xl font-black text-blue-600 mt-1 block">₹{{ number_format($onlineCollected, 2) }}</span>
            <span class="text-[11px] text-slate-500">G Pay, Phone Pay, Paytm</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Cash on Delivery (COD)</span>
            <span class="text-2xl font-black text-amber-600 mt-1 block">₹{{ number_format($codCollected, 2) }}</span>
            <span class="text-[11px] text-slate-500">Collected at doorstep</span>
        </div>
    </div>

    <!-- FILTER BAR -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row items-center justify-between gap-3">
        <form action="{{ route('admin.payments.index') }}" method="GET" class="w-full flex flex-col md:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-xs">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search Transaction ID, Order Number, Customer..." class="w-full text-xs rounded-xl border border-slate-200 pl-9 pr-3 py-2 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>

            <div class="flex items-center gap-2 w-full md:w-auto">
                <select name="payment_mode" class="text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 focus:ring-2 focus:ring-emerald-500">
                    <option value="all">All Modes</option>
                    <option value="ONLINE" {{ request('payment_mode') === 'ONLINE' ? 'selected' : '' }}>Online</option>
                    <option value="COD" {{ request('payment_mode') === 'COD' ? 'selected' : '' }}>Cash on Delivery (COD)</option>
                </select>

                <select name="status" class="text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 focus:ring-2 focus:ring-emerald-500">
                    <option value="all">All Status</option>
                    <option value="SUCCESS" {{ request('status') === 'SUCCESS' ? 'selected' : '' }}>SUCCESS</option>
                    <option value="PENDING" {{ request('status') === 'PENDING' ? 'selected' : '' }}>PENDING</option>
                    <option value="FAILED" {{ request('status') === 'FAILED' ? 'selected' : '' }}>FAILED</option>
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
                        <th class="py-3.5 px-4">Transaction ID & Date</th>
                        <th class="py-3.5 px-4">Order ID</th>
                        <th class="py-3.5 px-4">Customer & Store</th>
                        <th class="py-3.5 px-4">Payment Mode</th>
                        <th class="py-3.5 px-4">Amount</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Receipt</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($payments as $p)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4">
                                <span class="font-mono font-bold text-slate-900 block text-xs">{{ $p->transaction_id }}</span>
                                <span class="text-[10px] text-slate-400 mt-0.5 block">{{ $p->created_at->format('d M Y, h:i A') }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($p->order)
                                    <a href="{{ route('admin.orders.show', $p->order_id) }}" class="font-mono font-bold text-emerald-600 hover:underline">
                                        {{ $p->order->order_number }}
                                    </a>
                                @else
                                    <span class="font-mono text-slate-400">#{{ $p->order_id }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-slate-900 block">{{ $p->order->customer->name ?? 'Customer' }}</span>
                                <span class="text-[11px] text-slate-500">{{ $p->order->branch->name ?? 'Store' }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold {{ $p->payment_mode === 'COD' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ $p->payment_mode }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-black text-slate-900 text-sm">
                                ₹{{ number_format($p->amount, 2) }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $p->status === 'SUCCESS' ? 'bg-emerald-100 text-emerald-800' : ($p->status === 'PENDING' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                    {{ $p->status }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                @if($p->order)
                                    <a href="{{ route('admin.invoices.show', $p->order_id) }}" target="_blank" class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition inline-block" title="Invoice">
                                        <i class="fa-solid fa-receipt"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-empty-state title="No Transactions Found" message="Payment gateway logs and COD receipts will be listed here." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payments->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50">{{ $payments->links() }}</div>
        @endif
    </div>

</div>
@endsection
