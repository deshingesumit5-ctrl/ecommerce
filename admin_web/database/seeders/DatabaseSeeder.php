<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Branch;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Product;
use App\Models\DailyPrice;
use App\Models\Customer;
use App\Models\Coupon;
use App\Models\Discount;
use App\Models\DeliveryChargeConfig;
use App\Models\DeliveryBoy;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusLog;
use App\Models\Payment;
use App\Models\Notification;
use App\Models\Setting;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Admin User
        $admin = User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'System Super Admin',
                'username' => 'admin',
                'mobile' => '9876543210',
                'password' => Hash::make('admin@123'),
                'role' => 'admin',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // 2. Stores / Branches (Store Master)
        $sataraBranch = Branch::create([
            'name' => 'Satara Store',
            'code' => 'SAT-01',
            'contact_phone' => '9876543210',
            'address' => 'Powai Naka, Rajwada Road, Satara, Maharashtra 415001',
            'latitude' => 17.6805,
            'longitude' => 74.0183,
            'radius_km' => 3.00,
            'status' => 'active',
        ]);

        $koregaonBranch = Branch::create([
            'name' => 'Koregaon Store',
            'code' => 'KOR-02',
            'contact_phone' => '9876543211',
            'address' => 'Station Road, Market Yard, Koregaon, Satara, Maharashtra 415501',
            'latitude' => 17.7000,
            'longitude' => 74.1700,
            'radius_km' => 3.00,
            'status' => 'active',
        ]);

        // 3. Categories & Subcategories
        $catVeg = Category::create([
            'name' => 'Farm Fresh Vegetables',
            'slug' => 'farm-fresh-vegetables',
            'image' => 'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=300&auto=format&fit=crop&q=80',
            'display_order' => 1,
            'status' => 'active',
        ]);

        $subLeafy = SubCategory::create([
            'category_id' => $catVeg->id,
            'name' => 'Leafy Greens & Herbs',
            'slug' => 'leafy-greens-herbs',
            'image' => 'https://images.unsplash.com/photo-1576045057995-568f588f82fb?w=300&auto=format&fit=crop&q=80',
            'display_order' => 1,
            'status' => 'active',
        ]);

        $subRoot = SubCategory::create([
            'category_id' => $catVeg->id,
            'name' => 'Root & Daily Veggies',
            'slug' => 'root-daily-veggies',
            'image' => 'https://images.unsplash.com/photo-1518977676601-b53f82aba655?w=300&auto=format&fit=crop&q=80',
            'display_order' => 2,
            'status' => 'active',
        ]);

        $catFruit = Category::create([
            'name' => 'Fresh Fruits',
            'slug' => 'fresh-fruits',
            'image' => 'https://images.unsplash.com/photo-1619566636858-adf3ef46400b?w=300&auto=format&fit=crop&q=80',
            'display_order' => 2,
            'status' => 'active',
        ]);

        $subApples = SubCategory::create([
            'category_id' => $catFruit->id,
            'name' => 'Apples, Pears & Citrus',
            'slug' => 'apples-pears-citrus',
            'image' => 'https://images.unsplash.com/photo-1560806887-1e4cd0b6cbd6?w=300&auto=format&fit=crop&q=80',
            'display_order' => 1,
            'status' => 'active',
        ]);

        $subTropical = SubCategory::create([
            'category_id' => $catFruit->id,
            'name' => 'Tropical & Melons',
            'slug' => 'tropical-melons',
            'image' => 'https://images.unsplash.com/photo-1528825871115-3581a5387919?w=300&auto=format&fit=crop&q=80',
            'display_order' => 2,
            'status' => 'active',
        ]);

        $catDairy = Category::create([
            'name' => 'Dairy & Bakery',
            'slug' => 'dairy-bakery',
            'image' => 'https://images.unsplash.com/photo-1528732263440-4dd1a18a4cc2?w=300&auto=format&fit=crop&q=80',
            'display_order' => 3,
            'status' => 'active',
        ]);

        $subMilk = SubCategory::create([
            'category_id' => $catDairy->id,
            'name' => 'Milk, Paneer & Butter',
            'slug' => 'milk-paneer-butter',
            'image' => 'https://images.unsplash.com/photo-1550583724-b2692b85b150?w=300&auto=format&fit=crop&q=80',
            'display_order' => 1,
            'status' => 'active',
        ]);

        $subBread = SubCategory::create([
            'category_id' => $catDairy->id,
            'name' => 'Artisanal Breads & Buns',
            'slug' => 'artisanal-breads-buns',
            'image' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=300&auto=format&fit=crop&q=80',
            'display_order' => 2,
            'status' => 'active',
        ]);

        $catStaples = Category::create([
            'name' => 'Daily Staples & Groceries',
            'slug' => 'daily-staples-groceries',
            'image' => 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=300&auto=format&fit=crop&q=80',
            'display_order' => 4,
            'status' => 'active',
        ]);

        $subGrains = SubCategory::create([
            'category_id' => $catStaples->id,
            'name' => 'Rice, Atta & Dals',
            'slug' => 'rice-atta-dals',
            'image' => 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=300&auto=format&fit=crop&q=80',
            'display_order' => 1,
            'status' => 'active',
        ]);

        $catBev = Category::create([
            'name' => 'Beverages & Drinks',
            'slug' => 'beverages-drinks',
            'image' => 'https://images.unsplash.com/photo-1622483767028-3f66f32aef97?w=300&auto=format&fit=crop&q=80',
            'display_order' => 5,
            'status' => 'active',
        ]);

        // 4. Products
        $p1 = Product::create([
            'category_id' => $catVeg->id,
            'sub_category_id' => $subRoot->id,
            'name' => 'Farm Fresh Hybrid Tomatoes',
            'slug' => 'farm-fresh-hybrid-tomatoes',
            'description' => 'Locally sourced red ripe juicy tomatoes, handpicked daily from Satara valley farms.',
            'image' => 'https://images.unsplash.com/photo-1546094096-0df4bcaaa337?w=300&auto=format&fit=crop&q=80',
            'unit' => 'kg',
            'base_price' => 45.00,
            'current_daily_price' => 38.00,
            'in_stock' => true,
            'status' => 'active',
        ]);

        $p2 = Product::create([
            'category_id' => $catVeg->id,
            'sub_category_id' => $subLeafy->id,
            'name' => 'Organic Fresh Palak (Spinach)',
            'slug' => 'organic-fresh-palak-spinach',
            'description' => 'Crisp organic nutrient-rich green spinach bunch washed and ready for cooking.',
            'image' => 'https://images.unsplash.com/photo-1576045057995-568f588f82fb?w=300&auto=format&fit=crop&q=80',
            'unit' => 'bunch',
            'base_price' => 30.00,
            'current_daily_price' => 25.00,
            'in_stock' => true,
            'status' => 'active',
        ]);

        $p3 = Product::create([
            'category_id' => $catVeg->id,
            'sub_category_id' => $subRoot->id,
            'name' => 'Mahabaleshwar Fresh Red Potatoes',
            'slug' => 'mahabaleshwar-fresh-red-potatoes',
            'description' => 'Premium hill soil potatoes with smooth skin and exceptional taste.',
            'image' => 'https://images.unsplash.com/photo-1518977676601-b53f82aba655?w=300&auto=format&fit=crop&q=80',
            'unit' => 'kg',
            'base_price' => 40.00,
            'current_daily_price' => 35.00,
            'in_stock' => true,
            'status' => 'active',
        ]);

        $p4 = Product::create([
            'category_id' => $catFruit->id,
            'sub_category_id' => $subTropical->id,
            'name' => 'Fresh Yellow Bananas (Robusta)',
            'slug' => 'fresh-yellow-bananas-robusta',
            'description' => 'Naturally ripened sweet bananas packed with energy and potassium.',
            'image' => 'https://images.unsplash.com/photo-1528825871115-3581a5387919?w=300&auto=format&fit=crop&q=80',
            'unit' => 'dozen',
            'base_price' => 60.00,
            'current_daily_price' => 50.00,
            'in_stock' => true,
            'status' => 'active',
        ]);

        $p5 = Product::create([
            'category_id' => $catFruit->id,
            'sub_category_id' => $subApples->id,
            'name' => 'Himachal Royal Gala Apples',
            'slug' => 'himachal-royal-gala-apples',
            'description' => 'Crisp sweet sweet red blush apples directly transported from orchard batches.',
            'image' => 'https://images.unsplash.com/photo-1560806887-1e4cd0b6cbd6?w=300&auto=format&fit=crop&q=80',
            'unit' => 'kg',
            'base_price' => 190.00,
            'current_daily_price' => 175.00,
            'in_stock' => true,
            'status' => 'active',
        ]);

        $p6 = Product::create([
            'category_id' => $catDairy->id,
            'sub_category_id' => $subMilk->id,
            'name' => 'Farm Fresh Cow Milk Pouch',
            'slug' => 'farm-fresh-cow-milk-pouch',
            'description' => 'Pasteurized standardized fresh full cream cow milk delivered chilled.',
            'image' => 'https://images.unsplash.com/photo-1550583724-b2692b85b150?w=300&auto=format&fit=crop&q=80',
            'unit' => 'liter',
            'base_price' => 68.00,
            'current_daily_price' => 64.00,
            'in_stock' => true,
            'status' => 'active',
        ]);

        $p7 = Product::create([
            'category_id' => $catDairy->id,
            'sub_category_id' => $subMilk->id,
            'name' => 'Malai Fresh Soft Paneer',
            'slug' => 'malai-fresh-soft-paneer',
            'description' => 'Ultra soft tender fresh cottage cheese prepared daily without preservatives.',
            'image' => 'https://images.unsplash.com/photo-1589301760014-d929f3979dbc?w=300&auto=format&fit=crop&q=80',
            'unit' => 'gm (200g)',
            'base_price' => 95.00,
            'current_daily_price' => 88.00,
            'in_stock' => true,
            'status' => 'active',
        ]);

        $p8 = Product::create([
            'category_id' => $catDairy->id,
            'sub_category_id' => $subBread->id,
            'name' => '100% Whole Wheat Brown Bread',
            'slug' => '100-whole-wheat-brown-bread',
            'description' => 'Freshly baked wholesome brown loaf with zero maida and rich in dietary fiber.',
            'image' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=300&auto=format&fit=crop&q=80',
            'unit' => 'pack (400g)',
            'base_price' => 50.00,
            'current_daily_price' => 45.00,
            'in_stock' => true,
            'status' => 'active',
        ]);

        $p9 = Product::create([
            'category_id' => $catStaples->id,
            'sub_category_id' => $subGrains->id,
            'name' => 'Kolam Supreme Premium Rice (5kg)',
            'slug' => 'kolam-supreme-premium-rice-5kg',
            'description' => 'Aromatic soft cooking medium grain daily rice aged to perfection.',
            'image' => 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=300&auto=format&fit=crop&q=80',
            'unit' => 'pack (5kg)',
            'base_price' => 380.00,
            'current_daily_price' => 350.00,
            'in_stock' => true,
            'status' => 'active',
        ]);

        // 5. Daily Prices
        $products = Product::all();
        foreach ($products as $prod) {
            DailyPrice::create([
                'product_id' => $prod->id,
                'branch_id' => $sataraBranch->id,
                'price' => $prod->current_daily_price,
                'effective_date' => now()->toDateString(),
            ]);
            DailyPrice::create([
                'product_id' => $prod->id,
                'branch_id' => $koregaonBranch->id,
                'price' => $prod->current_daily_price,
                'effective_date' => now()->toDateString(),
            ]);
        }

        // 6. Delivery Charge Config
        DeliveryChargeConfig::create([
            'branch_id' => $sataraBranch->id,
            'min_order_free_delivery' => 500.00,
            'standard_charge' => 40.00,
            'express_charge' => 70.00,
            'status' => 'active',
        ]);

        DeliveryChargeConfig::create([
            'branch_id' => $koregaonBranch->id,
            'min_order_free_delivery' => 500.00,
            'standard_charge' => 40.00,
            'express_charge' => 70.00,
            'status' => 'active',
        ]);

        // 7. Delivery Boys
        $db1 = DeliveryBoy::create([
            'branch_id' => $sataraBranch->id,
            'name' => 'Rohan Patil',
            'mobile' => '9822110011',
            'vehicle_type' => 'Bike (Hero Splendor)',
            'vehicle_number' => 'MH-11-AB-1234',
            'license_number' => 'DL-MH11-2023-009',
            'is_online' => true,
            'wallet_balance' => 480.00,
            'current_lat' => 17.6840,
            'current_lng' => 74.0150,
            'status' => 'active',
        ]);

        $db2 = DeliveryBoy::create([
            'branch_id' => $sataraBranch->id,
            'name' => 'Amit Deshmukh',
            'mobile' => '9822110022',
            'vehicle_type' => 'Scooter (Activa 6G)',
            'vehicle_number' => 'MH-11-CD-5678',
            'license_number' => 'DL-MH11-2022-045',
            'is_online' => true,
            'wallet_balance' => 1250.00,
            'current_lat' => 17.6790,
            'current_lng' => 74.0210,
            'status' => 'active',
        ]);

        $db3 = DeliveryBoy::create([
            'branch_id' => $koregaonBranch->id,
            'name' => 'Sagar Jadhav',
            'mobile' => '9822110033',
            'vehicle_type' => 'Bike (Honda Shine)',
            'vehicle_number' => 'MH-11-EF-9012',
            'license_number' => 'DL-MH11-2024-012',
            'is_online' => true,
            'wallet_balance' => 840.00,
            'current_lat' => 17.7010,
            'current_lng' => 74.1720,
            'status' => 'active',
        ]);

        $db4 = DeliveryBoy::create([
            'branch_id' => $koregaonBranch->id,
            'name' => 'Vishal Shinde',
            'mobile' => '9822110044',
            'vehicle_type' => 'Electric Scooter (Ola S1)',
            'vehicle_number' => 'MH-11-EV-3344',
            'license_number' => 'DL-MH11-2023-088',
            'is_online' => false,
            'wallet_balance' => 310.00,
            'current_lat' => 17.6980,
            'current_lng' => 74.1680,
            'status' => 'active',
        ]);

        // 8. Customers
        $c1 = Customer::create([
            'name' => 'Dr. Pravin More',
            'email' => 'pravin.more@gmail.com',
            'mobile' => '9850123456',
            'address' => 'Flat 402, Sai Shraddha Heights, Sadar Bazar, Satara',
            'city' => 'Satara',
            'pincode' => '415001',
            'lat' => 17.6820,
            'lng' => 74.0160,
            'status' => 'active',
        ]);

        $c2 = Customer::create([
            'name' => 'Sunita Kulkarni',
            'email' => 'sunita.k@gmail.com',
            'mobile' => '9850123457',
            'address' => 'Plot 18, Radhika Road, Near Shivaji Circle, Satara',
            'city' => 'Satara',
            'pincode' => '415002',
            'lat' => 17.6860,
            'lng' => 74.0200,
            'status' => 'active',
        ]);

        $c3 = Customer::create([
            'name' => 'Mahesh Bhosale',
            'email' => 'mahesh.b@gmail.com',
            'mobile' => '9850123458',
            'address' => 'House 12, Market Chowk, Main Bazar, Koregaon',
            'city' => 'Koregaon',
            'pincode' => '415501',
            'lat' => 17.7020,
            'lng' => 74.1710,
            'status' => 'active',
        ]);

        $c4 = Customer::create([
            'name' => 'Anjali Salunkhe',
            'email' => 'anjali.s@gmail.com',
            'mobile' => '9850123459',
            'address' => 'Bungalow No 4, Godoli Naka, Satara',
            'city' => 'Satara',
            'pincode' => '415001',
            'lat' => 17.6750,
            'lng' => 74.0120,
            'status' => 'active',
        ]);

        // 9. Coupons
        $coupon1 = Coupon::create([
            'code' => 'WELCOME50',
            'description' => '50% instant discount up to ₹100 for your first fresh order',
            'discount_type' => 'percentage',
            'discount_value' => 50.00,
            'min_order_amount' => 200.00,
            'max_discount_amount' => 100.00,
            'usage_limit' => 200,
            'times_used' => 42,
            'start_date' => now()->subDays(10)->toDateString(),
            'end_date' => now()->addDays(30)->toDateString(),
            'status' => 'active',
        ]);

        $coupon2 = Coupon::create([
            'code' => 'HYPER100',
            'description' => 'Flat ₹100 OFF on orders above ₹600',
            'discount_type' => 'fixed',
            'discount_value' => 100.00,
            'min_order_amount' => 600.00,
            'max_discount_amount' => 100.00,
            'usage_limit' => 500,
            'times_used' => 128,
            'start_date' => now()->subDays(5)->toDateString(),
            'end_date' => now()->addDays(60)->toDateString(),
            'status' => 'active',
        ]);

        // 10. Discounts
        Discount::create([
            'title' => 'Monsoon Fresh Harvest Fest',
            'category_id' => $catVeg->id,
            'discount_type' => 'percentage',
            'discount_value' => 10.00,
            'start_date' => now()->subDays(3)->toDateString(),
            'end_date' => now()->addDays(15)->toDateString(),
            'status' => 'active',
        ]);

        // 11. Orders & Items & Logs
        // Order 1: Placed (Pending)
        $ord1 = Order::create([
            'order_number' => 'ORD-2026-00101',
            'branch_id' => $sataraBranch->id,
            'customer_id' => $c1->id,
            'delivery_boy_id' => null,
            'coupon_id' => $coupon1->id,
            'subtotal' => 238.00,
            'discount_amount' => 50.00,
            'delivery_charge' => 40.00,
            'tax_amount' => 11.40,
            'total_amount' => 239.40,
            'payment_mode' => 'COD',
            'payment_status' => 'PENDING',
            'order_status' => 'PLACED',
            'delivery_address' => $c1->address,
            'delivery_lat' => $c1->lat,
            'delivery_lng' => $c1->lng,
            'special_notes' => 'Please deliver between 4 PM to 6 PM',
            'placed_at' => now()->subMinutes(25),
        ]);

        OrderItem::create([
            'order_id' => $ord1->id,
            'product_id' => $p1->id,
            'product_name' => $p1->name,
            'unit' => $p1->unit,
            'price' => 38.00,
            'quantity' => 2,
            'total' => 76.00,
        ]);
        OrderItem::create([
            'order_id' => $ord1->id,
            'product_id' => $p7->id,
            'product_name' => $p7->name,
            'unit' => $p7->unit,
            'price' => 88.00,
            'quantity' => 1,
            'total' => 88.00,
        ]);
        OrderItem::create([
            'order_id' => $ord1->id,
            'product_id' => $p6->id,
            'product_name' => $p6->name,
            'unit' => $p6->unit,
            'price' => 64.00,
            'quantity' => 1,
            'total' => 64.00,
        ]);

        OrderStatusLog::create([
            'order_id' => $ord1->id,
            'from_status' => null,
            'to_status' => 'PLACED',
            'remarks' => 'Order placed by customer via Mobile Web',
            'changed_by' => 'Customer',
        ]);

        Payment::create([
            'order_id' => $ord1->id,
            'transaction_id' => 'TXN-COD-' . rand(100000, 999999),
            'payment_mode' => 'COD',
            'amount' => 239.40,
            'status' => 'PENDING',
            'collected_at' => null,
            'cod_verified' => false,
        ]);

        // Order 2: Out for Delivery
        $ord2 = Order::create([
            'order_number' => 'ORD-2026-00102',
            'branch_id' => $sataraBranch->id,
            'customer_id' => $c2->id,
            'delivery_boy_id' => $db1->id,
            'coupon_id' => null,
            'subtotal' => 525.00,
            'discount_amount' => 0.00,
            'delivery_charge' => 0.00, // Free delivery >= 500
            'tax_amount' => 26.25,
            'total_amount' => 551.25,
            'payment_mode' => 'ONLINE',
            'payment_status' => 'PAID',
            'order_status' => 'OUT_FOR_DELIVERY',
            'delivery_address' => $c2->address,
            'delivery_lat' => $c2->lat,
            'delivery_lng' => $c2->lng,
            'special_notes' => 'Call before arriving',
            'placed_at' => now()->subHours(1)->subMinutes(15),
        ]);

        OrderItem::create([
            'order_id' => $ord2->id,
            'product_id' => $p9->id,
            'product_name' => $p9->name,
            'unit' => $p9->unit,
            'price' => 350.00,
            'quantity' => 1,
            'total' => 350.00,
        ]);
        OrderItem::create([
            'order_id' => $ord2->id,
            'product_id' => $p5->id,
            'product_name' => $p5->name,
            'unit' => $p5->unit,
            'price' => 175.00,
            'quantity' => 1,
            'total' => 175.00,
        ]);

        OrderStatusLog::create([
            'order_id' => $ord2->id,
            'from_status' => null,
            'to_status' => 'PLACED',
            'remarks' => 'Order placed successfully',
            'changed_by' => 'Customer',
        ]);
        OrderStatusLog::create([
            'order_id' => $ord2->id,
            'from_status' => 'PLACED',
            'to_status' => 'CONFIRMED',
            'remarks' => 'Store manager accepted order',
            'changed_by' => 'Store Admin',
        ]);
        OrderStatusLog::create([
            'order_id' => $ord2->id,
            'from_status' => 'CONFIRMED',
            'to_status' => 'PACKED',
            'remarks' => 'Order items packed and sealed',
            'changed_by' => 'Packer Satara',
        ]);
        OrderStatusLog::create([
            'order_id' => $ord2->id,
            'from_status' => 'PACKED',
            'to_status' => 'ASSIGNED',
            'remarks' => 'Assigned to Rohan Patil',
            'changed_by' => 'Dispatch Controller',
        ]);
        OrderStatusLog::create([
            'order_id' => $ord2->id,
            'from_status' => 'ASSIGNED',
            'to_status' => 'OUT_FOR_DELIVERY',
            'remarks' => 'Rohan picked up package from store',
            'changed_by' => 'Rohan Patil',
        ]);

        Payment::create([
            'order_id' => $ord2->id,
            'transaction_id' => 'TXN-RAZORPAY-' . rand(1000000, 9999999),
            'payment_mode' => 'ONLINE',
            'amount' => 551.25,
            'status' => 'SUCCESS',
            'collected_at' => now()->subHours(1),
            'gateway_response' => '{"razorpay_payment_id": "pay_Oih87sdfg78", "status": "captured"}',
            'cod_verified' => true,
        ]);

        // Order 3: Delivered (Koregaon Branch)
        $ord3 = Order::create([
            'order_number' => 'ORD-2026-00103',
            'branch_id' => $koregaonBranch->id,
            'customer_id' => $c3->id,
            'delivery_boy_id' => $db3->id,
            'coupon_id' => null,
            'subtotal' => 197.00,
            'discount_amount' => 0.00,
            'delivery_charge' => 40.00,
            'tax_amount' => 9.85,
            'total_amount' => 246.85,
            'payment_mode' => 'COD',
            'payment_status' => 'PAID',
            'order_status' => 'DELIVERED',
            'delivery_address' => $c3->address,
            'delivery_lat' => $c3->lat,
            'delivery_lng' => $c3->lng,
            'special_notes' => 'Leave near door if not answering',
            'placed_at' => now()->subHours(3),
            'delivered_at' => now()->subHours(2)->addMinutes(10),
        ]);

        OrderItem::create([
            'order_id' => $ord3->id,
            'product_id' => $p6->id,
            'product_name' => $p6->name,
            'unit' => $p6->unit,
            'price' => 64.00,
            'quantity' => 2,
            'total' => 128.00,
        ]);
        OrderItem::create([
            'order_id' => $ord3->id,
            'product_id' => $p8->id,
            'product_name' => $p8->name,
            'unit' => $p8->unit,
            'price' => 45.00,
            'quantity' => 1,
            'total' => 45.00,
        ]);
        OrderItem::create([
            'order_id' => $ord3->id,
            'product_id' => $p2->id,
            'product_name' => $p2->name,
            'unit' => $p2->unit,
            'price' => 24.00,
            'quantity' => 1,
            'total' => 24.00,
        ]);

        OrderStatusLog::create([
            'order_id' => $ord3->id,
            'from_status' => null,
            'to_status' => 'PLACED',
            'remarks' => 'Order placed',
            'changed_by' => 'Customer',
        ]);
        OrderStatusLog::create([
            'order_id' => $ord3->id,
            'from_status' => 'OUT_FOR_DELIVERY',
            'to_status' => 'DELIVERED',
            'remarks' => 'Delivered to customer. Cash collected ₹247.',
            'changed_by' => 'Sagar Jadhav',
        ]);

        Payment::create([
            'order_id' => $ord3->id,
            'transaction_id' => 'TXN-COD-' . rand(100000, 999999),
            'payment_mode' => 'COD',
            'amount' => 246.85,
            'status' => 'SUCCESS',
            'collected_at' => now()->subHours(2),
            'cod_verified' => true,
        ]);

        // 12. Real-time Notifications
        Notification::create([
            'type' => 'order',
            'title' => 'New Order #ORD-2026-00101 Placed',
            'message' => 'New hyperlocal grocery order received for Satara Store from Dr. Pravin More (₹239.40 COD).',
            'url' => '/admin/orders/' . $ord1->id,
            'is_read' => false,
            'created_at' => now()->subMinutes(25),
        ]);

        Notification::create([
            'type' => 'delivery',
            'title' => 'Order #ORD-2026-00102 Out For Delivery',
            'message' => 'Rohan Patil is out for delivery to Sunita Kulkarni (Satara Store).',
            'url' => '/admin/orders/' . $ord2->id,
            'is_read' => true,
            'read_at' => now()->subMinutes(10),
            'created_at' => now()->subMinutes(30),
        ]);

        Notification::create([
            'type' => 'payment',
            'title' => 'COD Collected ₹246.85',
            'message' => 'Sagar Jadhav marked order #ORD-2026-00103 as delivered & collected cash.',
            'url' => '/admin/delivery-boys/cod',
            'is_read' => true,
            'read_at' => now()->subMinutes(50),
            'created_at' => now()->subHours(2),
        ]);

        // 13. System Settings
        $settings = [
            'store_name' => 'Metaglobe Hyperlocal Mart',
            'currency_symbol' => '₹',
            'gst_percentage' => '5',
            'support_phone' => '9876543200',
            'support_email' => 'admin@metaglobe.com',
            'satara_radius_km' => '3.00',
            'koregaon_radius_km' => '3.00',
            'sms_notifications_enabled' => '1',
            'whatsapp_notifications_enabled' => '1',
            'auto_assign_orders' => '0',
        ];

        foreach ($settings as $k => $v) {
            Setting::set($k, $v);
        }
    }
}
