<?php

namespace App\Http\Controllers;

use App\Models\BillingRecord;
use App\Models\PaymentSubmission;
use App\Models\SubscriptionPlan;
use App\Models\Subscriber;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillingController extends Controller
{
    public function index(Request $request)
    {
        // Billing Records
        $billingRecords = BillingRecord::with('subscriber')
            ->orderByDesc('billing_year')
            ->orderByDesc('billing_month')
            ->latest()
            ->paginate(10);

        // Return partial view for AJAX pagination requests
        if ($request->ajax()) {
            return view('admin.partials.billing_records_table', compact('billingRecords'))->render();
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

        // Subscription Plans
        $plans = SubscriptionPlan::latest()->get();

        return view('admin.billing', compact(
            'pendingPayments',
            'billingRecords',
            'plans'
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

        // Point to resources/views/reports/soa.blade.php
        return view('reports.soa', compact(
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

    // Approve Payment
    public function approve($id)
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
        });

        return back()->with('success', 'Payment approved successfully.');
    }

    // Reject Payment
    public function reject(Request $request, $id)
    {
        $request->validate([
            'admin_note' => 'nullable|string|max:1000',
        ]);

        $payment = PaymentSubmission::with([
            'billingRecord',
            'billingAllocations.billingRecord',
            'subscriber',
        ])->findOrFail($id);

        if ($payment->status !== 'pending') {
            return back()->with('error', 'This payment has already been reviewed.');
        }

        DB::transaction(function () use ($payment, $request) {
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
                'admin_note' => $request->admin_note,
                'reviewed_at' => now(),
            ]);
        });

        return back()->with('success', 'Payment rejected.');
    }

    // Subscription Plan Management
    public function storePlan(Request $request)
    {
        $request->validate([
            'plan_name' => 'required|string|max:255|unique:subscription_plans,plan_name',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
        ]);

        SubscriptionPlan::create([
            'plan_name' => $request->plan_name,
            'price' => $request->price,
            'description' => $request->description,
            'status' => $request->status,
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
            'status' => 'required|in:active,inactive',
        ]);

        $plan->update([
            'plan_name' => $request->plan_name,
            'price' => $request->price,
            'description' => $request->description,
            'status' => $request->status,
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