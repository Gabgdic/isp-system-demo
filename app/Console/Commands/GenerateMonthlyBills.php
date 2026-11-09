<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscriber;
use App\Models\BillingRecord;
use App\Services\PhilSmsService;
use Carbon\Carbon;

class GenerateMonthlyBills extends Command
{
    protected $signature = 'billing:generate-monthly';

    protected $description = 'Generate monthly billing records on each subscriber anniversary';

    public function handle(PhilSmsService $smsService)
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
                )->addMonth();
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
            $dueMonth = $now->copy()->startOfMonth()->addMonth();
            $dueDate = $dueMonth
                ->day(min($signupDay, $dueMonth->daysInMonth))
                ->startOfDay();
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
                || $now->day < $billingDay
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

        $remindersSent = 0;
        BillingRecord::query()
            ->with('subscriber')
            ->whereDate('due_date', $now->copy()->addDays(5)->toDateString())
            ->whereIn('status', ['unpaid', 'overdue'])
            ->whereNull('due_date_reminder_sent_at')
            ->orderBy('id')
            ->chunkById(100, function ($billingRecords) use ($smsService, &$remindersSent): void {
                foreach ($billingRecords as $billingRecord) {
                    if ($smsService->sendDueDateReminder($billingRecord)) {
                        $billingRecord->update([
                            'due_date_reminder_sent_at' => now(),
                        ]);
                        $remindersSent++;
                    }
                }
            });

        $this->info("Generated {$count} new billing records, corrected {$updatedDueDates} existing due dates, and sent {$remindersSent} due-date reminders.");
        return Command::SUCCESS;
    }
}