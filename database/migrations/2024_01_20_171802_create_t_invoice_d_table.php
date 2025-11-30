<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTInvoiceDTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('t_invoice_d', function (Blueprint $table) {
            $table->integer('id')->length(11)->autoIncrement();
            $table->integer('hid');
            $table->integer('id_product');
            $table->string('SKU',25);
            $table->string('no_inv',30);
            $table->date('tgl_inv');
            $table->integer('qty');
            $table->integer('price');
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
        Schema::dropIfExists('t_invoice_d');
    }
}