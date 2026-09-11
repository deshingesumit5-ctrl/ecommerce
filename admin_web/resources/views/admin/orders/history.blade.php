@extends('layouts.admin')

@section('title', 'Order History & Audit Trail')

@section('content')
<div class="space-y-6">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-emerald-600 mb-1">
                <i class="fa-solid fa-timeline"></i>
                <span>Compliance & Audit Logs</span>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">Order History & Audit Trail</h1>
            <p class="text-xs text-slate-500 mt-0.5">Comprehensive chronological log of every status transition and actions performed on orders.</p>
        </div>
        <a href="{{ route('admin.orders.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center space-x-2">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back to Order Management</span>
        </a>
    </div>

    <!-- FILTER BAR -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row items-center justify-between gap-3">
        <form action="{{ route('admin.orders.history') }}" method="GET" class="w-full flex flex-col md:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-xs">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search Order ID, remarks, actor..." class="w-full text-xs rounded-xl border border-slate-200 pl-9 pr-3 py-2 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>

            <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition">Filter Audit Logs</button>
        </form>
    </div>

    <!-- AUDIT TRAIL TABLE -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200">
                        <th class="py-3.5 px-4">Timestamp</th>
                        <th class="py-3.5 px-4">Order ID</th>
                        <th class="py-3.5 px-4">Customer & Store</th>
                        <th class="py-3.5 px-4">Previous Status</th>
                        <th class="py-3.5 px-4">New Status</th>
                        <th class="py-3.5 px-4">Remarks</th>
                        <th class="py-3.5 px-4">Performed By</th>
                        <th class="py-3.5 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4 font-mono text-slate-500">
                                {{ $log->created_at->format('d M Y, h:i:s A') }}
                                <span class="block text-[10px] text-slate-400 font-sans">{{ $log->created_at->diffForHumans() }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($log->order)
                                    <a href="{{ route('admin.orders.show', $log->order_id) }}" class="font-mono font-bold text-emerald-600 hover:underline">
                                        {{ $log->order->order_number }}
                                    </a>
                                @else
                                    <span class="font-mono text-slate-400">#{{ $log->order_id }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-slate-900 block">{{ $log->order->customer->name ?? 'Customer' }}</span>
                                <span class="text-[11px] text-slate-500">{{ $log->order->branch->name ?? 'Store' }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-400">
                                {{ $log->from_status ?: '—' }}
                            </td>
                            <td class="py-3.5 px-4 font-bold">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                    {{ $log->to_status }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600 max-w-xs">
                                {{ $log->remarks ?: 'Status transition logged.' }}
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-700">
                                {{ $log->changed_by }}
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                @if($log->order)
                                    <a href="{{ route('admin.orders.show', $log->order_id) }}" class="p-1.5 rounded-lg bg-slate-100 hover:bg-emerald-600 hover:text-white text-slate-600 transition inline-block">
                                        <i class="fa-solid fa-chevron-right text-xs"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <x-empty-state title="No Audit Logs Found" message="Status changes and order lifecycle transitions will be recorded here automatically." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50">{{ $logs->links() }}</div>
        @endif
    </div>

</div>
@endsection
