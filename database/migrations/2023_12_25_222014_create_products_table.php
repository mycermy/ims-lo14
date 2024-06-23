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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->integer('quantity')->default(0);
            $table->decimal('buy_price')->default(0.00)->comment('Buying Price');
            $table->decimal('sell_price')->default(0.00)->comment('Selling Price');
            $table->integer('quantity_alert')->default(0);
            $table->string('part_number')->nullable();
            $table->text('compatible')->nullable();
            $table->string('model')->nullable();
            $table->boolean('is_active')->nullable()->default(true);
            $table->boolean('is_nonstock')->nullable();
            $table->boolean('is_noninventory')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            $table->foreignId('category_id')->default(1)
                  ->constrained('categories','id','dfk_prod_cat_id')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();
            $table->foreignId('created_by')->default(1)
                  ->constrained('users','id','dfk_prod_cre_by')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()
                  ->constrained('users','id','dfk_prod_upd_by')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
