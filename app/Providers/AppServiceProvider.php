<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        View::composer('includes.sidebar', function ($view) {

            $user = Auth::user();

            // =========================
            // SAFETY
            // =========================
            if (!$user || !$user->role_id) {
                $view->with('menus', []);
                return;
            }

            $roleId = $user->role_id;

            // =========================
            // 1️⃣ AMBIL SEMUA MENU AKTIF
            // =========================
            $allMenus = DB::table('menus')
                ->where('is_active', 1)
                ->orderBy('order_no')
                ->get();

            // =========================
            // 2️⃣ MENU YANG BOLEH DILIHAT ROLE
            // =========================
            $allowedMenuIds = DB::table('role_menus')
                ->where('role_id', $roleId)
                ->where('can_view', 1)
                ->where('is_enabled', 1)
                ->pluck('menu_id')
                ->toArray();

            // =========================
            // 3️⃣ BUILD TREE MENU
            // =========================
            $menus = [];

            foreach ($allMenus as $menu) {

                // ---------- CHILD ----------
                if ($menu->parent_menu_id) {
                    if (in_array($menu->menu_id, $allowedMenuIds)) {
                        $menus[$menu->parent_menu_id][] = $menu;
                    }
                    continue;
                }

                // ---------- PARENT ----------
                $menus[null][] = $menu;
            }

            // =========================
            // 4️⃣ HAPUS PARENT TANPA CHILD
            // =========================
            $menus[null] = collect($menus[null] ?? [])
                ->filter(fn ($parent) => !empty($menus[$parent->menu_id]))
                ->values()
                ->all();

            $view->with('menus', $menus);
        });
    }
}