<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mproduct extends Model
{
    use HasFactory;
    protected $table   = 'mproduct';
    protected $guarded = [];
    
    public function product_unit(){
        return $this->belongsTo('App\Models\MproductUnit','id_unit','id');
    }
    
    public function product_type(){
        return $this->belongsTo('App\Models\MproductType','id_type','id');
    }
}