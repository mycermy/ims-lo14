<?php

namespace App\Models\Product;

use Carbon\Carbon;
use App\Models\User;
use Orchid\Screen\AsSource;
use Orchid\Filters\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockAdjustment extends Model
{
    use HasFactory, AsSource, Filterable;

    public const TYPE_ADD = 'add';
    public const TYPE_SUB = 'sub';

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

    // guna casts pun boleh tapi berguna pada input dalam page edit
    public function getDateAttribute($value) {
        return Carbon::parse($value)->format('d M Y');
    }

}
