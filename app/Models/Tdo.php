<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tdo extends Model
{
    use HasFactory, SoftDeletes;
    protected $table   = 'tdos';
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'id','id_po','id_supplier','tgl_do','tgl_po','code_cust','nama_cust','no_po','no_so','no_do','shipping_via','status_lmpr_do','reason_do','flag_approve','approve_by','approve_date'
    ];

    public function po()
    {
    	return $this->hasOne('App\Models\Tpo', 'id', 'id_po');
    }
    
    public function supplier()
    {
    	return $this->hasOne('App\Models\MSupplier', 'id', 'id_supplier');
    }
}