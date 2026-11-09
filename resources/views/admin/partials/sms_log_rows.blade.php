@forelse($smsLogs as $smsLog)
    @php
        $recipientName = $smsLog->recipient_type === 'subscriber'
            ? ($smsLog->subscriber?->full_name ?? 'Subscriber #' . $smsLog->recipient_id)
            : 'System';
        $notificationName = $smsLog->notification_type === 'soa_sent'
            ? 'SOA'
            : ucwords(str_replace('_', ' ', $smsLog->notification_type));
        $notificationDate = $smsLog->sent_at ?? $smsLog->created_at;
    @endphp
    <article class="sms-log-row py-3 border-b border-slate-200 last:border-b-0">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-1">
            <p class="sms-log-message text-sm font-semibold text-slate-800">Notification: {{ $notificationName }} {{ $smsLog->status === 'sent' ? 'sent' : $smsLog->status }} to {{ $recipientName }}</p>
            <time class="sms-log-time text-xs text-slate-500 whitespace-nowrap" datetime="{{ $notificationDate?->toIso8601String() }}">
                {{ $notificationDate?->format('M j, Y g:i A') ?? 'Date unavailable' }}
            </time>
        </div>
        @if($smsLog->status === 'failed' && $smsLog->error_message)
            <p class="sms-log-error mt-1 text-xs text-red-600">Failed: {{ $smsLog->error_message }}</p>
        @endif
    </article>
@empty
    <p class="sms-log-empty text-sm text-slate-500 text-center py-8">No SMS notification logs for this period.</p>
@endforelse
