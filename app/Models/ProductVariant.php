<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{

    public function variantName()
    {
        return $this->belongsTo('App\Models\Variant','variant_id','id');
    }

    public function product(){
        return $this->belongsTo('App\Models\Product');
    }
    
}
