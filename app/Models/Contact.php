<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Orchid\Filters\Filterable;
use Orchid\Filters\Types\Like;
use Orchid\Filters\Types\Where;
use Orchid\Filters\Types\WhereDateStartEnd;
use Orchid\Screen\AsSource;

class Contact extends Model
{
    use HasFactory, SoftDeletes, AsSource, Filterable;

    public const TYPE_CUSTOMER = 'customer';
    public const TYPE_VENDOR   = 'vendor';
    public const TYPE_EMPLOYEE = 'employee';

    protected $guarded = ['id'];
    protected $perPage = 15;

    protected $casts = [
        'enable_portal' => 'boolean',
        'created_at' => 'datetime:Y-m-d',
        'updated_at' => 'datetime:Y-m-d',
    ];

    /**
     * @var array
     */
    protected $allowedFilters = [
        'id'            => Where::class,
        'type'          => Like::class,
        'name'          => Like::class,
        'email'         => Like::class,
        'phone'         => Like::class,
        'tax_number'    => Like::class,
        'contact_name'  => Like::class,
        'company_name'  => Like::class,
        'website'       => Like::class,
        'created_by'    => Where::class,
        'updated_by'    => Where::class,
        'created_at'    => WhereDateStartEnd::class,
        'updated_at'    => WhereDateStartEnd::class,
        'deleted_at'    => WhereDateStartEnd::class,
    ];

    /**
     * @var array
     */
    protected $allowedSorts = [
        'id',
        'type',
        'name',
        'email',
        'phone',
        'tax_number',
        'contact_name',
        'company_name',
        'website',
        'created_by',
        'updated_by',
    ];

    // ===================== ORM Definition START ===================== //

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function billingAddress()
    {
        return $this->hasOne(Address::class)->where('type', Address::TYPE_BILLING);
    }

    public function shippingAddress()
    {
        return $this->hasOne(Address::class)->where('type', Address::TYPE_SHIPPING);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ===================== ORM Definition END ===================== //

    public function scopeCustomer($query)
    {
        $query->where('type', Contact::TYPE_CUSTOMER);
    }

    public function scopeSupplier($query)
    {
        $query->where('type', Contact::TYPE_VENDOR);
    }
}
