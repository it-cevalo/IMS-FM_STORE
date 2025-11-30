<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMproductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mproduct', function (Blueprint $table) {
            $table->integer('id')->length(11)->autoIncrement();
            $table->string('SKU',25);
            $table->string('nama_barang',50);            
            $table->integer('id_unit')->unsigned()->nullable();
            $table->integer('id_type')->unsigned()->nullable();
            $table->date('tanggal');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('id_unit')->references('id')->on('mproduct_unit');
            $table->foreign('id_type')->references('id')->on('mproduct_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mproduct');
    }
}
