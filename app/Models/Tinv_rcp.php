<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tinv_rcp extends Model
{
    use HasFactory,SoftDeletes;
    
    protected $table   = 'tinv_rcp';
    protected $guarded = [];
    protected $dates = ['deleted_at'];

    
    public function inv()
    {
    	return $this->hasOne('App\Models\Tinv', 'id', 'id_inv');
    }

    public function customer()
    {
    	return $this->hasOne('App\Models\MCustomer', 'code_cust', 'code_cust');
    }
    
    public function courier()
    {
    	return $this->hasOne('App\Models\MCourier', 'code_courier', 'code_courier');
    }
}