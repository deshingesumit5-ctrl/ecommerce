<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel Web</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body { font-family: 'Inter', sans-serif; }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; }
            .invoice-box { border: none !important; box-shadow: none !important; margin: 0 !important; width: 100% !important; max-width: 100% !important; }
        }
    </style>
</head>
<body class="p-4 sm:p-8 min-h-full flex flex-col items-center">

    <!-- Print / Download Controls Bar -->
    <div class="max-w-3xl w-full mb-4 flex items-center justify-between no-print">
        <a href="{{ route('admin.orders.show', $order->id) }}" class="px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 text-xs font-bold shadow-sm hover:bg-slate-50 transition flex items-center space-x-1.5">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back to Order</span>
        </a>
        <div class="flex items-center space-x-2">
            <button onclick="window.print()" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-600/20 transition flex items-center space-x-2">
                <i class="fa-solid fa-print"></i>
                <span>Print Invoice / Save PDF</span>
            </button>
        </div>
    </div>

    <!-- GST INVOICE SHEET -->
    <div class="invoice-box max-w-3xl w-full bg-white rounded-3xl border border-slate-200 shadow-xl p-8 sm:p-12 text-slate-800">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between pb-8 border-b border-slate-200 gap-4">
            <div>
                <div class="flex items-center space-x-2 mb-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-600 text-white font-bold flex items-center justify-center text-sm">
                        <i class="fa-solid fa-bolt"></i>
                    </div>
                    <span class="text-xl font-extrabold tracking-tight text-slate-900">Admin Panel Web</span>
                </div>
                <h2 class="text-sm font-bold text-slate-700">{{ $storeName }}</h2>
                <p class="text-xs text-slate-500 mt-0.5">{{ $order->branch->address ?? 'Satara, Maharashtra' }}</p>
                <p class="text-xs text-slate-500">Phone: {{ $order->branch->contact_phone ?? $supportPhone }} &bull; Email: {{ $supportEmail }}</p>
                <span class="inline-block mt-2 px-2 py-0.5 rounded bg-slate-100 text-slate-600 font-mono text-[10px] font-bold">GSTIN: 27AABCM9876Q1Z2</span>
            </div>

            <div class="sm:text-right">
                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800 inline-block mb-2">
                    TAX INVOICE
                </span>
                <h3 class="text-base font-black font-mono text-slate-900">{{ $order->order_number }}</h3>
                <p class="text-xs text-slate-500 mt-1">Invoice Date: <strong class="text-slate-700">{{ $order->placed_at->format('d M Y') }}</strong></p>
                <p class="text-xs text-slate-500">Time: <strong class="text-slate-700">{{ $order->placed_at->format('h:i A') }}</strong></p>
                <p class="text-xs text-slate-500">Payment: <strong class="text-slate-700">{{ $order->payment_mode }} ({{ $order->payment_status }})</strong></p>
            </div>
        </div>

        <!-- Billed To -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 py-6 border-b border-slate-200 text-xs">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Customer Details (Billed To):</span>
                <span class="font-bold text-slate-900 block text-sm">{{ $order->customer->name ?? 'Customer' }}</span>
                <span class="text-slate-600 block mt-0.5 font-mono">{{ $order->customer->mobile ?? '' }}</span>
                <span class="text-slate-500 block">{{ $order->customer->email ?? '' }}</span>
            </div>
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Delivery Address:</span>
                <p class="text-slate-700 bg-slate-50 p-2.5 rounded-xl border border-slate-100">{{ $order->delivery_address }}</p>
            </div>
        </div>

        <!-- Items Table -->
        <div class="py-6">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 text-[11px] font-bold uppercase text-slate-500 border-b border-slate-200">
                        <th class="py-3 px-3">#</th>
                        <th class="py-3 px-3">Item Description</th>
                        <th class="py-3 px-3">Unit</th>
                        <th class="py-3 px-3 text-right">Price</th>
                        <th class="py-3 px-3 text-center">Qty</th>
                        <th class="py-3 px-3 text-right">Amount (₹)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($order->items as $index => $item)
                        <tr>
                            <td class="py-3 px-3 text-slate-400">{{ $index + 1 }}</td>
                            <td class="py-3 px-3 font-bold text-slate-800">{{ $item->product_name }}</td>
                            <td class="py-3 px-3 font-mono text-slate-600">{{ $item->unit }}</td>
                            <td class="py-3 px-3 text-right">₹{{ number_format($item->price, 2) }}</td>
                            <td class="py-3 px-3 text-center font-bold">{{ $item->quantity }}</td>
                            <td class="py-3 px-3 text-right font-bold text-slate-900">₹{{ number_format($item->total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Financial Breakdown -->
        <div class="border-t border-slate-200 pt-4 flex flex-col sm:flex-row sm:justify-between items-start text-xs gap-4">
            <div class="max-w-xs text-slate-500 space-y-1">
                <p><strong class="text-slate-700">Terms & Conditions:</strong></p>
                <p>1. Fresh farm produce goods once sold can be returned at the doorstep during delivery inspection.</p>
                <p>2. Computer-generated official tax invoice.</p>
            </div>

            <div class="w-full sm:w-72 space-y-2">
                <div class="flex justify-between text-slate-600">
                    <span>Items Subtotal:</span>
                    <span>₹{{ number_format($order->subtotal, 2) }}</span>
                </div>
                @if($order->discount_amount > 0)
                    <div class="flex justify-between text-emerald-600 font-bold">
                        <span>Discount Promo ({{ $order->coupon->code ?? 'PROMO' }}):</span>
                        <span>- ₹{{ number_format($order->discount_amount, 2) }}</span>
                    </div>
                @endif
                <div class="flex justify-between text-slate-600">
                    <span>Hyperlocal Delivery Fee:</span>
                    <span>{{ $order->delivery_charge > 0 ? '₹' . number_format($order->delivery_charge, 2) : 'FREE' }}</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Applicable GST ({{ $gstPercent }}%):</span>
                    <span>₹{{ number_format($order->tax_amount, 2) }}</span>
                </div>
                <div class="flex justify-between text-base font-black text-slate-900 pt-2 border-t border-slate-200">
                    <span>Invoice Total:</span>
                    <span class="text-emerald-700 font-mono">₹{{ number_format($order->total_amount, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Footer Seal -->
        <div class="mt-8 pt-6 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
            <span>Authorized Signatory &bull; {{ $storeName }}</span>
            <span>Thank you for shopping local!</span>
        </div>

    </div>

</body>
</html>
