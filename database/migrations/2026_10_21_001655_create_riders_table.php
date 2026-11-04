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
        Schema::create('riders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete();

            // vehicle
            $table->string('vehicle_type')->nullable();   // bike, bicycle, car
            $table->string('plate_number')->nullable();

            $table->json('documents')->nullable();
            $table->timestamp('nin_verified_at')->nullable();
            $table->string('approval_status')->default('pending'); // pending, approved, rejected, suspended
            $table->string('rejection_reason')->nullable();
            $table->timestamp('approved_at')->nullable();

            // availability
            $table->boolean('is_online')->default(false);

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamp('location_updated_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['approval_status', 'is_online', 'latitude', 'longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riders');
    }
};
