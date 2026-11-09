<?php

namespace App\Services;

use App\Models\BillingRecord;
use App\Models\PaymentSubmission;
use App\Models\SmsLog;
use App\Models\SystemSetting;
use App\Models\SubscriberOfficeVisitRequest;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use Throwable;

class PhilSmsService
{
    public function sendDueDateReminder(BillingRecord $billingRecord): bool
    {
        $subscriber = $billingRecord->subscriber;
        $systemName = SystemSetting::query()->value('system_name') ?: config('app.name');
        $billingPeriod = \Carbon\Carbon::create(
            $billingRecord->billing_year,
            $billingRecord->billing_month,
            1
        )->format('M Y');

        return $this->send(
            $subscriber?->phone_number,
            sprintf(
                'Hello %s, %s: Your %s bill for PHP %s is due %s. View your statement in your subscriber portal: %s',
                $subscriber?->full_name ?? 'subscriber',
                $systemName,
                $billingPeriod,
                number_format((float) $billingRecord->amount, 2),
                $billingRecord->due_date?->format('M d, Y') ?? 'N/A',
                url('/portal')
            ),
            'subscriber',
            $subscriber?->id ?? $billingRecord->subscriber_id,
            'due_date_reminder'
        )['success'];
    }

    public function sendPaymentApproved(PaymentSubmission $payment): bool
    {
        $systemName = SystemSetting::query()->value('system_name') ?: config('app.name');

        return $this->send(
            $payment->subscriber?->phone_number,
            sprintf(
                'Hello %s, %s: We recorded your payment of PHP %s. Thank you.',
                $payment->subscriber?->full_name ?? 'subscriber',
                $systemName,
                number_format((float) $payment->amount_paid, 2)
            ),
            'subscriber',
            $payment->subscriber_id,
            'payment_approved'
        )['success'];
    }

    public function sendPaymentRejected(PaymentSubmission $payment): bool
    {
        $systemName = SystemSetting::query()->value('system_name') ?: config('app.name');

        return $this->send(
            $payment->subscriber?->phone_number,
            sprintf(
                'Hello %s, %s: We could not accept your payment of PHP %s. Reason: %s. Contact our office if you need help.',
                $payment->subscriber?->full_name ?? 'subscriber',
                $systemName,
                number_format((float) $payment->amount_paid, 2),
                $payment->admin_note ?: 'Please contact the office.'
            ),
            'subscriber',
            $payment->subscriber_id,
            'payment_rejected'
        )['success'];
    }

    public function sendOfficeVisitRequestUpdate(
        SubscriberOfficeVisitRequest $officeVisitRequest,
        bool $rescheduled
    ): bool {
        $subscriber = $officeVisitRequest->subscriber;
        $systemName = SystemSetting::query()->value('system_name') ?: config('app.name');
        $message = $rescheduled
            ? 'Hello %s, %s: Your ISP office visit for a payment reduction request has been moved to %s. Please bring or show your SOA.'
            : 'Hello %s, %s: Your ISP office visit request for a payment reduction is accepted for %s. Please bring or show your SOA.';

        return $this->send(
            $subscriber?->phone_number,
            sprintf(
                $message,
                $subscriber?->full_name ?? 'subscriber',
                $systemName,
                $officeVisitRequest->visit_date->format('M d, Y')
            ),
            'subscriber',
            $subscriber?->id ?? $officeVisitRequest->subscriber_id,
            $rescheduled ? 'office_visit_rescheduled' : 'office_visit_accepted'
        )['success'];
    }

    public function send(
        ?string $phone,
        string $message,
        string $recipientType = 'system',
        ?int $recipientId = null,
        string $notificationType = 'general'
    ): array {
        $smsLog = SmsLog::create([
            'recipient_type' => $recipientType,
            'recipient_id' => $recipientId,
            'phone_number' => $phone ?? '',
            'notification_type' => $notificationType,
            'message' => $message,
            'status' => 'pending',
        ]);

        try {
            $phone = $this->normalizePhilippineNumber($phone ?? '');
            $url = config('services.philsms.api_url');
            $token = trim((string) config('services.philsms.api_token'));
            $sender = trim((string) config('services.philsms.sender_id'));

            if (!$url || !$token || !$sender) {
                throw new \RuntimeException('PhilSMS configuration is missing.');
            }

            $response = Http::withToken($token)
                ->acceptJson()
                ->asJson()
                ->timeout(15)
                ->post($url, [
                    'recipient' => $phone,
                    'sender_id' => $sender,
                    'type' => 'plain',
                    'message' => $message,
                ]);

            $data = $response->json();
            $success = $response->successful() && data_get($data, 'status') === 'success';

            if ($success) {
                $smsLog->update([
                    'phone_number' => $phone,
                    'status' => 'sent',
                    'sent_at' => now(),
                    'error_message' => null,
                ]);

                return [
                    'success' => true,
                    'http_status' => $response->status(),
                    'data' => $data,
                    'sms_log_id' => $smsLog->id,
                ];
            }

            $errorMessage = data_get($data, 'message')
                ?? data_get($data, 'error')
                ?? $response->body();
            $smsLog->update([
                'phone_number' => $phone,
                'status' => 'failed',
                'error_message' => $errorMessage,
            ]);

            return [
                'success' => false,
                'http_status' => $response->status(),
                'data' => $data ?? $response->body(),
                'sms_log_id' => $smsLog->id,
            ];
        } catch (Throwable $exception) {
            $smsLog->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
            ]);

            return [
                'success' => false,
                'http_status' => null,
                'data' => null,
                'error' => $exception->getMessage(),
                'sms_log_id' => $smsLog->id,
            ];
        }
    }

    private function normalizePhilippineNumber(string $phoneNumber): string
    {
        $number = preg_replace('/\D+/', '', $phoneNumber);

        if (str_starts_with($number, '09') && strlen($number) === 11) {
            return '63' . substr($number, 1);
        }

        if (str_starts_with($number, '9') && strlen($number) === 10) {
            return '63' . $number;
        }

        if (str_starts_with($number, '63') && strlen($number) === 12) {
            return $number;
        }

        throw new InvalidArgumentException('Invalid Philippine mobile number.');
    }
}
