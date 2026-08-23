<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_records', function (Blueprint $table) {
            $table->id();

            // Subscriber who owns this bill
            $table->foreignId('subscriber_id')
                ->constrained('subscribers')
                ->cascadeOnDelete();

            // Billing period
            $table->unsignedTinyInteger('billing_month');
            $table->unsignedSmallInteger('billing_year');

            // Amount that should be paid
            $table->decimal('amount', 10, 2);

            // When the bill is due
            $table->date('due_date')->nullable();

            $table->enum('status', [
                'unpaid',
                'pending',
                'paid',
                'overdue',
            ])->default('unpaid');

            // Actual time admin accepted the payment
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            // A subscriber should only have one bill for a given month.
            $table->unique([
                'subscriber_id',
                'billing_month',
                'billing_year'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_records');
    }
};