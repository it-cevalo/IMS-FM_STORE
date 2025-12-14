<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MSku extends Model
{
    use HasFactory;
    protected $table   = 'msku';
    protected $primaryKey = 'kode';   // ⬅️ PENTING
    public $incrementing = false;      // ⬅️ BUKAN auto increment
    protected $keyType = 'string';     // ⬅️ karena kode string
    protected $guarded = [];
}