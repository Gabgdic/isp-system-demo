<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use App\Models\SubscriptionPlan;
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

        $plans = SubscriptionPlan::latest()->get();

        return view('admin.settings', compact('settings', 'plans'));
    }

    public function updateSystem(Request $request)
    {
        $request->validate([
            'system_name' => 'required|string|max:255',
            'gcash_name' => 'required|string|max:255',
            'gcash_number' => 'required|string|max:30',
            'system_logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
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

    public function storePlan(Request $request)
    {
        $request->validate([
            'plan_name' => 'required|string|max:255|unique:subscription_plans,plan_name',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
        ]);

        SubscriptionPlan::create([
            'plan_name' => $request->plan_name,
            'price' => $request->price,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        return back()->with('success', 'Subscription plan created successfully.');
    }

    public function updatePlan(Request $request, $id)
    {
        $plan = SubscriptionPlan::findOrFail($id);

        $request->validate([
            'plan_name' => 'required|string|max:255|unique:subscription_plans,plan_name,' . $plan->id,
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
        ]);

        $plan->update([
            'plan_name' => $request->plan_name,
            'price' => $request->price,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        return back()->with('success', 'Subscription plan updated successfully.');
    }

    public function deletePlan($id)
    {
        $plan = SubscriptionPlan::findOrFail($id);
        $plan->delete();

        return back()->with('success', 'Subscription plan deleted successfully.');
    }
}