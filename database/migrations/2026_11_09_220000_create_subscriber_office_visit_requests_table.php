<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriber_office_visit_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscriber_id')->constrained()->cascadeOnDelete();
            $table->string('request_type');
            $table->date('visit_date');
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->index(['status', 'visit_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriber_office_visit_requests');
    }
};
