<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRejectColumnsToTproductOutbound extends Migration
{
    public function up()
    {
        Schema::table('tproduct_outbound', function (Blueprint $table) {
            $table->timestamp('rejected_at')->nullable()->after('sync_by');
            $table->unsignedBigInteger('rejected_by')->nullable()->after('rejected_at');
            $table->string('reject_reason', 500)->nullable()->after('rejected_by');
        });
    }

    public function down()
    {
        Schema::table('tproduct_outbound', function (Blueprint $table) {
            $table->dropColumn(['rejected_at', 'rejected_by', 'reject_reason']);
        });
    }
}
