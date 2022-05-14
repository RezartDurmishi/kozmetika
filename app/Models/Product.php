<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'brand',
        'price',
        'description',
        'expirationDate',
        'image',
        'categoryId',
    ];

    /**
     * Auto-generated attributes
     */
    protected $guarded = [
        'id',
    ];

    /**
     * The categories that belong to the product.
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * Get the order that owns the product.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
