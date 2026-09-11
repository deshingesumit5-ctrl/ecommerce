@extends('layouts.admin')

@section('title', 'System & Store Settings')

@section('content')
<div class="space-y-6">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-emerald-600 mb-1">
                <i class="fa-solid fa-sliders"></i>
                <span>Platform Configuration</span>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">System & Store Settings</h1>
            <p class="text-xs text-slate-500 mt-0.5">Manage store branding, currency symbol (₹), GST rates, and notification channels.</p>
        </div>
    </div>

    <!-- SETTINGS FORM -->
    <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Store Profile & Branding -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-slate-900 flex items-center space-x-2 pb-3 border-b border-slate-100">
                    <i class="fa-solid fa-store text-emerald-600"></i>
                    <span>Store Profile & Identification</span>
                </h3>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Store / Business Name <span class="text-red-500">*</span></label>
                    <input type="text" name="store_name" value="{{ $settings['store_name'] ?? 'Metaglobe Hyperlocal Mart' }}" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:ring-2 focus:ring-emerald-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Customer Support Mobile <span class="text-red-500">*</span></label>
                        <input type="text" name="support_phone" value="{{ $settings['support_phone'] ?? '9876543200' }}" required maxlength="10" pattern="[0-9]{10}" class="strict-mobile w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 font-mono focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Support Email <span class="text-red-500">*</span></label>
                        <input type="email" name="support_email" value="{{ $settings['support_email'] ?? 'admin@metaglobe.com' }}" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Currency Symbol</label>
                    <input type="text" name="currency_symbol" value="{{ $settings['currency_symbol'] ?? '₹' }}" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 font-bold text-emerald-700 focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>

            <!-- Tax & Financial Settings -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-slate-900 flex items-center space-x-2 pb-3 border-b border-slate-100">
                    <i class="fa-solid fa-percent text-emerald-600"></i>
                    <span>Tax & Hyperlocal Logistics Rates</span>
                </h3>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">GST Tax Rate (%) <span class="text-red-500">*</span></label>
                    <input type="number" step="0.1" min="0" max="100" name="gst_percentage" value="{{ $settings['gst_percentage'] ?? '5' }}" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 font-bold text-slate-900 focus:ring-2 focus:ring-emerald-500">
                    <span class="text-[10px] text-slate-400 mt-1 block">Applied to standard checkout and GST tax invoice generation.</span>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Satara Base Radius (KM)</label>
                        <input type="number" step="0.5" min="0.5" name="satara_radius_km" value="{{ $settings['satara_radius_km'] ?? '3.00' }}" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 font-bold text-emerald-600 focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Koregaon Base Radius (KM)</label>
                        <input type="number" step="0.5" min="0.5" name="koregaon_radius_km" value="{{ $settings['koregaon_radius_km'] ?? '3.00' }}" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 font-bold text-blue-600 focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>
            </div>

            <!-- Notification Channels & Automations -->
            <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-slate-900 flex items-center space-x-2 pb-3 border-b border-slate-100">
                    <i class="fa-solid fa-bell text-emerald-600"></i>
                    <span>Real-time Alert Automations</span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                    <label class="p-4 rounded-xl border border-slate-200 bg-slate-50 flex items-center space-x-3 cursor-pointer hover:bg-slate-100 transition">
                        <input type="checkbox" name="sms_notifications_enabled" value="1" {{ ($settings['sms_notifications_enabled'] ?? '1') == '1' ? 'checked' : '' }} class="rounded text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <span class="font-bold text-slate-900 block">SMS Notifications</span>
                            <span class="text-[11px] text-slate-500">Send OTP and dispatch SMS to customers</span>
                        </div>
                    </label>

                    <label class="p-4 rounded-xl border border-slate-200 bg-slate-50 flex items-center space-x-3 cursor-pointer hover:bg-slate-100 transition">
                        <input type="checkbox" name="whatsapp_notifications_enabled" value="1" {{ ($settings['whatsapp_notifications_enabled'] ?? '1') == '1' ? 'checked' : '' }} class="rounded text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <span class="font-bold text-slate-900 block">WhatsApp Alerts</span>
                            <span class="text-[11px] text-slate-500">Send live delivery tracking updates</span>
                        </div>
                    </label>

                    <label class="p-4 rounded-xl border border-slate-200 bg-slate-50 flex items-center space-x-3 cursor-pointer hover:bg-slate-100 transition">
                        <input type="checkbox" name="auto_assign_orders" value="1" {{ ($settings['auto_assign_orders'] ?? '0') == '1' ? 'checked' : '' }} class="rounded text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <span class="font-bold text-slate-900 block">Auto-Assign Fleet</span>
                            <span class="text-[11px] text-slate-500">Automatically assign nearest online delivery boy</span>
                        </div>
                    </label>
                </div>
            </div>

        </div>

        <div class="flex justify-end pt-2">
            <button type="submit" class="px-6 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-lg shadow-emerald-600/20 flex items-center space-x-2">
                <i class="fa-solid fa-floppy-disk"></i>
                <span>Save All System Settings</span>
            </button>
        </div>
    </form>

</div>
@endsection
