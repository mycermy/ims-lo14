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

    protected $guarded = ['id'];

    public function purchase() {
        return $this->belongsTo(Purchase::class, 'purchase_id', 'id');
    }

    // public function setAmountAttribute($value) {
    //     $this->attributes['amount'] = $value * 100;
    // }

    // public function getAmountAttribute($value) {
    //     return $value / 100;
    // }

    public function getDateAttribute($value) {
        return Carbon::parse($value)->format('d M Y');
    }

    public function scopeByPurchase($query) {
        return $query->where('purchase_id', request()->route('purchase_id'));
    }
}
