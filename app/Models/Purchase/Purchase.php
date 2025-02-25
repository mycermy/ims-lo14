<?php

namespace App\Models\Purchase;

use App\Models\Contact\Supplier;
use App\Models\User;
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
    protected $with = ['supplier'];

    protected $casts = [
        'date' => 'datetime:d M Y',
        'created_at' => 'datetime:d M Y',
        'updated_at' => 'datetime:d M Y',
        'total_amount' => 'decimal:2',
    ];

    public function purchaseDetails()
    {
        return $this->hasMany(PurchaseDetail::class, 'purchase_id', 'id');
    }

    public function purchasePayments()
    {
        return $this->hasMany(PurchasePayment::class, 'purchase_id', 'id');
    }

    public function returns()
    {
        return $this->hasMany(PurchaseReturn::class);
    }

    /**
     * @return BelongsTo
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (blank($model->reference)) {
                // $number = Purchase::max('id') + 1;
                // Count the number of service jobs created in the current year
                $currentYear = now()->year;
                $yearlyCount = self::whereYear('created_at', $currentYear)->count() + 1;
                $model->reference = make_reference_id('PD', $yearlyCount);
            }
        });
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

    // // 
    // public function getRouteKeyName()
    // {
    //     return 'reference';
    // }
    // 
}
