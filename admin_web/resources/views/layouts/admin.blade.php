<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Panel Web</title>
    <link rel="icon" href="data:,">
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- FontAwesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Tailwind CSS CDN for instant robust styling & Leaflet CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Alpine.js for interactive modals, drawers, and dropdowns -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b',
                        },
                        navy: {
                            800: '#1e293b',
                            900: '#0f172a',
                        }
                    }
                }
            }
        }
    </script>
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #1e293b;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #475569;
            border-radius: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #64748b;
        }
        /* Sticky Table Headers */
        .sticky-table-header th {
            position: sticky;
            top: 0;
            z-index: 10;
            background-color: #f8fafc;
        }
    </style>
</head>
<body class="min-h-full flex flex-col bg-slate-50 text-slate-800 antialiased" x-data="adminApp()">

    @php
        $unreadNotificationsCount = \App\Models\Notification::where('is_read', false)->count();
        $latestNotifications = \App\Models\Notification::latest()->take(6)->get();
        $activeBranchId = session('active_branch_id', 'all');
        $headerBranches = \App\Models\Branch::where('status', 'active')->get();
        $activeBranchName = 'All Stores';
        if ($activeBranchId !== 'all') {
            $foundBranch = $headerBranches->firstWhere('id', $activeBranchId);
            if ($foundBranch) $activeBranchName = $foundBranch->name;
        }
    @endphp

    <!-- ============================================================== -->
    <!-- 1. TOP HEADER (BRAND & HORIZONTAL NAVBAR FOR LAPTOP / DESKTOP) -->
    <!-- ============================================================== -->
   <header class="sticky top-0 z-40 bg-slate-900 text-white shadow-md border-b border-slate-800">
<div class="max-w-7xl mx-auto pl-1 pr-4 sm:pl-2 sm:pr-6 lg:pl-2 lg:pr-8">
    <div class="flex items-center justify-between h-14 sm:h-16">
        
            <!-- Left: Hamburger + Brand + Nav Modules (all in one line now) -->
            <div class="flex items-center space-x-3 lg:space-x-5 min-w-0">
                <button type="button" @click="mobileMenuOpen = true" class="lg:hidden p-2 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>

                <a href="{{ route('admin.dashboard') }}" class="flex items-center group flex-shrink-0">
                    <span class="text-lg font-extrabold tracking-tight text-white group-hover:text-emerald-400 transition">Admin Panel Web</span>
                </a>

                <!-- Desktop Nav Modules: moved out of the separate <nav> bar, Settings removed -->
                <nav class="hidden lg:flex items-center space-x-1 text-xs font-medium">

                    <a href="{{ route('admin.dashboard') }}" class="px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.dashboard') ? 'bg-emerald-600 text-white font-semibold shadow-sm' : 'text-slate-300 hover:text-white hover:bg-slate-700/60' }}">
                        Dashboard
                    </a>

                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.away="open = false" class="px-3 py-2 rounded-lg transition flex items-center space-x-1.5 {{ request()->is('admin/branches*', 'admin/delivery-radius*', 'admin/categories*', 'admin/subcategories*', 'admin/products*') ? 'bg-slate-700 text-emerald-400 font-semibold' : 'text-slate-300 hover:text-white hover:bg-slate-700/60' }}">
                            <span>Business Masters</span>
                            <i class="fa-solid fa-chevron-down text-[9px] opacity-70 transition duration-200" :class="{'rotate-180': open}"></i>
                        </button>
                        <div x-show="open" x-cloak class="absolute left-0 mt-1 w-64 rounded-2xl bg-white shadow-2xl border border-slate-200/90 p-2 z-50 text-slate-700 divide-y divide-slate-100">
                            <div class="space-y-1 pb-1">
                                <a href="{{ route('admin.branches.index') }}" class="flex items-center space-x-2.5 p-2 rounded-xl transition {{ request()->routeIs('admin.branches.index') ? 'bg-emerald-50 text-emerald-700 font-bold' : 'hover:bg-slate-50 text-slate-700 hover:text-emerald-700' }}">
                                    <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ request()->routeIs('admin.branches.index') ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-500' }} text-xs"><i class="fa-solid fa-store"></i></div>
                                    <div class="min-w-0 flex-1"><div class="text-xs font-semibold leading-tight">Store Master</div><div class="text-[10px] text-slate-400">Stores, hubs & GPS setup</div></div>
                                </a>
                                <a href="{{ route('admin.branches.radius') }}" class="flex items-center space-x-2.5 p-2 rounded-xl transition {{ request()->routeIs('admin.branches.radius') ? 'bg-emerald-50 text-emerald-700 font-bold' : 'hover:bg-slate-50 text-slate-700 hover:text-emerald-700' }}">
                                    <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ request()->routeIs('admin.branches.radius') ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-500' }} text-xs"><i class="fa-solid fa-location-dot"></i></div>
                                    <div class="min-w-0 flex-1"><div class="text-xs font-semibold leading-tight">Delivery Radius Config</div><div class="text-[10px] text-slate-400">Geofencing & KM tiers</div></div>
                                </a>
                            </div>
                            <div class="space-y-1 pt-1">
                                <a href="{{ route('admin.categories.index') }}" class="flex items-center space-x-2.5 p-2 rounded-xl transition {{ request()->routeIs('admin.categories.index') ? 'bg-emerald-50 text-emerald-700 font-bold' : 'hover:bg-slate-50 text-slate-700 hover:text-emerald-700' }}">
                                    <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ request()->routeIs('admin.categories.index') ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-500' }} text-xs"><i class="fa-solid fa-layer-group"></i></div>
                                    <div class="min-w-0 flex-1"><div class="text-xs font-semibold leading-tight">Category Master</div><div class="text-[10px] text-slate-400">Root catalog groups</div></div>
                                </a>
                                <a href="{{ route('admin.subcategories.index') }}" class="flex items-center space-x-2.5 p-2 rounded-xl transition {{ request()->routeIs('admin.subcategories.index') ? 'bg-emerald-50 text-emerald-700 font-bold' : 'hover:bg-slate-50 text-slate-700 hover:text-emerald-700' }}">
                                    <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ request()->routeIs('admin.subcategories.index') ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-500' }} text-xs"><i class="fa-solid fa-boxes-stacked"></i></div>
                                    <div class="min-w-0 flex-1"><div class="text-xs font-semibold leading-tight">Sub-Category Master</div><div class="text-[10px] text-slate-400">Child segments</div></div>
                                </a>
                                <a href="{{ route('admin.products.index') }}" class="flex items-center space-x-2.5 p-2 rounded-xl transition {{ request()->routeIs('admin.products.index') ? 'bg-emerald-50 text-emerald-700 font-bold' : 'hover:bg-slate-50 text-slate-700 hover:text-emerald-700' }}">
                                    <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ request()->routeIs('admin.products.index') ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-500' }} text-xs"><i class="fa-solid fa-box-open"></i></div>
                                    <div class="min-w-0 flex-1"><div class="text-xs font-semibold leading-tight">Product Master</div><div class="text-[10px] text-slate-400">Items, units & pricing</div></div>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.away="open = false" class="px-3 py-2 rounded-lg transition flex items-center space-x-1.5 {{ request()->is('admin/daily-prices*', 'admin/coupons*', 'admin/discounts*', 'admin/delivery-charges*') ? 'bg-slate-700 text-emerald-400 font-semibold' : 'text-slate-300 hover:text-white hover:bg-slate-700/60' }}">
                            <span>Pricing & Discounts</span>
                            <i class="fa-solid fa-chevron-down text-[9px] opacity-70 transition duration-200" :class="{'rotate-180': open}"></i>
                        </button>
                        <div x-show="open" x-cloak class="absolute left-0 mt-1 w-64 rounded-2xl bg-white shadow-2xl border border-slate-200/90 p-2 z-50 text-slate-700 space-y-1">
                            <a href="{{ route('admin.daily_prices.index') }}" class="flex items-center space-x-2.5 p-2 rounded-xl transition {{ request()->routeIs('admin.daily_prices.index') ? 'bg-emerald-50 text-emerald-700 font-bold' : 'hover:bg-slate-50 text-slate-700 hover:text-emerald-700' }}">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ request()->routeIs('admin.daily_prices.index') ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-500' }} text-xs"><i class="fa-solid fa-chart-line"></i></div>
                                <div class="min-w-0 flex-1"><div class="text-xs font-semibold leading-tight">Daily Price Management</div><div class="text-[10px] text-slate-400">Live store market prices</div></div>
                            </a>
                            <a href="{{ route('admin.coupons.index') }}" class="flex items-center space-x-2.5 p-2 rounded-xl transition {{ request()->routeIs('admin.coupons.index') ? 'bg-emerald-50 text-emerald-700 font-bold' : 'hover:bg-slate-50 text-slate-700 hover:text-emerald-700' }}">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ request()->routeIs('admin.coupons.index') ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-500' }} text-xs"><i class="fa-solid fa-ticket"></i></div>
                                <div class="min-w-0 flex-1"><div class="text-xs font-semibold leading-tight">Coupon Management</div><div class="text-[10px] text-slate-400">Promo codes & limits</div></div>
                            </a>
                            <a href="{{ route('admin.discounts.index') }}" class="flex items-center space-x-2.5 p-2 rounded-xl transition {{ request()->routeIs('admin.discounts.index') ? 'bg-emerald-50 text-emerald-700 font-bold' : 'hover:bg-slate-50 text-slate-700 hover:text-emerald-700' }}">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ request()->routeIs('admin.discounts.index') ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-500' }} text-xs"><i class="fa-solid fa-percent"></i></div>
                                <div class="min-w-0 flex-1"><div class="text-xs font-semibold leading-tight">Discount Management</div><div class="text-[10px] text-slate-400">Catalog markdown deals</div></div>
                            </a>
                            <a href="{{ route('admin.delivery_charges.index') }}" class="flex items-center space-x-2.5 p-2 rounded-xl transition {{ request()->routeIs('admin.delivery_charges.index') ? 'bg-emerald-50 text-emerald-700 font-bold' : 'hover:bg-slate-50 text-slate-700 hover:text-emerald-700' }}">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ request()->routeIs('admin.delivery_charges.index') ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-500' }} text-xs"><i class="fa-solid fa-truck-ramp-box"></i></div>
                                <div class="min-w-0 flex-1"><div class="text-xs font-semibold leading-tight">Delivery Charge Config</div><div class="text-[10px] text-slate-400">Free delivery rules & slabs</div></div>
                            </a>
                        </div>
                    </div>

                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.away="open = false" class="px-3 py-2 rounded-lg transition flex items-center space-x-1.5 {{ request()->is('admin/orders*') ? 'bg-slate-700 text-emerald-400 font-semibold' : 'text-slate-300 hover:text-white hover:bg-slate-700/60' }}">
                            <span>Orders & Dispatch</span>
                            <i class="fa-solid fa-chevron-down text-[9px] opacity-70 transition duration-200" :class="{'rotate-180': open}"></i>
                        </button>
                        <div x-show="open" x-cloak class="absolute left-0 mt-1 w-64 rounded-2xl bg-white shadow-2xl border border-slate-200/90 p-2 z-50 text-slate-700 space-y-1">
                            <a href="{{ route('admin.orders.index') }}" class="flex items-center space-x-2.5 p-2 rounded-xl transition {{ request()->routeIs('admin.orders.index') ? 'bg-emerald-50 text-emerald-700 font-bold' : 'hover:bg-slate-50 text-slate-700 hover:text-emerald-700' }}">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ request()->routeIs('admin.orders.index') ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-500' }} text-xs"><i class="fa-solid fa-cart-shopping"></i></div>
                                <div class="min-w-0 flex-1"><div class="text-xs font-semibold leading-tight">Order Management</div><div class="text-[10px] text-slate-400">Active fulfillment pipeline</div></div>
                            </a>
                            <a href="{{ route('admin.orders.assign_view') }}" class="flex items-center space-x-2.5 p-2 rounded-xl transition {{ request()->routeIs('admin.orders.assign_view') ? 'bg-emerald-50 text-emerald-700 font-bold' : 'hover:bg-slate-50 text-slate-700 hover:text-emerald-700' }}">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ request()->routeIs('admin.orders.assign_view') ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-500' }} text-xs"><i class="fa-solid fa-motorcycle"></i></div>
                                <div class="min-w-0 flex-1"><div class="text-xs font-semibold leading-tight">Delivery Assignment</div><div class="text-[10px] text-slate-400">Rider assign & dispatch</div></div>
                            </a>
                            <a href="{{ route('admin.orders.history') }}" class="flex items-center space-x-2.5 p-2 rounded-xl transition {{ request()->routeIs('admin.orders.history') ? 'bg-emerald-50 text-emerald-700 font-bold' : 'hover:bg-slate-50 text-slate-700 hover:text-emerald-700' }}">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ request()->routeIs('admin.orders.history') ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-500' }} text-xs"><i class="fa-solid fa-clock-rotate-left"></i></div>
                                <div class="min-w-0 flex-1"><div class="text-xs font-semibold leading-tight">Order History & Audit</div><div class="text-[10px] text-slate-400">Completed & past logs</div></div>
                            </a>
                        </div>
                    </div>

                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.away="open = false" class="px-3 py-2 rounded-lg transition flex items-center space-x-1.5 {{ request()->is('admin/delivery-boys*') ? 'bg-slate-700 text-emerald-400 font-semibold' : 'text-slate-300 hover:text-white hover:bg-slate-700/60' }}">
                            <span>Delivery & Fleet</span>
                            <i class="fa-solid fa-chevron-down text-[9px] opacity-70 transition duration-200" :class="{'rotate-180': open}"></i>
                        </button>
                        <div x-show="open" x-cloak class="absolute left-0 mt-1 w-64 rounded-2xl bg-white shadow-2xl border border-slate-200/90 p-2 z-50 text-slate-700 space-y-1">
                            <a href="{{ route('admin.delivery_boys.index') }}" class="flex items-center space-x-2.5 p-2 rounded-xl transition {{ request()->routeIs('admin.delivery_boys.index') ? 'bg-emerald-50 text-emerald-700 font-bold' : 'hover:bg-slate-50 text-slate-700 hover:text-emerald-700' }}">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ request()->routeIs('admin.delivery_boys.index') ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-500' }} text-xs"><i class="fa-solid fa-person-biking"></i></div>
                                <div class="min-w-0 flex-1"><div class="text-xs font-semibold leading-tight">Delivery Boy Master</div><div class="text-[10px] text-slate-400">Riders, status & duty</div></div>
                            </a>
                            <a href="{{ route('admin.delivery_boys.cod') }}" class="flex items-center space-x-2.5 p-2 rounded-xl transition {{ request()->routeIs('admin.delivery_boys.cod') ? 'bg-emerald-50 text-emerald-700 font-bold' : 'hover:bg-slate-50 text-slate-700 hover:text-emerald-700' }}">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ request()->routeIs('admin.delivery_boys.cod') ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-500' }} text-xs"><i class="fa-solid fa-sack-dollar"></i></div>
                                <div class="min-w-0 flex-1"><div class="text-xs font-semibold leading-tight">COD Collections</div><div class="text-[10px] text-slate-400">Cash settle & audit</div></div>
                            </a>
                        </div>
                    </div>

                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.away="open = false" class="px-3 py-2 rounded-lg transition flex items-center space-x-1.5 {{ request()->is('admin/customers*', 'admin/payments*', 'admin/reports*') ? 'bg-slate-700 text-emerald-400 font-semibold' : 'text-slate-300 hover:text-white hover:bg-slate-700/60' }}">
                            <span>Users & Financials</span>
                            <i class="fa-solid fa-chevron-down text-[9px] opacity-70 transition duration-200" :class="{'rotate-180': open}"></i>
                        </button>
                        <div x-show="open" x-cloak class="absolute left-0 mt-1 w-64 rounded-2xl bg-white shadow-2xl border border-slate-200/90 p-2 z-50 text-slate-700 space-y-1">
                            <a href="{{ route('admin.customers.index') }}" class="flex items-center space-x-2.5 p-2 rounded-xl transition {{ request()->routeIs('admin.customers.index') ? 'bg-emerald-50 text-emerald-700 font-bold' : 'hover:bg-slate-50 text-slate-700 hover:text-emerald-700' }}">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ request()->routeIs('admin.customers.index') ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-500' }} text-xs"><i class="fa-solid fa-users"></i></div>
                                <div class="min-w-0 flex-1"><div class="text-xs font-semibold leading-tight">Customer Management</div><div class="text-[10px] text-slate-400">Buyers, orders & status</div></div>
                            </a>
                            <a href="{{ route('admin.payments.index') }}" class="flex items-center space-x-2.5 p-2 rounded-xl transition {{ request()->routeIs('admin.payments.index') ? 'bg-emerald-50 text-emerald-700 font-bold' : 'hover:bg-slate-50 text-slate-700 hover:text-emerald-700' }}">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ request()->routeIs('admin.payments.index') ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-500' }} text-xs"><i class="fa-solid fa-credit-card"></i></div>
                                <div class="min-w-0 flex-1"><div class="text-xs font-semibold leading-tight">Payment Management</div><div class="text-[10px] text-slate-400">Gateways, razorpay & logs</div></div>
                            </a>
                            <a href="{{ route('admin.reports.index') }}" class="flex items-center space-x-2.5 p-2 rounded-xl transition {{ request()->routeIs('admin.reports.index') ? 'bg-emerald-50 text-emerald-700 font-bold' : 'hover:bg-slate-50 text-slate-700 hover:text-emerald-700' }}">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ request()->routeIs('admin.reports.index') ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-500' }} text-xs"><i class="fa-solid fa-chart-pie"></i></div>
                                <div class="min-w-0 flex-1"><div class="text-xs font-semibold leading-tight">Analytics Reports</div><div class="text-[10px] text-slate-400">Sales, revenue & metrics</div></div>
                            </a>
                        </div>
                    </div>

                    <!-- Settings link intentionally removed -->
                </nav>
            </div>

            <!-- Right: Store Switcher + Bell + Profile (desktop) / Bell + Hamburger fallback (mobile) -->
            <div class="flex items-center space-x-2">

                <div class="relative hidden lg:block" x-data="{ branchOpen: false }">
                    <button @click="branchOpen = !branchOpen" @click.away="branchOpen = false" class="flex items-center space-x-1.5 text-xs font-semibold px-2.5 py-1.5 rounded-lg bg-slate-900/80 hover:bg-slate-900 text-emerald-400 border border-slate-700/80 transition">
                        <span class="max-w-[120px] truncate">{{ $activeBranchName }}</span>
                        <i class="fa-solid fa-chevron-down text-[9px] text-slate-400"></i>
                    </button>
                    <div x-show="branchOpen" x-cloak class="absolute right-0 mt-1 w-56 rounded-xl bg-white shadow-2xl border border-slate-200 py-1.5 z-50 text-slate-700">
                        <div class="px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100">Switch Store Context</div>
                        <form action="{{ route('admin.switch_branch') }}" method="POST">
                            @csrf
                            <button type="submit" name="branch_id" value="all" class="w-full text-left px-3 py-2 text-xs flex items-center justify-between hover:bg-slate-50 transition {{ $activeBranchId === 'all' ? 'text-emerald-600 font-bold bg-emerald-50' : '' }}">
                                <span>All Stores</span>
                                @if($activeBranchId === 'all') <i class="fa-solid fa-check text-emerald-600"></i> @endif
                            </button>
                            @foreach($headerBranches as $hBranch)
                                <button type="submit" name="branch_id" value="{{ $hBranch->id }}" class="w-full text-left px-3 py-2 text-xs flex items-center justify-between hover:bg-slate-50 transition {{ (string)$activeBranchId === (string)$hBranch->id ? 'text-emerald-600 font-bold bg-emerald-50' : '' }}">
                                    <span class="truncate">{{ $hBranch->name }} ({{ $hBranch->code }})</span>
                                    @if((string)$activeBranchId === (string)$hBranch->id) <i class="fa-solid fa-check text-emerald-600"></i> @endif
                                </button>
                            @endforeach
                        </form>
                    </div>
                </div>

                <div class="relative" x-data="{ notifOpen: false }">
                    <button @click="notifOpen = !notifOpen" @click.away="notifOpen = false" class="relative p-1.5 rounded-lg text-slate-300 hover:text-white hover:bg-slate-700/70 transition focus:outline-none" title="Notifications">
                        <i class="fa-regular fa-bell text-base"></i>
                        @if($unreadNotificationsCount > 0)
                            <span class="absolute top-0.5 right-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-red-600 text-[10px] font-bold text-white shadow-md animate-pulse">{{ $unreadNotificationsCount }}</span>
                        @endif
                    </button>
                    <div x-show="notifOpen" x-cloak class="absolute right-0 mt-2 w-80 sm:w-96 rounded-2xl bg-white shadow-2xl border border-slate-200 z-50 text-slate-800 overflow-hidden">
                        <div class="px-4 py-3 bg-slate-900 text-white flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <i class="fa-solid fa-bell text-emerald-400"></i>
                                <span class="font-bold text-sm">Notifications</span>
                                <span class="bg-red-600 text-white text-[11px] font-bold px-1.5 py-0.5 rounded-full">({{ $unreadNotificationsCount }})</span>
                            </div>
                            @if($unreadNotificationsCount > 0)
                                <form action="{{ route('admin.notifications.read_all') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-xs text-emerald-400 hover:underline">Mark all read</button>
                                </form>
                            @endif
                        </div>
                        <div class="max-h-80 overflow-y-auto divide-y divide-slate-100 custom-scrollbar">
                            @forelse($latestNotifications as $notif)
                                <form action="{{ route('admin.notifications.read', $notif->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="redirect" value="1">
                                    <button type="submit" class="w-full text-left p-3.5 hover:bg-slate-50 transition flex items-start space-x-3 {{ !$notif->is_read ? 'bg-emerald-50/50' : '' }}">
                                        <div class="mt-0.5 p-2 rounded-lg {{ $notif->type === 'order' ? 'bg-blue-100 text-blue-600' : ($notif->type === 'delivery' ? 'bg-emerald-100 text-emerald-600' : 'bg-amber-100 text-amber-600') }} text-xs">
                                            <i class="fa-solid {{ $notif->type === 'order' ? 'fa-cart-shopping' : ($notif->type === 'delivery' ? 'fa-motorcycle' : 'fa-indian-rupee-sign') }}"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-semibold text-slate-800 truncate">{{ $notif->title }}</p>
                                            <p class="text-[11px] text-slate-500 line-clamp-2 mt-0.5">{{ $notif->message }}</p>
                                            <span class="text-[10px] text-slate-400 mt-1 block">{{ $notif->created_at->diffForHumans() }}</span>
                                        </div>
                                        @if(!$notif->is_read)<span class="w-2 h-2 rounded-full bg-red-500 mt-1.5 flex-shrink-0"></span>@endif
                                    </button>
                                </form>
                            @empty
                                <div class="p-6 text-center text-slate-400 text-xs">
                                    <i class="fa-regular fa-bell-slash text-2xl mb-2 text-slate-300"></i>
                                    <p>No new notifications</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="relative hidden lg:block" x-data="{ profileOpen: false }">
                    <button @click="profileOpen = !profileOpen" @click.away="profileOpen = false" class="p-1.5 rounded-lg text-slate-300 hover:text-white hover:bg-slate-700/70 transition focus:outline-none" title="Admin Account">
                        <i class="fa-regular fa-user text-base"></i>
                    </button>
                    <div x-show="profileOpen" x-cloak class="absolute right-0 mt-2 w-64 rounded-2xl bg-white shadow-2xl border border-slate-200 py-2 z-50 text-slate-700">
                        <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/70">
                            <div class="flex items-center space-x-3">
                                <div class="w-9 h-9 rounded-full bg-emerald-600 text-white font-bold text-xs flex items-center justify-center shadow-sm">AD</div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-bold text-slate-900 truncate">{{ auth()->user()->name ?? 'System Super Admin' }}</p>
                                    <p class="text-[11px] text-slate-500 truncate">{{ auth()->user()->email ?? 'admin@gmail.com' }}</p>
                                    <span class="inline-block mt-0.5 px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-semibold">Super Admin</span>
                                </div>
                            </div>
                        </div>
                        <div class="py-1">
                            <button type="button" @click="resetModalOpen = true; profileOpen = false" class="w-full text-left px-4 py-2 text-xs flex items-center text-slate-700 hover:bg-slate-50 transition">
                                <i class="fa-solid fa-key text-slate-400 mr-2 text-xs"></i>
                                <span>Reset Password</span>
                            </button>
                        </div>
                        <div class="border-t border-slate-100 my-1"></div>
                        <form action="{{ route('admin.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-xs flex items-center text-red-600 hover:bg-red-50 transition font-semibold">
                                <i class="fa-solid fa-arrow-right-from-bracket mr-2 text-xs text-red-500"></i>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Mobile Only Quick Notification fallback removed — bell above is now always visible -->
            </div>

        </div>
    </div>
</header>

    <!-- ============================================================== -->
    <!-- 2. MOBILE SIDEBAR DRAWER (Shown ONLY on Mobile / Small Screens)-->
    <!-- ============================================================== -->
    <div x-show="mobileMenuOpen" x-cloak class="relative z-50 lg:hidden" role="dialog" aria-modal="true">
        <div x-show="mobileMenuOpen" x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm" @click="mobileMenuOpen = false"></div>

        <div class="fixed inset-0 flex">
            <div x-show="mobileMenuOpen" x-transition:enter="transition ease-in-out duration-300 transform" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in-out duration-300 transform" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" class="relative mr-16 flex w-full max-w-xs flex-1 flex-col bg-slate-900 pt-5 pb-4 shadow-2xl">
                
                <!-- Close Drawer Button -->
                <div class="absolute top-0 right-0 -mr-12 pt-4">
                    <button type="button" @click="mobileMenuOpen = false" class="ml-1 flex h-10 w-10 items-center justify-center rounded-full focus:outline-none text-white hover:bg-slate-800">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>

                <!-- Mobile Brand -->
                <div class="flex items-center space-x-3 px-5 pb-4 border-b border-slate-800">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-400 flex items-center justify-center text-white font-bold text-lg shadow">
                        <i class="fa-solid fa-bolt"></i>
                    </div>
                    <span class="text-xl font-bold tracking-tight text-white">Admin Panel Web</span>
                </div>

                <!-- Store Switcher in Mobile Sidebar -->
                <div class="px-4 py-3 border-b border-slate-800" x-data="{ mBranchOpen: false }">
                    <button @click="mBranchOpen = !mBranchOpen" class="w-full flex items-center justify-between text-xs font-semibold px-3 py-2 rounded-lg bg-slate-800 text-emerald-400 border border-slate-700">
                        <span>Store: {{ $activeBranchName }}</span>
                        <i class="fa-solid fa-chevron-down text-[10px]"></i>
                    </button>
                    <div x-show="mBranchOpen" class="mt-2 space-y-1 bg-slate-950 p-2 rounded-lg border border-slate-800">
                        <form action="{{ route('admin.switch_branch') }}" method="POST">
                            @csrf
                            <button type="submit" name="branch_id" value="all" class="w-full text-left px-2 py-1.5 text-xs rounded hover:bg-slate-800 transition {{ $activeBranchId === 'all' ? 'text-emerald-400 font-bold' : 'text-slate-300' }}">
                                All Stores
                            </button>
                            @foreach($headerBranches as $hBranch)
                                <button type="submit" name="branch_id" value="{{ $hBranch->id }}" class="w-full text-left px-2 py-1.5 text-xs rounded hover:bg-slate-800 transition {{ (string)$activeBranchId === (string)$hBranch->id ? 'text-emerald-400 font-bold' : 'text-slate-300' }}">
                                    {{ $hBranch->name }} ({{ $hBranch->code }})
                                </button>
                            @endforeach
                        </form>
                    </div>
                </div>

                <!-- Mobile Menu Items -->
                <div class="mt-2 flex-1 h-0 overflow-y-auto px-3 space-y-1 custom-scrollbar text-sm">
                    <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded-lg text-slate-200 hover:bg-slate-800 hover:text-white font-medium {{ request()->routeIs('admin.dashboard') ? 'bg-emerald-600 text-white font-bold' : '' }}">
                        Dashboard
                    </a>

                    <!-- Business Masters -->
                    <div class="pt-2 text-[11px] font-bold uppercase tracking-wider text-slate-500 px-3">Business Masters</div>
                    <a href="{{ route('admin.branches.index') }}" class="block px-3 py-1.5 rounded-lg text-slate-300 hover:bg-slate-800 text-xs {{ request()->routeIs('admin.branches.index') ? 'text-emerald-400 font-bold' : '' }}">
                        Store Master
                    </a>
                    <a href="{{ route('admin.branches.radius') }}" class="block px-3 py-1.5 rounded-lg text-slate-300 hover:bg-slate-800 text-xs {{ request()->routeIs('admin.branches.radius') ? 'text-emerald-400 font-bold' : '' }}">
                        Delivery Radius Config
                    </a>
                    <a href="{{ route('admin.categories.index') }}" class="block px-3 py-1.5 rounded-lg text-slate-300 hover:bg-slate-800 text-xs {{ request()->routeIs('admin.categories.index') ? 'text-emerald-400 font-bold' : '' }}">
                        Category Master
                    </a>
                    <a href="{{ route('admin.subcategories.index') }}" class="block px-3 py-1.5 rounded-lg text-slate-300 hover:bg-slate-800 text-xs {{ request()->routeIs('admin.subcategories.index') ? 'text-emerald-400 font-bold' : '' }}">
                        Sub-Category Master
                    </a>
                    <a href="{{ route('admin.products.index') }}" class="block px-3 py-1.5 rounded-lg text-slate-300 hover:bg-slate-800 text-xs {{ request()->routeIs('admin.products.index') ? 'text-emerald-400 font-bold' : '' }}">
                        Product Master
                    </a>

                    <!-- Pricing & Discounts -->
                    <div class="pt-3 text-[11px] font-bold uppercase tracking-wider text-slate-500 px-3">Pricing & Discounts</div>
                    <a href="{{ route('admin.daily_prices.index') }}" class="block px-3 py-1.5 rounded-lg text-slate-300 hover:bg-slate-800 text-xs {{ request()->routeIs('admin.daily_prices.index') ? 'text-emerald-400 font-bold' : '' }}">
                        Daily Price Management
                    </a>
                    <a href="{{ route('admin.coupons.index') }}" class="block px-3 py-1.5 rounded-lg text-slate-300 hover:bg-slate-800 text-xs {{ request()->routeIs('admin.coupons.index') ? 'text-emerald-400 font-bold' : '' }}">
                        Coupon Management
                    </a>
                    <a href="{{ route('admin.discounts.index') }}" class="block px-3 py-1.5 rounded-lg text-slate-300 hover:bg-slate-800 text-xs {{ request()->routeIs('admin.discounts.index') ? 'text-emerald-400 font-bold' : '' }}">
                        Discount Management
                    </a>
                    <a href="{{ route('admin.delivery_charges.index') }}" class="block px-3 py-1.5 rounded-lg text-slate-300 hover:bg-slate-800 text-xs {{ request()->routeIs('admin.delivery_charges.index') ? 'text-emerald-400 font-bold' : '' }}">
                        Delivery Charge Config
                    </a>

                    <!-- Orders & Fleet -->
                    <div class="pt-3 text-[11px] font-bold uppercase tracking-wider text-slate-500 px-3">Orders & Fleet</div>
                    <a href="{{ route('admin.orders.index') }}" class="block px-3 py-1.5 rounded-lg text-slate-300 hover:bg-slate-800 text-xs {{ request()->routeIs('admin.orders.index') ? 'text-emerald-400 font-bold' : '' }}">
                        Order Management
                    </a>
                    <a href="{{ route('admin.orders.assign_view') }}" class="block px-3 py-1.5 rounded-lg text-slate-300 hover:bg-slate-800 text-xs {{ request()->routeIs('admin.orders.assign_view') ? 'text-emerald-400 font-bold' : '' }}">
                        Delivery Assignment
                    </a>
                    <a href="{{ route('admin.orders.history') }}" class="block px-3 py-1.5 rounded-lg text-slate-300 hover:bg-slate-800 text-xs {{ request()->routeIs('admin.orders.history') ? 'text-emerald-400 font-bold' : '' }}">
                        Order History & Audit
                    </a>
                    <a href="{{ route('admin.delivery_boys.index') }}" class="block px-3 py-1.5 rounded-lg text-slate-300 hover:bg-slate-800 text-xs {{ request()->routeIs('admin.delivery_boys.index') ? 'text-emerald-400 font-bold' : '' }}">
                        Delivery Boy Master
                    </a>
                    <a href="{{ route('admin.delivery_boys.cod') }}" class="block px-3 py-1.5 rounded-lg text-slate-300 hover:bg-slate-800 text-xs {{ request()->routeIs('admin.delivery_boys.cod') ? 'text-emerald-400 font-bold' : '' }}">
                        COD Collections
                    </a>

                    <!-- Users & Reports -->
                    <div class="pt-3 text-[11px] font-bold uppercase tracking-wider text-slate-500 px-3">Users & Reports</div>
                    <a href="{{ route('admin.customers.index') }}" class="block px-3 py-1.5 rounded-lg text-slate-300 hover:bg-slate-800 text-xs {{ request()->routeIs('admin.customers.index') ? 'text-emerald-400 font-bold' : '' }}">
                        Customer Management
                    </a>
                    <a href="{{ route('admin.payments.index') }}" class="block px-3 py-1.5 rounded-lg text-slate-300 hover:bg-slate-800 text-xs {{ request()->routeIs('admin.payments.index') ? 'text-emerald-400 font-bold' : '' }}">
                        Payment Management
                    </a>
                    <a href="{{ route('admin.reports.index') }}" class="block px-3 py-1.5 rounded-lg text-slate-300 hover:bg-slate-800 text-xs {{ request()->routeIs('admin.reports.index') ? 'text-emerald-400 font-bold' : '' }}">
                        Reports & Analytics
                    </a>
                    <a href="{{ route('admin.settings.index') }}" class="block px-3 py-1.5 rounded-lg text-slate-300 hover:bg-slate-800 text-xs {{ request()->routeIs('admin.settings.index') ? 'text-emerald-400 font-bold' : '' }}">
                        Settings
                    </a>

                    <div class="pt-4 border-t border-slate-800">
                        <form action="{{ route('admin.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full text-left px-3 py-2 rounded-lg text-red-400 hover:bg-red-950/40 text-xs font-semibold">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- 3. MAIN BODY CONTENT CONTAINER                                -->
    <!-- ============================================================== -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        
        <!-- SQUARE SUCCESS BANNER IN THE CENTER OF LAPTOP -->
        @if(session('success'))
            <div id="globalFlashSuccessModal" class="fixed inset-0 z-[99998] flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">
                <div class="relative w-[320px] max-w-[90vw] aspect-square rounded-3xl bg-white shadow-2xl border border-slate-200 p-6 flex flex-col items-center justify-between text-center">
                    <button type="button" onclick="document.getElementById('globalFlashSuccessModal').remove()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 w-8 h-8 rounded-full flex items-center justify-center hover:bg-slate-100 transition">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>

                    <div class="mt-3">
                        <div class="w-16 h-16 mx-auto rounded-2xl bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-600 text-3xl shadow-sm">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                    </div>

                    <div class="px-2 my-auto">
                        <h3 class="text-base font-bold text-slate-900 leading-snug">Success</h3>
                        <p class="text-sm font-semibold text-emerald-700 mt-2 px-1 leading-relaxed">{{ session('success') }}</p>
                    </div>

                    <div class="w-full pt-2 border-t border-slate-100">
                        <button type="button" onclick="document.getElementById('globalFlashSuccessModal').remove()" class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/30 transition">
                            OK
                        </button>
                    </div>
                </div>
            </div>
            <script>
                setTimeout(() => {
                    const el = document.getElementById('globalFlashSuccessModal');
                    if (el) el.remove();
                }, 4000);
            </script>
        @endif

        @if($errors->any())
            <div id="globalFlashErrorModal" class="fixed inset-0 z-[99998] flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">
                <div class="relative w-[340px] max-w-[90vw] aspect-square rounded-3xl bg-white shadow-2xl border border-slate-200 p-6 flex flex-col items-center justify-between text-center">
                    <button type="button" onclick="document.getElementById('globalFlashErrorModal').remove()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 w-8 h-8 rounded-full flex items-center justify-center hover:bg-slate-100 transition">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>

                    <div class="mt-2">
                        <div class="w-16 h-16 mx-auto rounded-2xl bg-rose-50 border border-rose-200 flex items-center justify-center text-rose-600 text-3xl shadow-sm">
                            <i class="fa-solid fa-circle-exclamation"></i>
                        </div>
                    </div>

                    <div class="px-2 my-auto max-h-32 overflow-y-auto">
                        <h3 class="text-base font-bold text-slate-900 leading-snug">Notice</h3>
                        <ul class="mt-2 text-xs font-semibold text-rose-600 space-y-1 text-center">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="w-full pt-2 border-t border-slate-100">
                        <button type="button" onclick="document.getElementById('globalFlashErrorModal').remove()" class="w-full py-2.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs shadow-md transition">
                            Dismiss
                        </button>
                    </div>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- ============================================================== -->
    <!-- 4. FOOTER                                                     -->
    <!-- ============================================================== -->
    <footer class="bg-white border-t border-slate-200 mt-auto py-4 text-center text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-2">
            <div>
                <span class="font-bold text-slate-700">Admin Panel Web</span> &copy; {{ date('Y') }} Hyperlocal E-Commerce & Delivery Management System
            </div>
            <div class="flex items-center space-x-4 text-slate-400">
                <span><i class="fa-solid fa-circle-check text-emerald-500 mr-1"></i> Production Ready</span>
                <span>Satara & Koregaon Operational</span>
            </div>
        </div>
    </footer>

    <!-- ============================================================== -->
    <!-- 5. DYNAMIC RESET PASSWORD MODAL                               -->
    <!-- ============================================================== -->
    <div x-show="resetModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm transition-opacity" @click="resetModalOpen = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-200 p-6">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900 flex items-center space-x-2">
                        <i class="fa-solid fa-key text-emerald-600"></i>
                        <span>Reset Account Password</span>
                    </h3>
                    <button @click="resetModalOpen = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <form id="globalResetPasswordForm" action="{{ route('admin.password.reset') }}" method="POST" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Admin Email Address <span class="text-red-500">*</span></label>
                        <input type="email" name="email" value="{{ auth()->user()->email ?? 'admin@gmail.com' }}" required class="w-full text-xs rounded-lg border-slate-200 bg-slate-50 px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">New Password <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="password" id="modal_new_pass" name="password" required minlength="6" placeholder="Enter at least 6 characters" class="w-full text-xs rounded-lg border-slate-200 px-3 py-2 pr-10 focus:ring-emerald-500 focus:border-emerald-500">
                            <button type="button" onclick="togglePass('modal_new_pass')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Confirm New Password <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="password" id="modal_confirm_pass" name="password_confirmation" required minlength="6" placeholder="Confirm your new password" class="w-full text-xs rounded-lg border-slate-200 px-3 py-2 pr-10 focus:ring-emerald-500 focus:border-emerald-500">
                            <button type="button" onclick="togglePass('modal_confirm_pass')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-2 pt-2">
                        <button type="button" @click="resetModalOpen = false" class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-semibold text-slate-700">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-xs font-semibold text-white shadow-sm shadow-emerald-600/30">Update Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- SQUARE CONFIRMATION BANNER IN THE CENTER OF LAPTOP             -->
    <!-- ============================================================== -->
    <div id="globalConfirmModal" class="fixed inset-0 z-[99999] hidden items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm transition-opacity">
        <div class="relative w-[340px] max-w-[90vw] aspect-square rounded-3xl bg-white shadow-2xl border border-slate-200/80 p-6 flex flex-col items-center justify-between text-center transform transition-all animate-in fade-in zoom-in duration-200">
            <!-- Top Close Button -->
            <button type="button" onclick="closeConfirmModal(false)" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 w-8 h-8 rounded-full flex items-center justify-center hover:bg-slate-100 transition">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>

            <!-- Warning Icon in Center Box -->
            <div class="mt-2">
                <div class="w-16 h-16 mx-auto rounded-2xl bg-rose-50 border border-rose-200 flex items-center justify-center text-rose-600 text-2xl shadow-inner">
                    <i class="fa-solid fa-trash-can"></i>
                </div>
            </div>

            <!-- Message content -->
            <div class="px-2 my-auto">
                <h3 id="confirmModalTitle" class="text-base font-bold text-slate-900 leading-snug">Confirm Delete</h3>
                <p id="confirmModalMessage" class="text-sm font-semibold text-slate-700 mt-2 px-1">Delete product?</p>
                <p class="text-[11px] text-slate-400 mt-1">This action cannot be undone.</p>
            </div>

            <!-- Action Buttons at bottom of square -->
            <div class="w-full grid grid-cols-2 gap-3 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeConfirmModal(false)" class="w-full py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                    Cancel
                </button>
                <button type="button" id="confirmModalOkBtn" onclick="closeConfirmModal(true)" class="w-full py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md shadow-rose-600/30 transition">
                    OK
                </button>
            </div>
        </div>
    </div>

    <!-- GLOBAL CLIENT-SIDE SCRIPTS -->
    <script>
        function adminApp() {
            return {
                mobileMenuOpen: false,
                resetModalOpen: false,
            };
        }

        function togglePass(id) {
            const input = document.getElementById(id);
            if (input.type === 'password') {
                input.type = 'text';
            } else {
                input.type = 'password';
            }
        }

        // Global strict 10-digit mobile number enforcement on typing
        document.addEventListener('input', function(e) {
            if (e.target && (e.target.name === 'mobile' || e.target.name === 'contact_phone' || e.target.classList.contains('strict-mobile'))) {
                e.target.value = e.target.value.replace(/[^0-9]/g, '').slice(0, 10);
            }
        });

        // SQUARE CONFIRMATION BANNER LOGIC
        let pendingConfirmCallback = null;

        function showSquareConfirm(message, onConfirm) {
            const modal = document.getElementById('globalConfirmModal');
            const msgEl = document.getElementById('confirmModalMessage');
            const titleEl = document.getElementById('confirmModalTitle');
            if (!modal) {
                if (window.confirm(message)) onConfirm();
                return;
            }
            msgEl.textContent = message || 'Are you sure you want to proceed?';
            if (message && message.toLowerCase().includes('delete')) {
                titleEl.textContent = 'Confirm Delete';
            } else {
                titleEl.textContent = 'Confirmation';
            }
            pendingConfirmCallback = onConfirm;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeConfirmModal(confirmed) {
            const modal = document.getElementById('globalConfirmModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
            if (confirmed && typeof pendingConfirmCallback === 'function') {
                const cb = pendingConfirmCallback;
                pendingConfirmCallback = null;
                cb();
            } else {
                pendingConfirmCallback = null;
            }
        }

        // Global interceptor for all confirmation forms
        document.addEventListener('DOMContentLoaded', function() {
            function bindConfirmForms() {
                document.querySelectorAll('form').forEach(form => {
                    const onsubmitAttr = form.getAttribute('onsubmit');
                    if (onsubmitAttr && onsubmitAttr.includes('confirm(')) {
                        const match = onsubmitAttr.match(/confirm\(['"](.*?)['"]\)/);
                        const confirmMsg = match ? match[1] : 'Are you sure you want to proceed?';
                        form.removeAttribute('onsubmit');
                        form.addEventListener('submit', function(e) {
                            e.preventDefault();
                            showSquareConfirm(confirmMsg, () => {
                                form.submit();
                            });
                        });
                    }
                });
            }

            bindConfirmForms();
            const observer = new MutationObserver(() => bindConfirmForms());
            observer.observe(document.body, { childList: true, subtree: true });
        });
    </script>
    
    @stack('scripts')
</body>
</html>
