<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payment_submission_billings')) {
            return;
        }

        Schema::create('payment_submission_billings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_submission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('billing_record_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount_applied', 10, 2);
            $table->timestamps();
            $table->unique(['payment_submission_id', 'billing_record_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_submission_billings');
    }
};