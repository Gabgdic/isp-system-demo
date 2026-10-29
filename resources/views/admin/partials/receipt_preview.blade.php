@php
    $systemSettings = $systemSettings ?? \App\Models\SystemSetting::first();
    $receiptNumber = isset($paymentSubmission)
        ? ($paymentSubmission->reference_number ?: 'PAY-' . $paymentSubmission->id)
        : 'REC-' . str_pad($billingRecord->id, 6, '0', STR_PAD_LEFT);
    $receiptDate = isset($paymentSubmission)
        ? ($paymentSubmission->reviewed_at ?? $billingRecord->paid_at)
        : $billingRecord->paid_at;
    $receiptSubscriber = isset($paymentSubmission)
        ? $paymentSubmission->subscriber
        : $billingRecord->subscriber;
    $receiptTotal = isset($paymentSubmission)
        ? $paymentSubmission->amount_paid
        : $billingRecord->amount;
    $receiptPeriods = isset($receiptItems)
        ? $receiptItems->map(fn ($item) => \Carbon\Carbon::create(
            $item->bill->billing_year,
            $item->bill->billing_month,
            1
        )->format('M Y'))->unique()->implode(', ')
        : \Carbon\Carbon::create($billingRecord->billing_year, $billingRecord->billing_month, 1)->format('F Y');
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Official Receipt - {{ $receiptNumber }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }

        @media print {
            body {
                background: white !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .receipt-card {
                box-shadow: none !important;
                border: none !important;
                border-radius: 0 !important;
                padding: 12mm 15mm !important;
                max-width: 100% !important;
                width: 100% !important;
            }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen py-8 text-slate-800 font-sans antialiased">

    <!-- A4 OFFICIAL RECEIPT SHEET CONTAINER -->
    <div class="max-w-[210mm] min-h-[297mm] mx-auto bg-white border border-slate-200 rounded-2xl p-10 shadow-sm receipt-card flex flex-col justify-between">
        
        <div>
            <!-- BRANDING HEADER (MATCHING SOA FORMAT EXACTLY) -->
            <div class="flex items-center justify-between pb-6 mb-6 border-b border-slate-200">
                <div class="flex items-center gap-4">
                    @if(isset($systemSettings->system_logo) && $systemSettings->system_logo)
                        <img src="{{ asset('storage/' . $systemSettings->system_logo) }}" alt="Logo" class="h-14 w-auto object-contain">
                    @else
                        <div class="w-12 h-12 rounded-xl bg-slate-900 text-white font-black flex items-center justify-center text-xl shadow-sm">
                            {{ strtoupper(substr($systemSettings->system_name ?? 'K', 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        <h1 class="text-lg font-black tracking-wide uppercase text-slate-900">
                            {{ $systemSettings->system_name ?? 'KRNDS Network Services' }}
                        </h1>
                        <p class="text-xs text-slate-500 font-medium mt-0.5">
                            {{ $systemSettings->soa_location ?? 'Zone 1, Sogod, Southern Leyte' }}
                        </p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="inline-block bg-emerald-600 text-white font-black text-[11px] px-3.5 py-1 rounded-lg tracking-widest uppercase shadow-xs">
                        Official Receipt
                    </span>
                    <p class="text-xs font-mono font-bold text-slate-700 mt-2">
                        {{ $receiptNumber }}
                    </p>
                    <p class="text-[11px] font-semibold text-slate-500 mt-0.5">
                        Payment Date: <span class="text-slate-800">{{ $receiptDate ? \Carbon\Carbon::parse($receiptDate)->format('M d, Y') : \Carbon\Carbon::now()->format('M d, Y') }}</span>
                    </p>
                </div>
            </div>

            <!-- RECEIVED FROM & PAYMENT SUMMARY GRID -->
            <div class="grid grid-cols-2 gap-6 mb-6">
                <!-- RECEIVED FROM -->
                <div class="border border-slate-200 rounded-xl p-4 bg-slate-50/60">
                    <h2 class="text-[11px] font-black uppercase text-slate-400 tracking-wider mb-2.5">
                        RECEIVED FROM:
                    </h2>
                    <div class="space-y-1.5 text-xs">
                        <p class="text-slate-500">
                            Subscriber Name: <span class="font-bold text-slate-900 text-sm ml-1">{{ $receiptSubscriber->full_name ?? 'N/A' }}</span>
                        </p>
                        <p class="text-slate-500">
                            Service Address: <span class="font-semibold text-slate-800 ml-1">{{ $receiptSubscriber->address ?? 'N/A' }}</span>
                        </p>
                    </div>
                </div>

                <!-- PAYMENT DETAILS -->
                <div class="border border-slate-200 rounded-xl p-4 bg-slate-50/60">
                    <h2 class="text-[11px] font-black uppercase text-slate-400 tracking-wider mb-2.5">
                        PAYMENT DETAILS
                    </h2>
                    <div class="space-y-1.5 text-xs">
                        <p class="text-slate-500">
                            Billing Period(s): <span class="font-bold text-slate-900 ml-1">{{ $receiptPeriods }}</span>
                        </p>
                        <p class="text-slate-500">
                            Status: <span class="font-bold text-emerald-600 uppercase ml-1">Paid / Cleared</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- PAYMENT BREAKDOWN TABLE (FILTERING OUT REJECTED PAYMENTS) -->
            <div class="overflow-hidden border border-slate-200 rounded-xl mb-6">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-100/80 border-b border-slate-200 text-slate-700 uppercase tracking-wider font-bold text-[11px]">
                            <th class="py-3 px-4">Description</th>
                            <th class="py-3 px-4 text-center">Ref No. / Payment Method</th>
                            <th class="py-3 px-4 text-right">Amount Paid</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 text-slate-800 font-medium">
                        @if(isset($paymentSubmission))
                            @foreach($receiptItems as $item)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="py-3 px-4 font-semibold text-slate-900">
                                        Subscription Fee - {{ \Carbon\Carbon::create($item->bill->billing_year, $item->bill->billing_month, 1)->format('F Y') }}
                                    </td>
                                    <td class="py-3 px-4 text-center text-slate-600">
                                        {{ $paymentSubmission->reference_number ?? 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-bold text-slate-900">
                                        ₱{{ number_format($item->amount, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        @else
                        @php
                            // Filter allocations to exclude rejected payment submissions
                            $validAllocations = $billingRecord->paymentAllocations ? $billingRecord->paymentAllocations->filter(function($allocation) {
                                return optional($allocation->paymentSubmission)->status !== 'rejected';
                            }) : collect();
                        @endphp

                        @if($validAllocations->isNotEmpty())
                            @foreach($validAllocations as $allocation)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="py-3 px-4 font-semibold text-slate-900">
                                        Subscription Fee - {{ \Carbon\Carbon::create($billingRecord->billing_year, $billingRecord->billing_month, 1)->format('F Y') }}
                                    </td>
                                    <td class="py-3 px-4 text-center text-slate-600">
                                        {{ $allocation->paymentSubmission->reference_number ?? 'N/A' }}
                                        @if(isset($allocation->paymentSubmission->payment_method))
                                            <span class="text-slate-500">({{ ucfirst($allocation->paymentSubmission->payment_method) }})</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-right font-bold text-slate-900">
                                        ₱{{ number_format($allocation->amount_allocated ?? $billingRecord->amount, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr class="hover:bg-slate-50/50">
                                <td class="py-3 px-4 font-semibold text-slate-900">
                                    Subscription Fee — {{ \Carbon\Carbon::create($billingRecord->billing_year, $billingRecord->billing_month, 1)->format('F Y') }}
                                </td>
                                <td class="py-3 px-4 text-center text-slate-600">Cash / Direct Payment</td>
                                <td class="py-3 px-4 text-right font-bold text-slate-900">
                                    ₱{{ number_format($receiptTotal, 2) }}
                                </td>
                            </tr>
                        @endif
                        @endif
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-50 font-black text-xs text-slate-900 border-t-2 border-slate-300">
                            <td colspan="2" class="py-3.5 px-4 text-right uppercase tracking-wider">Total Amount Paid</td>
                            <td class="py-3.5 px-4 text-right text-base font-extrabold text-emerald-600">
                                ₱{{ number_format($receiptTotal, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- OFFICIAL ACKNOWLEDGMENT NOTE -->
            <div class="mb-8 p-4 rounded-xl bg-slate-50/50 border border-slate-200/80 text-xs text-slate-600 leading-relaxed text-justify">
                This document serves as an official proof of payment for the monthly network service subscription fee indicated above. Please keep this receipt for your records.
            </div>
        </div>

        <!-- FOOTER SECTION (SIGNATORY REMOVED) -->
        <div>
            <div class="pt-4 border-t border-slate-200 text-center">
                <p class="text-xs font-bold text-slate-700 tracking-wide">
                    Thank you for being a valued subscriber of {{ $systemSettings->system_name ?? 'KRNDS Network Services' }}!
                </p>
            </div>
        </div>

    </div>

</body>
</html>