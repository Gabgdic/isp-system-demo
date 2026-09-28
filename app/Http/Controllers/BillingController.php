<?php

namespace App\Http\Controllers;

use App\Models\BillingRecord;
use App\Models\PaymentSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillingController extends Controller
{
    public function index()
    {
        // Pending Payment Submissions
        $pendingPayments = PaymentSubmission::with([
            'subscriber',
            'billingRecord',
            'billingAllocations.billingRecord',
        ])
            ->where('status', 'pending')
            ->latest()
            ->get();

        // Billing Records
        $billingRecords = BillingRecord::with('subscriber')
            ->orderByDesc('billing_year')
            ->orderByDesc('billing_month')
            ->latest()
            ->paginate(10);

        return view('admin.billing', compact(
            'pendingPayments',
            'billingRecords'
        ));
    }

    // Approve Payment
    public function approve($id)
    {
        $payment = PaymentSubmission::with([
            'billingRecord',
            'billingAllocations.billingRecord',
            'subscriber',
        ])->findOrFail($id);

        // Make sure payment is still pending
        if ($payment->status !== 'pending') {
            return back()->with(
                'error',
                'This payment has already been reviewed.'
            );
        }

        DB::transaction(function () use ($payment) {

            /*
            |--------------------------------------------------------------------------
            | 1. Multi-Bill Allocations Update
            |--------------------------------------------------------------------------
            */
            if ($payment->billingAllocations->isNotEmpty()) {
                foreach ($payment->billingAllocations as $allocation) {
                    if ($allocation->billingRecord) {
                        $allocation->billingRecord->update([
                            'status' => 'paid',
                            'paid_at' => now(),
                        ]);
                    }
                }
            }

            /*
            |--------------------------------------------------------------------------
            | 2. Single-Bill Legacy Update
            |--------------------------------------------------------------------------
            */
            if ($payment->billingRecord) {
                $payment->billingRecord->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | 3. Update Payment Status
            |--------------------------------------------------------------------------
            */
            $payment->update([
                'status' => 'approved',
                'reviewed_at' => now(),
            ]);
        });

        return back()->with(
            'success',
            'Payment approved successfully. All billing records included in the payment have been marked as paid.'
        );
    }

    // Reject Payment
    public function reject(Request $request, $id)
    {
        $request->validate([
            'admin_note' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        $payment = PaymentSubmission::with([
            'billingRecord',
            'billingAllocations.billingRecord',
            'subscriber',
        ])->findOrFail($id);

        // Make sure payment is still pending
        if ($payment->status !== 'pending') {
            return back()->with(
                'error',
                'This payment has already been reviewed.'
            );
        }

        DB::transaction(function () use ($payment, $request) {

            $resetBillStatus = function ($bill) {
                if (!$bill) return;

                $status = 'unpaid';
                if ($bill->due_date && $bill->due_date->isPast()) {
                    $status = 'overdue';
                }

                $bill->update([
                    'status' => $status,
                    'paid_at' => null,
                ]);
            };

            /*
            |--------------------------------------------------------------------------
            | 1. Multi-Bill Allocations Revert
            |--------------------------------------------------------------------------
            */
            if ($payment->billingAllocations->isNotEmpty()) {
                foreach ($payment->billingAllocations as $allocation) {
                    $resetBillStatus($allocation->billingRecord);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | 2. Single-Bill Legacy Revert
            |--------------------------------------------------------------------------
            */
            if ($payment->billingRecord) {
                $resetBillStatus($payment->billingRecord);
            }

            /*
            |--------------------------------------------------------------------------
            | 3. Update Payment Status
            |--------------------------------------------------------------------------
            */
            $payment->update([
                'status' => 'rejected',
                'admin_note' => $request->admin_note,
                'reviewed_at' => now(),
            ]);
        });

        return back()->with(
            'success',
            'Payment rejected. All billing records included in the payment remain outstanding.'
        );
    }
}