<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tpayment extends Model
{
    use HasFactory,SoftDeletes;
    
    protected $table   = 'tpayment';
    protected $guarded = [];
    protected $dates = ['deleted_at'];

    
    public function customer()
    {
    	return $this->hasOne('App\Models\MCustomer', 'id', 'id_cust');
    }

    public function tax_inv()
    {
    	return $this->hasOne('App\Models\Tinv', 'id', 'id_tax_inv');
    }

    public function po()
    {
    	return $this->hasOne('App\Models\Tpo', 'id', 'id_po');
    }
    
    public function do()
    {
    	return $this->hasOne('App\Models\Tdo', 'id', 'id_do');
    }
}