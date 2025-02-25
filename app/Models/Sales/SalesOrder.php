<?php

namespace App\Models\Sales;

use App\Finller\Invoice\FormatForPdf;
use App\Models\Contact\Customer;
use App\Models\User;
use Brick\Money\Money;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class SalesOrder extends Model
{
    use HasFactory, AsSource, Filterable;
    use FormatForPdf;

    public const STATUS_DRAFT = 'draft';            // in editing process.
    public const STATUS_PENDING = 'pending';        // waiting approved. notification to respected party. boleh tukar jadi draft
    public const STATUS_APPROVED = 'approved';      // akan update qty to stock. boleh delete sahaja
    public const STATUS_COMPLETED = 'completed';    // complete bila payment pun paid. boleh delete sahaja

    protected $guarded = ['id'];
    protected $with = ['customer'];

    protected $casts = [
        'date' => 'datetime:d M Y',
        'created_at' => 'datetime:d M Y',
        'updated_at' => 'datetime:d M Y',
        'total_amount' => 'decimal:2',
    ];

    /**
     * @return BelongsTo
     */
    public function updatedBy() {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function orderItems()
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function orderPayments()
    {
        return $this->hasMany(SalesPayment::class);
    }

    public function returns()
    {
        return $this->hasMany(SalesOrderReturn::class);
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
                $model->reference = make_reference_id('OD', $yearlyCount);
            }
        });
    }

    // guna casts pun boleh tapi berguna pada input dalam page edit
    public function getDateAttribute($value)
    {
        return Carbon::parse($value)->format('d M Y');
    }

    public function getCurrency(): string
    {
        return config('invoices.default_currency');
    }

    public function totalAmount(): Money
    {
        return Money::of($this->total_amount, $this->getCurrency());
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }
}
