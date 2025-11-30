<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MproductStock extends Model
{
    use HasFactory;
    protected $table   = 'mproduct_stock';
    protected $guarded = [];
    
    public function warehouse()
    {
    	return $this->hasOne('App\Models\MWarehouse', 'id_warehouse', 'id');
    }

    public function product()
    {
    	return $this->hasOne('App\Models\Mproduct', 'id_product', 'id');
    }
}