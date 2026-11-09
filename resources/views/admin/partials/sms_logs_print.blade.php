<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMS Notification Logs</title>
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

        .period {
            color: #64748b;
            font-size: 12px;
            margin: 0 0 20px;
        }

        .sms-log-row {
            border-bottom: 1px solid #e2e8f0;
            padding: 12px 0;
            break-inside: avoid;
        }

        .sms-log-message {
            font-size: 13px;
            font-weight: 600;
            margin: 0;
        }

        .sms-log-time {
            color: #64748b;
            display: block;
            font-size: 11px;
            margin-top: 5px;
        }

        .sms-log-error {
            color: #dc2626;
            font-size: 11px;
            margin: 6px 0 0;
        }

        .sms-log-empty {
            color: #64748b;
            font-size: 13px;
        }

        @media print {
            body {
                margin: 16mm;
            }
        }
    </style>
</head>
<body>
    <h1>SMS Notification Logs</h1>
    <p class="period">Period: {{ $reportPeriodLabel }}</p>
    @include('admin.partials.sms_log_rows', ['smsLogs' => $smsLogs])
</body>
</html>
