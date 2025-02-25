<?php

namespace App\Models\Sales;

use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class SalesOrderReturnItem extends Model
{
    use HasFactory, AsSource, Filterable;

    protected $guarded = ['id'];

    public function orderReturn() {
        return $this->belongsTo(SalesOrderReturn::class);
    }

    public function orderItem() {
        return $this->belongsTo(SalesOrderItem::class);
    }

    public function product() {
        return $this->belongsTo(Product::class);
    }
}
