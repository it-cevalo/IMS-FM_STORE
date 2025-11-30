<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockMutationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_mutation', function (Blueprint $table) {
            $table->integer('id')->length(11)->autoIncrement();
            $table->integer('id_product');
            $table->integer('id_warehouse');
            $table->integer('qty_start');
            $table->integer('qty_in');
            $table->integer('qty_out');
            $table->date('tgl_mutasi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_mutation');
    }
}