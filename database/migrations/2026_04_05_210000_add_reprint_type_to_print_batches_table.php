<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddReprintTypeToPrintBatchesTable extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE `print_batches` ADD COLUMN `batch_type` VARCHAR(20) NOT NULL DEFAULT 'regular' AFTER `batch_name`");
        DB::statement("ALTER TABLE `print_batches` ADD COLUMN `reprint_request_ids` TEXT NULL AFTER `batch_type`");
    }

    public function down()
    {
        DB::statement("ALTER TABLE `print_batches` DROP COLUMN `reprint_request_ids`");
        DB::statement("ALTER TABLE `print_batches` DROP COLUMN `batch_type`");
    }
}
