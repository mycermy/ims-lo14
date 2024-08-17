<?php

use App\Models\Contact;
use App\Models\User;
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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('reference');
            $table->string('supplier_name');
            $table->decimal('tax_percentage')->default(0);
            $table->decimal('tax_amount', 20)->default(0.00);
            $table->decimal('discount_percentage')->default(0);
            $table->decimal('discount_amount', 20)->default(0.00);
            $table->decimal('shipping_amount', 20)->default(0.00);
            $table->decimal('total_amount', 20)->default(0.00);
            $table->decimal('total_amount_return', 20)->default(0.00);
            $table->decimal('paid_amount', 20)->default(0.00);
            $table->decimal('due_amount', 20)->default(0.00);
            $table->string('status')->default('draf');
            $table->string('payment_status')->default('unpaid');
            // $table->string('payment_method')->default('others');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreignIdFor(Contact::class, 'supplier_id')
                  ->constrained('contacts')
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();

            $table->foreignIdFor(User::class, 'updated_by')->default(1)
                  ->constrained('users')
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
        Schema::dropIfExists('purchases');
    }
};
