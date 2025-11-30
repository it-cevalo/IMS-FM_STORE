<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TProductTransferD extends Model
{
    use HasFactory;
    protected $table   = 't_product_trf_d';
    protected $guarded = [];
    
    public function product()
    {
    	return $this->hasOne('App\Models\Mproduct', 'id', 'id_product');
    }
    
    public function product_trf_h()
    {
    	return $this->hasOne('App\Models\TProductTransferH', 'id', 'id_product_trf_h');
    }
}
