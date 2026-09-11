<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Branch;
use App\Models\DailyPrice;
use App\Models\Category;

class DailyPriceController extends Controller
{
    public function index(Request $request)
    {
        $branches = Branch::where('status', 'active')->get();
        $selectedBranchId = $request->get('branch_id', $branches->first()?->id);
        $selectedCategoryId = $request->get('category_id', 'all');

        $query = Product::with(['category', 'dailyPrices' => function($q) use ($selectedBranchId) {
            $q->where('branch_id', $selectedBranchId)->latest('effective_date');
        }])->where('status', 'active');

        if ($selectedCategoryId && $selectedCategoryId !== 'all') {
            $query->where('category_id', $selectedCategoryId);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        $products = $query->orderBy('name')->paginate(15)->withQueryString();
        $categories = Category::where('status', 'active')->orderBy('display_order')->get();

        return view('admin.pricing.daily', compact('products', 'branches', 'categories', 'selectedBranchId', 'selectedCategoryId'));
    }

    public function updateSingle(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'branch_id' => 'required|exists:branches,id',
            'price' => 'required|numeric|min:0.01',
        ]);

        $product = Product::findOrFail($request->product_id);
        $product->current_daily_price = $request->price;
        $product->save();

        DailyPrice::updateOrCreate(
            [
                'product_id' => $request->product_id,
                'branch_id' => $request->branch_id,
                'effective_date' => now()->toDateString(),
            ],
            [
                'price' => $request->price,
            ]
        );

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Updated {$product->name} daily price to ₹{$request->price}",
            ]);
        }

        return redirect()->back()->with('success', "Updated {$product->name} daily price to ₹{$request->price}");
    }

    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'prices' => 'required|array',
            'prices.*' => 'required|numeric|min:0.01',
            'branch_id' => 'required|exists:branches,id',
        ]);

        $branchId = $request->branch_id;
        $today = now()->toDateString();
        $count = 0;

        foreach ($request->prices as $productId => $price) {
            $product = Product::find($productId);
            if ($product) {
                $product->current_daily_price = $price;
                $product->save();

                DailyPrice::updateOrCreate(
                    [
                        'product_id' => $productId,
                        'branch_id' => $branchId,
                        'effective_date' => $today,
                    ],
                    [
                        'price' => $price,
                    ]
                );
                $count++;
            }
        }

        return redirect()->back()->with('success', "Successfully updated daily prices for {$count} products in 1 click!");
    }
}
