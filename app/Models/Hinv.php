<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hinv extends Model
{
    use HasFactory;
    
    protected $table   = 'inv_his';
    protected $guarded = [];

    public function courier()
    {
    	return $this->hasOne('App\Models\MCourier', 'code_courier', 'code_courier');
    }

    
    public function customer()
    {
    	return $this->hasOne('App\Models\MCustomer', 'code_cust', 'code_cust');
    }
}
