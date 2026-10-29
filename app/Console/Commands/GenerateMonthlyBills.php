<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscriber;
use App\Models\BillingRecord;
use Carbon\Carbon;

class GenerateMonthlyBills extends Command
{
    protected $signature = 'billing:generate-monthly';

    protected $description = 'Generate monthly billing records on each subscriber anniversary';

    public function handle()
    {
        $now = Carbon::now();
        
        $subscribers = Subscriber::all();
        $count = 0;
        $updatedDueDates = 0;

        foreach ($subscribers as $subscriber) {
            $signupDay = $subscriber->created_at->day;

            foreach ($subscriber->billingRecords as $existingRecord) {
                $billingMonth = Carbon::create(
                    $existingRecord->billing_year,
                    $existingRecord->billing_month,
                    1
                );
                $recordDueDate = $billingMonth
                    ->copy()
                    ->day(min($signupDay, $billingMonth->daysInMonth))
                    ->startOfDay();

                if ($existingRecord->due_date?->toDateString() !== $recordDueDate->toDateString()) {
                    $existingRecord->update(['due_date' => $recordDueDate]);
                    $updatedDueDates++;
                }
            }

            $subscriber->syncOverdueStatus();

            $billingDay = min($signupDay, $now->daysInMonth);
            $dueDate = $now->copy()->day($billingDay)->startOfDay();
            $record = BillingRecord::firstOrNew([
                'subscriber_id' => $subscriber->id,
                'billing_month' => $now->month,
                'billing_year'  => $now->year,
            ]);

            if ($record->exists) {
                continue;
            }

            if (
                !in_array($subscriber->status, ['active', 'inactive'], true)
                || $now->day !== $billingDay
            ) {
                continue;
            }

            $record->fill([
                'amount'   => $subscriber->monthly_fee,
                'due_date' => $dueDate,
                'status'   => 'unpaid',
                'paid_at'  => null,
            ])->save();
            $count++;
        }

        $this->info("Generated {$count} new billing records and corrected {$updatedDueDates} existing due dates.");
        return Command::SUCCESS;
    }
}