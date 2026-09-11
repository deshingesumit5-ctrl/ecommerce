<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Category;
use App\Models\SubCategory;

class SubCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = SubCategory::with('category');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhereHas('category', function($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('category_id') && $request->category_id !== 'all') {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $perPage = $request->get('per_page', 10);
        $subCategories = $query->withCount('products')->orderBy('display_order')->paginate($perPage)->withQueryString();
        $categories = Category::where('status', 'active')->orderBy('display_order')->get();

        return view('admin.subcategories.index', compact('subCategories', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'image' => 'nullable|string',
            'display_order' => 'required|integer|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        SubCategory::create($validated);

        return redirect()->route('admin.subcategories.index')->with('success', 'Sub-Category created successfully!');
    }

    public function update(Request $request, SubCategory $subCategory)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'image' => 'nullable|string',
            'display_order' => 'required|integer|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $subCategory->update($validated);

        return redirect()->route('admin.subcategories.index')->with('success', 'Sub-Category updated successfully!');
    }

    public function toggleStatus(SubCategory $subCategory)
    {
        $subCategory->status = $subCategory->status === 'active' ? 'inactive' : 'active';
        $subCategory->save();

        return redirect()->back()->with('success', 'Sub-Category status updated.');
    }

    public function destroy(SubCategory $subCategory)
    {
        $subCategory->delete();
        return redirect()->route('admin.subcategories.index')->with('success', 'Sub-Category deleted successfully.');
    }

    public function getByCategory(Category $category)
    {
        return response()->json($category->subCategories()->where('status', 'active')->get());
    }
}
