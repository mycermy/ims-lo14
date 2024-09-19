<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class AdjustedProduct extends Model
{
    use HasFactory, AsSource, Filterable;

    protected $guarded = ['id'];

    protected $with = ['product'];

    public function adjustment() {
        return $this->belongsTo(StockAdjustment::class, 'stock_adjustment_id');
    }

    public function product() {
        return $this->belongsTo(Product::class);
    }
}
