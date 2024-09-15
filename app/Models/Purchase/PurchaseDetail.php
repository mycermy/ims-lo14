<?php

namespace App\Models\Purchase;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class PurchaseDetail extends Model
{
    use HasFactory, AsSource, Filterable;

    protected $guarded = ['id'];

    protected $with = ['product', 'purchase'];

    protected $casts = [
        'date' => 'datetime:d M Y',
        'unit_price' => 'decimal:2',
        'sub_total' => 'decimal:2',
        'product_tax_amount' => 'decimal:2',
    ];

    public function product() {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function purchase() {
        return $this->belongsTo(Purchase::class, 'purchase_id', 'id');
    }
}
