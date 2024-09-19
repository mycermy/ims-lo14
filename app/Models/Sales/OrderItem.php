<?php

namespace App\Models\Sales;

use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class OrderItem extends Model
{
    use HasFactory, AsSource, Filterable;

    protected $guarded = ['id'];

    protected $casts = [
        'date' => 'datetime:d M Y',
        'unit_price' => 'decimal:2',
        'sub_total' => 'decimal:2',
        'product_tax_amount' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
