<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TDoTransferH extends Model
{
    use HasFactory;
    
    protected $table   = 'do_transfer_h';
    protected $guarded = [];
    
    public function warehouse_from()
    {
    	return $this->hasOne('App\Models\MWarehouse', 'id', 'id_warehouse_from');
    }

    public function warehouse_to()
    {
    	return $this->hasOne('App\Models\MWarehouse', 'id', 'id_warehouse_to');
    }
}