<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Branch;

class BranchController extends Controller
{
    public function index(Request $request)
    {
        $query = Branch::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('contact_phone', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $perPage = $request->get('per_page', 10);
        $branches = $query->withCount(['orders', 'deliveryBoys'])->latest()->paginate($perPage)->withQueryString();

        return view('admin.branches.index', compact('branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:branches,code',
            'contact_phone' => 'required|digits:10',
            'address' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius_km' => 'required|numeric|min:0.5|max:100',
            'status' => 'required|in:active,inactive',
        ]);

        Branch::create($validated);

        return redirect()->route('admin.branches.index')->with('success', 'Store created successfully!');
    }

    public function update(Request $request, Branch $branch)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:branches,code,' . $branch->id,
            'contact_phone' => 'required|digits:10',
            'address' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius_km' => 'required|numeric|min:0.5|max:100',
            'status' => 'required|in:active,inactive',
        ]);

        $branch->update($validated);

        return redirect()->route('admin.branches.index')->with('success', 'Store updated successfully!');
    }

    public function toggleStatus(Branch $branch)
    {
        $branch->status = $branch->status === 'active' ? 'inactive' : 'active';
        $branch->save();

        return redirect()->back()->with('success', 'Store status updated to ' . ucfirst($branch->status));
    }

    public function destroy(Branch $branch)
    {
        $branch->delete();
        return redirect()->route('admin.branches.index')->with('success', 'Store deleted successfully.');
    }

    public function radius(Request $request)
    {
        $branches = Branch::all();
        return view('admin.branches.radius', compact('branches'));
    }

    public function updateRadius(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'radius_km' => 'required|numeric|min:0.5|max:100',
        ]);

        $branch = Branch::findOrFail($request->branch_id);
        $branch->radius_km = $request->radius_km;
        $branch->save();

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json(['success' => true, 'message' => "Delivery radius for {$branch->name} updated to {$branch->radius_km} KM."]);
        }

        return redirect()->back()->with('success', "Delivery radius for {$branch->name} updated to {$branch->radius_km} KM.");
    }
}
