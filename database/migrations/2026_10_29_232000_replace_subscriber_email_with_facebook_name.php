<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('subscribers')
            ->whereNull('facebook_name')
            ->whereNotNull('email')
            ->update(['facebook_name' => DB::raw('email')]);

        Schema::table('subscribers', function (Blueprint $table) {
            $table->dropColumn('email');
        });
    }

    public function down(): void
    {
        Schema::table('subscribers', function (Blueprint $table) {
            $table->string('email')->nullable();
        });

        DB::table('subscribers')
            ->whereNull('email')
            ->whereNotNull('facebook_name')
            ->update(['email' => DB::raw('facebook_name')]);
    }
};
