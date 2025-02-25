<?php

use App\Models\Contact\Customer;
use App\Models\Product\Product;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderItem;
use App\Models\Sales\SalesOrderReturn;
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
            // Schema::dropIfExists('sales_refunds');
            // Schema::dropIfExists('sales_payments');
            // Schema::dropIfExists('sales_order_return_items');
            // Schema::dropIfExists('sales_order_returns');
            // Schema::dropIfExists('cogs');
            // Schema::dropIfExists('sales_order_items');
            // Schema::dropIfExists('sales_orders');

            Schema::create('sales_orders', function (Blueprint $table) {
                  $table->id();
                  $table->date('date');
                  $table->string('reference')->unique();
                  $table->string('customer_name');
                  $table->decimal('tax_percentage')->default(0);
                  $table->decimal('tax_amount', 20)->default(0.00);
                  $table->decimal('discount_percentage')->default(0);
                  $table->decimal('discount_amount', 20)->default(0.00);
                  $table->json('discounts')->nullable();
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

                  $table->string('pdf_path')->nullable();
            });

            Schema::create('sales_order_items', function (Blueprint $table) {
                  $table->id();

                  $table->integer('quantity')->default(1);
                  $table->string('quantity_unit')->nullable(); // such as hours, days, number, ...

                  $table->integer('quantity_return')->default(0);
                  $table->decimal('unit_price');
                  $table->string('currency')->nullable();
                  $table->decimal('sub_total', 20);
                  /**
                   * Store taxes as an amount for each unit
                   * Total taxes will be computed by multiplying with quantity
                   * Ideal for complex situation where taxes are combined
                   **/
                  $table->decimal('unit_tax')->nullable();
                  
                  /**
                   * Store taxes as a percentage of the amount
                   * Ideal for most use common situation such as VAT in Europe
                   * Will be overriden by unit_tax if unit_tax is defined
                   **/
                  $table->decimal('tax_percentage', 5)->nullable();
                  $table->decimal('product_tax_amount', 20)->default(0.00);
                  
                  $table->decimal('unit_discount')->nullable();
                  $table->decimal('discount_percentage', 5)->nullable();
                  $table->decimal('product_discount_amount', 20)->default(0.00);
                  $table->string('product_discount_type')->default('fixed');

                  $table->timestamps();

                  $table->foreignIdFor(SalesOrder::class)
                        ->constrained()
                        ->cascadeOnUpdate()
                        ->restrictOnDelete();

                  $table->foreignIdFor(Product::class)
                        ->constrained()
                        ->cascadeOnUpdate()
                        ->cascadeOnDelete();

                  $table->string('product_name');
            });

            Schema::create('cogs', function (Blueprint $table) {
                $table->id();
                $table->decimal('cogs_amount', 20)->default(0.00);
                $table->string('cogs_method')->default('FIFO'); // e.g., 'FIFO', 'LIFO'
                $table->timestamps();

                $table->foreignIdFor(SalesOrderItem::class)// One COGS record per sales order item
                      ->constrained()
                      ->cascadeOnUpdate()
                      ->restrictOnDelete();

                $table->foreignIdFor(Product::class)
                      ->constrained()
                      ->cascadeOnUpdate()
                      ->cascadeOnDelete();
            });

            Schema::create('sales_order_returns', function (Blueprint $table) {
                  $table->id();
                  $table->string('reference')->unique();
                  $table->decimal('total_amount', 20)->default(0.00);
                  $table->text('reason')->nullable();
                  $table->timestamps();

                  $table->foreignIdFor(SalesOrder::class)
                        ->constrained()
                        ->cascadeOnUpdate()
                        ->restrictOnDelete();

                  $table->foreignIdFor(User::class, 'updated_by')->default(1)
                        ->constrained('users')
                        ->cascadeOnUpdate()
                        ->cascadeOnDelete();
            });

            Schema::create('sales_order_return_items', function (Blueprint $table) {
                  $table->id();
                  $table->integer('quantity');
                  $table->decimal('sub_total', 20)->default(0.00);
                  $table->timestamps();

                  $table->foreignIdFor(SalesOrderReturn::class)
                        ->constrained()
                        ->cascadeOnUpdate()
                        ->restrictOnDelete();

                  $table->foreignIdFor(SalesOrderItem::class)
                        ->constrained()
                        ->cascadeOnUpdate()
                        ->cascadeOnDelete();

                  $table->foreignIdFor(Product::class)
                        ->constrained()
                        ->cascadeOnUpdate()
                        ->cascadeOnDelete();
            });

            Schema::create('sales_payments', function (Blueprint $table) {
                  $table->id();
                  $table->date('date');
                  $table->string('reference')->unique();
                  $table->decimal('amount', 20)->default(0.00);
                  $table->string('payment_method');
                  $table->text('note')->nullable();
                  $table->timestamps();

                  $table->foreignIdFor(SalesOrder::class)
                        ->constrained()
                        ->cascadeOnUpdate()
                        ->restrictOnDelete();

                  $table->foreignIdFor(User::class, 'updated_by')->default(1)
                        ->constrained('users')
                        ->cascadeOnUpdate()
                        ->cascadeOnDelete();
            });

            Schema::create('sales_refunds', function (Blueprint $table) {
                  $table->id();
                  $table->date('date');
                  $table->string('reference')->unique();
                  $table->decimal('amount', 20)->default(0.00);
                  $table->text('reason')->nullable();
                  $table->timestamps();
                  // $table->unsignedBigInteger('transaction_id');
                  // $table->foreign('transaction_id')->references('transaction_id')->on('transactions');

                  $table->foreignIdFor(SalesOrder::class)
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
            Schema::dropIfExists('sales_refunds');
            Schema::dropIfExists('sales_payments');
            Schema::dropIfExists('sales_order_return_items');
            Schema::dropIfExists('sales_order_returns');
            Schema::dropIfExists('cogs');
            Schema::dropIfExists('sales_order_items');
            Schema::dropIfExists('sales_orders');
      }
};
