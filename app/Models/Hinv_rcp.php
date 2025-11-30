<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hinv_rcp extends Model
{
    use HasFactory;    
    protected $table   = 'inv_rcp_his';
    protected $guarded = [];

    public function courier()
    {
    	return $this->hasOne('App\Models\MCourier', 'code_courier', 'code_courier');
    }
}
