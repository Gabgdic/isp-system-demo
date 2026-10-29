<div class="overflow-x-auto">
    <p class="px-4 py-2 text-xs text-slate-500">
        {{ number_format($paidBillRecords->total()) }} paid {{ \Illuminate\Support\Str::plural('payment', $paidBillRecords->total()) }} found
    </p>
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 border-b border-slate-100">
            <tr>
                <th class="px-4 py-2.5 text-left font-semibold">Subscriber</th>
                <th class="px-4 py-2.5 text-center font-semibold">Billing Period</th>
                <th class="px-4 py-2.5 text-right font-semibold">Amount</th>
                <th class="px-4 py-2.5 text-center font-semibold">Date Paid</th>
                <th class="px-4 py-2.5 text-center font-semibold">Receipts</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($paidBillRecords as $row)
                <tr class="hover:bg-slate-100 transition">
                    <td class="px-4 py-3">
                        <div class="font-semibold text-slate-900">{{ $row->subscriber->full_name ?? 'N/A' }}</div>
                        <div class="text-xs text-slate-500">{{ $row->subscriber->username ?? 'N/A' }}</div>
                    </td>
                    <td class="px-4 py-3 text-center text-slate-700 whitespace-nowrap">
                        {{ $row->periods }}
                    </td>
                    <td class="px-4 py-3 text-right font-bold text-slate-900 whitespace-nowrap">
                        ₱{{ number_format($row->amount, 2) }}
                    </td>
                    <td class="px-4 py-3 text-center text-slate-700 whitespace-nowrap">
                        {{ $row->paid_at?->format('M d, Y') ?? 'N/A' }}
                    </td>
                    <td class="px-4 py-3 text-center whitespace-nowrap">
                        <button
                            type="button"
                            data-receipt-url="{{ $row->payment
                                ? route('billing.payment-receipt.preview', $row->payment)
                                : route('billing.receipt.preview', $row->legacy_bill) }}"
                            class="open-report-receipt text-slate-700 hover:underline text-xs font-semibold"
                        >Official receipt</button>
                        @if($row->payment && filled($row->payment->payment_screenshot))
                            <span class="text-slate-300 mx-1">|</span>
                            <button
                                type="button"
                                data-proof-url="{{ asset('storage/' . $row->payment->payment_screenshot) }}"
                                class="open-payment-proof text-slate-700 hover:underline text-xs font-semibold"
                            >Payment proof</button>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-slate-400 text-sm">
                        No paid bills found for this report period.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="px-4 py-2 border-t border-slate-200 bg-slate-50/50">
    {{ $paidBillRecords->links('vendor.pagination.compact') }}
</div>
