<?php

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sales\Order;
use App\Models\Sales\OrderItem;
use App\Models\Sales\OrderReturn;
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
            Schema::create('orders', function (Blueprint $table) {
                  $table->id();
                  $table->date('date');
                  $table->string('reference');
                  $table->string('customer_name');
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
                  $table->string('payment_method')->default('others');
                  $table->text('note')->nullable();
                  $table->timestamps();

                  $table->foreignIdFor(Customer::class)
                        ->constrained('contacts')
                        ->cascadeOnUpdate()
                        ->restrictOnDelete();

                  $table->foreignIdFor(User::class, 'updated_by')->default(1)
                        ->constrained('users')
                        ->cascadeOnUpdate()
                        ->cascadeOnDelete();
            });

            Schema::create('order_items', function (Blueprint $table) {
                  $table->id();
                  // $table->string('product_name');
                  $table->integer('quantity');
                  $table->integer('quantity_return')->default(0);
                  $table->decimal('unit_price');
                  $table->decimal('sub_total', 20);
                  $table->decimal('product_discount_amount', 20)->default(0.00);
                  $table->string('product_discount_type')->default('fixed');
                  $table->decimal('product_tax_amount', 20)->default(0.00);
                  $table->timestamps();

                  $table->foreignIdFor(Order::class)
                        ->constrained()
                        ->cascadeOnUpdate()
                        ->restrictOnDelete();

                  $table->foreignIdFor(Product::class)
                        ->constrained()
                        ->cascadeOnUpdate()
                        ->cascadeOnDelete();
            });

            Schema::create('order_returns', function (Blueprint $table) {
                  $table->id();
                  $table->string('reference');
                  $table->decimal('total_amount', 20);
                  $table->text('reason')->nullable();
                  $table->timestamps();

                  $table->foreignIdFor(Order::class)
                        ->constrained()
                        ->cascadeOnUpdate()
                        ->restrictOnDelete();

                  $table->foreignIdFor(User::class, 'updated_by')->default(1)
                        ->constrained('users')
                        ->cascadeOnUpdate()
                        ->cascadeOnDelete();
            });

            Schema::create('order_return_items', function (Blueprint $table) {
                  $table->id();
                  $table->integer('quantity');
                  $table->decimal('sub_total', 20);
                  $table->timestamps();

                  $table->foreignIdFor(OrderReturn::class)
                        ->constrained()
                        ->cascadeOnUpdate()
                        ->restrictOnDelete();

                  $table->foreignIdFor(OrderItem::class)
                        ->constrained()
                        ->cascadeOnUpdate()
                        ->cascadeOnDelete();

                  $table->foreignIdFor(Product::class)
                        ->constrained()
                        ->cascadeOnUpdate()
                        ->cascadeOnDelete();
            });

            Schema::create('order_payments', function (Blueprint $table) {
                  $table->id();
                  $table->date('date');
                  $table->string('reference');
                  $table->decimal('amount', 20);
                  $table->string('payment_method');
                  $table->text('note')->nullable();
                  $table->timestamps();

                  $table->foreignIdFor(Order::class)
                        ->constrained()
                        ->cascadeOnUpdate()
                        ->restrictOnDelete();

                  $table->foreignIdFor(User::class, 'updated_by')->default(1)
                        ->constrained('users')
                        ->cascadeOnUpdate()
                        ->cascadeOnDelete();
            });
      }

      /**
       * Reverse the migrations.
       */
      public function down(): void
      {
            Schema::dropIfExists('order_payments');
            Schema::dropIfExists('order_return_items');
            Schema::dropIfExists('order_returns');
            Schema::dropIfExists('order_items');
            Schema::dropIfExists('orders');
      }
};
