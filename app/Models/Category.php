<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
    ];

    /**
     * Auto-generated attributes
     */
    protected $guarded = [
        'id',
    ];

    /**
     * The products that belong to the category.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'category_product',
            'category_id', 'product_id');
    }

//    public $timestamps = false;

}
