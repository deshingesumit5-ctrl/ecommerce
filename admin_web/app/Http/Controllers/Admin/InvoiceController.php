<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Setting;

class InvoiceController extends Controller
{
    public function show(Order $order)
    {
        $order->load(['customer', 'branch', 'deliveryBoy', 'items.product', 'coupon']);
        
        $gstPercent = (float) Setting::get('gst_percentage', 5);
        $storeName = Setting::get('store_name', 'Metaglobe Hyperlocal Mart');
        $supportPhone = Setting::get('support_phone', '9876543200');
        $supportEmail = Setting::get('support_email', 'admin@metaglobe.com');

        return view('admin.invoices.show', compact('order', 'gstPercent', 'storeName', 'supportPhone', 'supportEmail'));
    }
}
