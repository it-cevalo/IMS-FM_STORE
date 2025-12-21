<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tdo_Detail extends Model
{
    use HasFactory;

    protected $table   = 'tdo_detail';
    protected $guarded = [];
    
    public function do()
    {
    	return $this->hasOne('App\Models\Tdo', 'id', 'id_do');
    }
}