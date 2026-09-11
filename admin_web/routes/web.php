<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\SubCategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\DailyPriceController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\DiscountController;
use App\Http\Controllers\Admin\DeliveryChargeController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DeliveryBoyController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\BranchSessionController;

// Root redirect
Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('admin.login');
Route::get('/admin/login', [AuthController::class, 'showLogin']);
Route::post('/login', [AuthController::class, 'login'])->name('admin.login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('admin.password.reset');

// Protected Admin Panel Routes
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Branch Switcher
    Route::post('/switch-branch', [BranchSessionController::class, 'switchBranch'])->name('switch_branch');

    // 1. Store Master (Branches)
    Route::resource('branches', BranchController::class)->except(['create', 'edit', 'show'])->names('branches');
    Route::post('branches/{branch}/toggle-status', [BranchController::class, 'toggleStatus'])->name('branches.toggle');
    Route::get('delivery-radius', [BranchController::class, 'radius'])->name('branches.radius');
    Route::post('delivery-radius/update', [BranchController::class, 'updateRadius'])->name('branches.radius.update');

    // 2. Category Master
    Route::resource('categories', CategoryController::class)->except(['create', 'edit', 'show'])->names('categories');
    Route::post('categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('categories.toggle');
    Route::get('categories/{category}/subcategories-json', [SubCategoryController::class, 'getByCategory'])->name('categories.subcategories_json');

    // 3. Sub-Category Master
    Route::resource('subcategories', SubCategoryController::class)->except(['create', 'edit', 'show'])->names('subcategories');
    Route::post('subcategories/{subCategory}/toggle-status', [SubCategoryController::class, 'toggleStatus'])->name('subcategories.toggle');

    // 4. Product Master
    Route::resource('products', ProductController::class)->except(['create', 'edit', 'show'])->names('products');
    Route::post('products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('products.toggle');
    Route::post('products/{product}/toggle-stock', [ProductController::class, 'toggleStock'])->name('products.toggle_stock');

    // 5. Daily Price Management
    Route::get('daily-prices', [DailyPriceController::class, 'index'])->name('daily_prices.index');
    Route::post('daily-prices/update-single', [DailyPriceController::class, 'updateSingle'])->name('daily_prices.update_single');
    Route::post('daily-prices/bulk-update', [DailyPriceController::class, 'bulkUpdate'])->name('daily_prices.bulk_update');

    // 6. Coupon Management
    Route::resource('coupons', CouponController::class)->except(['create', 'edit', 'show'])->names('coupons');
    Route::post('coupons/{coupon}/toggle-status', [CouponController::class, 'toggleStatus'])->name('coupons.toggle');

    // 7. Discount Management
    Route::resource('discounts', DiscountController::class)->except(['create', 'edit', 'show'])->names('discounts');
    Route::post('discounts/{discount}/toggle-status', [DiscountController::class, 'toggleStatus'])->name('discounts.toggle');

    // 8. Delivery Charge Configuration
    Route::resource('delivery-charges', DeliveryChargeController::class)->except(['create', 'edit', 'show'])->names('delivery_charges');

    // 9. Customer Management
    Route::resource('customers', CustomerController::class)->except(['create', 'edit'])->names('customers');
    Route::post('customers/{customer}/toggle-status', [CustomerController::class, 'toggleStatus'])->name('customers.toggle');

    // 10. Delivery Boy Master & Fleet
    Route::resource('delivery-boys', DeliveryBoyController::class)->except(['create', 'edit', 'show'])->names('delivery_boys');
    Route::post('delivery-boys/{deliveryBoy}/toggle-status', [DeliveryBoyController::class, 'toggleStatus'])->name('delivery_boys.toggle');
    Route::post('delivery-boys/{deliveryBoy}/toggle-online', [DeliveryBoyController::class, 'toggleOnline'])->name('delivery_boys.toggle_online');
    Route::get('delivery-boys-cod', [DeliveryBoyController::class, 'codCollections'])->name('delivery_boys.cod');
    Route::post('orders/{order}/verify-cod', [DeliveryBoyController::class, 'verifyCod'])->name('orders.verify_cod');

    // 11. Orders & Dispatch
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/assign', [OrderController::class, 'assignView'])->name('orders.assign_view');
    Route::post('orders/assign-delivery', [OrderController::class, 'assignDelivery'])->name('orders.assign_delivery');
    Route::get('orders/history', [OrderController::class, 'history'])->name('orders.history');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('orders/{order}/update-status', [OrderController::class, 'updateStatus'])->name('orders.update_status');

    // 12. Payment Management
    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');

    // 13. Invoice Management
    Route::get('invoices/{order}', [InvoiceController::class, 'show'])->name('invoices.show');

    // 14. Reports
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');

    // 15. Real-Time Notifications
    Route::get('notifications-json', [NotificationController::class, 'getNotifications'])->name('notifications.get');
    Route::post('notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read_all');

    // 16. Settings
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [SettingController::class, 'update'])->name('settings.update');
});
