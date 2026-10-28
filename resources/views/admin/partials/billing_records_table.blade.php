<div class="flex-1 overflow-y-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 sticky top-0 z-10 border-b border-slate-100">
            <tr>
                <th class="px-4 py-2.5 text-center font-semibold">Subscriber</th>
                <th class="px-4 py-2.5 text-center font-semibold">Period</th>
                <th class="px-4 py-2.5 text-center font-semibold">Due Date</th>
                <th class="px-4 py-2.5 text-center font-semibold">Amount</th>
                <th class="px-4 py-2.5 text-center font-semibold">Status</th>
                <th class="px-4 py-2.5 text-center font-semibold">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($billingRecords as $record)
                <tr class="hover:bg-slate-100 transition">
                    <td class="px-4 py-3 text-center">
                        <div class="font-semibold text-slate-900">
                            {{ $record->subscriber->full_name ?? 'N/A' }}
                        </div>
                        <div class="text-xs text-slate-500">
                            {{ $record->subscriber->username ?? 'N/A' }}
                        </div>
                    </td>
                    <td class="px-4 py-3 text-center text-slate-700 font-medium">
                        {{ \Carbon\Carbon::create($record->billing_year, $record->billing_month, 1)->format('M Y') }}
                    </td>
                    <td class="px-4 py-3 text-center text-slate-700 whitespace-nowrap">
                        {{ $record->due_date?->format('M d, Y') ?? 'N/A' }}
                    </td>
                    <td class="px-4 py-3 text-center font-bold text-slate-900 whitespace-nowrap">
                        ₱{{ number_format($record->amount, 2) }}
                    </td>
                    <td class="px-4 py-3 text-center font-medium text-slate-700">
                        {{ ucfirst($record->status) }}
                    </td>
                    <td class="px-4 py-3 text-center whitespace-nowrap">
                        @if($record->status === 'paid')
                            <button
                                type="button"
                                onclick="openReceiptModal({{ $record->subscriber_id }}, {{ $record->id }})"
                                class="text-slate-700 hover:underline text-xs font-semibold"
                            >
                                Receipt
                            </button>
                        @elseif($record->due_date && $record->due_date->lte(today()->addDays(5)))
                            <button
                                type="button"
                                onclick="openSoaModal({{ $record->subscriber_id }})"
                                class="text-slate-700 hover:underline text-xs font-semibold"
                            >
                                View SOA
                            </button>
                        @else
                            <span class="text-slate-400">&mdash;</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-slate-400 text-sm">
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