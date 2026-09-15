<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Product;
use App\Models\Category;
use App\Models\SubCategory;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'subCategory']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('unit', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id') && $request->category_id !== 'all') {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('stock') && $request->stock !== 'all') {
            $query->where('in_stock', $request->stock === 'in_stock');
        }

        $perPage = $request->get('per_page', 10);
        $products = $query->latest()->paginate($perPage)->withQueryString();

        $categories = Category::where('status', 'active')->with('subCategories')->orderBy('display_order')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'nullable|exists:sub_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:10240',
            'unit' => 'required|string|max:50',
            'base_price' => 'required|numeric|min:0.01',
            'current_daily_price' => 'required|numeric|min:0.01',
            'in_stock' => 'nullable',
            'status' => 'required|in:active,inactive',
        ]);

        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $extension = $file->getClientOriginalExtension() ?: 'png';
            $filename = time() . '_' . Str::random(10) . '.' . $extension;
            $uploadDir = public_path('uploads/products');
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }
            $file->move($uploadDir, $filename);
            $validated['image'] = url('uploads/products/' . $filename);
        }

        unset($validated['image_file']);
        $validated['slug'] = Str::slug($validated['name']) . '-' . rand(100, 999);
        $validated['in_stock'] = $request->has('in_stock') && ($request->in_stock == '1' || $request->in_stock == 'on' || $request->in_stock === true || $request->boolean('in_stock'));

        Product::create($validated);

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully!');
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'nullable|exists:sub_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:10240',
            'remove_image' => 'nullable|string',
            'unit' => 'required|string|max:50',
            'base_price' => 'required|numeric|min:0.01',
            'current_daily_price' => 'required|numeric|min:0.01',
            'in_stock' => 'nullable',
            'status' => 'required|in:active,inactive',
        ]);

        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $extension = $file->getClientOriginalExtension() ?: 'png';
            $filename = time() . '_' . Str::random(10) . '.' . $extension;
            $uploadDir = public_path('uploads/products');
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }
            $file->move($uploadDir, $filename);
            $validated['image'] = url('uploads/products/' . $filename);
        } elseif ($request->input('remove_image') === '1') {
            $validated['image'] = null;
        }

        unset($validated['image_file']);
        unset($validated['remove_image']);
        $validated['slug'] = Str::slug($validated['name']) . '-' . $product->id;
        $validated['in_stock'] = $request->has('in_stock') && ($request->in_stock == '1' || $request->in_stock == 'on' || $request->in_stock === true || $request->boolean('in_stock'));

        $product->update($validated);

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully!');
    }

    public function toggleStatus(Product $product)
    {
        $product->status = $product->status === 'active' ? 'inactive' : 'active';
        $product->save();

        return redirect()->back()->with('success', 'Product status updated.');
    }

    public function toggleStock(Product $product)
    {
        $product->in_stock = !$product->in_stock;
        $product->save();

        return redirect()->back()->with('success', 'Product stock status updated to ' . ($product->in_stock ? 'In Stock' : 'Out of Stock'));
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully.');
    }
}
