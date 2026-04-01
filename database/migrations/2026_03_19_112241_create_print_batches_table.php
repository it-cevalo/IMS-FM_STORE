<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrintBatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('print_batches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('id_po');
            $table->string('batch_name'); // Contoh: "Batch 1 (1-100)"
            $table->integer('total_labels');
            $table->enum('status', ['Pending', 'Processing', 'Ready', 'Failed'])->default('Pending');
            $table->string('file_path')->nullable(); // Lokasi PDF di storage
            $table->text('error_message')->nullable();
            $table->timestamps();

            // Indexing for performance
            $table->index('user_id');
            $table->index('id_po');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('print_batches');
    }
}
