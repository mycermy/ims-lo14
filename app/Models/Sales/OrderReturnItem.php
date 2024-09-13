<?php

namespace App\Models\Sales;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class OrderReturnItem extends Model
{
    use HasFactory, AsSource, Filterable;

    protected $guarded = ['id'];

    public function orderReturn() {
        return $this->belongsTo(OrderReturn::class);
    }

    public function orderItem() {
        return $this->belongsTo(OrderItem::class);
    }

    public function product() {
        return $this->belongsTo(Product::class);
    }
}
