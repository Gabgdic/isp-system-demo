<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->string('recipient_type')->default('system');
            $table->unsignedBigInteger('recipient_id')->nullable();
            $table->string('phone_number', 32);
            $table->string('notification_type')->default('general');
            $table->text('message');
            $table->string('status', 16)->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['recipient_type', 'recipient_id']);
            $table->index(['notification_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
    }
};
