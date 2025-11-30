<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseRequestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_request', function (Blueprint $table) {
            $table->integer('id')->length(11)->autoIncrement();
            $table->string('code_pr',30);
            $table->integer('id_warehouse');
            $table->integer('total_qty_req');
            $table->string('desc_req');
            $table->integer('id_user_request');
            $table->date('request_date');
            $table->integer('id_user_approved');
            $table->date('approved_date');
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
        Schema::dropIfExists('purchase_request');
    }
}