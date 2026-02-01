<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class AutoInjectStock extends Command
{
    protected $signature = 'stock:auto-inject {--warehouse=1}';
    protected $description = 'Auto inject saldo awal + generate QR + inbound + log manual';

    private $logHandle;

    public function handle()
    {
        $warehouseId = intval($this->option('warehouse'));
        $excelPath   = storage_path('app/export/saldo_awal.xlsx');
        $date        = date('Y-m-d');
        $logPath     = storage_path("logs/AutoInjectStock_$date.log");

        $this->info('=== AUTO INJECT STOCK START ===');

        // buka file log (append)
        $this->logHandle = fopen($logPath, 'a');

        if (!file_exists($excelPath)) {
            $this->error('File saldo_awal.xlsx tidak ditemukan');
            $this->writeLog('-', 'FAILED', 'FAILED', 'FAILED', 'File saldo_awal.xlsx tidak ditemukan');
            return;
        }

        $rows = Excel::toArray([], $excelPath)[0];
        unset($rows[0]); // buang header

        try {
            $qrPerSku   = [];
            $totalSku  = 0;
            $totalQR   = 0;

            // ===============================
            // 🔒 TRACK SKU AGAR TIDAK DOUBLE
            // ===============================
            $processedSku = [];

            foreach ($rows as $index => $row) {

                $nama = trim($row[0] ?? '');
                $sku  = trim($row[1] ?? '');
                $qty  = intval($row[5] ?? 0);

                if ($sku === '') {
                    $this->warn("[SKIP] Baris {$index} | SKU kosong");
                    continue;
                }

                // ⛔ SKIP JIKA SKU SUDAH DIPROSES DI LOOP INI
                if (isset($processedSku[$sku])) {
                    $this->warn("[SKIP] SKU {$sku} sudah diproses sebelumnya");
                    continue;
                }

                $processedSku[$sku] = true;

                DB::beginTransaction();

                try {
                    $totalSku++;
                    $this->line("▶️  [START] SKU {$sku} | Qty {$qty}");

                    // ===============================
                    // 1️⃣ PRODUCT
                    // ===============================
                    $product = DB::table('mproduct')->where('sku', $sku)->first();

                    if (!$product) {
                        $productId = DB::table('mproduct')->insertGetId([
                            'sku'         => $sku,
                            'nama_barang' => $nama,
                            'flag_active' => 'Y',
                            'id_type'     => '1',
                            'id_unit'     => '1',
                            'stock_minimum' => '1',
                            'created_at'  => now()
                        ]);
                    } else {
                        $productId = $product->id;
                    }

                    // ===============================
                    // 2️⃣ STOCK OPNAME (ANTI DOUBLE)
                    // ===============================
                    $opnameExists = DB::table('t_stock_opname')
                        ->where('id_warehouse', $warehouseId)
                        ->where('id_product', $productId)
                        ->whereDate('tgl_opname', date('Y-m-d'))
                        ->exists();

                    if (!$opnameExists) {
                        DB::table('t_stock_opname')->insert([
                            'id_warehouse' => $warehouseId,
                            'id_product'   => $productId,
                            'qty_last'     => $qty,
                            'tgl_opname'   => now(),
                            'created_at'   => now()
                        ]);
                    }

                    // ===============================
                    // 3️⃣ QR + INBOUND
                    // ===============================
                    if ($qty > 0) {
                        for ($i = 1; $i <= $qty; $i++) {

                            $seq = $this->getNextGlobalSequenceBySKU($sku);
                            $seqStr = str_pad($seq, 4, '0', STR_PAD_LEFT);

                            $qrPayload = "SA|{$sku}|{$seqStr}";

                            DB::table('tproduct_qr')->insert([
                                'id_po'        => 0,
                                'id_po_detail' => 0,
                                'id_product'   => $productId,
                                'sku'          => $sku,
                                'qr_code'      => $qrPayload,
                                'sequence_no'  => $seqStr,
                                'nama_barang'  => $nama,
                                'status'       => 'NEW',
                                'used_for'     => 'IN',
                                'printed_by'   => 'SYSTEM',
                                'created_at'   => now()
                            ]);

                            DB::table('tproduct_inbound')->insert([
                                'id_po'          => 0,
                                'id_po_detail'   => 0,
                                'id_warehouse'   => $warehouseId,
                                'id_product'     => $productId,
                                'sku'            => $sku,
                                'qr_code'        => $qrPayload,
                                'qty'            => 1,
                                'inbound_source' => 'SALDO_AWAL',
                                'created_by'     => 0,
                                'created_at'     => now()
                            ]);

                            $totalQR++;
                        }
                    }

                    DB::commit();

                    $this->writeLog($sku, 'OK', 'OK', 'OK', 'success');
                    $this->info("✔ [DONE] SKU {$sku}");

                } catch (\Throwable $e) {

                    DB::rollBack();

                    $this->error("✖ SKU {$sku} FAILED");
                    $this->error($e->getMessage());

                    $this->writeLog($sku, 'FAILED', 'FAILED', 'FAILED', $e->getMessage());
                }
            }

            DB::commit();
            fclose($this->logHandle);

            $this->info("=== SELESAI ===");
            $this->info("Total SKU : {$totalSku}");
            $this->info("Total QR  : {$totalQR}");

        } catch (\Throwable $e) {
            DB::rollBack();
            fclose($this->logHandle);
            $this->error("FATAL ERROR: " . $e->getMessage());
        }
    }

    /**
     * GLOBAL SEQUENCE
     */
    private function getNextGlobalSequenceBySKU(string $sku): int
    {
        $last = DB::table('tproduct_qr')
            ->where('sku', $sku)
            ->lockForUpdate() // ⬅️ WAJIB
            ->max(DB::raw('CAST(sequence_no AS UNSIGNED)'));

        return $last ? $last + 1 : 1;
    }

    /**
     * MANUAL FILE LOGGING
     */
    private function writeLog(
        string $sku,
        string $statusInbound,
        string $statusQR,
        string $statusStock,
        string $message
    ): void {
        $line = sprintf(
            "%s|%s|%s|%s|%s|%s\n",
            now()->format('Y-m-d H:i:s'),
            $sku,
            $statusInbound,
            $statusQR,
            $statusStock,
            $message
        );

        fwrite($this->logHandle, $line);
    }
}
