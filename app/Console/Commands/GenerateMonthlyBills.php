<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscriber;
use App\Models\BillingRecord;
use Carbon\Carbon;

class GenerateMonthlyBills extends Command
{
    protected $signature = 'billing:generate-monthly';

    protected $description = 'Generate monthly billing records for active and inactive subscribers';

    public function handle()
    {
        $now = Carbon::now();
        
        $subscribers = Subscriber::all();
        $count = 0;

        foreach ($subscribers as $subscriber) {
            $subscriber->syncOverdueStatus();

            if (!in_array($subscriber->status, ['active', 'inactive'], true)) {
                continue;
            }

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
        return Command::SUCCESS;
    }
}