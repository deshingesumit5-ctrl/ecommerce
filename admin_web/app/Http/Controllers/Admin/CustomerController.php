<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::withCount('orders');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $perPage = $request->get('per_page', 10);
        $customers = $query->with('orders')->latest()->paginate($perPage)->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'mobile' => 'required|digits:10|unique:customers,mobile',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'status' => 'required|in:active,inactive,blocked',
        ]);

        Customer::create($validated);

        return redirect()->route('admin.customers.index')->with('success', 'Customer registered successfully!');
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'mobile' => 'required|digits:10|unique:customers,mobile,' . $customer->id,
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'status' => 'required|in:active,inactive,blocked',
        ]);

        $customer->update($validated);

        return redirect()->route('admin.customers.index')->with('success', 'Customer details updated successfully!');
    }

    public function toggleStatus(Customer $customer)
    {
        $customer->status = $customer->status === 'active' ? 'inactive' : 'active';
        $customer->save();

        return redirect()->back()->with('success', 'Customer status updated.');
    }

    public function show(Customer $customer)
    {
        $customer->load(['orders.items', 'orders.branch']);
        return response()->json($customer);
    }
}
