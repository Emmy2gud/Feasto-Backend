<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $columnsToDrop = [
                'currency',
                'tax',
                'delivery_status',
                'pickup_address',
                'pickup_latitude',
                'pickup_longitude',
            ];

            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'product_id')) {
                $table->dropConstrainedForeignId('product_id');
            }

            $columnsToDrop = [
                'product_name',
                'unit_price',
                'total',
            ];

            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('order_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('order_item_addons', function (Blueprint $table) {
            $columnsToDrop = [
                'addon_name',
                'addon_price',
                'addon_quantity',
            ];

            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('order_item_addons', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_item_addons', function (Blueprint $table) {
            if (! Schema::hasColumn('order_item_addons', 'addon_name')) {
                $table->string('addon_name')->nullable();
            }

            if (! Schema::hasColumn('order_item_addons', 'addon_price')) {
                $table->decimal('addon_price', 12, 2)->nullable();
            }

            if (! Schema::hasColumn('order_item_addons', 'addon_quantity')) {
                $table->unsignedInteger('addon_quantity')->default(1);
            }
        });

        Schema::table('order_items', function (Blueprint $table) {
            if (! Schema::hasColumn('order_items', 'product_id')) {
                $table->foreignId('product_id')->nullable()->constrained();
            }

            if (! Schema::hasColumn('order_items', 'product_name')) {
                $table->string('product_name')->nullable();
            }

            if (! Schema::hasColumn('order_items', 'unit_price')) {
                $table->decimal('unit_price', 12, 2)->nullable();
            }

            if (! Schema::hasColumn('order_items', 'total')) {
                $table->decimal('total', 12, 2)->nullable();
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'currency')) {
                $table->string('currency', 3)->default('USD');
            }

            if (! Schema::hasColumn('orders', 'tax')) {
                $table->decimal('tax', 12, 2)->default(0);
            }

            if (! Schema::hasColumn('orders', 'delivery_status')) {
                $table->string('delivery_status')->default('pending');
            }

            if (! Schema::hasColumn('orders', 'pickup_address')) {
                $table->text('pickup_address')->nullable();
            }

            if (! Schema::hasColumn('orders', 'pickup_latitude')) {
                $table->decimal('pickup_latitude', 10, 8)->nullable();
            }

            if (! Schema::hasColumn('orders', 'pickup_longitude')) {
                $table->decimal('pickup_longitude', 11, 8)->nullable();
            }
        });
    }
};
