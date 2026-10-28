<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_submissions', function (Blueprint $table) {
            $table->string('payment_screenshot')->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('payment_submissions')
            ->whereNull('payment_screenshot')
            ->update(['payment_screenshot' => '']);

        Schema::table('payment_submissions', function (Blueprint $table) {
            $table->string('payment_screenshot')->nullable(false)->change();
        });
    }
};