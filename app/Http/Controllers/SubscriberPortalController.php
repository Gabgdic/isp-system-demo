<?php

namespace App\Http\Controllers;

use App\Models\BillingRecord;
use App\Models\Subscriber;
use App\Models\SystemSetting;
use App\Models\PaymentSubmission;
use App\Models\SubscriberOfficeVisitRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class SubscriberPortalController extends Controller
{
    public function index()
    {
        $subscriber = Subscriber::with('disconnections')->find(session('subscriber_id'));

        if (!$subscriber) {
            return redirect()->route('login')->with('error', 'Please sign in to access your portal.');
        }

        // Fetch billing records with payment allocations and submissions for receipt/rejection details
        $billingRecords = BillingRecord::with(['paymentAllocations.paymentSubmission'])
            ->where('subscriber_id', $subscriber->id)
            ->orderByDesc('billing_year')
            ->orderByDesc('billing_month')
            ->get();

        foreach ($billingRecords as $bill) {
            if ($bill->due_date) {
                continue;
            }

            $dueMonth = \Carbon\Carbon::create($bill->billing_year, $bill->billing_month, 1)
                ->addMonth();
            $bill->due_date = $dueMonth
                ->day(min($subscriber->created_at->day, $dueMonth->daysInMonth))
                ->startOfDay();
            /** @var BillingRecord $bill */
            $bill->save();
        }

        // Automatically update overdue bills and evaluate subscriber status
        $subscriber->updateStatusBasedOnBills();

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

        $officeVisitRequests = $subscriber->officeVisitRequests()
            ->latest()
            ->get();
        $pendingRequestTypes = $officeVisitRequests
            ->where('status', 'pending')
            ->pluck('request_type')
            ->unique()
            ->values()
            ->all();

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
        $monthsInService = max(0, (int) $subscriber->created_at->diffInMonths(now()));

        return view('subscriber.portal', compact(
            'subscriber',
            'settings',
            'billingRecords',
            'rejectedPayments',
            'recentlyAcceptedPayments',
            'officeVisitRequests',
            'pendingRequestTypes',
            'payableBills',
            'pendingBills',
            'outstandingBalance',
            'currentBill',
            'soaAvailable',
            'monthsInService'
        ));
    }

    /**
     * Update Subscriber Profile (Full Name & Photo - Max 4MB)
     */
    public function updateProfile(Request $request)
    {
        $subscriber = Subscriber::findOrFail(session('subscriber_id'));

        $request->validate([
            'first_name'       => 'required|string|max:80',
            'middle_name'      => 'nullable|string|max:80',
            'surname'          => 'required|string|max:80',
            'suffix'           => ['required', Rule::in(['N/A', 'Jr.', 'Sr.', 'II', 'III', 'IV'])],
            'facebook_name'    => 'nullable|string|max:255',
            'phone_number'     => 'required|string|max:20',
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

        $subscriber->first_name = trim($request->first_name);
        $subscriber->middle_name = $request->filled('middle_name') ? trim($request->middle_name) : null;
        $subscriber->surname = trim($request->surname);
        $subscriber->suffix = $request->suffix;
        $subscriber->full_name = collect([
            $subscriber->first_name,
            $subscriber->middle_name,
            $subscriber->surname,
            $subscriber->suffix !== 'N/A' ? $subscriber->suffix : null,
        ])->filter()->implode(' ');
        $subscriber->facebook_name = $request->facebook_name;
        $subscriber->phone_number = $request->phone_number;

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

    public function storeOfficeVisitRequest(Request $request)
    {
        $validated = $request->validate([
            'request_type' => ['required', Rule::in(['payment_reduction', 'internet_fix'])],
            'visit_date' => [
                Rule::excludeIf(fn () => $request->input('request_type') !== 'payment_reduction'),
                Rule::requiredIf(fn () => $request->input('request_type') === 'payment_reduction'),
                'nullable',
                'date',
                'after_or_equal:today',
            ],
        ]);

        $created = DB::transaction(function () use ($validated): bool {
            $subscriber = Subscriber::query()
                ->lockForUpdate()
                ->findOrFail(session('subscriber_id'));

            if ($subscriber->officeVisitRequests()
                ->where('request_type', $validated['request_type'])
                ->where('status', 'pending')
                ->exists()) {
                return false;
            }

            $subscriber->officeVisitRequests()->create([
                'request_type' => $validated['request_type'],
                'visit_date' => $validated['request_type'] === 'payment_reduction'
                    ? $validated['visit_date']
                    : null,
                'status' => 'pending',
            ]);

            return true;
        });

        if (!$created) {
            return back()
                ->withInput()
                ->with('error', 'You already have a pending request of this type. Please wait for it to be reviewed before submitting another.');
        }

        return redirect()->route('subscriber.portal')
            ->with(
                'success',
                $validated['request_type'] === 'payment_reduction'
                    ? 'Your payment reduction request has been submitted. Please visit the ISP office on your selected date and bring your SOA.'
                    : 'Your internet repair request has been submitted. The ISP office will contact you with a schedule.'
            );
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