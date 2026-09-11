<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Discount;
use App\Models\Category;

class DiscountController extends Controller
{
    public function index(Request $request)
    {
        $query = Discount::with('category');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('title', 'like', "%{$search}%");
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $perPage = $request->get('per_page', 10);
        $discounts = $query->latest()->paginate($perPage)->withQueryString();
        $categories = Category::where('status', 'active')->orderBy('display_order')->get();

        return view('admin.discounts.index', compact('discounts', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0.01',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:active,inactive',
        ]);

        Discount::create($validated);

        return redirect()->route('admin.discounts.index')->with('success', 'Discount promotion created successfully!');
    }

    public function update(Request $request, Discount $discount)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0.01',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:active,inactive',
        ]);

        $discount->update($validated);

        return redirect()->route('admin.discounts.index')->with('success', 'Discount promotion updated successfully!');
    }

    public function toggleStatus(Discount $discount)
    {
        $discount->status = $discount->status === 'active' ? 'inactive' : 'active';
        $discount->save();

        return redirect()->back()->with('success', 'Discount status updated.');
    }

    public function destroy(Discount $discount)
    {
        $discount->delete();
        return redirect()->route('admin.discounts.index')->with('success', 'Discount deleted successfully.');
    }
}
