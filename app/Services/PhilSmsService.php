<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PhilSmsService
{
    public function send($phone, $message)
    {
        $url = env('PHILSMS_API_URL');
        $token = trim((string) env('PHILSMS_API_TOKEN'));
        $sender = trim((string) env('PHILSMS_SENDER_ID'));

        if (!$url || !$token || !$sender) {
            Log::warning('PhilSMS configuration is missing.');
            return false;
        }

        $phone = preg_replace('/\D/', '', $phone);

        if (preg_match('/^09\d{9}$/', $phone)) {
            $phone = '63' . substr($phone, 1);
        } elseif (preg_match('/^9\d{9}$/', $phone)) {
            $phone = '63' . $phone;
        }

        if (!preg_match('/^639\d{9}$/', $phone)) {
            Log::warning('Invalid recipient number.');
            return false;
        }

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->timeout(15)
                ->post($url, [
                    'recipient' => $phone,
                    'sender_id' => $sender,
                    'type' => 'plain',
                    'message' => $message,
                ]);

            if ($response->successful() &&
                $response->json('status') === 'success') {
                return true;
            }

            Log::warning('PhilSMS rejected an SMS request.', [
                'http_status' => $response->status(),
                'api_status' => $response->json('status'),
                'api_message' => $response->json('message'),
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::error('PhilSMS connection failed.', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
