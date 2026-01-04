<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Role extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'guard_name'
    ];

    public function menus()
    {
        return $this->belongsToMany(
            Menu::class,
            'role_menus',
            'role_id',
            'menu_id',
            'id',
            'menu_id'
        )->withPivot('is_enabled');
    }
}