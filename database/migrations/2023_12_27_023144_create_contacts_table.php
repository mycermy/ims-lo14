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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('name');
            $table->string('email')->unique()->nullable();
            $table->string('phone')->nullable();
            $table->string('tax_number')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('company_name')->nullable();
            $table->string('website')->nullable();
            $table->boolean('enabled')->default(true);
            $table->string('reference')->nullable();
            $table->boolean('enable_portal')->default(false);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['type']);
            $table->unique(['type', 'email', 'deleted_at']);

            $table->foreignId('user_id')->nullable()
                  ->constrained('users','id','dfk_con_user_id')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();
            $table->foreignId('created_by')->default(1)
                  ->constrained('users','id','dfk_con_cre_by')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();
            $table->foreignId('updated_by')->default(1)
                  ->constrained('users','id','dfk_con_upd_by')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
