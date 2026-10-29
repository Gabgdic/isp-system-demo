<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscribers', function (Blueprint $table) {
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('surname')->nullable();
            $table->string('suffix', 10)->nullable();
            $table->string('barangay')->nullable();
            $table->string('municipality')->nullable();
            $table->string('province')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('subscribers', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'middle_name',
                'surname',
                'suffix',
                'barangay',
                'municipality',
                'province',
            ]);
        });
    }
};
