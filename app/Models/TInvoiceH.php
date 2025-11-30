<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TInvoiceH extends Model
{
    use HasFactory;
    protected $table   = 't_invoice_h';
    protected $guarded = [];
    
    public function customer()
    {
    	return $this->hasOne('App\Models\MCustomer', 'id', 'id_cust');
    }
    
    public function bank()
    {
    	return $this->hasOne('App\Models\MBank', 'id', 'id_bank');
    }
}