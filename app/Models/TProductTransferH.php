<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TProductTransferH extends Model
{
    use HasFactory;
    
    protected $table   = 't_product_trf_h';
    protected $guarded = [];
    
    public function warehouse_from()
    {
    	return $this->hasOne('App\Models\MWarehouse', 'id', 'id_warehouse_from');
    }

    public function warehouse_to()
    {
    	return $this->hasOne('App\Models\MWarehouse', 'id', 'id_warehouse_to');
    }
}
