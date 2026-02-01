<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Permission
{
    /**
     * Cek hak akses role ke menu & aksi
     *
     * contoh:
     * Permission::can('MENU-0301', 'print')
     */
    public static function can(string $menuId, string $action): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        return DB::table('role_menus')
            ->where('role_id', $user->role_id)
            ->where('menu_id', $menuId)
            ->where("can_{$action}", 1)
            ->exists();
    }

    /**
     * Shortcut khusus view (read)
     */
    public static function view(string $menuId): bool
    {
        return self::can($menuId, 'view');
    }

    /**
     * Shortcut khusus approve
     */
    public static function approve(string $menuId): bool
    {
        return self::can($menuId, 'approve');
    }

    /**
     * Shortcut khusus reject
     */
    public static function reject(string $menuId): bool
    {
        return self::can($menuId, 'reject');
    }

    /**
     * Shortcut khusus print
     */
    public static function print(string $menuId): bool
    {
        return self::can($menuId, 'print');
    }
    
    public static function create(string $menuId): bool
    {
        return self::can($menuId, 'create');
    }
}