<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Address extends Model
{
    use HasFactory, SoftDeletes, AsSource, Filterable;

    public const TYPE_BILLING = 'billing';
    public const TYPE_SHIPPING = 'shipping';

    protected $guarded = ['id'];
    protected $perPage = 15;

    protected $casts = [
        'created_at' => 'datetime:Y-m-d',
        'updated_at' => 'datetime:Y-m-d',
    ];

    // ===================== ORM Definition START ===================== //

    public function contact()
    {
        return $this->belongsTo(Contact::class);
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

    // ===================== ORM Definition END ===================== //



    public function scopeByContact($query, $contact_id)
    {
        $query->where('contact_id',$contact_id);
    }

    public function scopeBillingAddress($query)
    {
        $query->where('type', Address::TYPE_BILLING);
    }

    public function scopeShippingAddress($query)
    {
        $query->where('type', Address::TYPE_SHIPPING);
    }

    public function scopeBillingAddressByContact($query, $contact_id)
    {
        $query->byContact($contact_id)->billingAddress();
    }

    //
}
