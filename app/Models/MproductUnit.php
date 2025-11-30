<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MproductUnit extends Model
{
    use HasFactory;
    protected $table   = 'mproduct_unit';
    protected $fillable = [
        'id','nama_unit','created_at','updated_at'
    ];
    
    public function product(){
        return $this->hasOne('App\Models\Product','id','id_unit');
    }
}