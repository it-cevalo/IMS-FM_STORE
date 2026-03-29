<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TdoScanStaging extends Model
{
    protected $table = 'tdo_scan_staging';
    protected $guarded = [];
    public $timestamps = false;

    public function product()
    {
        return $this->belongsTo(Mproduct::class, 'id_product', 'id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
