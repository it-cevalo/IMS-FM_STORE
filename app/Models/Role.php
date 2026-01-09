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
    /**
     * Apps access (IMS, WMS, dll)
     */
    public function apps()
    {
        return $this->belongsToMany(
            App::class,
            'role_apps',
            'role_id',
            'app_code',
            'id',
            'app_code'
        )->withPivot('is_enabled');
    }
}