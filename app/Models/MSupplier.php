<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MSupplier extends Model
{
    use HasFactory;
    protected $table   = 'm_suppliers';
    protected $fillable = [
        'id','code_spl','nama_spl','npwp_spl','address_spl','address_npwp','tgl_spl','phone','email','name_pic','phone_pic','email_pic'
    ];
}