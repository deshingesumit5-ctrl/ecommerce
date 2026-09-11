<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\Order;
use App\Models\DeliveryBoy;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Category;
use App\Models\DailyPrice;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $branchId = session('active_branch_id', 'all');

        $ordersQuery = Order::query();
        $deliveryBoysQuery = DeliveryBoy::query();
        $productsQuery = Product::query();

        if ($branchId && $branchId !== 'all') {
            $ordersQuery->where('branch_id', $branchId);
            $deliveryBoysQuery->where('branch_id', $branchId);
        }

        // Metrics
        $todayStart = Carbon::today()->startOfDay();
        $todaySales = (clone $ordersQuery)->where('placed_at', '>=', $todayStart)
            ->where('payment_status', 'PAID')
            ->sum('total_amount');

        $todayOrdersCount = (clone $ordersQuery)->where('placed_at', '>=', $todayStart)->count();
        $pendingOrdersCount = (clone $ordersQuery)->whereIn('order_status', ['PLACED', 'CONFIRMED'])->count();
        $packedOrdersCount = (clone $ordersQuery)->where('order_status', 'PACKED')->count();
        $outForDeliveryCount = (clone $ordersQuery)->where('order_status', 'OUT_FOR_DELIVERY')->count();
        $deliveredCount = (clone $ordersQuery)->where('order_status', 'DELIVERED')->count();
        $cancelledCount = (clone $ordersQuery)->where('order_status', 'CANCELLED')->count();

        $activeDeliveryBoys = (clone $deliveryBoysQuery)->where('is_online', true)->where('status', 'active')->count();
        $totalDeliveryBoys = (clone $deliveryBoysQuery)->count();
        $totalCustomers = Customer::count();
        $totalProducts = Product::count();
        $lowStockProducts = Product::where('in_stock', false)->orWhere('status', 'inactive')->take(5)->get();

        // Recent Orders with relations
        $recentOrders = (clone $ordersQuery)->with(['customer', 'branch', 'deliveryBoy'])
            ->latest('placed_at')
            ->take(8)
            ->get();

        // Fleet List
        $deliveryFleet = (clone $deliveryBoysQuery)->with('branch')->take(6)->get();

        // Branches for Map & Filter
        $branches = Branch::where('status', 'active')->get();
        $allBranches = Branch::all();

        // Quick Daily Price Products
        $quickPriceProducts = Product::with('category')->where('status', 'active')->take(6)->get();

        return view('admin.dashboard.index', compact(
            'todaySales',
            'todayOrdersCount',
            'pendingOrdersCount',
            'packedOrdersCount',
            'outForDeliveryCount',
            'deliveredCount',
            'cancelledCount',
            'activeDeliveryBoys',
            'totalDeliveryBoys',
            'totalCustomers',
            'totalProducts',
            'recentOrders',
            'deliveryFleet',
            'branches',
            'allBranches',
            'quickPriceProducts',
            'lowStockProducts',
            'branchId'
        ));
    }
}
