<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Orchid\Filters\Filterable;
use Orchid\Filters\Types\Like;
use Orchid\Filters\Types\Where;
use Orchid\Screen\AsSource;

class Product extends Model
{
    use HasFactory, SoftDeletes, AsSource, Filterable;

    protected $guarded = ['id'];
    protected $perPage = 15;

    protected $casts = [
        'created_at' => 'datetime:Y-m-d',
        'updated_at' => 'datetime:Y-m-d',
        'sell_price' => 'decimal:2',
    ];

    /**
     * @var array
     */
    protected $allowedFilters = [
        'id'    => Where::class,
        'category_id'    => Where::class,
        'name'  => Like::class,
        'code'  => Like::class,
        'part_number'  => Like::class,
        'compatible'  => Like::class,
        'created_by'  => Where::class,
        'updated_by'  => Where::class,
    ];

    /**
     * @var array
     */
    protected $allowedSorts = [
        'id',
        'category_id',
        'name',
        'code',
        'part_number',
        'compatible',
        'created_by',
        'updated_by',
    ];

    // ===================== ORM Definition START ===================== //

    /**
     * @return BelongsTo
     */
    public function category(){
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsTo
     */
    public function createdBy(){
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return BelongsTo
     */
    public function updatedBy(){
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * @return BelongsToMany
     */
    // public function suppliers(){
    //     return $this->belongsToMany(Supplier::class);
    // }

    /**
     * @return BelongsToMany
     */
    // public function purchases(){
    //     return $this->belongsToMany(Purchase::class, 'purchase_details')->withPivot('quantity', 'unit_price', 'sub_total');
    // }

    /**
     * @return HasMany
     */
    // public function orderDetails(){
    //     return $this->hasMany(OrderDetail::class);
    // }

    // ===================== ORM Definition END ===================== //

    
}
