<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::first();

        if (!$settings) {
            $settings = new SystemSetting();
            $settings->system_name = 'Client Area';
            $settings->system_logo = null;
            $settings->soa_location = 'Zone 1, Sogod, Southern Leyte';
            $settings->authorized_signatory = 'JURISH A. SY';
            $settings->signatory_title = 'Proprietor';
            $settings->soa_body_statement = "You are hereby reminded of your monthly subscription due for the billing period stated above. We humbly request that payment be settled on or before the due date to avoid any possible service interruption.\n\nIf payment has already been made before receiving this notice, please disregard this billing statement. We truly appreciate your prompt payment and continued support.";
            $settings->save();
        }

        return view('admin.settings', compact('settings'));
    }

    public function updateSystem(Request $request)
    {
        $request->validate([
            'system_name' => 'required|string|max:255',
            'gcash_name' => 'required|string|max:255',
            'gcash_number' => 'required|string|max:30',
            'system_logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $settings = SystemSetting::first();

        if (!$settings) {
            $settings = new SystemSetting();
        }

        $settings->system_name = $request->system_name;
        $settings->gcash_name = $request->gcash_name;
        $settings->gcash_number = $request->gcash_number;

        if ($request->hasFile('system_logo')) {
            if ($settings->system_logo && Storage::disk('public')->exists($settings->system_logo)) {
                Storage::disk('public')->delete($settings->system_logo);
            }

            $settings->system_logo = $request->file('system_logo')->store('system', 'public');
        }

        $settings->save();

        return back()->with('success', 'System settings updated successfully.');
    }

    public function updateSoa(Request $request)
    {
        $request->validate([
            'soa_location' => 'nullable|string|max:255',
            'authorized_signatory' => 'required|string|max:255',
            'signatory_title' => 'required|string|max:255',
            'authorized_signature' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'soa_body_statement' => 'required|string',
        ]);

        $settings = SystemSetting::first();

        if (!$settings) {
            $settings = new SystemSetting();
        }

        $settings->soa_location = $request->soa_location;
        $settings->authorized_signatory = $request->authorized_signatory;
        $settings->signatory_title = $request->signatory_title;
        $settings->soa_body_statement = $request->soa_body_statement;

        if ($request->hasFile('authorized_signature')) {
            if ($settings->authorized_signature && Storage::disk('public')->exists($settings->authorized_signature)) {
                Storage::disk('public')->delete($settings->authorized_signature);
            }

            $settings->authorized_signature = $request->file('authorized_signature')->store('signatures', 'public');
        }

        $settings->save();

        return back()->with('success', 'SOA settings updated successfully.');
    }
}