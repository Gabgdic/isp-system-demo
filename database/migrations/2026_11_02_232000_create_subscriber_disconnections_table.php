<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriber_disconnections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscriber_id')->constrained()->cascadeOnDelete();
            $table->timestamp('disconnected_at');
            $table->timestamps();

            $table->index(['disconnected_at', 'subscriber_id']);
        });

        DB::table('subscribers')
            ->whereNotNull('disconnected_at')
            ->orderBy('id')
            ->each(function (object $subscriber): void {
                DB::table('subscriber_disconnections')->insert([
                    'subscriber_id' => $subscriber->id,
                    'disconnected_at' => $subscriber->disconnected_at,
                    'created_at' => $subscriber->disconnected_at,
                    'updated_at' => $subscriber->disconnected_at,
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriber_disconnections');
    }
};
