<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Branch;
use App\Models\Payment;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());
        $branchId = $request->get('branch_id', 'all');

        $query = Order::whereDate('placed_at', '>=', $startDate)
                      ->whereDate('placed_at', '<=', $endDate);

        if ($branchId && $branchId !== 'all') {
            $query->where('branch_id', $branchId);
        }

        $totalOrders = (clone $query)->count();
        $totalRevenue = (clone $query)->where('payment_status', 'PAID')->sum('total_amount');
        $deliveredOrders = (clone $query)->where('order_status', 'DELIVERED')->count();
        $cancelledOrders = (clone $query)->where('order_status', 'CANCELLED')->count();

        // Branch-wise summary
        $branchStats = Branch::withCount(['orders' => function($q) use ($startDate, $endDate) {
            $q->whereDate('placed_at', '>=', $startDate)->whereDate('placed_at', '<=', $endDate);
        }])->get();

        // Payment mode breakdown
        $paymentBreakdown = (clone $query)->where('payment_status', 'PAID')
            ->select('payment_mode', DB::raw('count(*) as count'), DB::raw('sum(total_amount) as total'))
            ->groupBy('payment_mode')
            ->get();

        // Top Selling Products
        $topProducts = OrderItem::select('product_name', DB::raw('sum(quantity) as total_qty'), DB::raw('sum(total) as total_sales'))
            ->whereHas('order', function($oq) use ($startDate, $endDate, $branchId) {
                $oq->whereDate('placed_at', '>=', $startDate)->whereDate('placed_at', '<=', $endDate);
                if ($branchId && $branchId !== 'all') {
                    $oq->where('branch_id', $branchId);
                }
            })
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->take(8)
            ->get();

        $branches = Branch::where('status', 'active')->get();

        return view('admin.reports.index', compact(
            'totalOrders',
            'totalRevenue',
            'deliveredOrders',
            'cancelledOrders',
            'branchStats',
            'paymentBreakdown',
            'topProducts',
            'startDate',
            'endDate',
            'branchId',
            'branches'
        ));
    }
}
