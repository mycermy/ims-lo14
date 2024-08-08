<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Purchase extends Model
{
    use HasFactory, AsSource, Filterable;

    public const STATUS_DRAFT = 'draft';            // in editing process.
    public const STATUS_PENDING = 'pending';        // waiting approved. notification to respected party. boleh tukar jadi draft
    public const STATUS_APPROVED = 'approved';      // akan update qty to stock. boleh delete sahaja
    public const STATUS_COMPLETED = 'completed';    // complete bila payment pun paid. boleh delete sahaja

    protected $guarded = ['id'];
    protected $perPage = 15;

    protected $casts = [
        'date' => 'datetime:d M Y',
        'created_at' => 'datetime:Y-m-d',
        'updated_at' => 'datetime:Y-m-d',
        'sell_price' => 'decimal:2',
    ];

    public function purchaseDetails() {
        return $this->hasMany(PurchaseDetail::class, 'purchase_id', 'id');
    }

    public function purchasePayments() {
        return $this->hasMany(PurchasePayment::class, 'purchase_id', 'id');
    }

    public static function boot() {
        parent::boot();

        static::creating(function ($model) {
            $number = Purchase::max('id') + 1;
            $model->reference = make_reference_id('PR', $number);
        });
    }

    // guna casts pun boleh tapi berguna pada input dalam page edit
    public function getDateAttribute($value) {
        return Carbon::parse($value)->format('d M Y');
    }

    public function scopeCompleted($query) {
        return $query->where('status', self::STATUS_COMPLETED);
    }
    // 
}
