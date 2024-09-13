<?php

namespace App\Models\Sales;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class OrderReturn extends Model
{
    use HasFactory, AsSource, Filterable;

    protected $guarded = ['id'];

    protected $casts = [
        'date' => 'datetime:d M Y',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'total_amount' => 'decimal:2',
    ];


    /**
     * @return BelongsTo
     */
    public function updatedBy() {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function order() {
        return $this->belongsTo(Order::class);
    }

    public function returnItems()
    {
        return $this->hasMany(OrderReturnItem::class);
    }

    // guna casts pun boleh tapi berguna pada input dalam page edit
    public function getDateAttribute($value)
    {
        return Carbon::parse($value)->format('d M Y');
    }
}
