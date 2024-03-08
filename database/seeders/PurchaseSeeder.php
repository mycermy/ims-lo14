<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PurchaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $purchase = \App\Models\Purchase::factory()->create();
        $totalAmount = 0;
        //
        for ($i=0; $i < fake()->randomDigitNotZero(); $i++) { 
            $qty = fake()->randomDigitNotZero();
            $unitPrice = fake()->numberBetween(50,300);
            $productId = \App\Models\Product::all()->random()->id;
            $product = \App\Models\Product::findOrFail($productId);
            // 
            # code...
            $purchaseDetail = new \App\Models\PurchaseDetail();
            $purchaseDetail->fill([
                'purchase_id' => $purchase->id,
                'product_id' => $productId,
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'sub_total' => $qty * $unitPrice,
            ]);
            $purchaseDetail->save();
            // 
            $product->update([
                'quantity' => $product->quantity + $qty
            ]);
            // 
            $totalAmount += $qty * $unitPrice;
        }
        // 
        $purchase->update([
            'total_amount' => $totalAmount
        ]);
    }
}
