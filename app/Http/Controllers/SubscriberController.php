<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PaymentSubmission;
use App\Models\PaymentSubmissionBilling;
use App\Models\Subscriber;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class SubscriberController extends Controller
{
    private const BARANGAYS_BY_MUNICIPALITY = [
        'Bontoc' => [
            'Banahao', 'Baugo', 'Beniton', 'Buenavista', 'Bunga', 'Casao', 'Catmon',
            'Catoogan', 'Cawayanan', 'Dao', 'Divisoria', 'Esperanza', 'Guinsangaan',
            'Hibagwan', 'Hilaan', 'Himakilo', 'Hitawos', 'Lanao', 'Lawgawan',
            'Mahayahay', 'Malbago', 'Mauylab', 'Paku', 'Pamahawan', 'Pamigsian',
            'Pangi', 'Poblacion', 'Sampongon', 'San Vicente', 'Santa Cruz', 'Taa',
            'Talisay', 'Taytagan', 'Tuburan', 'Union', 'Olisihan', 'Anahao',
            'Pong-on', 'San Ramon', 'Santo Niño',
        ],
        'Sogod' => [
            'Benit', 'Buac Daku', 'Buac Gamay', 'Cabadbaran', 'Concepcion',
            'Consolacion', 'Dagsa', 'Hibod-hibod', 'Hindangan', 'Hipantag', 'Javier',
            'Kahupian', 'Kanangkaan', 'Kauswagan', 'La Purisima Concepcion', 'Libas',
            'Lum-an', 'Mabicay', 'Mac', 'Magatas', 'Mahayahay', 'Malinao',
            'Maria Plana', 'Milagroso', 'Pancho Villa', 'Pandan', 'Zone I (Pob.)',
            'Zone II (Pob.)', 'Zone III (Pob.)', 'Zone IV (Pob.)', 'Zone V (Pob.)',
            'Rizal', 'Salvacion', 'San Francisco Mabuhay', 'San Isidro', 'San Jose',
            'San Juan', 'San Miguel', 'San Pedro', 'San Roque', 'San Vicente',
            'Santa Maria', 'Suba', 'Tampoong', 'Olisihan',
        ],
    ];

    private const SUFFIXES = ['N/A', 'Jr.', 'Sr.', 'II', 'III', 'IV'];

    /**
     * Display a listing of the subscribers.
     */
    public function index(Request $request)
    {
        Subscriber::query()->chunkById(100, function ($subscribers) {
            $subscribers->each->syncOverdueStatus();
        });

        $subscribersQuery = Subscriber::withSum([
            'billingRecords as past_due_balance' => fn ($query) => $query
                ->whereDate('due_date', '<', today())
                ->whereIn('status', ['unpaid', 'overdue']),
        ], 'amount')
            ->withCount([
                'billingRecords as past_due_pending_count' => fn ($query) => $query
                    ->whereDate('due_date', '<', today())
                    ->where('status', 'pending'),
            ]);

        $search = trim($request->input('search', ''));
        if ($search !== '') {
            $subscribersQuery->where(function ($query) use ($search) {
                $query->where('full_name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%")
                    ->orWhere('facebook_name', 'like', "%{$search}%")
                    ->orWhere('plan_name', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('monthly_fee', 'like', "%{$search}%");
            });
        }

        $subscribers = $subscribersQuery->latest()->paginate(10)->withQueryString();

        if ($request->ajax()) {
            return view('admin.partials.subscribers_table', compact('subscribers', 'search'))->render();
        }

        $plans = SubscriptionPlan::all();
        $barangaysByMunicipality = self::BARANGAYS_BY_MUNICIPALITY;
        $suffixes = self::SUFFIXES;

        $totalSubscribers = Subscriber::count();
        $activeSubscribers = Subscriber::where('status', 'active')->count();
        $inactiveSubscribers = Subscriber::where('status', 'inactive')->count();
        $disconnectedSubscribers = Subscriber::where('status', 'disconnected')->count();

        return view('admin.subscriber', compact(
            'subscribers',
            'plans',
            'barangaysByMunicipality',
            'suffixes',
            'totalSubscribers',
            'activeSubscribers',
            'inactiveSubscribers',
            'disconnectedSubscribers',
            'search'
        ));
    }

    /**
     * Store a newly created subscriber in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'first_name' => 'required|string|max:80',
            'middle_name' => 'nullable|string|max:80',
            'surname' => 'required|string|max:80',
            'suffix' => ['required', Rule::in(self::SUFFIXES)],
            'username' => 'required|string|max:255|unique:subscribers,username',
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
            'facebook_name' => 'nullable|string|max:255',
            'phone_number' => 'required|string|max:20',
            'subscription_plan_id' => 'required|exists:subscription_plans,id',
            'municipality' => ['required', Rule::in(array_keys(self::BARANGAYS_BY_MUNICIPALITY))],
            'province' => ['required', Rule::in(['Southern Leyte'])],
            'barangay' => [
                'required',
                Rule::in(self::BARANGAYS_BY_MUNICIPALITY[$request->input('municipality')] ?? []),
            ],
        ]);

        $plan = SubscriptionPlan::findOrFail($request->subscription_plan_id);

        $photoPath = null;
        if ($request->hasFile('profile_photo')) {
            $photoPath = $request->file('profile_photo')->store('subscribers', 'public');
        }

        Subscriber::create([
            'profile_photo' => $photoPath,
            'first_name' => trim($request->first_name),
            'middle_name' => $request->filled('middle_name') ? trim($request->middle_name) : null,
            'surname' => trim($request->surname),
            'suffix' => $request->suffix,
            'full_name' => $this->formatFullName($request),
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'facebook_name' => $request->facebook_name,
            'phone_number' => $request->phone_number,
            'subscription_plan_id' => $plan->id,
            'plan_name' => $plan->plan_name,
            'monthly_fee' => $plan->price,
            'barangay' => $request->barangay,
            'municipality' => $request->municipality,
            'province' => $request->province,
            'address' => $this->formatAddress($request),
            'status' => 'active',
        ]);

        return redirect()->route('subscriber')->with('success', 'Subscriber created successfully.');
    }

    /**
     * Update the specified subscriber in storage.
     */
    public function update(Request $request, $id)
    {
        $subscriber = Subscriber::findOrFail($id);

        $request->validate([
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'first_name' => 'required|string|max:80',
            'middle_name' => 'nullable|string|max:80',
            'surname' => 'required|string|max:80',
            'suffix' => ['required', Rule::in(self::SUFFIXES)],
            'username' => 'required|string|max:255|unique:subscribers,username,' . $subscriber->id,
            'password' => [
                'nullable',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
            'facebook_name' => 'nullable|string|max:255',
            'phone_number' => 'required|string|max:20',
            'subscription_plan_id' => 'required|exists:subscription_plans,id',
            'municipality' => ['required', Rule::in(array_keys(self::BARANGAYS_BY_MUNICIPALITY))],
            'province' => ['required', Rule::in(['Southern Leyte'])],
            'barangay' => [
                'required',
                Rule::in(self::BARANGAYS_BY_MUNICIPALITY[$request->input('municipality')] ?? []),
            ],
        ]);

        $plan = SubscriptionPlan::findOrFail($request->subscription_plan_id);

        if ($request->hasFile('profile_photo')) {
            if ($subscriber->profile_photo && Storage::disk('public')->exists($subscriber->profile_photo)) {
                Storage::disk('public')->delete($subscriber->profile_photo);
            }
            $subscriber->profile_photo = $request->file('profile_photo')->store('subscribers', 'public');
        }

        $subscriber->first_name = trim($request->first_name);
        $subscriber->middle_name = $request->filled('middle_name') ? trim($request->middle_name) : null;
        $subscriber->surname = trim($request->surname);
        $subscriber->suffix = $request->suffix;
        $subscriber->full_name = $this->formatFullName($request);
        $subscriber->username = $request->username;
        $subscriber->facebook_name = $request->facebook_name;
        $subscriber->phone_number = $request->phone_number;
        $subscriber->subscription_plan_id = $plan->id; // Assign to subscription_plan_id, NOT subscriber->id
        $subscriber->plan_name = $plan->plan_name;
        $subscriber->monthly_fee = $plan->price;
        $subscriber->barangay = $request->barangay;
        $subscriber->municipality = $request->municipality;
        $subscriber->province = $request->province;
        $subscriber->address = $this->formatAddress($request);

        if ($request->filled('password')) {
            $subscriber->password = Hash::make($request->password);
        }

        $subscriber->save();

        return redirect()->route('subscriber')->with('success', 'Subscriber updated successfully.');
    }

    private function formatFullName(Request $request): string
    {
        return collect([
            trim($request->first_name),
            $request->filled('middle_name') ? trim($request->middle_name) : null,
            trim($request->surname),
            $request->suffix !== 'N/A' ? $request->suffix : null,
        ])->filter()->implode(' ');
    }

    private function formatAddress(Request $request): string
    {
        return implode(', ', [
            $request->barangay,
            $request->municipality,
            $request->province,
        ]);
    }

    public function recordPastDuePayment($id)
    {
        $subscriber = Subscriber::findOrFail($id);

        $result = DB::transaction(function () use ($subscriber) {
            $subscriber->syncOverdueStatus();

            if ($subscriber->status !== 'disconnected') {
                return ['error' => 'Only disconnected subscribers can have an in-person recovery payment recorded here.'];
            }

            $pastDueBills = $subscriber->billingRecords()
                ->whereDate('due_date', '<', today())
                ->whereIn('status', ['unpaid', 'overdue', 'pending'])
                ->lockForUpdate()
                ->get();

            if ($pastDueBills->contains(fn ($bill) => $bill->status === 'pending')) {
                return ['error' => 'A past-due payment is already awaiting verification. Review it before recording another payment.'];
            }

            $unpaidBills = $pastDueBills->whereIn('status', ['unpaid', 'overdue']);

            if ($unpaidBills->isEmpty()) {
                return ['error' => 'There are no unpaid past-due bills to record.'];
            }

            $amountPaid = $unpaidBills->sum(fn ($bill) => (float) $bill->amount);
            $payment = PaymentSubmission::create([
                'billing_record_id' => null,
                'subscriber_id' => $subscriber->id,
                'amount_paid' => $amountPaid,
                'reference_number' => 'ADMIN-' . $subscriber->id . '-' . Str::upper(Str::random(8)),
                'payment_screenshot' => null,
                'status' => 'approved',
                'admin_note' => 'In-person payment recorded by admin.',
                'reviewed_at' => now(),
            ]);

            foreach ($unpaidBills as $bill) {
                $bill->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);

                PaymentSubmissionBilling::create([
                    'payment_submission_id' => $payment->id,
                    'billing_record_id' => $bill->id,
                    'amount_applied' => $bill->amount,
                ]);
            }

            return ['amount' => $amountPaid];
        });

        if (isset($result['error'])) {
            return back()->with('error', $result['error']);
        }

        return redirect()->route('subscriber')->with(
            'success',
            'Recorded in-person payment of ₱' . number_format($result['amount'], 2) . '. Reactivate the subscriber after confirming all past-due bills are paid.'
        );
    }

    public function reactivate($id)
    {
        $subscriber = Subscriber::findOrFail($id);
        $subscriber->syncOverdueStatus();

        if ($subscriber->status !== 'disconnected') {
            return back()->with('error', 'This subscriber is not disconnected.');
        }

        $hasUnpaidPastDueBills = $subscriber->billingRecords()
            ->whereDate('due_date', '<', today())
            ->whereIn('status', ['unpaid', 'overdue', 'pending'])
            ->exists();

        if ($hasUnpaidPastDueBills) {
            return back()->with('error', 'All past-due bills must be paid and verified before reactivation.');
        }

        $subscriber->update(['status' => 'active']);

        return redirect()->route('subscriber')->with('success', 'Subscriber account reactivated.');
    }

    /**
     * Remove the specified subscriber from storage.
     */
    public function destroy(Request $request, $id)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        if (!Hash::check($request->password, auth()->user()->password)) {
            return redirect()->back()->with('error', 'Incorrect admin password. Deletion aborted.');
        }

        $subscriber = Subscriber::findOrFail($id);

        if ($subscriber->profile_photo && Storage::disk('public')->exists($subscriber->profile_photo)) {
            Storage::disk('public')->delete($subscriber->profile_photo);
        }

        $subscriber->delete();

        return redirect()->route('subscriber')->with('success', 'Subscriber deleted successfully.');
    }
}