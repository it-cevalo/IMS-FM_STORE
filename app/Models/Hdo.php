<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hdo extends Model
{
    use HasFactory;
    protected $table   = 'do_his';

    protected $fillable = [
        'id','tgl_do','id_do','id_po','id_supplier','code_cust','nama_cust','no_do','reason_do'
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
