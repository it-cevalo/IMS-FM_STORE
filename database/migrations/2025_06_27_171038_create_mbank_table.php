<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMbankTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mbank', function (Blueprint $table) {
            $table->id();
            $table->string('code_bank',3);
            $table->string('nama_bank',10);
            $table->string('norek_bank',20);
            $table->string('atasnama_bank',100);
            $table->string('company_id',50)->nullable();
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
        Schema::dropIfExists('mbank');
    }
}