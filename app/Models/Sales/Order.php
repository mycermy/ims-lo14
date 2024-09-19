<?php

namespace App\Models\Sales;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Order extends Model
{
    use HasFactory, AsSource, Filterable;

    public const STATUS_DRAFT = 'draft';            // in editing process.
    public const STATUS_PENDING = 'pending';        // waiting approved. notification to respected party. boleh tukar jadi draft
    public const STATUS_APPROVED = 'approved';      // akan update qty to stock. boleh delete sahaja
    public const STATUS_COMPLETED = 'completed';    // complete bila payment pun paid. boleh delete sahaja

    protected $guarded = ['id'];

    protected $casts = [
        'date' => 'datetime:d M Y',
        'created_at' => 'datetime:Y-m-d',
        'updated_at' => 'datetime:Y-m-d',
        'total_amount' => 'decimal:2',
    ];

    /**
     * @return BelongsTo
     */
    public function updatedBy() {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function orderPayments()
    {
        return $this->hasMany(OrderPayment::class);
    }

    public function returns()
    {
        return $this->hasMany(OrderReturn::class);
    }

    // guna casts pun boleh tapi berguna pada input dalam page edit
    public function getDateAttribute($value)
    {
        return Carbon::parse($value)->format('d M Y');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }
}
