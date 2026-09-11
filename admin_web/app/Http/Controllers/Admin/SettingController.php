<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key');
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->except(['_token', '_method']);

        // Checkbox booleans
        $data['sms_notifications_enabled'] = $request->has('sms_notifications_enabled') ? '1' : '0';
        $data['whatsapp_notifications_enabled'] = $request->has('whatsapp_notifications_enabled') ? '1' : '0';
        $data['auto_assign_orders'] = $request->has('auto_assign_orders') ? '1' : '0';

        foreach ($data as $key => $val) {
            Setting::set($key, is_array($val) ? json_encode($val) : $val);
        }

        return redirect()->back()->with('success', 'System and Store settings updated successfully!');
    }
}
