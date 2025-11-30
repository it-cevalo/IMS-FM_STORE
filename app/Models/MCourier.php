<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MCourier extends Model
{
    use HasFactory;    
    protected $table   = 'm_couriers';
    protected $fillable = [
        'id','code_courier','nama_courier'
    ];
}
