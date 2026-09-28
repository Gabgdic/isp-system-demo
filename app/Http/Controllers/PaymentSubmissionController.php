<?php

namespace App\Http\Controllers;

use App\Models\BillingRecord;
use App\Models\PaymentSubmission;
use App\Models\PaymentSubmissionBilling;
use App\Models\Subscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentSubmissionController extends Controller
{
    public function store(Request $request)
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

        // Validate Request
        $request->validate([
            'amount_paid' => [
                'required',
                'numeric',
                'min:0.01',
            ],
            'reference_number' => [
                'required',
                'string',
                'max:255',
            ],
            'payment_screenshot' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Get Payable Bills
        |--------------------------------------------------------------------------
        | Only unpaid and overdue bills can be paid.
        | Pending bills already have a payment under review.
        */

        $payableBills = BillingRecord::where(
                'subscriber_id',
                $subscriber->id
            )
            ->whereIn('status', [
                'unpaid',
                'overdue',
            ])
            ->orderBy('billing_year')
            ->orderBy('billing_month')
            ->get();

        if ($payableBills->isEmpty()) {
            return back()->with(
                'error',
                'You currently have no outstanding balance to pay.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Prevent Duplicate Payment
        |--------------------------------------------------------------------------
        | Only one payment can be waiting for review at a time.
        */

        $pendingPayment = PaymentSubmission::where(
                'subscriber_id',
                $subscriber->id
            )
            ->where('status', 'pending')
            ->exists();

        if ($pendingPayment) {
            return back()->with(
                'error',
                'You already have a payment waiting for administrator verification.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Calculate Outstanding Balance
        |--------------------------------------------------------------------------
        */

        $outstandingBalance = $payableBills->sum(
            function ($bill) {
                return (float) $bill->amount;
            }
        );

        $amountPaid = (float) $request->amount_paid;

        /*
        |--------------------------------------------------------------------------
        | Require Full Outstanding Balance
        |--------------------------------------------------------------------------
        */

        if (
            round($amountPaid, 2) !==
            round($outstandingBalance, 2)
        ) {
            return back()->with(
                'error',
                'You must pay the full outstanding balance of ₱' .
                number_format($outstandingBalance, 2) .
                '.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Store Payment Screenshot
        |--------------------------------------------------------------------------
        */

        $screenshotPath = $request
            ->file('payment_screenshot')
            ->store(
                'payment_screenshots',
                'public'
            );

        /*
        |--------------------------------------------------------------------------
        | Create Payment Submission
        |--------------------------------------------------------------------------
        */

        DB::transaction(function () use (
            $subscriber,
            $payableBills,
            $amountPaid,
            $request,
            $screenshotPath
        ) {
            $payment = PaymentSubmission::create([
                // New payments use the allocation table.
                'billing_record_id' => null,
                'subscriber_id' => $subscriber->id,
                'amount_paid' => $amountPaid,
                'reference_number' =>
                    $request->reference_number,
                'payment_screenshot' =>
                    $screenshotPath,
                'status' => 'pending',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Attach All Outstanding Bills
            |--------------------------------------------------------------------------
            */

            foreach ($payableBills as $bill) {
                PaymentSubmissionBilling::create([
                    'payment_submission_id' =>
                        $payment->id,
                    'billing_record_id' =>
                        $bill->id,
                    'amount_applied' =>
                        $bill->amount,
                ]);

                /*
                Mark these bills as pending while
                the admin reviews the payment.
                */

                $bill->update([
                    'status' => 'pending',
                ]);
            }
        });

        return back()->with(
            'success',
            'Your payment for the full outstanding balance has been submitted and is waiting for administrator verification.'
        );
    }
}