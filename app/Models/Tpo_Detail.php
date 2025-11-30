<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tpo_Detail extends Model
{
    use HasFactory;

    protected $table   = 'tpo_detail';
    protected $guarded = [];
    
    public function po()
    {
    	return $this->hasOne('App\Models\Tpo', 'id', 'id_po');
    }
}
