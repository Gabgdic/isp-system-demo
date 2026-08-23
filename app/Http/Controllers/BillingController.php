<?php

namespace App\Http\Controllers;

use App\Models\BillingRecord;
use App\Models\PaymentSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillingController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Billing Dashboard
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | Pending Payment Submissions
        |--------------------------------------------------------------------------
        */

        $pendingPayments = PaymentSubmission::with([
            'subscriber',
            'billingRecord',
        ])
            ->where('status', 'pending')
            ->latest()
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Billing Records
        |--------------------------------------------------------------------------
        */

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

    /*
    |--------------------------------------------------------------------------
    | Approve Payment
    |--------------------------------------------------------------------------
    */

    public function approve($id)
    {
        $payment = PaymentSubmission::with([
            'billingRecord',
            'subscriber',
        ])->findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | Make Sure Payment Is Still Pending
        |--------------------------------------------------------------------------
        */

        if ($payment->status !== 'pending') {
            return back()->with(
                'error',
                'This payment has already been reviewed.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Make Sure Billing Record Exists
        |--------------------------------------------------------------------------
        */

        if (!$payment->billingRecord) {
            return back()->with(
                'error',
                'The billing record associated with this payment could not be found.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Prevent Approving A Paid Bill
        |--------------------------------------------------------------------------
        */

        if ($payment->billingRecord->status === 'paid') {

            $payment->update([
                'status' => 'approved',
                'reviewed_at' => now(),
            ]);

            return back()->with(
                'error',
                'This billing month has already been marked as paid.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Approve Payment
        |--------------------------------------------------------------------------
        */

        DB::transaction(function () use ($payment) {

            $payment->update([
                'status' => 'approved',
                'reviewed_at' => now(),
            ]);

            /*
            |--------------------------------------------------------------
            | Only the corresponding billing month becomes paid.
            |--------------------------------------------------------------
            */

            $payment->billingRecord->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);
        });

        return back()->with(
            'success',
            'Payment approved successfully. The corresponding billing month has been marked as paid.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Reject Payment
    |--------------------------------------------------------------------------
    */

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
            'subscriber',
        ])->findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | Make Sure Payment Is Still Pending
        |--------------------------------------------------------------------------
        */

        if ($payment->status !== 'pending') {
            return back()->with(
                'error',
                'This payment has already been reviewed.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Make Sure Billing Record Exists
        |--------------------------------------------------------------------------
        */

        if (!$payment->billingRecord) {
            return back()->with(
                'error',
                'The billing record associated with this payment could not be found.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Reject Payment
        |--------------------------------------------------------------------------
        */

        DB::transaction(function () use ($payment, $request) {

            $payment->update([
                'status' => 'rejected',
                'admin_note' => $request->admin_note,
                'reviewed_at' => now(),
            ]);

            /*
            |--------------------------------------------------------------
            | Return only this billing month to unpaid/overdue.
            |--------------------------------------------------------------
            */

            $status = 'unpaid';

            if (
                $payment->billingRecord->due_date &&
                $payment->billingRecord->due_date->isPast()
            ) {
                $status = 'overdue';
            }

            $payment->billingRecord->update([
                'status' => $status,
                'paid_at' => null,
            ]);
        });

        return back()->with(
            'success',
            'Payment rejected. The billing month remains outstanding.'
        );
    }
}