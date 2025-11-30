<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MStore extends Model
{
    use HasFactory;
    protected $table   = 'm_stores';
    protected $fillable = [
        'id','code_store','nama_store','address','phone','email'
    ];
    
}