<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'title', 'sku', 'description'
    ];

    public function ProductVariantPrice()
    {
        return $this->hasMany('App\Models\ProductVariantPrice');
    }
}
