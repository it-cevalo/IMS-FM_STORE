<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tinv extends Model
{
    use HasFactory,SoftDeletes;
    
    protected $table   = 'tinvs';
    protected $guarded = [];
    protected $dates = ['deleted_at'];

    
    public function po()
    {
    	return $this->hasOne('App\Models\Tpo', 'id', 'id_po');
    }

    public function do()
    {
    	return $this->hasOne('App\Models\Tdo', 'id', 'id_do');
    }

    public function customer()
    {
    	return $this->hasOne('App\Models\MCustomer', 'code_cust', 'code_cust');
    }
    
    public function payment()
    {
        return $this->belongsTo('App\Models\Tpayment');
    }
}
