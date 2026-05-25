<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AssignStockReportMenuToAdmin extends Migration
{
    public function up()
    {
        $alreadyExists = DB::table('role_menus')
            ->where('role_id', 1)
            ->where('menu_id', 'MENU-0503')
            ->exists();

        if (!$alreadyExists) {
            DB::table('role_menus')->insert([
                'role_id'    => 1,
                'menu_id'    => 'MENU-0503',
                'can_view'   => 1,
                'can_create' => 0,
                'can_update' => 0,
                'can_delete' => 0,
                'can_approve'=> 0,
                'can_reject' => 0,
                'can_print'  => 1,
                'is_enabled' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        DB::table('role_menus')
            ->where('role_id', 1)
            ->where('menu_id', 'MENU-0503')
            ->delete();
    }
}
