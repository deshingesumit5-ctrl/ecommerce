<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%");
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $perPage = $request->get('per_page', 10);
        $categories = $query->withCount(['subCategories', 'products'])->orderBy('display_order')->paginate($perPage)->withQueryString();

        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|string',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:10240',
            'display_order' => 'required|integer|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $extension = $file->getClientOriginalExtension() ?: 'png';
            $filename = time() . '_' . Str::random(10) . '.' . $extension;
            $uploadDir = public_path('uploads/categories');
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }
            $file->move($uploadDir, $filename);
            $validated['image'] = url('uploads/categories/' . $filename);
        }

        unset($validated['image_file']);
        $validated['slug'] = Str::slug($validated['name']);
        Category::create($validated);

        return redirect()->route('admin.categories.index')->with('success', 'Category created successfully!');
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|string',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:10240',
            'remove_image' => 'nullable|string',
            'display_order' => 'required|integer|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $extension = $file->getClientOriginalExtension() ?: 'png';
            $filename = time() . '_' . Str::random(10) . '.' . $extension;
            $uploadDir = public_path('uploads/categories');
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }
            $file->move($uploadDir, $filename);
            $validated['image'] = url('uploads/categories/' . $filename);
        } elseif ($request->input('remove_image') === '1') {
            $validated['image'] = null;
        }

        unset($validated['image_file']);
        unset($validated['remove_image']);
        $validated['slug'] = Str::slug($validated['name']);
        $category->update($validated);

        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully!');
    }

    public function toggleStatus(Category $category)
    {
        $category->status = $category->status === 'active' ? 'inactive' : 'active';
        $category->save();

        return redirect()->back()->with('success', 'Category status updated.');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully.');
    }
}
