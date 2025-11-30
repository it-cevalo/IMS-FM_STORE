<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MCustomer extends Model
{
    use HasFactory;
    protected $table   = 'm_customers';
    protected $fillable = [
        'id','code_cust','nama_cust','npwp_cust','type_cust','address_cust','address_npwp','tgl_cust','phone','email'
    ];
}