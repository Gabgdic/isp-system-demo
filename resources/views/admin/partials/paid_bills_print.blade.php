<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paid Bills Report</title>
    <style>
        body {
            color: #1e293b;
            font-family: Arial, sans-serif;
            margin: 32px;
        }

        h1 {
            font-size: 20px;
            margin: 0 0 4px;
        }

        .period,
        .summary {
            color: #64748b;
            font-size: 12px;
            margin: 0 0 8px;
        }

        .summary {
            margin-bottom: 20px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid #cbd5e1;
            padding: 8px;
            text-align: left;
            vertical-align: top;
            font-size: 11px;
        }

        th {
            background: #f1f5f9;
        }

        .amount {
            text-align: right;
            white-space: nowrap;
        }

        .total-row td {
            font-weight: 700;
        }

        @media print {
            body {
                margin: 14mm;
            }
        }
    </style>
</head>
<body>
    <h1>Paid Bills Report</h1>
    <p class="period">Period: {{ $reportPeriodLabel }}</p>
    <p class="summary">{{ number_format($paidBillRows->count()) }} paid {{ \Illuminate\Support\Str::plural('payment', $paidBillRows->count()) }}</p>

    <table>
        <thead>
            <tr>
                <th>Subscriber</th>
                <th>Billing Period</th>
                <th class="amount">Amount</th>
                <th>Date Paid</th>
                <th>Reference</th>
            </tr>
        </thead>
        <tbody>
            @forelse($paidBillRows as $row)
                <tr class="paid-bill-row">
                    <td>
                        {{ $row->subscriber?->full_name ?? 'N/A' }}
                    </td>
                    <td>{{ $row->periods }}</td>
                    <td class="amount">₱{{ number_format($row->amount, 2) }}</td>
                    <td>{{ $row->paid_at?->format('M d, Y') ?? 'N/A' }}</td>
                    <td>{{ $row->payment?->reference_number ?? 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">No paid bills found for this report period.</td>
                </tr>
            @endforelse
            @if($paidBillRows->isNotEmpty())
                <tr class="total-row">
                    <td colspan="2">Total</td>
                    <td class="amount">₱{{ number_format($paidBillRows->sum('amount'), 2) }}</td>
                    <td colspan="2"></td>
                </tr>
            @endif
        </tbody>
    </table>
</body>
</html>
