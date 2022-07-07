<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Variant extends Model
{
    protected $fillable = [
        'title', 'description'
    ];

public function productVariant()
{
    return $this->hasMany('App\Models\ProductVariant')
    ->select('variant_id','variant')
    ->DISTINCT('variant');
    // return $this->hasMany('App\Models\ProductVariant')->select('id','variant')->distinct('variant');
}

}
