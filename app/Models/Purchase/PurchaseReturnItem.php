<?php

namespace App\Models\Purchase;

use App\Models\Product\Product;
use App\Traits\HasLocalDates;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class PurchaseReturnItem extends Model
{
    use HasFactory, AsSource, Filterable, HasLocalDates;

    protected $guarded = ['id'];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function purchaseReturn()
    {
        return $this->belongsTo(PurchaseReturn::class);
    }

    public function purchaseDetail()
    {
        return $this->belongsTo(PurchaseDetail::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
