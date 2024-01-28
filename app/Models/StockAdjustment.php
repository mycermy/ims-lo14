<?php

namespace App\Models;

use Carbon\Carbon;
use Orchid\Filters\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Orchid\Screen\AsSource;

class StockAdjustment extends Model
{
    use HasFactory, AsSource, Filterable;

    // protected $table = 'adjustments';
    protected $guarded = ['id'];
    protected $withCount = ['adjustedProducts'];
    protected $casts = ['date' => 'datetime:d M Y',];

    public function adjustedProducts() {
        return $this->hasMany(AdjustedProduct::class);
    }

    /**
     * @return BelongsTo
     */
    public function updatedBy() {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ===================== ORM Definition END ===================== //


    public static function boot() {
        parent::boot();

        static::creating(function ($model) {
            $number = StockAdjustment::max('id') + 1;
            $model->reference = make_reference_id('ADJ', $number);
        });
    }

    // guna casts
    // public function getDateAttribute($value) {
    //     return Carbon::parse($value)->format('d M Y');
    // }
}
