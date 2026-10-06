<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::firstOrCreate([
            'id' => 1,
        ], [
            'system_name' => 'Client Area',
            'system_logo' => null,
        ]);

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

            if (
                $settings->system_logo &&
                Storage::disk('public')->exists($settings->system_logo)
            ) {
                Storage::disk('public')->delete($settings->system_logo);
            }

            $settings->system_logo = $request
                ->file('system_logo')
                ->store('system', 'public');
        }

        $settings->save();

        return back()->with(
            'success',
            'System settings updated successfully.'
        );
    }
}