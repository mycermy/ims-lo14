<?php

namespace App\Models\Sales;

use App\Models\User;
use App\Orchid\Presenters\OrderPaymentPresenter;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class SalesRefund extends Model
{
    use HasFactory, AsSource, Filterable;

    public const PAYMENT_REFUND = 'refund';

    protected $guarded = ['id'];

    protected $casts = [
        'date' => 'datetime:d M Y',
        'amount' => 'decimal:2',
    ];


    /**
     * @return BelongsTo
     */
    public function updatedBy() {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function order() {
        return $this->belongsTo(SalesOrder::class);
    }

    // guna casts pun boleh tapi berguna pada input dalam page edit
    public function getDateAttribute($value)
    {
        return Carbon::parse($value)->format('d M Y');
    }

    public function presenter() {
        return new OrderPaymentPresenter($this);
    }
}
