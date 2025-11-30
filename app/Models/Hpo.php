<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hpo extends Model
{
    use HasFactory;
    protected $table   = 'po_his';

    protected $fillable = [
        'id','tgl_po','id_po','id_cust','code_cust','nama_cust','no_po','no_so','reason_po','status_po'
    ];

    public function customer()
    {
    	return $this->hasOne('App\Models\MCustomer', 'id', 'id_cust');
    }
    
}
