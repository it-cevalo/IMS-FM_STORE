<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TInvoiceD extends Model
{
    use HasFactory;
    protected $table   = 't_invoice_d';
    protected $guarded = [];
    
    public function product()
    {
    	return $this->hasOne('App\Models\Mproduct', 'id', 'id_product');
    }
    
    public function invoice_h()
    {
    	return $this->hasOne('App\Models\TInvoiceH', 'id', 'hid');
    }
}