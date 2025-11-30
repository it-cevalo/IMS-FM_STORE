<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TStockOpname extends Model
{
    use HasFactory;
    
    protected $table   = 't_stock_opname';
    protected $guarded = [];
    
    public function warehouse()
    {
    	return $this->hasOne('App\Models\MWarehouse', 'id', 'id_warehouse');
    }

    public function product()
    {
    	return $this->hasOne('App\Models\Mproduct', 'id', 'id_product');
    }
}
