<?php

use App\Models\Product;
use App\Models\Purchase;
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
        Schema::create('purchase_details', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Purchase::class)
                  ->constrained()
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();

            $table->foreignIdFor(Product::class)
                  ->constrained()
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();
            // $table->string('product_name');
            // $table->string('product_code');
            $table->integer('quantity');
            // $table->integer('price');
            $table->decimal('unit_price');
            $table->decimal('sub_total', 20);
            $table->decimal('product_discount_amount', 20)->default(0.00);
            $table->string('product_discount_type')->default('fixed');
            $table->decimal('product_tax_amount', 20)->default(0.00);
            $table->timestamps();

            // 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_details');
    }
};
