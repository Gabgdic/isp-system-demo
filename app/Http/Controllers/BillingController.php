<?php

namespace App\Http\Controllers;

use App\Models\BillingRecord;
use App\Models\PaymentSubmission;
use App\Models\SubscriptionPlan;
use App\Models\Subscriber;
use App\Models\SystemSetting;
use App\Services\PhilSmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BillingController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string|max:255',
            'status' => ['nullable', Rule::in(['paid', 'unpaid', 'overdue'])],
            'billing_period' => ['nullable', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
        ]);

        // Billing Records
        $search = trim($request->input('search', ''));
        $status = $request->input('status');
        $billingPeriod = $request->input('billing_period');
        $billingMonths = BillingRecord::query()
            ->select('billing_year', 'billing_month')
            ->distinct()
            ->orderByDesc('billing_year')
            ->orderByDesc('billing_month')
            ->get()
            ->mapWithKeys(fn (BillingRecord $record) => [
                sprintf('%04d-%02d', $record->billing_year, $record->billing_month) => \Carbon\Carbon::create(
                    $record->billing_year,
                    $record->billing_month,
                    1
                )->format('M Y'),
            ]);

        $billingRecordsQuery = BillingRecord::with('subscriber');

        if ($status) {
            $billingRecordsQuery->where('status', $status);
        }

        if ($billingPeriod) {
            [$billingYear, $billingMonth] = explode('-', $billingPeriod);
            $billingRecordsQuery
                ->where('billing_year', (int) $billingYear)
                ->where('billing_month', (int) $billingMonth);
        }

        if ($search !== '') {
            $billingRecordsQuery->whereHas('subscriber', function ($subscriberQuery) use ($search) {
                $subscriberQuery->where('full_name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            });
        }

        $billingRecords = $billingRecordsQuery
            ->orderByDesc('billing_year')
            ->orderByDesc('billing_month')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // Return partial view for AJAX pagination requests
        if ($request->ajax()) {
            return view('admin.partials.billing_records_table', compact('billingRecords', 'search'))->render();
        }

        // Pending Payment Submissions
        $pendingPayments = PaymentSubmission::with([
            'subscriber',
            'billingRecord',
            'billingAllocations.billingRecord',
        ])
            ->where('status', 'pending')
            ->latest()
            ->get();

        $manualPaymentSubscribers = Subscriber::query()
            ->whereHas('billingRecords', function ($query) {
                $query->whereIn('status', ['unpaid', 'overdue']);
            })
            ->with(['billingRecords' => function ($query) {
                $query->whereIn('status', ['unpaid', 'overdue']);
            }])
            ->orderBy('full_name')
            ->get();

        // Subscription Plans
        $plans = SubscriptionPlan::latest()->get();

        return view('admin.billing', compact(
            'pendingPayments',
            'billingRecords',
            'manualPaymentSubscribers',
            'plans',
            'search',
            'status',
            'billingPeriod',
            'billingMonths'
        ));
    }

    // Statement of Account Preview (AJAX Modal Content)
    public function soaPreview($subscriberId)
    {
        $subscriber = Subscriber::findOrFail($subscriberId);

        $unpaidBills = BillingRecord::where('subscriber_id', $subscriberId)
            ->whereIn('status', ['unpaid', 'overdue', 'pending'])
            ->orderByDesc('billing_year')
            ->orderByDesc('billing_month')
            ->get();

        $totalOutstanding = $unpaidBills->sum('amount');
        $systemSettings = SystemSetting::first();

        return view('admin.partials.soa_content', compact(
            'subscriber',
            'unpaidBills',
            'totalOutstanding',
            'systemSettings'
        ))->render();
    }

    // Statement of Account Printable/Downloadable View
    public function soaDownload($subscriberId)
    {
        $subscriber = Subscriber::findOrFail($subscriberId);

        $unpaidBills = BillingRecord::where('subscriber_id', $subscriberId)
            ->whereIn('status', ['unpaid', 'overdue', 'pending'])
            ->orderByDesc('billing_year')
            ->orderByDesc('billing_month')
            ->get();

        $totalOutstanding = $unpaidBills->sum('amount');
        $systemSettings = SystemSetting::first();

        return view('admin.partials.soa_content', compact(
            'subscriber',
            'unpaidBills',
            'totalOutstanding',
            'systemSettings'
        ));
    }

    public function previewReceipt(BillingRecord $billingRecord)
    {
        // Load relations matching the BillingRecord model definition
        $billingRecord->load([
            'subscriber',
            'paymentAllocations.paymentSubmission'
        ]);

        return view('admin.partials.receipt_preview', compact('billingRecord'));
    }

    public function previewPaymentReceipt(PaymentSubmission $paymentSubmission)
    {
        abort_unless($paymentSubmission->status === 'approved', 404);

        $paymentSubmission->load([
            'subscriber',
            'billingRecord',
            'billingAllocations.billingRecord',
        ]);

        $receiptItems = $paymentSubmission->billingAllocations
            ->filter(fn ($allocation) => $allocation->billingRecord?->status === 'paid')
            ->map(fn ($allocation) => (object) [
                'bill' => $allocation->billingRecord,
                'amount' => (float) $allocation->amount_applied,
            ])
            ->values();

        if ($receiptItems->isEmpty() && $paymentSubmission->billingRecord?->status === 'paid') {
            $receiptItems->push((object) [
                'bill' => $paymentSubmission->billingRecord,
                'amount' => (float) $paymentSubmission->billingRecord->amount,
            ]);
        }

        abort_if($receiptItems->isEmpty(), 404);

        $billingRecord = $receiptItems->first()->bill;
        $systemSettings = SystemSetting::first();

        return view('admin.partials.receipt_preview', compact(
            'billingRecord',
            'paymentSubmission',
            'receiptItems',
            'systemSettings'
        ));
    }

    public function recordManualPayments(Request $request, PhilSmsService $smsService)
    {
        $validated = $request->validate([
            'subscriber_ids' => 'required|array|min:1',
            'subscriber_ids.*' => 'required|integer|distinct|exists:subscribers,id',
        ]);

        $payments = DB::transaction(function () use ($validated) {
            $billsBySubscriber = BillingRecord::query()
                ->whereIn('subscriber_id', $validated['subscriber_ids'])
                ->whereIn('status', ['unpaid', 'overdue'])
                ->orderBy('subscriber_id')
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->groupBy('subscriber_id');

            foreach ($validated['subscriber_ids'] as $subscriberId) {
                if (!isset($billsBySubscriber[$subscriberId])) {
                    throw ValidationException::withMessages([
                        'subscriber_ids' => 'One or more selected subscribers no longer have an outstanding balance. Refresh and try again.',
                    ]);
                }
            }

            $payments = [];
            foreach ($billsBySubscriber as $subscriberId => $bills) {
                $paidAt = now();
                $amountPaid = $bills->sum(fn ($bill) => (float) $bill->amount);
                $payment = PaymentSubmission::create([
                    'billing_record_id' => null,
                    'subscriber_id' => $subscriberId,
                    'amount_paid' => $amountPaid,
                    'reference_number' => 'ADMIN-' . $subscriberId . '-' . Str::upper(Str::random(8)),
                    'payment_screenshot' => null,
                    'status' => 'approved',
                    'admin_note' => 'Outstanding balance manually recorded by admin.',
                    'reviewed_at' => $paidAt,
                ]);
                $payments[] = $payment;

                foreach ($bills as $bill) {
                    $bill->update([
                        'status' => 'paid',
                        'paid_at' => $paidAt,
                    ]);

                    $payment->billingAllocations()->create([
                        'billing_record_id' => $bill->id,
                        'amount_applied' => $bill->amount,
                    ]);
                }

                Subscriber::findOrFail($subscriberId)->syncOverdueStatus();
            }

            return $payments;
        });

        foreach ($payments as $payment) {
            $smsService->sendPaymentApproved($payment);
        }

        return back()->with('success', 'Manual payments recorded for ' . count($payments) . ' subscriber(s).');
    }

    // Approve Payment
    public function approve($id, PhilSmsService $smsService)
    {
        $payment = PaymentSubmission::with([
            'billingRecord',
            'billingAllocations.billingRecord',
            'subscriber',
        ])->findOrFail($id);

        if ($payment->status !== 'pending') {
            return back()->with('error', 'This payment has already been reviewed.');
        }

        DB::transaction(function () use ($payment) {
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

            if ($payment->billingRecord) {
                $payment->billingRecord->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);
            }

            $payment->update([
                'status' => 'approved',
                'reviewed_at' => now(),
            ]);

            // Re-evaluate subscriber status immediately
            if ($payment->subscriber) {
                $payment->subscriber->syncOverdueStatus();
            }
        });

        $smsService->sendPaymentApproved($payment);

        return back()->with('success', 'Payment approved successfully.');
    }

    // Reject Payment
    public function reject(Request $request, $id, PhilSmsService $smsService)
    {
        $request->validate([
            'reject_reason' => [
                'required',
                Rule::in([
                    'reference_mismatch',
                    'blurry_image',
                    'payment_not_received',
                    'other',
                ]),
            ],
            'other_reason' => 'required_if:reject_reason,other|nullable|string|max:1000',
        ]);

        $adminNote = match ($request->reject_reason) {
            'reference_mismatch' => 'Reference number does not match.',
            'blurry_image' => 'Image is blurry or unreadable.',
            'payment_not_received' => 'Payment was not received.',
            'other' => 'Other: ' . trim($request->other_reason),
        };

        $payment = PaymentSubmission::with([
            'billingRecord',
            'billingAllocations.billingRecord',
            'subscriber',
        ])->findOrFail($id);

        if ($payment->status !== 'pending') {
            return back()->with('error', 'This payment has already been reviewed.');
        }

        DB::transaction(function () use ($payment, $adminNote) {
            $resetBillStatus = function ($bill) {
                if (!$bill) return;

                $status = 'unpaid';
                $dueDate = $bill->due_date ? \Carbon\Carbon::parse($bill->due_date) : null;
                
                if ($dueDate && $dueDate->isPast()) {
                    $status = 'overdue';
                }

                $bill->update([
                    'status' => $status,
                    'paid_at' => null,
                ]);
            };

            if ($payment->billingAllocations->isNotEmpty()) {
                foreach ($payment->billingAllocations as $allocation) {
                    $resetBillStatus($allocation->billingRecord);
                }
            }

            if ($payment->billingRecord) {
                $resetBillStatus($payment->billingRecord);
            }

            $payment->update([
                'status' => 'rejected',
                'admin_note' => $adminNote,
                'reviewed_at' => now(),
            ]);

            // Re-evaluate subscriber status immediately
            if ($payment->subscriber) {
                $payment->subscriber->syncOverdueStatus();
            }
        });

        $smsService->sendPaymentRejected($payment);

        return back()->with('success', 'Payment rejected.');
    }

    // Subscription Plan Management
    public function storePlan(Request $request)
    {
        $request->validate([
            'plan_name' => 'required|string|max:255|unique:subscription_plans,plan_name',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
        ]);

        SubscriptionPlan::create([
            'plan_name' => $request->plan_name,
            'price' => $request->price,
            'description' => $request->description,
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
        ]);

        $plan->update([
            'plan_name' => $request->plan_name,
            'price' => $request->price,
            'description' => $request->description,
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