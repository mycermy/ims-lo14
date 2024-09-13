<?php

namespace App\Models\Sales;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class OrderPayment extends Model
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


    /**
     * @return BelongsTo
     */
    public function updatedBy() {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function order() {
        return $this->belongsTo(Order::class);
    }

    // guna casts pun boleh tapi berguna pada input dalam page edit
    public function getDateAttribute($value)
    {
        return Carbon::parse($value)->format('d M Y');
    }
}
