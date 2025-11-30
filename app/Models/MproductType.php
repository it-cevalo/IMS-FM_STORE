<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MproductType extends Model
{
    use HasFactory;
    protected $table   = 'mproduct_type';
    protected $fillable = [
        'id','nama_tipe','created_at','updated_at'
    ];
    
    public function product(){
        return $this->hasOne('App\Models\Product','id','id_data');
    }
}
