<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTStockSnapshotTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('t_stock_snapshot')) {
            return;
        }

        Schema::create('t_stock_snapshot', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('snapshot_period', 7);   // YYYY-MM
            $table->date('snapshot_date');          // tanggal akhir bulan periode tsb
            $table->integer('id_product')->nullable();
            $table->integer('id_warehouse')->nullable();

            // disalin saat snapshot — nama/SKU produk bisa berubah, snapshot harus tetap apa adanya
            $table->string('sku', 100)->nullable();
            $table->string('nama_barang', 100)->nullable();

            $table->integer('qty_in')->default(0);
            $table->integer('qty_out')->default(0);
            $table->integer('qty_available')->default(0);  // t_stock_opname.qty_last

            $table->timestamps();

            $table->unique(
                ['snapshot_period', 'id_product', 'id_warehouse'],
                'uq_snapshot_period_product_warehouse'
            );
            $table->index('snapshot_date', 'idx_snapshot_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('t_stock_snapshot');
    }
}
