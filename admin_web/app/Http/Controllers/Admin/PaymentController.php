<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Branch;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $branchId = session('active_branch_id', 'all');
        $query = Payment::with(['order.customer', 'order.branch', 'order.deliveryBoy']);

        if ($branchId && $branchId !== 'all') {
            $query->whereHas('order', function($oq) use ($branchId) {
                $oq->where('branch_id', $branchId);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                  ->orWhereHas('order', function($oq) use ($search) {
                      $oq->where('order_number', 'like', "%{$search}%")
                         ->orWhereHas('customer', function($cq) use ($search) {
                             $cq->where('name', 'like', "%{$search}%");
                         });
                  });
            });
        }

        if ($request->filled('payment_mode') && $request->payment_mode !== 'all') {
            $query->where('payment_mode', $request->payment_mode);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $perPage = $request->get('per_page', 10);
        $payments = $query->latest()->paginate($perPage)->withQueryString();

        $totalCollected = Payment::where('status', 'SUCCESS')->sum('amount');
        $onlineCollected = Payment::where('status', 'SUCCESS')->where('payment_mode', 'ONLINE')->sum('amount');
        $codCollected = Payment::where('status', 'SUCCESS')->where('payment_mode', 'COD')->sum('amount');

        return view('admin.payments.index', compact('payments', 'totalCollected', 'onlineCollected', 'codCollected'));
    }
}
