<div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between gap-3 bg-slate-50/50 flex-shrink-0">
    <div>
        <h2 class="text-base font-bold text-slate-900">Billing Records</h2>
        <p class="text-xs text-slate-500">Track subscriber invoices, payment status, and statements.</p>
    </div>
</div>

<div class="flex-1 overflow-y-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 sticky top-0 z-10 border-b border-slate-100">
            <tr>
                <th class="px-4 py-2.5 text-left font-semibold">Subscriber</th>
                <th class="px-4 py-2.5 text-left font-semibold">Period</th>
                <th class="px-4 py-2.5 text-left font-semibold">Amount</th>
                <th class="px-4 py-2.5 text-left font-semibold">Status</th>
                <th class="px-4 py-2.5 text-right font-semibold">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($billingRecords as $record)
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-4 py-3">
                        <div class="font-semibold text-slate-900">
                            {{ $record->subscriber->full_name ?? 'N/A' }}
                        </div>
                        <div class="text-xs text-slate-500">
                            {{ $record->subscriber->username ?? 'N/A' }}
                        </div>
                    </td>
                    <td class="px-4 py-3 text-slate-700 font-medium">
                        {{ \Carbon\Carbon::create($record->billing_year, $record->billing_month, 1)->format('M Y') }}
                    </td>
                    <td class="px-4 py-3 font-bold text-slate-900 whitespace-nowrap">
                        ₱{{ number_format($record->amount, 2) }}
                    </td>
                    <td class="px-4 py-3">
                        @if($record->status === 'paid')
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-green-50 text-green-700 border border-green-200">
                                Paid
                            </span>
                        @elseif($record->status === 'overdue')
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-red-50 text-red-700 border border-red-200">
                                Overdue
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-yellow-50 text-yellow-700 border border-yellow-200">
                                Pending
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        @if($record->status === 'paid')
                            <!-- Receipt Button for Paid Records -->
                            <button 
                                type="button"
                                onclick="openReceiptModal({{ $record->subscriber_id }}, {{ $record->id }})"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 text-xs font-semibold transition"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Receipt
                            </button>
                        @else
                            <!-- Statement of Account Button for Unpaid/Pending Records -->
                            <button 
                                type="button"
                                onclick="openSoaModal({{ $record->subscriber_id }})"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition"
                            >
                                View SOA
                            </button>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-slate-400 text-sm">
                        No billing records found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination Footer -->
<div class="px-4 py-2 border-t border-slate-200 bg-slate-50/50 flex-shrink-0">
    {{ $billingRecords->links() }}
</div>