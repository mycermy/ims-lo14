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
            $table->foreignId('created_by')->nullable()
                  ->constrained('users','id','dfk_cat_cre_by')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()
                  ->constrained('users','id','dfk_cat_upd_by')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()
                  ->constrained('categories','id','dfk_cat_par_id')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();
                  
            $table->string('name');
            $table->string('slug')->unique();

            $table->timestamps();
            $table->softDeletes();
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
