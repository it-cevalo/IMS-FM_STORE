<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tpo extends Model
{
    use HasFactory, SoftDeletes;
    protected $table   = 'tpos';
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'id','id_cust','id_supplier','tgl_po','code_cust','nama_cust','code_spl','nama_spl','no_po','no_so','status_po','grand_total','reason_po', 'deleted_at','flag_approve','approve_by','approve_date','confirm_by','confirm_date'
    ];

    public function customer()
    {
    	return $this->hasOne('App\Models\MCustomer', 'id', 'id_cust');
    }
    
    public function supplier()
    {
    	return $this->hasOne('App\Models\MSupplier', 'id', 'id_supplier');
    }
    
    public function po_detail()
    {
        return $this->hasMany('App\Models\Tpo_Detail','id_po','id');
    }
}