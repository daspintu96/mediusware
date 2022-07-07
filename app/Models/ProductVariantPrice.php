<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariantPrice extends Model
{
    public function productVariant(){
        return $this->hasMany('App\Models\ProductVariant','product_id','product_id');
    }
    public function pvone(){
        return $this->belongsTo('App\Models\ProductVariant','product_variant_one','id')->select('id','variant');
    }
    public function pvtwo(){
        return $this->belongsTo('App\Models\ProductVariant','product_variant_two','id');
    }
    public function pvthree(){
        return $this->belongsTo('App\Models\ProductVariant','product_variant_three','id');
    }


}
