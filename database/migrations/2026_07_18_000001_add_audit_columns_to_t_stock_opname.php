<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAuditColumnsToTStockOpname extends Migration
{
    public function up()
    {
        Schema::table('t_stock_opname', function (Blueprint $table) {
            if (!Schema::hasColumn('t_stock_opname', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('updated_at');
            }
            if (!Schema::hasColumn('t_stock_opname', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            }
        });
    }

    public function down()
    {
        Schema::table('t_stock_opname', function (Blueprint $table) {
            $table->dropColumn(['created_by', 'updated_by']);
        });
    }
}
