<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Category;
use App\Models\Branch;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\DeliveryBoy;
use App\Models\Customer;
use App\Models\Coupon;
use Illuminate\Support\Facades\Hash;

// Helper to add CORS headers
if (!function_exists('corsResponse')) {
    function corsResponse($data, $status = 200) {
        return response()->json($data, $status)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
    }
}

function sendSmsOtp($mobile, $otp) {
    try {
        $authKey = env('MSG91_AUTH_KEY');
        $templateId = env('MSG91_TEMPLATE_ID');
        $senderId = env('MSG91_SENDER_ID', 'YOURID');

        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'authkey' => $authKey,
            'Content-Type' => 'application/json',
        ])->post('https://control.msg91.com/api/v5/flow/', [
            'template_id' => $templateId,
            'sender' => $senderId,
            'short_url' => '0',
            'mobiles' => '91' . $mobile,
            'OTP' => $otp,
        ]);

        \Illuminate\Support\Facades\Log::info('OTP SMS response', [
            'mobile' => $mobile,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return $response->successful();
    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\Log::error('OTP SMS failed: ' . $e->getMessage());
        return false;
    }
}

Route::options('{any}', function() {
    return response('', 200)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
})->where('any', '.*');

// 1. Products API (Dynamic from Admin Product Master)
Route::get('/products', function (Request $request) {
    $query = Product::with(['category', 'subCategory'])
        ->where('status', 'active');

    if ($request->has('in_stock_only') && $request->boolean('in_stock_only')) {
        $query->where('in_stock', true);
    }

    if ($request->filled('category_id')) {
        $query->where('category_id', $request->category_id);
    }

    $products = $query->latest()->get()->map(function ($p) {
        $img = $p->image;
        if ($img && !str_starts_with($img, 'http://') && !str_starts_with($img, 'https://')) {
            $img = url($img);
        }
        return [
            'id' => $p->id,
            'category_id' => $p->category_id,
            'category_name' => $p->category?->name ?? 'General',
            'sub_category_id' => $p->sub_category_id,
            'name' => $p->name,
            'description' => $p->description ?? '',
            'unit' => $p->unit,
            'base_price' => (float) $p->base_price,
            'daily_price' => (float) $p->current_daily_price,
            'is_available' => (bool) $p->in_stock,
            'image_url' => $img ?: 'https://images.unsplash.com/photo-1546094096-0df4bcaaa337?w=400',
        ];
    });

    return corsResponse($products);
});

// 2. Categories API
Route::get('/categories', function () {
    $categories = Category::where('status', 'active')
        ->orderBy('display_order')
        ->get()
        ->map(function ($c) {
            $img = $c->image;
            if ($img && !str_starts_with($img, 'http://') && !str_starts_with($img, 'https://')) {
                $img = url($img);
            }
            return [
                'id' => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
                'image' => $img,
            ];
        });

    return corsResponse($categories);
});

// 3. Stores / Branches API
Route::get('/stores', function () {
    $branches = Branch::where('status', 'active')->get()->map(function ($b) {
        return [
            'id' => $b->id,
            'name' => $b->name,
            'address' => $b->address,
            'contact' => $b->contact_phone,
            'latitude' => (float) $b->latitude,
            'longitude' => (float) $b->longitude,
            'delivery_radius_km' => (float) $b->radius_km,
            'is_active' => $b->status === 'active',
        ];
    });

    return corsResponse($branches);
});

// 4. Coupons API (Dynamic from Admin Coupon Management)
Route::get('/coupons', function () {
    $coupons = Coupon::where('status', 'active')->get()->map(function ($c) {
        return [
            'id' => $c->id,
            'code' => $c->code,
            'description' => $c->description,
            'discount_type' => $c->discount_type,
            'discount_value' => (float) $c->discount_value,
            'min_order_amount' => (float) $c->min_order_amount,
            'min_order_value' => (float) $c->min_order_amount,
            'max_discount_amount' => $c->max_discount_amount ? (float) $c->max_discount_amount : null,
            'max_discount' => $c->max_discount_amount ? (float) $c->max_discount_amount : null,
            'usage_limit' => (int) $c->usage_limit,
            'times_used' => (int) $c->times_used,
            'start_date' => $c->start_date ? date('Y-m-d', strtotime($c->start_date)) : null,
            'end_date' => $c->end_date ? date('Y-m-d', strtotime($c->end_date)) : null,
            'status' => $c->status,
        ];
    });

    return corsResponse($coupons);
});

Route::post('/coupons/apply', function (Request $request) {
    $data = $request->json()->all();
    if (empty($data)) $data = $request->all();
    if (empty($data)) $data = json_decode($request->getContent(), true) ?: [];

    $code = strtoupper(trim((string) ($data['code'] ?? $request->input('code') ?? '')));
    $subtotal = (float) ($data['subtotal'] ?? $request->input('subtotal') ?? 0);

    if (empty($code)) {
        return corsResponse([
            'success' => false,
            'message' => 'Please enter a coupon code.',
        ], 200);
    }

    $coupon = Coupon::whereRaw('UPPER(code) = ?', [$code])->first();

    if (!$coupon || $coupon->status !== 'active') {
        return corsResponse([
            'success' => false,
            'message' => 'Invalid coupon code.',
        ], 200);
    }

    // Check expiration date
    $today = date('Y-m-d');
    if (!empty($coupon->end_date)) {
        $endDate = date('Y-m-d', strtotime($coupon->end_date));
        if ($today > $endDate) {
            return corsResponse([
                'success' => false,
                'message' => 'Coupon validity expired',
            ], 200);
        }
    }

    // Check start date
    if (!empty($coupon->start_date)) {
        $startDate = date('Y-m-d', strtotime($coupon->start_date));
        if ($today < $startDate) {
            return corsResponse([
                'success' => false,
                'message' => 'Coupon is not active yet.',
            ], 200);
        }
    }

    // Check usage limit
    if ($coupon->usage_limit > 0 && $coupon->times_used >= $coupon->usage_limit) {
        return corsResponse([
            'success' => false,
            'message' => 'Coupon usage limit reached.',
        ], 200);
    }

    // Check minimum order amount
    $minOrder = (float) $coupon->min_order_amount;
    if ($subtotal < $minOrder) {
        $formattedMin = number_format($minOrder, 0, '', '');
        return corsResponse([
            'success' => false,
            'message' => "Coupons will apply above {$formattedMin} rs products",
            'min_order_amount' => $minOrder,
        ], 200);
    }

    // Calculate discount
    $discount = 0;
    $discVal = (float) $coupon->discount_value;
    if (strtolower($coupon->discount_type) === 'percentage') {
        $discount = ($subtotal * $discVal) / 100;
        if ($coupon->max_discount_amount && $discount > (float) $coupon->max_discount_amount) {
            $discount = (float) $coupon->max_discount_amount;
        }
    } else {
        $discount = min($subtotal, $discVal);
    }

    $discount = round($discount, 2);

    return corsResponse([
        'success' => true,
        'message' => "Coupon {$coupon->code} applied successfully!",
        'coupon' => [
            'id' => $coupon->id,
            'code' => $coupon->code,
            'description' => $coupon->description,
            'discount_type' => $coupon->discount_type,
            'discount_value' => (float) $coupon->discount_value,
            'min_order_amount' => (float) $coupon->min_order_amount,
            'min_order_value' => (float) $coupon->min_order_amount,
            'max_discount_amount' => $coupon->max_discount_amount ? (float) $coupon->max_discount_amount : null,
            'max_discount' => $coupon->max_discount_amount ? (float) $coupon->max_discount_amount : null,
            'start_date' => $coupon->start_date ? date('Y-m-d', strtotime($coupon->start_date)) : null,
            'end_date' => $coupon->end_date ? date('Y-m-d', strtotime($coupon->end_date)) : null,
            'status' => $coupon->status,
        ],
        'discount' => $discount,
    ]);
});

// 5. Orders API (Customer App <-> Admin Web <-> DB)
Route::get('/orders', function (Request $request) {
    try {
        // Query from main admin_web orders table with full relations
        $adminOrders = Order::with(['customer', 'branch', 'items'])->latest('id')->get();

        if ($adminOrders->isNotEmpty()) {
            $orders = $adminOrders->map(function ($o) {
                return [
                    'id' => (string) $o->id,
                    'order_number' => $o->order_number,
                    'store_id' => $o->branch_id,
                    'store_name' => $o->branch?->name ?? 'Satara Main Branch',
                    'customer_name' => $o->customer?->name ?? 'Customer',
                    'customer_mobile' => $o->customer?->mobile ?? '9876543210',
                    'delivery_address' => $o->delivery_address,
                    'subtotal' => (float) $o->subtotal,
                    'discount' => (float) $o->discount_amount,
                    'delivery_charge' => (float) $o->delivery_charge,
                    'final_amount' => (float) $o->total_amount,
                    'payment_mode' => $o->payment_mode,
                    'payment_status' => $o->payment_status,
                    'order_status' => $o->order_status,
                    'placed_at' => $o->placed_at ? $o->placed_at->toISOString() : now()->toISOString(),
                    'items' => $o->items->map(function ($item) {
                        return [
                            'product_id' => $item->product_id,
                            'product_name' => $item->product_name,
                            'unit_price' => (float) $item->price,
                            'quantity' => (int) $item->quantity,
                            'total_price' => (float) $item->total,
                        ];
                    })->values()->all(),
                ];
            });
            return corsResponse($orders);
        }

        // Fallback to customer_app database if admin_web orders is empty
        $customerOrders = DB::table('customer_app.customer_orders')
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($o) {
                return [
                    'id' => (string) $o->id,
                    'order_number' => $o->order_number,
                    'store_id' => $o->store_id,
                    'store_name' => $o->store_name,
                    'customer_name' => $o->customer_name,
                    'customer_mobile' => $o->customer_mobile,
                    'delivery_address' => $o->delivery_address,
                    'subtotal' => (float) $o->subtotal,
                    'discount' => (float) $o->discount,
                    'delivery_charge' => (float) $o->delivery_charge,
                    'final_amount' => (float) $o->final_amount,
                    'payment_mode' => $o->payment_mode,
                    'payment_status' => $o->payment_status,
                    'order_status' => $o->order_status,
                    'placed_at' => $o->placed_at,
                    'items' => json_decode($o->items ?? '[]', true),
                ];
            });

        return corsResponse($customerOrders);
    } catch (\Throwable $e) {
        return corsResponse([]);
    }
});

Route::post('/orders', function (Request $request) {
    $data = $request->all();

    $orderNum = 'ORD-' . strtoupper(substr(uniqid(), -6));
    $subtotal = (float) ($data['subtotal'] ?? 0);
    $discount = (float) ($data['discount'] ?? 0);
    $deliveryCharge = (float) ($data['delivery_charge'] ?? 0);
    $finalAmount = (float) ($data['final_amount'] ?? ($subtotal - $discount + $deliveryCharge));
    $rawMobile = trim((string) ($data['customer_mobile'] ?? '9876543210'));
    $mobile = preg_replace('/\D/', '', $rawMobile);
    if (strlen($mobile) > 10) {
        $mobile = substr($mobile, -10);
    }
    if (empty($mobile)) {
        $mobile = '9876543210';
    }
    $custName = trim((string) ($data['customer_name'] ?? 'Customer'));
    $deliveryAddress = trim((string) ($data['delivery_address'] ?? 'Customer Delivery Address'));
    $paymentMode = in_array(strtoupper($data['payment_mode'] ?? 'COD'), ['COD', 'ONLINE']) ? strtoupper($data['payment_mode']) : 'COD';
    $paymentStatus = $paymentMode === 'ONLINE' ? 'PAID' : 'PENDING';
    $storeId = (int) ($data['store_id'] ?? 1);

    // 1. Resolve or find Branch (Store)
    $branch = Branch::find($storeId);
    if (!$branch) {
        $branch = Branch::where('status', 'active')->first() ?: Branch::first();
    }
    $branchId = $branch ? $branch->id : 1;
    $branchName = $branch ? $branch->name : 'Satara Main Branch';

    // 2. Find or create Customer in admin_web.customers
    $customer = Customer::where('mobile', $mobile)->first();
    if (!$customer) {
        try {
            $customer = Customer::create([
                'name' => $custName,
                'mobile' => $mobile,
                'address' => $deliveryAddress,
                'city' => 'Satara',
                'status' => 'active',
                'password' => Hash::make($mobile),
                'plain_password' => $mobile,
            ]);
        } catch (\Throwable $e) {
            $customer = Customer::first();
        }
    } else {
        if (!empty($deliveryAddress) && empty($customer->address)) {
            $customer->address = $deliveryAddress;
            $customer->save();
        }
    }
    $customerId = $customer ? $customer->id : 1;

    // 3. Resolve Coupon if applied
    $couponId = null;
    if (!empty($data['coupon_id'])) {
        $couponId = $data['coupon_id'];
    } elseif (!empty($data['coupon_code'])) {
        $cObj = Coupon::whereRaw('UPPER(code) = ?', [strtoupper(trim($data['coupon_code']))])->first();
        if ($cObj) {
            $couponId = $cObj->id;
        }
    }

    // 4. Save to main admin_web.orders table (Status = PLACED, delivery_boy_id = NULL)
    $order = null;
    try {
        $order = Order::create([
            'order_number' => $orderNum,
            'branch_id' => $branchId,
            'customer_id' => $customerId,
            'delivery_boy_id' => null, // Unassigned: Admin will assign within 3km radius
            'coupon_id' => $couponId,
            'subtotal' => $subtotal,
            'discount_amount' => $discount,
            'delivery_charge' => $deliveryCharge,
            'tax_amount' => 0.00,
            'total_amount' => $finalAmount,
            'payment_mode' => $paymentMode,
            'payment_status' => $paymentStatus,
            'order_status' => 'PLACED',
            'delivery_address' => $deliveryAddress,
            'placed_at' => now(),
        ]);

        // Save order items in admin_web.order_items
        $itemsData = $data['items'] ?? [];
        if (is_array($itemsData)) {
            $firstProduct = Product::first();
            $defaultProductId = $firstProduct ? $firstProduct->id : null;
            foreach ($itemsData as $item) {
                $rawPId = $item['product_id'] ?? null;
                $pId = ($rawPId && Product::where('id', $rawPId)->exists()) ? $rawPId : $defaultProductId;
                if ($pId) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $pId,
                        'product_name' => $item['product_name'] ?? 'Item',
                        'unit' => $item['unit'] ?? 'kg',
                        'price' => (float) ($item['unit_price'] ?? 0),
                        'quantity' => (int) ($item['quantity'] ?? 1),
                        'total' => (float) ($item['total_price'] ?? (($item['unit_price'] ?? 0) * ($item['quantity'] ?? 1))),
                    ]);
                }
            }
        }

        // Save initial status log in admin_web.order_status_logs
        \App\Models\OrderStatusLog::create([
            'order_id' => $order->id,
            'from_status' => null,
            'to_status' => 'PLACED',
            'remarks' => 'Order placed online by customer via Customer App. Awaiting 3KM fleet assignment.',
            'changed_by' => 'Customer',
        ]);

        // Create Admin Notification in admin_web.notifications
        \App\Models\Notification::create([
            'type' => 'order',
            'title' => "New Order #{$orderNum} Placed!",
            'message' => "Customer {$custName} ({$mobile}) placed order #{$orderNum} worth ₹" . number_format($finalAmount, 2) . " at {$branchName}. Ready for 3KM fleet dispatch.",
            'url' => "/admin/orders/{$order->id}",
            'is_read' => false,
        ]);

        // If online payment, record in payments table
        if ($paymentMode === 'ONLINE') {
            \App\Models\Payment::create([
                'order_id' => $order->id,
                'transaction_id' => 'TXN-ONL-' . rand(100000, 999999),
                'payment_mode' => 'ONLINE',
                'amount' => $finalAmount,
                'status' => 'SUCCESS',
                'collected_at' => now(),
            ]);
        }
    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\Log::error('Order creation error in admin_web.orders: ' . $e->getMessage());
    }

    // 5. Also sync to customer_app database
    $itemsJson = json_encode($data['items'] ?? []);
    try {
        $customerOrderId = DB::table('customer_app.customer_orders')->insertGetId([
            'order_number' => $orderNum,
            'store_id' => $branchId,
            'store_name' => $branchName,
            'customer_name' => $custName,
            'customer_mobile' => $mobile,
            'delivery_address' => $deliveryAddress,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'delivery_charge' => $deliveryCharge,
            'final_amount' => $finalAmount,
            'payment_mode' => $paymentMode,
            'payment_status' => $paymentStatus,
            'order_status' => 'PLACED',
            'items' => $itemsJson,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Add customer notification
        DB::table('customer_app.customer_notifications')->insert([
            'title' => 'Order Placed Successfully!',
            'message' => 'Your order #' . $orderNum . ' has been received and is being prepared by ' . $branchName . '.',
            'is_read' => 0,
            'created_at' => now(),
        ]);
    } catch (\Throwable $e) {
        // Fallback if table issues
    }

    // NOTE: Order is NOT sent to delivery_boy_app yet!
    // It stays in 'PLACED' status until Admin assigns a delivery boy in the customer's 3KM radius.

    // Increment coupon times_used if applied
    if ($couponId) {
        try {
            Coupon::where('id', $couponId)->increment('times_used');
        } catch (\Throwable $e) {}
    }

    // Return the created order to customer_app
    return corsResponse([
        'id' => (string) ($order ? $order->id : ($customerOrderId ?? time())),
        'order_number' => $orderNum,
        'store_id' => $branchId,
        'store_name' => $branchName,
        'customer_name' => $custName,
        'customer_mobile' => $mobile,
        'delivery_address' => $deliveryAddress,
        'items' => $data['items'] ?? [],
        'subtotal' => $subtotal,
        'discount' => $discount,
        'delivery_charge' => $deliveryCharge,
        'final_amount' => $finalAmount,
        'payment_mode' => $paymentMode,
        'payment_status' => $paymentStatus,
        'order_status' => 'PLACED',
        'placed_at' => now()->toISOString(),
    ]);
});

// 5. Delivery Boy Authentication API
Route::post('/delivery/login', function (Request $request) {
    $data = $request->json()->all();
    if (empty($data)) {
        $data = $request->all();
    }
    if (empty($data)) {
        $data = json_decode($request->getContent(), true) ?: [];
    }

    $username = trim((string) ($data['username'] ?? ''));
    $password = (string) ($data['password'] ?? '');

    if (empty($username) || empty($password)) {
        return corsResponse([
            'success' => false,
            'message' => 'Please provide both username and password.'
        ], 400);
    }

    $deliveryBoy = DeliveryBoy::with('branch')
        ->where('username', $username)
        ->orWhere('mobile', $username)
        ->first();

    if (!$deliveryBoy) {
        return corsResponse([
            'success' => false,
            'message' => 'Account not found for username "' . $username . '".'
        ], 404);
    }

    if ($deliveryBoy->status !== 'active') {
        return corsResponse([
            'success' => false,
            'message' => 'Your account is currently ' . $deliveryBoy->status . '. Please contact Admin.'
        ], 403);
    }

    $passwordValid = false;
    if (Hash::check($password, $deliveryBoy->password)) {
        $passwordValid = true;
        if (empty($deliveryBoy->plain_password)) {
            $deliveryBoy->plain_password = $password;
            $deliveryBoy->save();
        }
    } elseif (!empty($deliveryBoy->plain_password) && $deliveryBoy->plain_password === $password) {
        $passwordValid = true;
        $deliveryBoy->password = Hash::make($password);
        $deliveryBoy->save();
    } elseif ($deliveryBoy->password === $password) {
        $passwordValid = true;
        $deliveryBoy->plain_password = $password;
        $deliveryBoy->password = Hash::make($password);
        $deliveryBoy->save();
    }

    if (!$passwordValid) {
        return corsResponse([
            'success' => false,
            'message' => 'Invalid password credentials. Please check your password.'
        ], 401);
    }

    $deliveredCount = 0;
    try {
        $deliveredCount = DB::table('delivery_boy_app.assigned_orders')
            ->where('delivery_boy_id', $deliveryBoy->id)
            ->where('delivery_status', 'DELIVERED')
            ->count();
    } catch (\Throwable $e) {}

    if ($deliveredCount === 0) {
        try {
            $deliveredCount = Order::where('delivery_boy_id', $deliveryBoy->id)
                ->where('order_status', 'DELIVERED')
                ->count();
        } catch (\Throwable $e) {}
    }

    return corsResponse([
        'success' => true,
        'message' => 'Login successful',
        'delivery_boy' => [
            'id' => $deliveryBoy->id,
            'name' => $deliveryBoy->name,
            'username' => $deliveryBoy->username,
            'email' => $deliveryBoy->username . '@metaglobe.com',
            'mobile' => $deliveryBoy->mobile,
            'assigned_store_id' => $deliveryBoy->branch_id,
            'assigned_store_name' => $deliveryBoy->branch?->name ?? 'Satara Main Store',
            'vehicle_type' => $deliveryBoy->vehicle_type,
            'vehicle_number' => $deliveryBoy->vehicle_number ?? '',
            'driving_license' => $deliveryBoy->license_number ?? '',
            'is_online' => (bool) $deliveryBoy->is_online,
            'rating' => 4.9,
            'total_completed_orders' => (int) $deliveredCount,
        ]
    ]);
});

Route::get('/delivery/profile', function (Request $request) {
    $id = $request->get('id');
    $username = $request->get('username');
    $deliveryBoy = DeliveryBoy::with('branch')
        ->when($id, fn($q) => $q->where('id', $id))
        ->when(!$id && $username, fn($q) => $q->where('username', $username))
        ->first();

    if (!$deliveryBoy) {
        return corsResponse(['success' => false, 'message' => 'Delivery partner not found'], 404);
    }

    $deliveredCount = 0;
    try {
        $deliveredCount = DB::table('delivery_boy_app.assigned_orders')
            ->where('delivery_boy_id', $deliveryBoy->id)
            ->where('delivery_status', 'DELIVERED')
            ->count();
    } catch (\Throwable $e) {}

    if ($deliveredCount === 0) {
        try {
            $deliveredCount = Order::where('delivery_boy_id', $deliveryBoy->id)
                ->where('order_status', 'DELIVERED')
                ->count();
        } catch (\Throwable $e) {}
    }

    return corsResponse([
        'success' => true,
        'delivery_boy' => [
            'id' => $deliveryBoy->id,
            'name' => $deliveryBoy->name,
            'username' => $deliveryBoy->username,
            'email' => $deliveryBoy->username . '@metaglobe.com',
            'mobile' => $deliveryBoy->mobile,
            'assigned_store_id' => $deliveryBoy->branch_id,
            'assigned_store_name' => $deliveryBoy->branch?->name ?? 'Satara Main Store',
            'vehicle_type' => $deliveryBoy->vehicle_type,
            'vehicle_number' => $deliveryBoy->vehicle_number ?? '',
            'driving_license' => $deliveryBoy->license_number ?? '',
            'is_online' => (bool) $deliveryBoy->is_online,
            'rating' => 4.9,
            'total_completed_orders' => (int) $deliveredCount,
        ]
    ]);
});

// 6. Delivery Boy Orders API (delivery_boy_app <-> DB)
Route::get('/delivery/orders', function (Request $request) {
    try {
        $deliveryBoyId = $request->get('delivery_boy_id') ?: $request->get('id');
        $query = DB::table('delivery_boy_app.assigned_orders');

        if ($deliveryBoyId) {
            $query->where('delivery_boy_id', $deliveryBoyId);
        }

        $orders = $query->orderBy('id', 'desc')
            ->get()
            ->map(function ($o) {
                return [
                    'id' => (string) $o->id,
                    'order_number' => $o->order_number,
                    'delivery_boy_id' => $o->delivery_boy_id ?? 1,
                    'store_name' => $o->store_name,
                    'customer_name' => $o->customer_name,
                    'customer_mobile' => $o->customer_mobile,
                    'delivery_address' => $o->delivery_address,
                    'order_amount' => (float) $o->order_amount,
                    'payment_mode' => $o->payment_mode,
                    'payment_status' => $o->payment_status,
                    'cod_amount_to_collect' => (float) $o->cod_amount_to_collect,
                    'is_cod_collected' => (bool) $o->is_cod_collected,
                    'delivery_status' => $o->delivery_status,
                    'items' => json_decode($o->items ?? '[]', true),
                    'customer_notes' => $o->customer_notes ?? '',
                    'assigned_time' => $o->assigned_time ?? '',
                    'delivered_time' => $o->delivered_time ?? '',
                ];
            });

        return corsResponse($orders);
    } catch (\Throwable $e) {
        return corsResponse([]);
    }
});

Route::post('/delivery/orders/{orderNumber}/status', function ($orderNumber, Request $request) {
    $status = $request->input('delivery_status');
    $isCodCollected = $request->boolean('is_cod_collected', false);
    $deliveredTime = $status === 'DELIVERED' ? now()->format('h:i A') : null;

    try {
        $update = [
            'delivery_status' => $status,
            'updated_at' => now(),
        ];
        if ($deliveredTime) {
            $update['delivered_time'] = $deliveredTime;
        }
        if ($isCodCollected || $status === 'DELIVERED') {
            $update['is_cod_collected'] = 1;
            $update['payment_status'] = 'PAID';
        }

        $dQuery = DB::table('delivery_boy_app.assigned_orders')->where('order_number', $orderNumber);
        if (is_numeric($orderNumber)) {
            $dQuery->orWhere('id', (int) $orderNumber);
        }
        $dQuery->update($update);

        // Also update customer_app orders status
        $custUpdate = [
            'order_status' => $status,
            'updated_at' => now(),
        ];
        if ($isCodCollected || $status === 'DELIVERED') {
            $custUpdate['payment_status'] = 'PAID';
        }
        $cQuery = DB::table('customer_app.customer_orders')->where('order_number', $orderNumber);
        if (is_numeric($orderNumber)) {
            $cQuery->orWhere('id', (int) $orderNumber);
        }
        $cQuery->update($custUpdate);

        // Notify customer
        DB::table('customer_app.customer_notifications')->insert([
            'title' => 'Order ' . str_replace('_', ' ', $status),
            'message' => 'Your order #' . $orderNumber . ' status is now ' . str_replace('_', ' ', $status) . '.',
            'is_read' => 0,
            'created_at' => now(),
        ]);

        // Sync with admin_web Order model, OrderStatusLog, and Notification
        try {
            $orderQuery = Order::where('order_number', $orderNumber);
            if (is_numeric($orderNumber)) {
                $orderQuery->orWhere('id', (int) $orderNumber);
            }
            $order = $orderQuery->first();
            if ($order) {
                $oldStatus = $order->order_status;
                $order->order_status = $status;
                if ($status === 'DELIVERED') {
                    $order->delivered_at = now();
                    if ($order->payment_mode === 'COD') {
                        $order->payment_status = 'PAID';
                        \App\Models\Payment::updateOrCreate(
                            ['order_id' => $order->id],
                            [
                                'transaction_id' => 'TXN-COD-' . rand(100000, 999999),
                                'payment_mode' => 'COD',
                                'amount' => $order->total_amount,
                                'status' => 'SUCCESS',
                                'collected_at' => now(),
                                'cod_verified' => true,
                            ]
                        );
                    }
                }
                $order->save();

                \App\Models\OrderStatusLog::create([
                    'order_id' => $order->id,
                    'from_status' => $oldStatus,
                    'to_status' => $status,
                    'remarks' => "Order updated to {$status} by delivery partner on mobile app",
                    'changed_by' => 'Delivery Partner',
                ]);

                \App\Models\Notification::create([
                    'type' => 'delivery',
                    'title' => "Order #{$order->order_number} is {$status}",
                    'message' => "Delivery partner marked order #{$order->order_number} as {$status}.",
                    'url' => "/admin/orders/{$order->id}",
                    'is_read' => false,
                ]);
            }
        } catch (\Throwable $e) {}

        return corsResponse(['success' => true, 'status' => $status]);
    } catch (\Throwable $e) {
        return corsResponse(['success' => false, 'error' => $e->getMessage()], 500);
    }
});

// 6. Notifications API
Route::get('/notifications', function (Request $request) {
    $type = $request->get('app', 'customer');
    $deliveryBoyId = $request->get('delivery_boy_id') ?: $request->get('id');

    try {
        if ($type === 'delivery') {
            $query = DB::table('delivery_boy_app.delivery_notifications');

            if ($deliveryBoyId) {
                $query->where(function ($q) use ($deliveryBoyId) {
                    $q->where('delivery_boy_id', $deliveryBoyId)
                      ->orWhereNull('delivery_boy_id');
                });
            }

            $notifs = $query->orderBy('id', 'desc')
                ->limit(25)
                ->get()
                ->map(function ($n) {
                    return [
                        'id' => (string) $n->id,
                        'title' => $n->title,
                        'message' => $n->message,
                        'order_id' => $n->order_id,
                        'is_read' => (bool) $n->is_read,
                        'time' => $n->created_at ? date('h:i A', strtotime($n->created_at)) : 'Just now',
                    ];
                });
        } else {
            $notifs = DB::table('customer_app.customer_notifications')
                ->orderBy('id', 'desc')
                ->limit(25)
                ->get()
                ->map(function ($n) {
                    return [
                        'id' => (string) $n->id,
                        'title' => $n->title,
                        'message' => $n->message,
                        'is_read' => (bool) $n->is_read,
                        'time' => $n->created_at ? date('h:i A', strtotime($n->created_at)) : 'Just now',
                    ];
                });
        }

        return corsResponse($notifs);
    } catch (\Throwable $e) {
        return corsResponse([]);
    }
});

Route::post('/notifications/{id}/read', function ($id, Request $request) {
    $type = $request->get('app', 'customer');
    try {
        if ($type === 'delivery') {
            DB::table('delivery_boy_app.delivery_notifications')->where('id', $id)->update(['is_read' => 1]);
        } else {
            DB::table('customer_app.customer_notifications')->where('id', $id)->update(['is_read' => 1]);
        }
        return corsResponse(['success' => true]);
    } catch (\Throwable $e) {
        return corsResponse(['success' => false], 500);
    }
});

// 7. Customer Authentication & OTP API
Route::post('/customer/register', function (Request $request) {
    $data = $request->json()->all();
    if (empty($data)) $data = $request->all();
    if (empty($data)) $data = json_decode($request->getContent(), true) ?: [];

    $name = trim($data['name'] ?? '');
    $rawMobile = trim((string) ($data['mobile'] ?? ''));
    $mobile = preg_replace('/\D/', '', $rawMobile);
    if (strlen($mobile) > 10) {
        $mobile = substr($mobile, -10);
    }
    $email = trim($data['email'] ?? '');
    $address = trim($data['address'] ?? '');
    $city = trim($data['city'] ?? 'Satara');
    $pincode = trim($data['pincode'] ?? '415001');
    $status = trim($data['status'] ?? 'active');
    $password = (string) ($data['password'] ?? '');

    if (empty($name)) {
        return corsResponse(['success' => false, 'message' => 'Customer Full Name is required.'], 400);
    }
    if (empty($mobile) || strlen($mobile) !== 10) {
        return corsResponse(['success' => false, 'message' => 'Please enter a valid 10-digit mobile number.'], 400);
    }
    if (empty($address)) {
        return corsResponse(['success' => false, 'message' => 'Saved Delivery Address is required.'], 400);
    }
    if (empty($city)) {
        $city = 'Satara';
    }
    if (!in_array($status, ['active', 'inactive', 'blocked'])) {
        $status = 'active';
    }

    // Default password to mobile number if not specified
    $passToHash = !empty($password) ? $password : $mobile;

    // Generate unique 4-digit OTP
    $otp = (string) random_int(1000, 9999);

    // Find existing or create new customer
    $customer = Customer::where('mobile', $mobile)->first();
    if ($customer) {
        $customer->name = $name;
        if (!empty($email)) $customer->email = $email;
        $customer->address = $address;
        $customer->city = $city;
        $customer->pincode = $pincode ?: '415001';
        $customer->status = $status;
        $customer->password = Hash::make($passToHash);
        $customer->plain_password = $passToHash;
        $customer->otp = $otp;
        $customer->save();
    } else {
        $customer = Customer::create([
            'name' => $name,
            'mobile' => $mobile,
            'email' => !empty($email) ? $email : null,
            'address' => $address,
            'city' => $city,
            'pincode' => $pincode ?: '415001',
            'status' => $status,
            'password' => Hash::make($passToHash),
            'plain_password' => $passToHash,
            'otp' => $otp,
            'lat' => 17.6850,
            'lng' => 73.9950,
        ]);
    }

    return corsResponse([
        'success' => true,
        'message' => 'Registration successful. Verification code has been sent.',
        'mobile' => $mobile,
        'customer' => [
            'id' => $customer->id,
            'name' => $customer->name,
            'mobile' => $customer->mobile,
            'email' => $customer->email ?? '',
            'address' => $customer->address ?? '',
            'city' => $customer->city ?? 'Satara',
            'pincode' => $customer->pincode ?? '415001',
            'status' => $customer->status ?? 'active',
            'addresses' => [
                [
                    'id' => 'addr-' . $customer->id,
                    'label' => 'Saved Address',
                    'address_line' => $customer->address ?: ($customer->city ?: 'Satara'),
                    'latitude' => (float) ($customer->lat ?: 17.6850),
                    'longitude' => (float) ($customer->lng ?: 73.9950),
                    'is_default' => true,
                ]
            ]
        ]
    ]);
});

Route::post('/customer/send-otp', function (Request $request) {
    $data = $request->json()->all();
    if (empty($data)) $data = $request->all();
    if (empty($data)) $data = json_decode($request->getContent(), true) ?: [];

    $rawMobile = trim((string) ($data['mobile'] ?? ''));
    $mobile = preg_replace('/\D/', '', $rawMobile);
    if (strlen($mobile) > 10) {
        $mobile = substr($mobile, -10);
    }

    if (empty($mobile) || strlen($mobile) !== 10) {
        return corsResponse(['success' => false, 'message' => 'Please enter a valid 10-digit mobile number.'], 400);
    }

    $customer = Customer::where('mobile', $mobile)->first();
    $otp = (string) random_int(1000, 9999);

    if ($customer) {
        $customer->otp = $otp;
        $customer->save();
    }

    $smsSent = sendSmsOtp($mobile, $otp);

    return corsResponse([
        'success' => true,
        'message' => $smsSent
            ? 'Verification code sent successfully to +91 ' . $mobile
            : 'Could not send SMS right now, please try resend.',
        'mobile' => $mobile,
    ]);
});

Route::post('/customer/verify-otp', function (Request $request) {
    $data = $request->json()->all();
    if (empty($data)) $data = $request->all();
    if (empty($data)) $data = json_decode($request->getContent(), true) ?: [];

    $rawMobile = trim((string) ($data['mobile'] ?? ''));
    $mobile = preg_replace('/\D/', '', $rawMobile);
    if (strlen($mobile) > 10) {
        $mobile = substr($mobile, -10);
    }
    $otp = trim((string) ($data['otp'] ?? ''));

    if (empty($mobile) || empty($otp)) {
        return corsResponse(['success' => false, 'message' => 'Mobile number and OTP are required.'], 400);
    }

    $customer = Customer::where('mobile', $mobile)->first();
    if (!$customer) {
        return corsResponse(['success' => false, 'message' => 'Customer account not found.'], 404);
    }

    if ($customer->otp !== $otp) {// allow master 1234 fallback for dev if needed
        return corsResponse(['success' => false, 'message' => 'Invalid OTP entered. Please check and enter again.'], 400);
    }

    // Clear OTP after successful verification
    $customer->otp = null;
    $customer->status = 'active';
    $customer->save();

    return corsResponse([
        'success' => true,
        'message' => 'OTP verified successfully!',
        'customer' => [
            'id' => $customer->id,
            'name' => $customer->name,
            'mobile' => $customer->mobile,
            'email' => $customer->email ?? '',
            'address' => $customer->address ?? '',
            'city' => $customer->city ?? 'Satara',
            'pincode' => $customer->pincode ?? '415001',
            'addresses' => [
                [
                    'id' => 'addr-' . $customer->id,
                    'label' => 'Saved Address',
                    'address_line' => $customer->address ?: ($customer->city ?: 'Satara'),
                    'latitude' => (float) ($customer->lat ?: 17.6850),
                    'longitude' => (float) ($customer->lng ?: 73.9950),
                    'is_default' => true,
                ]
            ]
        ]
    ]);
});

Route::post('/customer/resend-otp', function (Request $request) {
    $data = $request->json()->all();
    if (empty($data)) $data = $request->all();
    if (empty($data)) $data = json_decode($request->getContent(), true) ?: [];

    $rawMobile = trim((string) ($data['mobile'] ?? ''));
    $mobile = preg_replace('/\D/', '', $rawMobile);
    if (strlen($mobile) > 10) {
        $mobile = substr($mobile, -10);
    }

    if (empty($mobile) || strlen($mobile) !== 10) {
        return corsResponse(['success' => false, 'message' => 'Valid 10-digit mobile number is required.'], 400);
    }

    $customer = Customer::where('mobile', $mobile)->first();
    $otp = (string) random_int(1000, 9999);
    if ($customer) {
        $customer->otp = $otp;
        $customer->save();
    }

    sendSmsOtp($mobile, $otp);

    sendSmsOtp($mobile, $otp);

    return corsResponse([
        'success' => true,
        'message' => 'Registration successful. Verification code has been sent.',
        'mobile' => $mobile,
    ]);
});

Route::post('/customer/login', function (Request $request) {
    $data = $request->json()->all();
    if (empty($data)) $data = $request->all();
    if (empty($data)) $data = json_decode($request->getContent(), true) ?: [];

    $username = trim($data['username'] ?? $data['mobile'] ?? $data['email'] ?? '');
    $password = (string) ($data['password'] ?? '');

    if (empty($username) || empty($password)) {
        return corsResponse(['success' => false, 'message' => 'Please enter username/mobile and password.'], 400);
    }

    $customer = Customer::where('mobile', $username)
        ->orWhere('email', $username)
        ->orWhere('name', $username)
        ->first();

    if (!$customer) {
        return corsResponse(['success' => false, 'message' => 'Account not found with provided mobile or email.'], 404);
    }

    $valid = false;
    if ($customer->password && Hash::check($password, $customer->password)) {
        $valid = true;
    } elseif (!empty($customer->plain_password) && $customer->plain_password === $password) {
        $valid = true;
        $customer->password = Hash::make($password);
        $customer->save();
    } elseif ($customer->password === $password) {
        $valid = true;
        $customer->password = Hash::make($password);
        $customer->plain_password = $password;
        $customer->save();
    }

    if (!$valid) {
        return corsResponse(['success' => false, 'message' => 'Invalid password credentials. Please try again.'], 401);
    }

    return corsResponse([
        'success' => true,
        'message' => 'Login successful',
        'customer' => [
            'id' => $customer->id,
            'name' => $customer->name,
            'mobile' => $customer->mobile,
            'email' => $customer->email ?? '',
            'address' => $customer->address ?? '',
            'city' => $customer->city ?? 'Satara',
            'pincode' => $customer->pincode ?? '415001',
            'addresses' => [
                [
                    'id' => 'addr-' . $customer->id,
                    'label' => 'Saved Address',
                    'address_line' => $customer->address ?: ($customer->city ?: 'Satara'),
                    'latitude' => (float) ($customer->lat ?: 17.6850),
                    'longitude' => (float) ($customer->lng ?: 73.9950),
                    'is_default' => true,
                ]
            ]
        ]
    ]);
});

Route::post('/customer/update-profile', function (Request $request) {
    $data = $request->json()->all();
    if (empty($data)) $data = $request->all();
    if (empty($data)) $data = json_decode($request->getContent(), true) ?: [];

    $id = $data['id'] ?? null;
    $mobile = trim($data['mobile'] ?? '');

    $customer = Customer::when($id, fn($q) => $q->where('id', $id))
        ->when(!$id && $mobile, fn($q) => $q->where('mobile', $mobile))
        ->first();

    if (!$customer) {
        return corsResponse(['success' => false, 'message' => 'Customer not found.'], 404);
    }

    if (isset($data['name']) && !empty(trim($data['name']))) {
        $customer->name = trim($data['name']);
    }
    if (isset($data['email'])) {
        $customer->email = trim($data['email']);
    }
    if (isset($data['address'])) {
        $customer->address = trim($data['address']);
    }
    if (isset($data['city']) && !empty(trim($data['city']))) {
        $customer->city = trim($data['city']);
    }
    if (isset($data['pincode'])) {
        $customer->pincode = trim($data['pincode']);
    }
    if (!empty($data['password'])) {
        $newPass = (string) $data['password'];
        $customer->password = Hash::make($newPass);
        $customer->plain_password = $newPass;
    }

    $customer->save();

    return corsResponse([
        'success' => true,
        'message' => 'Profile updated successfully!',
        'customer' => [
            'id' => $customer->id,
            'name' => $customer->name,
            'mobile' => $customer->mobile,
            'email' => $customer->email ?? '',
            'address' => $customer->address ?? '',
            'city' => $customer->city ?? 'Satara',
            'pincode' => $customer->pincode ?? '415001',
            'addresses' => [
                [
                    'id' => 'addr-' . $customer->id,
                    'label' => 'Saved Address',
                    'address_line' => $customer->address ?: ($customer->city ?: 'Satara'),
                    'latitude' => (float) ($customer->lat ?: 17.6850),
                    'longitude' => (float) ($customer->lng ?: 73.9950),
                    'is_default' => true,
                ]
            ]
        ]
    ]);
});

