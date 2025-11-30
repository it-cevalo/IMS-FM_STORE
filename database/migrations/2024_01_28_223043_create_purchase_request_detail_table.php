<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseRequestDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_request_detail', function (Blueprint $table) {
            $table->integer('id')->length(11)->autoIncrement();
            $table->integer('pr_id');
            $table->integer('id_product');
            $table->string('SKU',25);
            $table->string('code_pr',30);
            $table->date('request_date');
            $table->integer('qty_prd');
            $table->string('desc_prd');
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
        Schema::dropIfExists('purchase_request_detail');
    }
}