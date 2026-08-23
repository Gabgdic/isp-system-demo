<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_submissions', function (Blueprint $table) {
            $table->id();

            // The bill being paid
            $table->foreignId('billing_record_id')
                ->constrained('billing_records')
                ->cascadeOnDelete();

            // Subscriber who submitted the payment
            $table->foreignId('subscriber_id')
                ->constrained('subscribers')
                ->cascadeOnDelete();

            // Amount the subscriber says they paid
            $table->decimal('amount_paid', 10, 2);

            // GCash reference number
            $table->string('reference_number');

            // Uploaded GCash receipt/screenshot
            $table->string('payment_screenshot');

            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
            ])->default('pending');

            // Optional admin review information
            $table->text('admin_note')->nullable();

            // When admin reviewed the submission
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_submissions');
    }
};