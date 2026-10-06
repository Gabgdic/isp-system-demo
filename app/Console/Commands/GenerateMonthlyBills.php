<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscriber;
use App\Models\BillingRecord;
use Carbon\Carbon;

class GenerateMonthlyBills extends Command
{
    protected $signature = 'billing:generate-monthly';

    protected $description = 'Generate monthly billing records for all active subscribers';

    public function handle()
    {
        $now = Carbon::now();
        $subscribers = Subscriber::where('status', 'active')->get();
        $count = 0;

        foreach ($subscribers as $subscriber) {
            $record = BillingRecord::firstOrCreate(
                [
                    'subscriber_id' => $subscriber->id,
                    'billing_month' => $now->month,
                    'billing_year'  => $now->year,
                ],
                [
                    'amount'   => $subscriber->monthly_fee,
                    'due_date' => $now->copy()->endOfMonth(),
                    'status'   => 'unpaid',
                    'paid_at'  => null,
                ]
            );

            if ($record->wasRecentlyCreated) {
                $count++;
            }
        }

        $this->info("Generated {$count} new billing records.");
    }
}