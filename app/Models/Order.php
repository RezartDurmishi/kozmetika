<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'product_id', //hasMany
        'user_id',    //hasOne
        'parent_id', //hasOne
        'quantity',
        'orderDate',
        'address',
        'status',
        'userId',
        'total',
    ];

    /**
     * Auto-generated attributes
     */
    protected $guarded = [
        'id',
    ];

    /**
     * Get the user that owns the order.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the products for the order.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the parent for the order.
     */
    public function order()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

}
