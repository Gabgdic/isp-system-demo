<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SubscriberController extends Controller
{
    public function index()
    {
        $subscribers = Subscriber::latest()->paginate(10);

        $plans = SubscriptionPlan::where('status', 'active')
            ->orderBy('plan_name')
            ->get();

        $totalSubscribers = Subscriber::count();
        $activeSubscribers = Subscriber::where('status', 'active')->count();
        $disconnectedSubscribers = Subscriber::where('status', 'disconnected')->count();

        return view('admin.subscriber', compact(
            'subscribers',
            'plans',
            'totalSubscribers',
            'activeSubscribers',
            'disconnectedSubscribers'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:subscribers,username',
            'phone_number' => 'required|string|max:30',
            'address' => 'required|string|max:1000',
            'subscription_plan_id' => 'required|exists:subscription_plans,id',
            'status' => 'required|in:active,inactive,disconnected',
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $plan = SubscriptionPlan::findOrFail($request->subscription_plan_id);

        $profilePhotoPath = null;
        if ($request->hasFile('profile_photo')) {
            $profilePhotoPath = $request->file('profile_photo')->store('subscriber_photos', 'public');
        }

        Subscriber::create([
            'full_name' => $request->full_name,
            'username' => $request->username,
            'phone_number' => $request->phone_number,
            'address' => $request->address,
            'plan_name' => $plan->plan_name,
            'monthly_fee' => $plan->price,
            'status' => $request->status,
            'profile_photo' => $profilePhotoPath,
        ]);

        return back()->with('success', 'Subscriber account created successfully.');
    }

    public function update(Request $request, $id)
    {
        $subscriber = Subscriber::findOrFail($id);

        $request->validate([
            'full_name' => 'required|string|max:255',
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('subscribers', 'username')->ignore($subscriber->id),
            ],
            'phone_number' => 'required|string|max:30',
            'address' => 'required|string|max:1000',
            'subscription_plan_id' => 'required|exists:subscription_plans,id',
            'status' => 'required|in:active,inactive,disconnected',
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $plan = SubscriptionPlan::findOrFail($request->subscription_plan_id);

        $subscriber->full_name = $request->full_name;
        $subscriber->username = $request->username;
        $subscriber->phone_number = $request->phone_number;
        $subscriber->address = $request->address;

        // copied from selected plan
        $subscriber->plan_name = $plan->plan_name;
        $subscriber->monthly_fee = $plan->price;

        $subscriber->status = $request->status;

        if ($request->hasFile('profile_photo')) {
            if ($subscriber->profile_photo && Storage::disk('public')->exists($subscriber->profile_photo)) {
                Storage::disk('public')->delete($subscriber->profile_photo);
            }

            $subscriber->profile_photo = $request->file('profile_photo')->store('subscriber_photos', 'public');
        }

        $subscriber->save();

        return back()->with('success', 'Subscriber information updated successfully.');
    }

    public function destroy($id)
    {
        $subscriber = Subscriber::findOrFail($id);

        if ($subscriber->profile_photo && Storage::disk('public')->exists($subscriber->profile_photo)) {
            Storage::disk('public')->delete($subscriber->profile_photo);
        }

        $subscriber->delete();

        return back()->with('success', 'Subscriber deleted successfully.');
    }
}