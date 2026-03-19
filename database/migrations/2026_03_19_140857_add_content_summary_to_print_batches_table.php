<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddContentSummaryToPrintBatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('print_batches', function (Blueprint $table) {
            $table->text('content_summary')->nullable()->after('batch_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('print_batches', function (Blueprint $table) {
            $table->dropColumn('content_summary');
        });
    }
}
