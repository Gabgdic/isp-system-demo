<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('billing_records', function (Blueprint $table) {
            $table->unique(
                [
                    'subscriber_id',
                    'billing_year',
                    'billing_month',
                ],
                'billing_records_subscriber_year_month_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('billing_records', function (Blueprint $table) {
            $table->dropUnique(
                'billing_records_subscriber_year_month_unique'
            );
        });
    }
};