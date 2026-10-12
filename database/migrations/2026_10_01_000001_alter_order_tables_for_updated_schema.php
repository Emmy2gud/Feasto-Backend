<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'idempotency_key')) {
                $table->string('idempotency_key')->unique()->nullable()->after('order_number');
            }

            if (! Schema::hasColumn('orders', 'tip')) {
                $table->decimal('tip', 10, 2)->default(0)->after('service_fee');
            }

            if (! Schema::hasColumn('orders', 'promo_code')) {
                $table->string('promo_code')->nullable()->after('discount');
            }

            if (! Schema::hasColumn('orders', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('payment_status');
            }

            if (! Schema::hasColumn('orders', 'distance_km')) {
                $table->decimal('distance_km', 8, 2)->nullable()->after('delivery_longitude');
            }

            if (! Schema::hasColumn('orders', 'dropoff_preference')) {
                $table->string('dropoff_preference')->nullable()->after('distance_km');
            }

            if (! Schema::hasColumn('orders', 'rider_assigned_at')) {
                $table->timestamp('rider_assigned_at')->nullable()->after('accepted_at');
            }
        });

        Schema::table('order_items', function (Blueprint $table) {
            if (! Schema::hasColumn('order_items', 'dish_id')) {
                $table->foreignId('dish_id')->nullable()->after('order_id');
            }

            if (! Schema::hasColumn('order_items', 'name')) {
                $table->string('name')->after('dish_id');
            }

            if (! Schema::hasColumn('order_items', 'price')) {
                $table->decimal('price', 10, 2)->after('name');
            }

            if (! Schema::hasColumn('order_items', 'special_instructions')) {
                $table->text('special_instructions')->nullable()->after('quantity');
            }
        });

        Schema::table('order_item_addons', function (Blueprint $table) {
            if (! Schema::hasColumn('order_item_addons', 'name')) {
                $table->string('name')->after('order_item_id');
            }

            if (! Schema::hasColumn('order_item_addons', 'price')) {
                $table->decimal('price', 10, 2)->after('name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_item_addons', function (Blueprint $table) {
            if (Schema::hasColumn('order_item_addons', 'price')) {
                $table->dropColumn('price');
            }

            if (Schema::hasColumn('order_item_addons', 'name')) {
                $table->dropColumn('name');
            }
        });

        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'special_instructions')) {
                $table->dropColumn('special_instructions');
            }

            if (Schema::hasColumn('order_items', 'price')) {
                $table->dropColumn('price');
            }

            if (Schema::hasColumn('order_items', 'name')) {
                $table->dropColumn('name');
            }

            if (Schema::hasColumn('order_items', 'dish_id')) {
                $table->dropColumn('dish_id');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'rider_assigned_at')) {
                $table->dropColumn('rider_assigned_at');
            }

            if (Schema::hasColumn('orders', 'dropoff_preference')) {
                $table->dropColumn('dropoff_preference');
            }

            if (Schema::hasColumn('orders', 'distance_km')) {
                $table->dropColumn('distance_km');
            }

            if (Schema::hasColumn('orders', 'payment_method')) {
                $table->dropColumn('payment_method');
            }

            if (Schema::hasColumn('orders', 'promo_code')) {
                $table->dropColumn('promo_code');
            }

            if (Schema::hasColumn('orders', 'tip')) {
                $table->dropColumn('tip');
            }

            if (Schema::hasColumn('orders', 'idempotency_key')) {
                $table->dropColumn('idempotency_key');
            }
        });
    }
};
