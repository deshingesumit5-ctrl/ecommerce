<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $deliveryBoys = DeliveryBoy::with('branch')->where('status', 'active')->get();

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
        $previousDeliveryBoyId = $order->delivery_boy_id;

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

        $deliveryBoy = $order->delivery_boy_id ? DeliveryBoy::with('branch')->find($order->delivery_boy_id) : null;
        $isReassignment = ($previousDeliveryBoyId && $order->delivery_boy_id && $previousDeliveryBoyId != $order->delivery_boy_id);

        $logRemark = $request->remarks ?: "Status updated from {$oldStatus} to {$newStatus}";
        if ($isReassignment && $deliveryBoy) {
            $prevBoy = DeliveryBoy::find($previousDeliveryBoyId);
            $logRemark .= " (Reassigned from " . ($prevBoy?->name ?? 'Previous Rider') . " to {$deliveryBoy->name})";
        }

        OrderStatusLog::create([
            'order_id' => $order->id,
            'from_status' => $oldStatus,
            'to_status' => $newStatus,
            'remarks' => $logRemark,
            'changed_by' => auth()->user()?->name ?: 'Admin',
        ]);

        // Trigger Admin Notification
        Notification::create([
            'type' => $order->delivery_boy_id ? 'delivery' : 'order',
            'title' => "Order #{$order->order_number} is {$newStatus}",
            'message' => "Order #{$order->order_number} status changed to {$newStatus}" . ($deliveryBoy ? " (Assigned to {$deliveryBoy->name})" : "") . ".",
            'url' => "/admin/orders/{$order->id}",
            'is_read' => false,
        ]);

        // Sync with delivery_boy_app & customer_app
        if ($deliveryBoy) {
            $this->syncToDeliveryAndCustomerApp($order, $deliveryBoy, $isReassignment, $previousDeliveryBoyId);
        }

        return redirect()->back()->with('success', "Order #{$order->order_number} updated to {$newStatus}!");
    }

    public function assignView(Request $request)
    {
        $branchId = session('active_branch_id', 'all');
        $filterTab = $request->get('tab', 'unassigned'); // unassigned, assigned, all

        $query = Order::with(['customer', 'branch', 'deliveryBoy', 'items']);

        if ($branchId && $branchId !== 'all') {
            $query->where('branch_id', $branchId);
        }

        if ($filterTab === 'unassigned') {
            $query->where(function($q) {
                $q->whereNull('delivery_boy_id')
                  ->orWhereIn('order_status', ['PLACED', 'CONFIRMED', 'PACKED']);
            })->whereNotIn('order_status', ['DELIVERED', 'CANCELLED']);
        } elseif ($filterTab === 'assigned') {
            $query->whereNotNull('delivery_boy_id');
        } else {
            // 'all': Keep all orders in the list (data remains and only status changes)
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")->orWhere('mobile', 'like', "%{$search}%");
                  })
                  ->orWhereHas('deliveryBoy', function($dq) use ($search) {
                      $dq->where('name', 'like', "%{$search}%")->orWhere('mobile', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->latest('placed_at')->get();
        
        $deliveryBoysQuery = DeliveryBoy::with('branch')->where('status', 'active');
        if ($branchId && $branchId !== 'all') {
            $deliveryBoysQuery->where('branch_id', $branchId);
        }
        $deliveryBoys = $deliveryBoysQuery->get();

        // Calculate counts for tab badges
        $unassignedCount = Order::when($branchId !== 'all', fn($q) => $q->where('branch_id', $branchId))
            ->where(function($q) {
                $q->whereNull('delivery_boy_id')
                  ->orWhereIn('order_status', ['PLACED', 'CONFIRMED', 'PACKED']);
            })
            ->whereNotIn('order_status', ['DELIVERED', 'CANCELLED'])
            ->count();

        $assignedCount = Order::when($branchId !== 'all', fn($q) => $q->where('branch_id', $branchId))
            ->whereNotNull('delivery_boy_id')
            ->count();

        $allUpcomingCount = Order::when($branchId !== 'all', fn($q) => $q->where('branch_id', $branchId))
            ->count();

        $branches = Branch::where('status', 'active')->get();

        return view('admin.orders.assign', compact('orders', 'deliveryBoys', 'branches', 'filterTab', 'unassignedCount', 'assignedCount', 'allUpcomingCount'));
    }

    public function assignDelivery(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'delivery_boy_id' => 'required|exists:delivery_boys,id',
        ]);

        $order = Order::with(['customer', 'branch', 'items'])->findOrFail($request->order_id);
        $deliveryBoy = DeliveryBoy::with('branch')->findOrFail($request->delivery_boy_id);

        $oldStatus = $order->order_status;
        $previousDeliveryBoyId = $order->delivery_boy_id;
        $previousBoy = $previousDeliveryBoyId ? DeliveryBoy::find($previousDeliveryBoyId) : null;
        $isReassignment = ($previousDeliveryBoyId && $previousDeliveryBoyId != $deliveryBoy->id);

        $order->delivery_boy_id = $deliveryBoy->id;
        $order->order_status = 'ASSIGNED';
        $order->save();

        $logRemark = $isReassignment
            ? "Order reassigned from " . ($previousBoy?->name ?? 'Previous Rider') . " ({$previousBoy?->mobile}) to {$deliveryBoy->name} ({$deliveryBoy->mobile})"
            : "Assigned to delivery partner {$deliveryBoy->name} ({$deliveryBoy->mobile}) for 3km area dispatch";

        OrderStatusLog::create([
            'order_id' => $order->id,
            'from_status' => $oldStatus,
            'to_status' => 'ASSIGNED',
            'remarks' => $logRemark,
            'changed_by' => auth()->user()?->name ?: 'Admin',
        ]);

        $branchName = $deliveryBoy->branch ? $deliveryBoy->branch->name : 'Store';
        Notification::create([
            'type' => 'delivery',
            'title' => $isReassignment ? "Order #{$order->order_number} Reassigned" : "Order #{$order->order_number} Assigned",
            'message' => $isReassignment
                ? "Order #{$order->order_number} reassigned to {$deliveryBoy->name} ({$branchName})."
                : "Order #{$order->order_number} assigned to {$deliveryBoy->name} ({$branchName}).",
            'url' => "/admin/orders/{$order->id}",
            'is_read' => false,
        ]);

        // Sync and dispatch notification to delivery_boy_app and customer_app
        $this->syncToDeliveryAndCustomerApp($order, $deliveryBoy, $isReassignment, $previousDeliveryBoyId);

        $successMsg = $isReassignment
            ? "Order #{$order->order_number} successfully reassigned to {$deliveryBoy->name}! Alert sent to rider app."
            : "Order #{$order->order_number} successfully assigned to {$deliveryBoy->name}! Notification sent to delivery app.";

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $successMsg,
            ]);
        }

        return redirect()->back()->with('success', $successMsg);
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

    /**
     * Helper to sync order and notifications to delivery_boy_app and customer_app databases in real-time
     */
    protected function syncToDeliveryAndCustomerApp(Order $order, DeliveryBoy $deliveryBoy, $isReassignment = false, $previousDeliveryBoyId = null)
    {
        // 1. Sync to delivery_boy_app database
        try {
            $itemsJson = json_encode($order->items->map(function ($item) {
                return [
                    'name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'unit' => $item->unit ?? 'kg',
                    'price' => (float) $item->price,
                    'total' => (float) $item->total,
                ];
            }));

            $existingAssigned = DB::table('delivery_boy_app.assigned_orders')
                ->where('order_number', $order->order_number)
                ->first();

            $assignedPayload = [
                'order_number' => $order->order_number,
                'delivery_boy_id' => $deliveryBoy->id,
                'store_name' => $order->branch?->name ?? 'Satara Store',
                'customer_name' => $order->customer?->name ?? 'Customer',
                'customer_mobile' => $order->customer?->mobile ?? '9876543210',
                'delivery_address' => $order->delivery_address ?: ($order->customer?->address ?? 'Doorstep Delivery'),
                'order_amount' => (float) $order->total_amount,
                'payment_mode' => $order->payment_mode,
                'payment_status' => $order->payment_status,
                'cod_amount_to_collect' => ($order->payment_mode === 'COD' && $order->payment_status !== 'PAID') ? (float) $order->total_amount : 0,
                'is_cod_collected' => ($order->payment_mode === 'ONLINE' || $order->payment_status === 'PAID') ? 1 : 0,
                'delivery_status' => $order->order_status === 'OUT_FOR_DELIVERY' ? 'OUT_FOR_DELIVERY' : 'ASSIGNED',
                'items' => $itemsJson,
                'customer_notes' => $order->special_notes ?: '3KM Area Hyperlocal Delivery',
                'assigned_time' => now()->format('h:i A'),
                'updated_at' => now(),
            ];

            if ($existingAssigned) {
                DB::table('delivery_boy_app.assigned_orders')
                    ->where('order_number', $order->order_number)
                    ->update($assignedPayload);
            } else {
                $assignedPayload['created_at'] = now();
                DB::table('delivery_boy_app.assigned_orders')->insert($assignedPayload);
            }

            // Notification for newly assigned delivery boy
            DB::table('delivery_boy_app.delivery_notifications')->insert([
                'delivery_boy_id' => $deliveryBoy->id,
                'title' => $isReassignment ? "📦 Order #{$order->order_number} Reassigned to You!" : "📦 New Order Assigned: #{$order->order_number}",
                'message' => "Order #{$order->order_number} (₹" . number_format($order->total_amount, 2) . ") in your 3km service area assigned to you. Deliver to " . ($order->customer?->name ?? 'Customer') . " at " . ($order->delivery_address ?: 'Customer Address') . ".",
                'order_id' => $order->order_number,
                'is_read' => 0,
                'created_at' => now(),
            ]);

            // If reassigned from another driver, notify previous driver of reassignment
            if ($isReassignment && $previousDeliveryBoyId) {
                DB::table('delivery_boy_app.delivery_notifications')->insert([
                    'delivery_boy_id' => $previousDeliveryBoyId,
                    'title' => "ℹ️ Order #{$order->order_number} Reassigned",
                    'message' => "Order #{$order->order_number} has been reassigned to another delivery boy by admin.",
                    'order_id' => $order->order_number,
                    'is_read' => 0,
                    'created_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Delivery boy DB sync warning: ' . $e->getMessage());
        }

        // 2. Sync to customer_app database & notifications
        try {
            DB::table('customer_app.customer_orders')
                ->where('order_number', $order->order_number)
                ->update([
                    'order_status' => $order->order_status,
                    'updated_at' => now(),
                ]);

            DB::table('customer_app.customer_notifications')->insert([
                'title' => $isReassignment ? 'Delivery Partner Reassigned' : 'Delivery Partner Assigned!',
                'message' => "Your order #{$order->order_number} is assigned to delivery partner {$deliveryBoy->name} ({$deliveryBoy->mobile}) for delivery.",
                'is_read' => 0,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Customer DB sync warning: ' . $e->getMessage());
        }
    }

    public function destroy(Order $order)
    {
        $orderNumber = $order->order_number;

        // Clean up from delivery_boy_app if exists
        try {
            DB::table('delivery_boy_app.assigned_orders')->where('order_number', $orderNumber)->delete();
            DB::table('delivery_boy_app.delivery_notifications')->where('order_id', $orderNumber)->delete();
        } catch (\Throwable $e) {}

        // Clean up from customer_app if exists
        try {
            DB::table('customer_app.customer_orders')->where('order_number', $orderNumber)->delete();
        } catch (\Throwable $e) {}

        // Clean up relations
        $order->items()->delete();
        $order->statusLogs()->delete();
        $order->payments()->delete();
        $order->delete();

        return redirect()->back()->with('success', "Order #{$orderNumber} deleted successfully.");
    }
}
