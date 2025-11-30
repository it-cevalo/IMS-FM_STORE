<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TPurchaseRequest extends Model
{
    use HasFactory;
     
    protected $table   = 'purchase_request'; 
    protected $guarded = [];

    public function warehouse()
    {
        return $this->hasOne('App\Models\MWarehouse', 'id', 'id_warehouse');
    }
    
    public function product()
    {
        return $this->hasOne('App\Models\Mproduct', 'id', 'id_product');
    }

    public function user_request()
    {
        return $this->hasOne('App\Models\User', 'id', 'id_user_request');
    }
    
    public function user_approved()
    {
        return $this->hasOne('App\Models\User', 'id', 'approve_by');
    }
}