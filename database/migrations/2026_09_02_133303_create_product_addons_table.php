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
        Schema::create('product_addons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();  
            $table->enum('addon_type', ['extra', 'topping', 'side'])->default('extra');
            $table->string('addon_name');
            $table->decimal('addon_price', 12, 2);
            $table->integer('addon_quantity')->default(1);
            $table->boolean('is_available')->default(true);
            $table->timestamps();       
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_addons');
    }
};
