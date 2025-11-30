<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TPurchaseRequestD extends Model
{
    use HasFactory;
    
    protected $table   = 'purchase_request_detail'; 
    protected $guarded = [];
    
    public function product()
    {
        return $this->hasOne('App\Models\Mproduct', 'id', 'id_product');
    }
}