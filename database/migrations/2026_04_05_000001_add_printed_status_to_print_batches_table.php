<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddPrintedStatusToPrintBatchesTable extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE `print_batches` MODIFY COLUMN `status` ENUM('Pending', 'Processing', 'Ready', 'Printed', 'Failed') NOT NULL DEFAULT 'Pending'");
    }

    public function down()
    {
        DB::statement("ALTER TABLE `print_batches` MODIFY COLUMN `status` ENUM('Pending', 'Processing', 'Ready', 'Failed') NOT NULL DEFAULT 'Pending'");
    }
}
