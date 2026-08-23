<?php

namespace App\Http\Controllers;

use App\Models\BillingRecord;
use App\Models\Subscriber;
use App\Models\SystemSetting;
use Carbon\Carbon;

class SubscriberPortalController extends Controller
{
    public function index()
    {
        $subscriber = Subscriber::find(session('subscriber_id'));

        if (!$subscriber) {
            return redirect()
                ->route('login')
                ->with('error', 'Please sign in to access your portal.');
        }

        /*
        |--------------------------------------------------------------------------
        | Generate All Missing Monthly Billing Records
        |--------------------------------------------------------------------------
        |
        | Starting from the subscriber's account creation month, generate
        | every missing monthly billing record up to the current month.
        |
        | Existing records are NEVER replaced.
        |
        */

        $accountStart = Carbon::parse($subscriber->created_at)
            ->startOfMonth();

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

        /*
        |--------------------------------------------------------------------------
        | Update Past Unpaid Records To Overdue
        |--------------------------------------------------------------------------
        |
        | Only unpaid bills whose due date has already passed are changed
        | to overdue.
        |
        | Pending and paid records are not touched.
        |
        */

        BillingRecord::where('subscriber_id', $subscriber->id)
            ->where('status', 'unpaid')
            ->whereDate('due_date', '<', now()->toDateString())
            ->update([
                'status' => 'overdue',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Get All Billing Records
        |--------------------------------------------------------------------------
        */

        $billingRecords = BillingRecord::where(
                'subscriber_id',
                $subscriber->id
            )
            ->orderByDesc('billing_year')
            ->orderByDesc('billing_month')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Outstanding Bills
        |--------------------------------------------------------------------------
        |
        | Pending bills are included in the outstanding balance because
        | payment has not yet been approved.
        |
        */

        $outstandingBills = $billingRecords->filter(function ($bill) {
            return in_array($bill->status, [
                'unpaid',
                'overdue',
                'pending',
            ]);
        });

        /*
        |--------------------------------------------------------------------------
        | Payable Bills
        |--------------------------------------------------------------------------
        |
        | Only unpaid and overdue bills can receive a new payment
        | submission.
        |
        | Pending bills are already waiting for admin verification.
        |
        */

        $payableBills = $billingRecords->filter(function ($bill) {
            return in_array($bill->status, [
                'unpaid',
                'overdue',
            ]);
        });

        /*
        |--------------------------------------------------------------------------
        | Outstanding Balance
        |--------------------------------------------------------------------------
        */

        $outstandingBalance = $outstandingBills->sum(function ($bill) {
            return (float) $bill->amount;
        });

        /*
        |--------------------------------------------------------------------------
        | Payable Balance
        |--------------------------------------------------------------------------
        |
        | This is useful for showing how much can currently be submitted.
        |
        */

        $payableBalance = $payableBills->sum(function ($bill) {
            return (float) $bill->amount;
        });

        /*
        |--------------------------------------------------------------------------
        | Current Month Bill
        |--------------------------------------------------------------------------
        */

        $currentBill = $billingRecords->first(function ($bill) use ($currentMonth) {
            return (int) $bill->billing_year === (int) $currentMonth->year
                && (int) $bill->billing_month === (int) $currentMonth->month;
        });

        /*
        |--------------------------------------------------------------------------
        | GCash Settings
        |--------------------------------------------------------------------------
        */

        $settings = SystemSetting::first();

        return view('subscriber.portal', compact(
            'subscriber',
            'settings',
            'billingRecords',
            'outstandingBills',
            'payableBills',
            'outstandingBalance',
            'payableBalance',
            'currentBill'
        ));
    }
}