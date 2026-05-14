<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddBatchRangeToPrintBatchesTable extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE `print_batches` ADD COLUMN `batch_start` INT UNSIGNED NULL AFTER `total_labels`");
        DB::statement("ALTER TABLE `print_batches` ADD COLUMN `batch_end` INT UNSIGNED NULL AFTER `batch_start`");
    }

    public function down()
    {
        DB::statement("ALTER TABLE `print_batches` DROP COLUMN `batch_end`");
        DB::statement("ALTER TABLE `print_batches` DROP COLUMN `batch_start`");
    }
}
