<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class App extends Model
{
    use HasFactory;

    protected $table = 'apps';

    protected $fillable = [
        'app_code',
        'name',
        'is_active',
    ];

    /**
     * Relasi ke role (many to many)
     */
    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'role_apps',
            'app_code',   // foreign key di pivot (role_apps)
            'role_id',    // related key di pivot
            'app_code',   // local key di apps
            'id'          // local key di roles
        )->withPivot('is_enabled');
    }
}