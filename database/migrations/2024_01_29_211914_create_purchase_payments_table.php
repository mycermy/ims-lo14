<?php

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
        Schema::create('purchase_payments', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', 20);
            $table->date('date');
            $table->string('reference');
            $table->string('payment_method');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreignIdFor(Purchase::class)
                  ->constrained()
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();
            // 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_payments');
    }
};
