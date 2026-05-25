<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddStockReportMenu extends Migration
{
    public function up()
    {
        DB::table('menus')->insert([
            'menu_id'        => 'MENU-0503',
            'parent_menu_id' => 'MENU-0500',
            'name'           => 'Stock Balance',
            'route_name'     => 'stock_report.index',
            'icon'           => 'fas fa-fw fa-boxes',
            'type'           => 'CHILD',
            'order_no'       => 3,
            'is_active'      => 1,
        ]);
    }

    public function down()
    {
        DB::table('menus')->where('menu_id', 'MENU-0503')->delete();
        DB::table('role_menus')->where('menu_id', 'MENU-0503')->delete();
    }
}
