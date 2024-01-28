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
        Schema::create('adjusted_products', function (Blueprint $table) {
            $table->id();
            $table->integer('quantity');
            $table->string('type');
            $table->timestamps();

            $table->foreignId('stock_adjustment_id')
                  ->constrained('stock_adjustments','id','dfk_adjprod_adj_id')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();
            $table->foreignId('product_id')
                  ->constrained('products','id','dfk_adjprod_prod_id')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adjusted_products');
    }
};
