<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DeliveryBoy;
use App\Models\Branch;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Hash;

class DeliveryBoyController extends Controller
{
    public function index(Request $request)
    {
        $branchId = session('active_branch_id', 'all');
        $query = DeliveryBoy::with('branch');

        if ($branchId && $branchId !== 'all') {
            $query->where('branch_id', $branchId);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhere('vehicle_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('online') && $request->online !== 'all') {
            $query->where('is_online', $request->online === 'online');
        }

        $perPage = $request->get('per_page', 10);
        $deliveryBoys = $query->withCount(['orders' => function($q) {
            $q->where('order_status', 'DELIVERED');
        }])->latest()->paginate($perPage)->withQueryString();

        $branches = Branch::where('status', 'active')->get();

        return view('admin.delivery_boys.index', compact('deliveryBoys', 'branches'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'is_online' => $request->boolean('is_online'),
        ]);

        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name' => 'required|string|max:255',
            'mobile' => 'required|digits:10|unique:delivery_boys,mobile',
            'vehicle_type' => 'required|string|max:100',
            'vehicle_number' => 'nullable|string|max:50',
            'license_number' => 'nullable|string|max:50',
            'username' => 'required|string|max:100|unique:delivery_boys,username',
            'password' => 'required|string|min:4|max:255',
            'is_online' => 'nullable|boolean',
            'status' => 'required|in:active,inactive,suspended',
        ]);

        $validated['is_online'] = (bool) $request->boolean('is_online');
        $validated['plain_password'] = $validated['password'];
        $validated['password'] = Hash::make($validated['password']);
        DeliveryBoy::create($validated);

        return redirect()->route('admin.delivery_boys.index')->with('success', 'Delivery Boy registered successfully!');
    }

    public function update(Request $request, DeliveryBoy $deliveryBoy)
    {
        $request->merge([
            'is_online' => $request->boolean('is_online'),
        ]);

        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name' => 'required|string|max:255',
            'mobile' => 'required|digits:10|unique:delivery_boys,mobile,' . $deliveryBoy->id,
            'vehicle_type' => 'required|string|max:100',
            'vehicle_number' => 'nullable|string|max:50',
            'license_number' => 'nullable|string|max:50',
            'username' => 'required|string|max:100|unique:delivery_boys,username,' . $deliveryBoy->id,
            'password' => 'nullable|string|min:4|max:255',
            'is_online' => 'nullable|boolean',
            'status' => 'required|in:active,inactive,suspended',
        ]);

        $validated['is_online'] = (bool) $request->boolean('is_online');
        if (!empty($validated['password'])) {
            $validated['plain_password'] = $validated['password'];
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }
        $deliveryBoy->update($validated);

        return redirect()->route('admin.delivery_boys.index')->with('success', 'Delivery Boy updated successfully!');
    }

    public function toggleOnline(DeliveryBoy $deliveryBoy)
    {
        $deliveryBoy->is_online = !$deliveryBoy->is_online;
        $deliveryBoy->save();

        return redirect()->back()->with('success', 'Delivery Boy is now ' . ($deliveryBoy->is_online ? 'ONLINE' : 'OFFLINE'));
    }

    public function toggleStatus(DeliveryBoy $deliveryBoy)
    {
        $deliveryBoy->status = $deliveryBoy->status === 'active' ? 'inactive' : 'active';
        $deliveryBoy->save();

        return redirect()->back()->with('success', 'Delivery Boy status updated.');
    }

    public function destroy(DeliveryBoy $deliveryBoy)
    {
        $deliveryBoy->delete();
        return redirect()->route('admin.delivery_boys.index')->with('success', 'Delivery Boy deleted successfully.');
    }

    public function codCollections(Request $request)
    {
        $branchId = session('active_branch_id', 'all');

        $query = Order::where('payment_mode', 'COD')
            ->whereIn('order_status', ['DELIVERED', 'OUT_FOR_DELIVERY'])
            ->with(['deliveryBoy', 'customer', 'branch', 'payments']);

        if ($branchId && $branchId !== 'all') {
            $query->where('branch_id', $branchId);
        }

        if ($request->filled('delivery_boy_id') && $request->delivery_boy_id !== 'all') {
            $query->where('delivery_boy_id', $request->delivery_boy_id);
        }

        if ($request->filled('reconciled') && $request->reconciled !== 'all') {
            if ($request->reconciled === 'verified') {
                $query->whereHas('payments', function($pq) {
                    $pq->where('cod_verified', true);
                });
            } else {
                $query->whereHas('payments', function($pq) {
                    $pq->where('cod_verified', false);
                });
            }
        }

        $perPage = $request->get('per_page', 10);
        $orders = $query->latest()->paginate($perPage)->withQueryString();

        $deliveryBoys = DeliveryBoy::where('status', 'active')->get();
        $totalCodCollected = Payment::where('payment_mode', 'COD')->where('status', 'SUCCESS')->sum('amount');
        $pendingVerification = Payment::where('payment_mode', 'COD')->where('cod_verified', false)->sum('amount');

        return view('admin.delivery_boys.cod', compact('orders', 'deliveryBoys', 'totalCodCollected', 'pendingVerification'));
    }

    public function verifyCod(Request $request, Order $order)
    {
        $payment = $order->payments()->where('payment_mode', 'COD')->first();
        if ($payment) {
            $payment->cod_verified = true;
            $payment->status = 'SUCCESS';
            $payment->collected_at = now();
            $payment->save();
        }

        return redirect()->back()->with('success', "COD payment of ₹{$order->total_amount} for Order #{$order->order_number} verified and reconciled!");
    }
}
