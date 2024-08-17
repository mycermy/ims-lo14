<?php

use App\Models\Product;
use App\Models\PurchaseDetail;
use App\Models\PurchaseReturn;
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
        Schema::create('purchase_return_items', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(PurchaseReturn::class)
                  ->constrained()
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();

            $table->foreignIdFor(PurchaseDetail::class)
                  ->constrained()
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();

            $table->foreignIdFor(Product::class)
                  ->constrained()
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();

            $table->integer('quantity');
            $table->decimal('sub_total', 20, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_return_items');
    }
};
