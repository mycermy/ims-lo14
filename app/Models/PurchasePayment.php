<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class PurchasePayment extends Model
{
    use HasFactory, AsSource, Filterable;

    public const STATUS_OVERDUE = 'overdue';
    public const STATUS_UNPAID = 'unpaid';
    public const STATUS_PARTIALLY_PAID = 'partially_paid';
    public const STATUS_PAID = 'paid';
    public const STATUS_OVERPAID = 'overpaid';

    public const PAYMENT_CASH = 'cash';
    public const PAYMENT_QRCODE = 'qr-code';
    public const PAYMENT_BANK_TRANSFER = 'bank transfer';
    public const PAYMENT_CREDIT_CARD = 'credit card';
    public const PAYMENT_CHEQUE = 'cheque';
    public const PAYMENT_OTHER = 'other';
    public const PAYMENT_REFUND = 'refund';

    protected $guarded = ['id'];

    protected $casts = [
        'date' => 'datetime:d M Y',
        'amount' => 'decimal:2',
    ];

    public function purchase() {
        return $this->belongsTo(Purchase::class, 'purchase_id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function updatedBy() {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // public function setAmountAttribute($value) {
    //     $this->attributes['amount'] = $value * 100;
    // }

    // public function getAmountAttribute($value) {
    //     return $value / 100;
    // }

    // public static function boot() {
    //     parent::boot();

    //     static::creating(function ($model) {
    //         $number = PurchasePayment::max('id') + 1;
    //         $model->reference = make_reference_id('PV', $number);
    //     });
    // }

    public function getDateAttribute($value) {
        return Carbon::parse($value)->format('d M Y');
    }

    public function scopeByPurchase($query) {
        return $query->where('purchase_id', request()->route('purchase_id'));
    }
}
