<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users_table', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('password');
            $table->string('role')->default('user'); // e.g. admin, user
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes(); // adds deleted_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};