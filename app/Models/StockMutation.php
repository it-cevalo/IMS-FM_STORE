<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMutation extends Model
{
    use HasFactory;

    protected $table   = 'stock_mutation';
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