<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = [
        'menu_id',
        'parent_menu_id',
        'name',
        'route_name',
        'icon',
        'type',
        'order_no',
        'is_active'
    ];

    public function children()
    {
        return $this->hasMany(Menu::class, 'parent_menu_id', 'menu_id')
                    ->orderBy('order_no');
    }
}