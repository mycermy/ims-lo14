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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            
            $table->timestamps();
            $table->softDeletes();

            $table->foreignId('parent_id')->nullable()
                  ->constrained('categories','id','dfk_cat_par_id')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();
            $table->foreignId('created_by')->default(1)
                  ->constrained('users','id','dfk_cat_cre_by')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()
                  ->constrained('users','id','dfk_cat_upd_by')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
