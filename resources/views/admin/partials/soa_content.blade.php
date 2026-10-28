<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statement of Account - {{ $subscriber->full_name }}</title>

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
            .soa-card {
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

    <!-- A4 SOA SHEET CONTAINER -->
    <div class="max-w-[210mm] min-h-[297mm] mx-auto bg-white border border-slate-200 rounded-2xl p-10 shadow-sm soa-card flex flex-col justify-between">
        
        <div>
            <!-- BRANDING HEADER -->
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
                    <span class="inline-block bg-slate-900 text-white font-black text-[11px] px-3.5 py-1 rounded-lg tracking-widest uppercase shadow-xs">
                        Statement of Account
                    </span>
                    <p class="text-xs font-semibold text-slate-500 mt-2">
                        Date Issued: <span class="text-slate-800">{{ \Carbon\Carbon::now()->format('M d, Y') }}</span>
                    </p>
                </div>
            </div>

            <!-- BILL TO & ACCOUNT SUMMARY GRID -->
            <div class="grid grid-cols-2 gap-6 mb-6">
                <!-- BILL TO -->
                <div class="border border-slate-200 rounded-xl p-4 bg-slate-50/60">
                    <h2 class="text-[11px] font-black uppercase text-slate-400 tracking-wider mb-2.5">
                        BILL TO:
                    </h2>
                    <div class="space-y-1.5 text-xs">
                        <p class="text-slate-500">
                            Subscriber Name: <span class="font-bold text-slate-900 text-sm ml-1">{{ $subscriber->full_name }}</span>
                        </p>
                        <p class="text-slate-500">
                            Service Address: <span class="font-semibold text-slate-800 ml-1">{{ $subscriber->address ?? 'N/A' }}</span>
                        </p>
                    </div>
                </div>

                <!-- ACCOUNT SUMMARY -->
                <div class="border border-slate-200 rounded-xl p-4 bg-slate-50/60">
                    <h2 class="text-[11px] font-black uppercase text-slate-400 tracking-wider mb-2.5">
                        ACCOUNT SUMMARY
                    </h2>
                    <div class="space-y-1.5 text-xs">
                        <p class="text-slate-500">
                            Plan Subscription: <span class="font-bold text-slate-900 ml-1">{{ $subscriber->plan_name ?? 'N/A' }}</span>
                        </p>
                        <p class="text-slate-500">
                            Monthly Rate: <span class="font-bold text-slate-900 ml-1">₱{{ number_format($subscriber->monthly_fee ?? 0, 2) }}</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- BILLING TABLE -->
            <div class="overflow-hidden border border-slate-200 rounded-xl mb-6">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-100/80 border-b border-slate-200 text-slate-700 uppercase tracking-wider font-bold text-[11px]">
                            <th class="py-3 px-4">Billing Period</th>
                            <th class="py-3 px-4">Due Date</th>
                            <th class="py-3 px-4 text-right">Amount Due</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 text-slate-800 font-medium">
                        @forelse($unpaidBills as $bill)
                            @php
                                $period = \Carbon\Carbon::create($bill->billing_year, $bill->billing_month, 1)->format('F Y');
                                $dueDate = $bill->due_date ? \Carbon\Carbon::parse($bill->due_date)->format('F d, Y') : 'N/A';
                            @endphp
                            <tr class="hover:bg-slate-50/50">
                                <td class="py-3 px-4 font-semibold text-slate-900">{{ $period }}</td>
                                <td class="py-3 px-4 text-slate-600">{{ $dueDate }}</td>
                                <td class="py-3 px-4 text-right font-bold text-slate-900">₱{{ number_format($bill->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-6 px-4 text-center text-slate-400">
                                    No outstanding bills at this time.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-50 font-black text-xs text-slate-900 border-t-2 border-slate-300">
                            <td colspan="2" class="py-3.5 px-4 text-right uppercase tracking-wider">Total Amount Due</td>
                            <td class="py-3.5 px-4 text-right text-base font-extrabold text-red-600">
                                ₱{{ number_format($totalOutstanding, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- CUSTOMIZABLE BODY REMINDER STATEMENT -->
            <div class="mb-8 p-4 rounded-xl bg-slate-50/50 border border-slate-200/80 text-xs text-slate-600 leading-relaxed text-justify whitespace-pre-line">
                {{ $systemSettings->soa_body_statement ?? "You are hereby reminded of your monthly subscription due for the billing period stated above. We humbly request that payment be settled on or before the due date to avoid any possible service interruption.\n\nIf payment has already been made before receiving this notice, please disregard this billing statement. We truly appreciate your prompt payment and continued support." }}
            </div>
        </div>

        <!-- FOOTER & AUTHORIZED SIGNATORY SECTION -->
        <div>
            <div class="pt-4 border-t border-slate-200 flex justify-start items-end">
                <div class="text-left">
                    <p class="text-xs font-semibold text-slate-500 mb-1">Authorized Signatory:</p>
                    <div class="relative min-h-[50px] flex items-end justify-start">
                        @if(isset($systemSettings->authorized_signature) && $systemSettings->authorized_signature)
                            <img src="{{ asset('storage/' . $systemSettings->authorized_signature) }}" alt="Signature" class="h-14 object-contain mb-1">
                        @else
                            <span class="text-xs font-bold text-slate-300 italic mb-1">(SGD)</span>
                        @endif
                    </div>
                    <p class="text-sm font-black text-slate-900 uppercase tracking-wide">
                        {{ $systemSettings->authorized_signatory ?? 'JURISH A. SY' }}
                    </p>
                    <p class="text-xs font-medium text-slate-500">
                        {{ $systemSettings->signatory_title ?? 'Proprietor' }}
                    </p>
                </div>
            </div>

            <div class="mt-8 pt-3 border-t border-slate-100 text-center">
                <p class="text-xs font-bold text-slate-700 tracking-wide">
                    Thank you for being a valued subscriber of {{ $systemSettings->system_name ?? 'KRNDS Network Services' }}!
                </p>
            </div>
        </div>

    </div>

</body>
</html>