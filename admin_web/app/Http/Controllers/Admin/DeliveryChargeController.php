<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DeliveryChargeConfig;
use App\Models\Branch;

class DeliveryChargeController extends Controller
{
    public function index(Request $request)
    {
        $configs = DeliveryChargeConfig::with('branch')->get();
        $branches = Branch::where('status', 'active')->get();

        return view('admin.delivery_charges.index', compact('configs', 'branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'min_order_free_delivery' => 'required|numeric|min:0',
            'standard_charge' => 'required|numeric|min:0',
            'express_charge' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        DeliveryChargeConfig::create($validated);

        return redirect()->route('admin.delivery-charges.index')->with('success', 'Delivery charge rule configured successfully!');
    }

    public function update(Request $request, DeliveryChargeConfig $deliveryCharge)
    {
        $validated = $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'min_order_free_delivery' => 'required|numeric|min:0',
            'standard_charge' => 'required|numeric|min:0',
            'express_charge' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        $deliveryCharge->update($validated);

        return redirect()->route('admin.delivery-charges.index')->with('success', 'Delivery charge rule updated successfully!');
    }

    public function destroy(DeliveryChargeConfig $deliveryCharge)
    {
        $deliveryCharge->delete();
        return redirect()->route('admin.delivery-charges.index')->with('success', 'Delivery charge rule deleted.');
    }
}
