<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statement of Account - {{ $subscriber->full_name }}</title>

    <!-- Local compiled Tailwind CSS via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: white !important;
                padding: 0 !important;
            }
            .soa-card {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
            }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen py-8 text-slate-800 font-sans antialiased">

    <!-- Action Buttons (Hidden when printing) -->
    <div class="max-w-2xl mx-auto mb-4 flex justify-between items-center px-4 no-print">
        <a href="{{ route('subscriber.portal') }}" class="inline-flex items-center gap-1 text-xs font-bold text-slate-600 hover:text-slate-900 bg-white px-3 py-2 rounded-lg border border-slate-200 shadow-sm transition">
            ← Back to Portal
        </a>
        <button onclick="window.print()" class="inline-flex items-center gap-2 text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 px-4 py-2 rounded-lg shadow-sm transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Print Statement
        </button>
    </div>

    <!-- SOA DOCUMENT CARD -->
    <div class="max-w-2xl mx-auto bg-white border border-slate-200 rounded-xl p-8 shadow-sm soa-card">
        
        <!-- HEADER -->
        <div class="text-center mb-6 pb-4 border-b border-slate-200">
            <h1 class="text-xl font-black tracking-wider uppercase text-slate-900">
                {{ $systemSettings->system_name ?? 'KRNDS Network Services' }}
            </h1>
            <p class="text-xs text-slate-500 font-semibold tracking-widest uppercase mt-0.5">
                Statement of Account
            </p>
        </div>

        <!-- BILL TO & ACCOUNT SUMMARY SECTION -->
        <div class="grid grid-cols-2 gap-6 mb-6">
            <!-- BILL TO -->
            <div class="border border-slate-200 rounded-lg p-3.5 bg-slate-50/50">
                <h2 class="text-[11px] font-black uppercase text-slate-400 tracking-wider mb-2">
                    BILL TO:
                </h2>
                <div class="space-y-1">
                    <p class="text-xs text-slate-500">
                        Client Name: <span class="font-bold text-slate-900">{{ $subscriber->full_name }}</span>
                    </p>
                    <p class="text-xs text-slate-500">
                        Address: <span class="font-semibold text-slate-800">{{ $subscriber->address ?? 'N/A' }}</span>
                    </p>
                </div>
            </div>

            <!-- ACCOUNT SUMMARY -->
            <div class="border border-slate-200 rounded-lg p-3.5 bg-slate-50/50">
                <h2 class="text-[11px] font-black uppercase text-slate-400 tracking-wider mb-2">
                    ACCOUNT SUMMARY
                </h2>
                <div class="space-y-1">
                    <p class="text-xs text-slate-500">
                        Plan Subscription: <span class="font-bold text-slate-900">{{ $subscriber->plan_name ?? 'N/A' }}</span>
                    </p>
                    <p class="text-xs text-slate-500">
                        Plan Amount: <span class="font-bold text-slate-900">Php {{ number_format($subscriber->monthly_fee, 2) }}</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- BILLING TABLE -->
        <div class="overflow-hidden border border-slate-200 rounded-lg mb-6">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-100 border-b border-slate-200 text-slate-700 uppercase tracking-wider font-bold text-[11px]">
                        <th class="py-2.5 px-4">Billing Period</th>
                        <th class="py-2.5 px-4">Due Date</th>
                        <th class="py-2.5 px-4 text-right">Amount Due</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-800 font-medium">
                    @forelse($unpaidBills as $bill)
                        @php
                            $period = \Carbon\Carbon::create($bill->billing_year, $bill->billing_month, 1)->format('F Y');
                            $dueDate = $bill->due_date ? \Carbon\Carbon::parse($bill->due_date)->format('F d, Y') : 'N/A';
                        @endphp
                        <tr>
                            <td class="py-2.5 px-4 font-semibold">{{ $period }}</td>
                            <td class="py-2.5 px-4 text-slate-600">{{ $dueDate }}</td>
                            <td class="py-2.5 px-4 text-right font-bold">Php {{ number_format($bill->amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-4 px-4 text-center text-slate-400">
                                No outstanding bills at this time.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-slate-50 font-black text-xs text-slate-900 border-t-2 border-slate-300">
                        <td colspan="2" class="py-3 px-4 text-right uppercase tracking-wider">TOTAL</td>
                        <td class="py-3 px-4 text-right text-sm">
                            Php {{ number_format($totalOutstanding, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- REMINDER STATEMENT -->
        <div class="mb-8 space-y-2 text-xs text-slate-600 leading-relaxed text-justify">
            <p>
                You are hereby reminded of your monthly subscription due for the billing period stated above. We humbly request that payment be settled on or before the due date to avoid any possible service interruption.
            </p>
            <p>
                If payment has already been made before receiving this notice, please disregard this billing statement. We truly appreciate your prompt payment and continued support.
            </p>
        </div>

        <!-- AUTHORIZED SIGNATORY -->
        <div class="mt-8 pt-4 flex justify-between items-end">
            <div>
                <p class="text-xs font-semibold text-slate-500 mb-1">Authorized Signatory:</p>
                <div class="relative min-h-[50px] flex items-end">
                    <!-- Display signature image if present, else fallback to text -->
                    @if(isset($systemSettings->authorized_signature) && $systemSettings->authorized_signature)
                        <img src="{{ asset('storage/' . $systemSettings->authorized_signature) }}" alt="Signature" class="h-14 object-contain mb-1">
                    @else
                        <span class="text-xs font-bold text-slate-400 italic mb-1">(SGD)</span>
                    @endif
                </div>
                <p class="text-sm font-black text-slate-900 uppercase">
                    {{ $systemSettings->authorized_signatory ?? 'JURISH A. SY' }}
                </p>
                <p class="text-xs font-medium text-slate-500">
                    {{ $systemSettings->signatory_title ?? 'Proprietor' }}
                </p>
            </div>
        </div>

        <!-- FOOTER THANK YOU NOTE -->
        <div class="mt-10 border-t border-slate-200 pt-4 text-center">
            <p class="text-xs font-bold text-slate-700 tracking-wide">
                Thank you for being a valued subscriber of {{ $systemSettings->system_name ?? 'KRNDS Network Services' }}!
            </p>
        </div>

    </div>

</body>
</html>