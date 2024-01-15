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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->string('type')->nullable();
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->string('address_street_1')->nullable();
            $table->string('address_street_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            // $table->integer('country_id')->unsigned()->nullable();
            // $table->foreign('country_id')->references('id')->on('countries');
            $table->string('zip')->nullable();
            $table->string('fax')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreignId('contact_id')->nullable()
                  ->constrained('contacts','id','dfk_adr_con_id')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();
            $table->foreignId('created_by')->default(1)
                  ->constrained('users','id','dfk_adr_cre_by')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();
            $table->foreignId('updated_by')->default(1)
                  ->constrained('users','id','dfk_adr_upd_by')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
