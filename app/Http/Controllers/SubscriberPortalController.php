<?php

namespace App\Http\Controllers;

use App\Models\BillingRecord;
use App\Models\Subscriber;
use App\Models\SystemSetting;
use App\Models\PaymentSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class SubscriberPortalController extends Controller
{
    public function index()
    {
        $subscriber = Subscriber::find(session('subscriber_id'));

        if (!$subscriber) {
            return redirect()->route('login')->with('error', 'Please sign in to access your portal.');
        }

        // Automatically update overdue bills and evaluate subscriber status
        $subscriber->updateStatusBasedOnBills();

        // Fetch billing records with payment allocations and submissions for receipt/rejection details
        $billingRecords = BillingRecord::with(['paymentAllocations.paymentSubmission'])
            ->where('subscriber_id', $subscriber->id)
            ->orderByDesc('billing_year')
            ->orderByDesc('billing_month')
            ->get();

        // Fetch recently accepted payments (approved in the last 7 days)
        $recentlyAcceptedPayments = PaymentSubmission::where('subscriber_id', $subscriber->id)
            ->where('status', 'approved')
            ->where(function ($query) {
                $query->where('reviewed_at', '>=', now()->subDays(7))
                      ->orWhereNull('reviewed_at');
            })
            ->latest('updated_at')
            ->get();

        // Fetch rejected payments, but hide them if a newer payment was approved
        $latestApprovedDate = PaymentSubmission::where('subscriber_id', $subscriber->id)
            ->where('status', 'approved')
            ->max('updated_at');

        $rejectedPaymentsQuery = PaymentSubmission::where('subscriber_id', $subscriber->id)
            ->where('status', 'rejected');

        if ($latestApprovedDate) {
            $rejectedPaymentsQuery->where('updated_at', '>', $latestApprovedDate);
        }

        $rejectedPayments = $rejectedPaymentsQuery->latest('reviewed_at')->get();

        $payableBills = $billingRecords->filter(fn($bill) => in_array($bill->status, ['unpaid', 'overdue']));
        $pendingBills = $billingRecords->filter(fn($bill) => $bill->status === 'pending');
        $outstandingBalance = $payableBills->sum(fn($bill) => (float) $bill->amount);
        $soaAvailable = $payableBills->contains(fn ($bill) =>
            $bill->due_date && $bill->due_date->lte(today()->addDays(5))
        );
        
        $currentBill = $billingRecords->first(function ($bill) {
            return (int) $bill->billing_year === (int) now()->year && (int) $bill->billing_month === (int) now()->month;
        });

        $settings = SystemSetting::first();

        return view('subscriber.portal', compact(
            'subscriber',
            'settings',
            'billingRecords',
            'rejectedPayments',
            'recentlyAcceptedPayments',
            'payableBills',
            'pendingBills',
            'outstandingBalance',
            'currentBill',
            'soaAvailable'
        ));
    }

    /**
     * Update Subscriber Profile (Full Name & Photo - Max 4MB)
     */
    public function updateProfile(Request $request)
    {
        $subscriber = Subscriber::findOrFail(session('subscriber_id'));

        $request->validate([
            'full_name'        => 'required|string|max:255',
            'profile_photo'    => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'current_password' => 'nullable|required_with:new_password',
            'new_password'     => 'nullable|min:6|confirmed',
        ], [
            'profile_photo.max'          => 'The profile picture must not exceed 4MB in size.',
            'profile_photo.image'        => 'The profile picture must be a valid image file.',
            'current_password.required_with' => 'Please enter your current password to set a new password.',
            'new_password.confirmed'     => 'New password confirmation does not match.',
            'new_password.min'           => 'New password must be at least 6 characters.',
        ]);

        // Check current password if updating password
        if ($request->filled('new_password')) {
            if (!Hash::check($request->current_password, $subscriber->password)) {
                return redirect()->back()->withErrors(['current_password' => 'Current password does not match our records.']);
            }
            $subscriber->password = Hash::make($request->new_password);
        }

        $subscriber->full_name = $request->full_name;

        if ($request->hasFile('profile_photo')) {
            if ($subscriber->profile_photo && Storage::disk('public')->exists($subscriber->profile_photo)) {
                Storage::disk('public')->delete($subscriber->profile_photo);
            }

            $path = $request->file('profile_photo')->store('subscribers', 'public');
            $subscriber->profile_photo = $path;
        }

        $subscriber->save();

        return redirect()->back()->with('success', 'Profile updated successfully.');
    }

    /**
     * View Statement of Account (SOA) for logged in subscriber
     */
    public function generateSOA()
    {
        $subscriberId = session('subscriber_id');

        $subscriber = Subscriber::with([
            'billingRecords' => function ($q) {
                $q->orderBy('billing_year', 'desc')
                  ->orderBy('billing_month', 'desc');
            },
            'paymentSubmissions' => function ($q) {
                $q->where('status', 'approved')
                  ->latest('reviewed_at');
            }
        ])->findOrFail($subscriberId);

        $unpaidBills = $subscriber->billingRecords
            ->whereIn('status', ['unpaid', 'overdue', 'pending']);

        $soaAvailable = $unpaidBills
            ->whereIn('status', ['unpaid', 'overdue'])
            ->contains(fn ($bill) => $bill->due_date && $bill->due_date->lte(today()->addDays(5)));

        if (!$soaAvailable) {
            return redirect()->route('subscriber.portal')
                ->with('error', 'Your statement will be available when an unpaid bill is due within five days.');
        }
        
        $totalOutstanding = $unpaidBills->sum('amount');
        $systemSettings = SystemSetting::first();

        return view('reports.soa', compact('subscriber', 'unpaidBills', 'totalOutstanding', 'systemSettings'));
    }
}