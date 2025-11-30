<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hpayment extends Model
{
    use HasFactory;
    protected $table   = 'tpayment_his';
    protected $guarded = [];

    public function payment(){
        return $this->hasOne('App\Models\Tpayment', 'id','id_payment');
    }
}
