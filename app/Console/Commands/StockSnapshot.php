<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Logs;

class StockSnapshot extends Command
{
    protected $signature = 'stock:snapshot {--month= : Periode YYYY-MM (default: bulan berjalan)} {--dry-run : Tampilkan hasil tanpa menyimpan}';
    protected $description = 'Snapshot stok tersedia per produk per gudang untuk akhir bulan (idempotent)';

    private function activityLog(string $section, string $content): void
    {
        try {
            (new Logs('Logs_StockSnapshot'))->write($section, $content);
        } catch (\Throwable $e) {
            \Log::error('[StockSnapshot] Gagal menulis log: ' . $e->getMessage());
        }
    }

    public function handle()
    {
        $monthOpt = $this->option('month');
        $dryRun   = (bool) $this->option('dry-run');

        try {
            $period = $monthOpt
                ? Carbon::createFromFormat('Y-m', $monthOpt)->startOfMonth()
                : Carbon::now()->startOfMonth();
        } catch (\Throwable $e) {
            $this->error("Format --month tidak valid: '{$monthOpt}'. Gunakan YYYY-MM, contoh: 2026-07");
            return 1;
        }

        $snapshotPeriod = $period->format('Y-m');
        $snapshotDate   = $period->copy()->endOfMonth()->toDateString();

        $this->info("=== STOCK SNAPSHOT {$snapshotPeriod} (per {$snapshotDate}) ===");
        if ($dryRun) {
            $this->warn('MODE DRY RUN — tidak ada data yang disimpan');
        }

        $this->activityLog('SNAPSHOT', "Periode: {$snapshotPeriod} | Tanggal: {$snapshotDate} | DryRun: " . ($dryRun ? 'Y' : 'N') . " | Status: PROCESS");

        // qty_last = stok tersedia (sumber yang sama dipakai endpoint qty_tersedia di DeliveryOrder)
        $rows = DB::table('t_stock_opname as so')
            ->leftJoin('mproduct as p', 'p.id', '=', 'so.id_product')
            ->select([
                'so.id_product',
                'so.id_warehouse',
                'p.sku',
                'p.nama_barang',
                'so.qty_in',
                'so.qty_out',
                'so.qty_last',
            ])
            ->orderBy('so.id_warehouse')
            ->orderBy('so.id_product')
            ->get();

        if ($rows->isEmpty()) {
            $this->warn('Tidak ada data di t_stock_opname — snapshot dilewati');
            $this->activityLog('SNAPSHOT', "Periode: {$snapshotPeriod} | Status: EMPTY");
            return 0;
        }

        $now      = now();
        $tersimpan = 0;
        $totalQty  = 0;

        try {
            if (!$dryRun) {
                DB::beginTransaction();
            }

            foreach ($rows->chunk(500) as $chunk) {
                $payload = [];

                foreach ($chunk as $row) {
                    $qtyAvailable = (int) ($row->qty_last ?? 0);
                    $totalQty    += $qtyAvailable;

                    $payload[] = [
                        'snapshot_period' => $snapshotPeriod,
                        'snapshot_date'   => $snapshotDate,
                        'id_product'      => $row->id_product,
                        'id_warehouse'    => $row->id_warehouse,
                        'sku'             => $row->sku,
                        'nama_barang'     => $row->nama_barang,
                        'qty_in'          => (int) ($row->qty_in ?? 0),
                        'qty_out'         => (int) ($row->qty_out ?? 0),
                        'qty_available'   => $qtyAvailable,
                        'created_at'      => $now,
                        'updated_at'      => $now,
                    ];
                }

                if (!$dryRun) {
                    // upsert: aman kalau command dijalankan ulang untuk periode yang sama
                    DB::table('t_stock_snapshot')->upsert(
                        $payload,
                        ['snapshot_period', 'id_product', 'id_warehouse'],
                        ['sku', 'nama_barang', 'qty_in', 'qty_out', 'qty_available', 'updated_at']
                    );
                }

                $tersimpan += count($payload);
                $this->line("   ...{$tersimpan}/{$rows->count()} baris diproses");
            }

            if (!$dryRun) {
                DB::commit();
            }

            $this->info("Selesai — {$tersimpan} baris, total qty tersedia: {$totalQty}");
            $this->activityLog('SNAPSHOT', "Periode: {$snapshotPeriod} | Baris: {$tersimpan} | TotalQty: {$totalQty} | Status: SUCCESS");

            return 0;

        } catch (\Throwable $e) {
            if (!$dryRun) {
                DB::rollBack();
            }

            $this->error("Gagal: {$e->getMessage()}");
            $this->activityLog('SNAPSHOT', "Periode: {$snapshotPeriod} | Status: FAILED | Error: {$e->getMessage()} | File: {$e->getFile()}:{$e->getLine()}");

            return 1;
        }
    }
}
