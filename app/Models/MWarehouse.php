<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MWarehouse extends Model
{
    use HasFactory;
    protected $table   = 'm_warehouses';
    protected $fillable = [
        'id','code_wh','nama_wh','address','code_store','phone','email','id_store'
    ];

    public function store()
    {
    	return $this->hasOne('App\Models\MStore', 'id', 'id_store');
    }
}