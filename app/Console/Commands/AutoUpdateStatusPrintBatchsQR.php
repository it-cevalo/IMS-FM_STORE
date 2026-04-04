<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AutoUpdateStatusPrintBatchsQR extends Command
{
    protected $signature = 'qr:update-batch-status
                            {--dry-run : Tampilkan rencana perubahan tanpa benar-benar mengubah data}';

    protected $description = 'Migrasi print_batches: set status Ready → Printed jika QR-nya sudah ada di tproduct_qr';

    private $logHandle;
    private int $countUpdated = 0;
    private int $countSkipped = 0;
    private int $countFailed  = 0;

    public function handle(): void
    {
        $isDryRun = $this->option('dry-run');
        $date     = now()->format('Y-m-d');
        $logPath  = storage_path("logs/AutoUpdateStatusPrintBatchsQR_{$date}.log");

        $this->logHandle = fopen($logPath, 'a');

        $this->writeHeader($isDryRun);
        $this->info('');
        $this->info('=== AutoUpdateStatusPrintBatchsQR ' . ($isDryRun ? '[DRY RUN] ' : '') . 'START ===');
        $this->info("Log: {$logPath}");
        $this->info('');

        // Ambil semua batch yang masih Ready
        $batches = DB::table('print_batches as pb')
            ->join('tpo as po', 'po.id', '=', 'pb.id_po')
            ->where('pb.status', 'Ready')
            ->orderBy('pb.id', 'asc')
            ->select(
                'pb.id',
                'pb.batch_name',
                'pb.id_po',
                'pb.content_summary',
                'pb.total_labels',
                'pb.created_at as batch_created_at',
                'po.no_po'
            )
            ->get();

        if ($batches->isEmpty()) {
            $this->info('Tidak ada batch dengan status Ready. Tidak ada yang perlu diproses.');
            $this->writeLog('-', '-', '-', 'SKIPPED', 'Tidak ada batch Ready ditemukan');
            $this->writeFooter();
            fclose($this->logHandle);
            return;
        }

        $this->info("Ditemukan {$batches->count()} batch dengan status Ready.");
        $this->info('');

        foreach ($batches as $batch) {
            $this->processBatch($batch, $isDryRun);
        }

        $this->writeFooter();
        fclose($this->logHandle);

        $this->info('');
        $this->info('=== SELESAI ===');
        $this->info("Updated : {$this->countUpdated}");
        $this->info("Skipped : {$this->countSkipped}");
        $this->info("Failed  : {$this->countFailed}");
        $this->info("Log     : {$logPath}");

        if ($isDryRun) {
            $this->warn('');
            $this->warn('[DRY RUN] Tidak ada data yang diubah. Jalankan tanpa --dry-run untuk eksekusi.');
        }
    }

    private function processBatch(object $batch, bool $isDryRun): void
    {
        $label = "Batch #{$batch->id} | PO {$batch->no_po} | {$batch->batch_name}";

        try {
            // Cek apakah ada QR yang sudah dicetak untuk PO ini
            $qrExists = DB::table('tproduct_qr')
                ->where('id_po', $batch->id_po)
                ->exists();

            if (!$qrExists) {
                // QR belum ada → batch memang belum pernah dicetak → biarkan Ready
                $reason = 'Tidak ada QR di tproduct_qr untuk PO ini — batch belum pernah dicetak';
                $this->line("  [SKIP]    {$label}");
                $this->line("            → {$reason}");
                $this->writeLog($batch->id, $batch->no_po, $batch->batch_name, 'SKIPPED', $reason);
                $this->countSkipped++;
                return;
            }

            // QR ada → batch sudah pernah dicetak → perlu diubah ke Printed
            if ($isDryRun) {
                $reason = "QR sudah ada di tproduct_qr — akan diubah ke Printed [DRY RUN, tidak dieksekusi]";
                $this->line("  [DRY RUN] {$label}");
                $this->line("            → {$reason}");
                $this->writeLog($batch->id, $batch->no_po, $batch->batch_name, 'DRY_RUN', $reason);
                $this->countUpdated++;
                return;
            }

            // Eksekusi update
            DB::table('print_batches')
                ->where('id', $batch->id)
                ->where('status', 'Ready') // guard: jangan ubah jika sudah berubah di tengah proses
                ->update([
                    'status'     => 'Printed',
                    'updated_at' => now(),
                ]);

            $reason = "QR ditemukan di tproduct_qr — status Ready → Printed (batch dibuat: {$batch->batch_created_at})";
            $this->info("  [UPDATED] {$label}");
            $this->line("            → {$reason}");
            $this->writeLog($batch->id, $batch->no_po, $batch->batch_name, 'UPDATED', $reason);
            $this->countUpdated++;

        } catch (\Throwable $e) {
            $reason = 'Exception: ' . $e->getMessage();
            $this->error("  [FAILED]  {$label}");
            $this->error("            → {$reason}");
            $this->writeLog($batch->id, $batch->no_po ?? '-', $batch->batch_name ?? '-', 'FAILED', $reason);
            $this->countFailed++;
        }
    }

    // ─────────────────────────────────────────────────────────────
    // LOG HELPERS
    // ─────────────────────────────────────────────────────────────

    private function writeHeader(bool $isDryRun): void
    {
        $separator = str_repeat('-', 120);
        $mode      = $isDryRun ? ' [DRY RUN - tidak ada perubahan data]' : '';

        fwrite($this->logHandle, "\n{$separator}\n");
        fwrite($this->logHandle, sprintf(
            "COMMAND  : AutoUpdateStatusPrintBatchsQR%s\n",
            $mode
        ));
        fwrite($this->logHandle, sprintf(
            "STARTED  : %s\n",
            now()->format('Y-m-d H:i:s')
        ));
        fwrite($this->logHandle, "{$separator}\n");
        fwrite($this->logHandle, sprintf(
            "%-19s | %-10s | %-20s | %-30s | %-10s | %s\n",
            'TIMESTAMP',
            'BATCH_ID',
            'NO_PO',
            'BATCH_NAME',
            'STATUS',
            'REASON'
        ));
        fwrite($this->logHandle, "{$separator}\n");
    }

    private function writeLog(
        mixed  $batchId,
        string $noPo,
        string $batchName,
        string $status,
        string $reason
    ): void {
        $line = sprintf(
            "%-19s | %-10s | %-20s | %-30s | %-10s | %s\n",
            now()->format('Y-m-d H:i:s'),
            $batchId,
            $noPo,
            $batchName,
            $status,
            $reason
        );

        fwrite($this->logHandle, $line);
    }

    private function writeFooter(): void
    {
        $separator = str_repeat('-', 120);

        fwrite($this->logHandle, "{$separator}\n");
        fwrite($this->logHandle, sprintf(
            "FINISHED : %s | UPDATED: %d | SKIPPED: %d | FAILED: %d\n",
            now()->format('Y-m-d H:i:s'),
            $this->countUpdated,
            $this->countSkipped,
            $this->countFailed
        ));
        fwrite($this->logHandle, "{$separator}\n");
    }
}
