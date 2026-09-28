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
}