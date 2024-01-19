<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Orchid\Filters\Filterable;
use Orchid\Filters\Types\Like;
use Orchid\Filters\Types\Where;
use Orchid\Screen\AsSource;

class Category extends Model
{
    use HasFactory, SoftDeletes, AsSource, Filterable;

    protected $guarded = ['id'];
    protected $perPage = 15;

    /**
     * @var array
     */
    protected $allowedFilters = [
        'id'    => Where::class,
        'parent_id'    => Where::class,
        'name'  => Like::class,
        'slug'  => Like::class,
        'created_by'  => Where::class,
        'updated_by'  => Where::class,
    ];

    /**
     * @var array
     */
    protected $allowedSorts = [
        'id',
        'parent_id',
        'name',
        'slug',
        'created_by',
        'updated_by',
    ];

    // ===================== ORM Definition START ===================== //

    /**
     * @return BelongsTo
     */
    public function parent() {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * @return HasMany
     */
    public function children(){
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * @return BelongsTo
     */
    public function createdBy() {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return BelongsTo
     */
    public function updatedBy() {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ===================== ORM Definition END ===================== //

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
