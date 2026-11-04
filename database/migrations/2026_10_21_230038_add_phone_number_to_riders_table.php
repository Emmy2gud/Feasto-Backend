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
        Schema::table('riders', function (Blueprint $table) {
            $table->string('phone_number')->nullable()->after('user_id');
            $table->string('email')->nullable();
            $table->string('emergency_name')->nullable();
            $table->string('emergency_phone')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('riders', function (Blueprint $table) {
            $table->dropColumn(['phone_number', 'email', 'emergency_name', 'emergency_phone']);
        });
    }
};
