<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->string('soa_location')->nullable();
            $table->string('authorized_signatory')->nullable();
            $table->string('signatory_title')->nullable();
            $table->string('authorized_signature')->nullable();
            $table->text('soa_body_statement')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->dropColumn([
                'soa_location',
                'authorized_signatory',
                'signatory_title',
                'authorized_signature',
                'soa_body_statement',
            ]);
        });
    }
};