<?php

namespace App\Http\Controllers;

use App\Models\BillingRecord;
use App\Models\Subscriber;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SubscriberPortalController extends Controller
{
    public function index()
    {
        $subscriber = Subscriber::find(
            session('subscriber_id')
        );

        if (!$subscriber) {
            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'Please sign in to access your portal.'
                );
        }

        // Generate all missing monthly billing records.
        $accountStart = Carbon::parse(
            $subscriber->created_at
        )->startOfMonth();

        $currentMonth = now()->startOfMonth();
        $month = $accountStart->copy();

        while ($month->lte($currentMonth)) {
            BillingRecord::firstOrCreate(
                [
                    'subscriber_id' => $subscriber->id,
                    'billing_month' => $month->month,
                    'billing_year' => $month->year,
                ],
                [
                    'amount' => $subscriber->monthly_fee,
                    'due_date' => $month->copy()->endOfMonth(),
                    'status' => 'unpaid',
                    'paid_at' => null,
                ]
            );

            $month->addMonth();
        }

        // Update old unpaid bills to overdue.
        BillingRecord::where(
                'subscriber_id',
                $subscriber->id
            )
            ->where('status', 'unpaid')
            ->whereDate(
                'due_date',
                '<',
                now()->toDateString()
            )
            ->update([
                'status' => 'overdue',
            ]);

        // Get all billing records.
        $billingRecords = BillingRecord::where(
                'subscriber_id',
                $subscriber->id
            )
            ->orderByDesc('billing_year')
            ->orderByDesc('billing_month')
            ->get();

        // Bills that can currently be paid.
        $payableBills = $billingRecords->filter(
            function ($bill) {
                return in_array(
                    $bill->status,
                    [
                        'unpaid',
                        'overdue',
                    ]
                );
            }
        );

        // Bills with a payment under review.
        $pendingBills = $billingRecords->filter(
            function ($bill) {
                return $bill->status === 'pending';
            }
        );

        // Full payable outstanding balance.
        $outstandingBalance = $payableBills->sum(
            function ($bill) {
                return (float) $bill->amount;
            }
        );

        // Current month bill.
        $currentBill = $billingRecords->first(
            function ($bill) use ($currentMonth) {
                return
                    (int) $bill->billing_year ===
                        (int) $currentMonth->year
                    &&
                    (int) $bill->billing_month ===
                        (int) $currentMonth->month;
            }
        );

        // GCash settings.
        $settings = SystemSetting::first();

        return view(
            'subscriber.portal',
            compact(
                'subscriber',
                'settings',
                'billingRecords',
                'payableBills',
                'pendingBills',
                'outstandingBalance',
                'currentBill'
            )
        );
    }

    /**
     * Update Subscriber Profile (Full Name & Photo - Max 4MB)
     */
    public function updateProfile(Request $request)
    {
        $subscriber = Subscriber::findOrFail(session('subscriber_id'));

        $request->validate([
            'full_name' => 'required|string|max:255',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096', // 4MB Limit
        ], [
            'profile_photo.max' => 'The profile picture must not exceed 4MB in size.',
            'profile_photo.image' => 'The profile picture must be a valid image file.',
        ]);

        $subscriber->full_name = $request->full_name;

        if ($request->hasFile('profile_photo')) {
            // Delete old photo if exists
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
        
        $totalOutstanding = $unpaidBills->sum('amount');
        $systemSettings = SystemSetting::first();

        return view('reports.soa', compact('subscriber', 'unpaidBills', 'totalOutstanding', 'systemSettings'));
    }
}