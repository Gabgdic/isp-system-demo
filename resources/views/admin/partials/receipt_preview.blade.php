<div class="space-y-6 bg-white p-2">
    <!-- Receipt Header -->
    <div class="flex justify-between items-start border-b border-slate-200 pb-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900 uppercase tracking-wider">Official Receipt</h2>
            <p class="text-xs text-slate-500">Receipt No: <span class="font-mono text-slate-800">#REC-{{ str_pad($billingRecord->id, 6, '0', STR_PAD_LEFT) }}</span></p>
        </div>
        <div class="text-right">
            <span class="inline-block bg-emerald-100 text-emerald-800 font-bold text-xs px-2.5 py-1 rounded-full uppercase border border-emerald-300">
                PAID
            </span>
            <p class="text-xs text-slate-500 mt-1">
                Date: {{ $billingRecord->paid_at ? \Carbon\Carbon::parse($billingRecord->paid_at)->format('M d, Y') : \Carbon\Carbon::now()->format('M d, Y') }}
            </p>
        </div>
    </div>

    <!-- Subscriber Details -->
    <div class="grid grid-cols-2 gap-4 text-xs bg-slate-50 p-3 rounded-xl border border-slate-100">
        <div>
            <p class="text-slate-400 font-semibold uppercase">Paid By</p>
            <p class="font-bold text-slate-800 text-sm mt-0.5">{{ $billingRecord->subscriber->full_name ?? 'N/A' }}</p>
            <p class="text-slate-500">{{ $billingRecord->subscriber->username ?? 'N/A' }}</p>
        </div>
        <div>
            <p class="text-slate-400 font-semibold uppercase">Billing Period</p>
            <p class="font-bold text-slate-800 text-sm mt-0.5">
                {{ \Carbon\Carbon::create($billingRecord->billing_year, $billingRecord->billing_month, 1)->format('F Y') }}
            </p>
        </div>
    </div>

    <!-- Payment Item Breakdown -->
    <table class="w-full text-xs text-left">
        <thead>
            <tr class="border-b border-slate-200 text-slate-400 uppercase font-semibold">
                <th class="py-2">Description</th>
                <th class="py-2 text-center">Ref No. / Method</th>
                <th class="py-2 text-right">Amount Paid</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-slate-700">
            @if($billingRecord->paymentAllocations->isNotEmpty())
                @foreach($billingRecord->paymentAllocations as $allocation)
                    <tr>
                        <td class="py-2.5">
                            Subscription Fee — {{ \Carbon\Carbon::create($billingRecord->billing_year, $billingRecord->billing_month, 1)->format('F Y') }}
                        </td>
                        <td class="py-2.5 text-center text-slate-500">
                            {{ $allocation->paymentSubmission->reference_number ?? 'N/A' }} 
                            @if(isset($allocation->paymentSubmission->payment_method))
                                ({{ ucfirst($allocation->paymentSubmission->payment_method) }})
                            @endif
                        </td>
                        <td class="py-2.5 text-right font-bold text-slate-900">
                            ₱{{ number_format($allocation->amount_allocated ?? $billingRecord->amount, 2) }}
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td class="py-2.5">
                        Subscription Fee — {{ \Carbon\Carbon::create($billingRecord->billing_year, $billingRecord->billing_month, 1)->format('F Y') }}
                    </td>
                    <td class="py-2.5 text-center text-slate-500">N/A</td>
                    <td class="py-2.5 text-right font-bold text-slate-900">
                        ₱{{ number_format($billingRecord->amount, 2) }}
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- Total Summary -->
    <div class="border-t border-slate-200 pt-3 flex justify-between items-center">
        <span class="text-xs font-bold text-slate-600 uppercase">Total Amount Paid</span>
        <span class="text-lg font-extrabold text-slate-900">₱{{ number_format($billingRecord->amount, 2) }}</span>
    </div>

    <!-- Footer Note -->
    <div class="text-center pt-4 border-t border-slate-100">
        <p class="text-[11px] text-slate-400">Thank you for your payment! This document serves as an official proof of payment.</p>
    </div>
</div>