<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->string('idempotency_key')->unique()->nullable();

            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rider_id')->nullable();

            $table->decimal('subtotal', 10, 2);
            $table->decimal('delivery_fee', 10, 2);
            $table->decimal('service_fee', 10, 2);
            $table->decimal('tip', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->string('promo_code')->nullable();
            $table->decimal('total', 10, 2);

            $table->string('status')->default('pending');
            $table->string('payment_status')->default('pending');
            $table->string('payment_method')->nullable();

            $table->text('delivery_address');
            $table->decimal('delivery_latitude', 10, 7);
            $table->decimal('delivery_longitude', 10, 7);
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->string('dropoff_preference')->nullable();
            $table->text('notes')->nullable();

            $table->timestamp('placed_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rider_assigned_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
