<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class PurchaseReturn extends Model
{
    use HasFactory, AsSource, Filterable;

    protected $guarded = ['id'];

    protected $casts = [
        'date' => 'datetime:d M Y',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'total_amount' => 'decimal:2',
    ];
    

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function returnItems()
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }

    /**
     * @return BelongsTo
     */
    public function updatedBy() {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
