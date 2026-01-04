<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TProductOutbound extends Model
{
    use HasFactory;
    protected $table   = 'tproduct_outbound';
    protected $guarded = [];
    
    public function product()
    {
    	return $this->hasOne('App\Models\Mproduct', 'id', 'id_product');
    }
}