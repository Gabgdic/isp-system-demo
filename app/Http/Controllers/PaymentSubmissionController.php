<?php

namespace App\Http\Controllers;

use App\Models\BillingRecord;
use App\Models\PaymentSubmission;
use App\Models\Subscriber;
use Illuminate\Http\Request;

class PaymentSubmissionController extends Controller
{
    public function store(Request $request)
    {
        $subscriber = Subscriber::find(session('subscriber_id'));

        if (!$subscriber) {
            return redirect()
                ->route('login')
                ->with('error', 'Please sign in to access your portal.');
        }

        /*
        |--------------------------------------------------------------------------
        | Validate Request
        |--------------------------------------------------------------------------
        */

        $request->validate([
            'billing_record_id' => [
                'required',
                'integer',
                'exists:billing_records,id',
            ],

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
        | Get Billing Record
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        | The billing record must belong to the logged-in subscriber.
        |
        */

        $bill = BillingRecord::where('id', $request->billing_record_id)
            ->where('subscriber_id', $subscriber->id)
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Already Paid
        |--------------------------------------------------------------------------
        */

        if ($bill->status === 'paid') {
            return back()->with(
                'error',
                'This billing month has already been paid.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Pending Submission
        |--------------------------------------------------------------------------
        |
        | Prevent multiple submissions for the same billing month while
        | an existing payment is waiting for admin review.
        |
        */

        $pendingSubmission = PaymentSubmission::where(
                'billing_record_id',
                $bill->id
            )
            ->where('subscriber_id', $subscriber->id)
            ->where('status', 'pending')
            ->exists();

        if ($pendingSubmission) {
            return back()->with(
                'error',
                'You already have a payment submission waiting for review for this billing month.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Validate Payment Amount
        |--------------------------------------------------------------------------
        |
        | The payment must cover this billing record.
        |
        */

        if ((float) $request->amount_paid < (float) $bill->amount) {
            return back()->with(
                'error',
                'The amount paid must be at least ₱' .
                number_format($bill->amount, 2) .
                ' for this billing month.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Store Payment Screenshot
        |--------------------------------------------------------------------------
        */

        $screenshotPath = $request->file('payment_screenshot')
            ->store('payment_screenshots', 'public');

        /*
        |--------------------------------------------------------------------------
        | Create Payment Submission
        |--------------------------------------------------------------------------
        */

        PaymentSubmission::create([
            'billing_record_id' => $bill->id,
            'subscriber_id' => $subscriber->id,
            'amount_paid' => $request->amount_paid,
            'reference_number' => $request->reference_number,
            'payment_screenshot' => $screenshotPath,
            'status' => 'pending',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Mark Only This Billing Month As Pending
        |--------------------------------------------------------------------------
        */

        $bill->update([
            'status' => 'pending',
        ]);

        return back()->with(
            'success',
            'Your payment for this billing month has been submitted and is waiting for admin verification.'
        );
    }
}