<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusLog;
use App\Models\DeliveryBoy;
use App\Models\Branch;
use App\Models\Payment;
use App\Models\Notification;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $branchId = session('active_branch_id', 'all');
        $query = Order::with(['customer', 'branch', 'deliveryBoy', 'items']);

        if ($branchId && $branchId !== 'all') {
            $query->where('branch_id', $branchId);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")->orWhere('mobile', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('order_status', $request->status);
        }

        if ($request->filled('payment_status') && $request->payment_status !== 'all') {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('payment_mode') && $request->payment_mode !== 'all') {
            $query->where('payment_mode', $request->payment_mode);
        }

        if ($request->filled('date')) {
            $query->whereDate('placed_at', $request->date);
        }

        $perPage = $request->get('per_page', 10);
        $orders = $query->latest('placed_at')->paginate($perPage)->withQueryString();

        $branches = Branch::where('status', 'active')->get();
        $deliveryBoys = DeliveryBoy::where('is_online', true)->where('status', 'active')->get();

        return view('admin.orders.index', compact('orders', 'branches', 'deliveryBoys'));
    }

    public function show(Order $order)
    {
        $order->load(['customer', 'branch', 'deliveryBoy', 'items.product', 'statusLogs', 'payments', 'coupon']);
        $availableDeliveryBoys = DeliveryBoy::where('branch_id', $order->branch_id)
            ->where('status', 'active')
            ->get();

        return view('admin.orders.show', compact('order', 'availableDeliveryBoys'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'order_status' => 'required|in:PLACED,CONFIRMED,PACKED,ASSIGNED,OUT_FOR_DELIVERY,DELIVERED,CANCELLED',
            'remarks' => 'nullable|string',
            'delivery_boy_id' => 'nullable|exists:delivery_boys,id',
        ]);

        $oldStatus = $order->order_status;
        $newStatus = $request->order_status;

        $order->order_status = $newStatus;

        if ($request->filled('delivery_boy_id')) {
            $order->delivery_boy_id = $request->delivery_boy_id;
        }

        if ($newStatus === 'DELIVERED') {
            $order->delivered_at = now();
            if ($order->payment_mode === 'COD') {
                $order->payment_status = 'PAID';
                $order->payments()->updateOrCreate(
                    ['order_id' => $order->id],
                    [
                        'transaction_id' => 'TXN-COD-' . rand(100000, 999999),
                        'payment_mode' => 'COD',
                        'amount' => $order->total_amount,
                        'status' => 'SUCCESS',
                        'collected_at' => now(),
                        'cod_verified' => true,
                    ]
                );
            }
        }

        $order->save();

        OrderStatusLog::create([
            'order_id' => $order->id,
            'from_status' => $oldStatus,
            'to_status' => $newStatus,
            'remarks' => $request->remarks ?: "Status updated from {$oldStatus} to {$newStatus}",
            'changed_by' => auth()->user()?->name ?: 'Admin',
        ]);

        // Trigger Notification
        Notification::create([
            'type' => 'order',
            'title' => "Order #{$order->order_number} is {$newStatus}",
            'message' => "Order #{$order->order_number} status changed to {$newStatus}.",
            'url' => "/admin/orders/{$order->id}",
            'is_read' => false,
        ]);

        return redirect()->back()->with('success', "Order #{$order->order_number} updated to {$newStatus}!");
    }

    public function assignView(Request $request)
    {
        $branchId = session('active_branch_id', 'all');

        $query = Order::whereIn('order_status', ['CONFIRMED', 'PACKED', 'ASSIGNED'])
            ->with(['customer', 'branch', 'deliveryBoy', 'items']);

        if ($branchId && $branchId !== 'all') {
            $query->where('branch_id', $branchId);
        }

        $orders = $query->latest('placed_at')->get();
        $deliveryBoys = DeliveryBoy::with('branch')->where('status', 'active')->get();

        return view('admin.orders.assign', compact('orders', 'deliveryBoys'));
    }

    public function assignDelivery(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'delivery_boy_id' => 'required|exists:delivery_boys,id',
        ]);

        $order = Order::findOrFail($request->order_id);
        $deliveryBoy = DeliveryBoy::findOrFail($request->delivery_boy_id);

        $oldStatus = $order->order_status;
        $order->delivery_boy_id = $deliveryBoy->id;
        $order->order_status = 'ASSIGNED';
        $order->save();

        OrderStatusLog::create([
            'order_id' => $order->id,
            'from_status' => $oldStatus,
            'to_status' => 'ASSIGNED',
            'remarks' => "Assigned to delivery boy {$deliveryBoy->name} ({$deliveryBoy->mobile})",
            'changed_by' => auth()->user()?->name ?: 'Admin',
        ]);

        Notification::create([
            'type' => 'delivery',
            'title' => "Order #{$order->order_number} Assigned",
            'message' => "Order assigned to {$deliveryBoy->name}.",
            'url' => "/admin/orders/{$order->id}",
            'is_read' => false,
        ]);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Order #{$order->order_number} successfully assigned to {$deliveryBoy->name}!",
            ]);
        }

        return redirect()->back()->with('success', "Order #{$order->order_number} successfully assigned to {$deliveryBoy->name}!");
    }

    public function history(Request $request)
    {
        $branchId = session('active_branch_id', 'all');
        $query = OrderStatusLog::with(['order.customer', 'order.branch']);

        if ($branchId && $branchId !== 'all') {
            $query->whereHas('order', function($oq) use ($branchId) {
                $oq->where('branch_id', $branchId);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('remarks', 'like', "%{$search}%")
                  ->orWhere('to_status', 'like', "%{$search}%")
                  ->orWhereHas('order', function($oq) use ($search) {
                      $oq->where('order_number', 'like', "%{$search}%");
                  });
            });
        }

        $perPage = $request->get('per_page', 15);
        $logs = $query->latest()->paginate($perPage)->withQueryString();

        return view('admin.orders.history', compact('logs'));
    }
}
