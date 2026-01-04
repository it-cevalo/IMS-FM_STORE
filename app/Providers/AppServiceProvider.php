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

            // Belum login / belum punya role
            if (!$user || !$user->role_id) {
                $view->with('menus', collect());
                return;
            }

            $menus = DB::table('menus as m')
                ->join('role_menus as rm', 'rm.menu_id', '=', 'm.menu_id')
                ->where('rm.role_id', $user->role_id)
                ->where('rm.is_enabled', 1)
                ->where('m.is_active', 1)
                ->orderBy('m.order_no')
                ->select('m.*')
                ->get()
                ->groupBy('parent_menu_id'); // 🔥 ROOT = NULL

            $view->with('menus', $menus);
        });
    }
}