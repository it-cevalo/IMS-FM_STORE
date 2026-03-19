<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Tpo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PDF; // Barryvdh/laravel-dompdf
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GenerateQRJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $batchRecord;
    protected $start;
    protected $end;
    protected $poId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($batchRecord, $start, $end, $poId)
    {
        $this->batchRecord = $batchRecord;
        $this->start = $start;
        $this->end = $end;
        $this->poId = $poId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Update status ke Processing
        DB::table('print_batches')->where('id', $this->batchRecord->id)->update(['status' => 'Processing']);

        try {
            $po = Tpo::with('po_detail')->findOrFail($this->poId);
            
            // Logika pengumpulan data QR untuk batch ini
            $qrList = $this->collectQRData($po, $this->start, $this->end);

            /*
            |--------------------------------------------------------------------------
            | Generate SVG di Job (JANGAN DI BLADE)
            |--------------------------------------------------------------------------
            */
            foreach ($qrList as &$q) {
                $svg = QrCode::format('svg')
                    ->size(220)
                    ->margin(0)
                    ->generate($q['qr_payload']);
            
                $q['qr_svg'] = 'data:image/svg+xml;base64,' . base64_encode($svg);
            }
            unset($q);

            /*
            |--------------------------------------------------------------------------
            | UKURAN LABEL (33 x 15 mm)
            |--------------------------------------------------------------------------
            | 1 mm = 2.83465 pt
            */
            $width  = 33 * 2.83465;
            $height = 15 * 2.83465;

            // Render PDF (Gunakan view yang sama dengan Controller)
            $pdf = PDF::loadView('pages.transaction.purchase_order.purchase_order_qrcode', [
                'po' => $po,
                'qrList' => $qrList
            ])->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => false,
                'defaultFont'          => 'DejaVu Sans'
            ])
            ->setPaper([0, 0, $width, $height], 'portrait');

            // Simpan ke storage (public agar bisa diakses browser)
            $fileName = 'qr_po_' . $this->poId . '_batch_' . $this->batchRecord->id . '.pdf';
            $path = 'public/temp_prints/' . $fileName;
            
            Storage::put($path, $pdf->output());

            // Update status ke Ready
            DB::table('print_batches')->where('id', $this->batchRecord->id)->update([
                'status' => 'Ready',
                'file_path' => $fileName
            ]);

        } catch (\Exception $e) {
            // Update status ke Failed
            DB::table('print_batches')->where('id', $this->batchRecord->id)->update([
                'status' => 'Failed',
                'error_message' => $e->getMessage()
            ]);
        }
    }

    private function collectQRData($po, $start, $end)
    {
        $qrList = [];
        $currentSeq = 0;

        foreach ($po->po_detail as $detail) {
            for ($i = 1; $i <= $detail->qty; $i++) {
                $currentSeq++;

                // Hanya ambil yang masuk dalam range batch ini
                if ($currentSeq >= $this->start && $currentSeq <= $this->end) {
                    $seqStr = str_pad($i, 4, '0', STR_PAD_LEFT);
                    
                    $qrList[] = [
                        'sku' => $detail->part_number,
                        'nama_barang' => $detail->product_name,
                        'nomor_urut' => $seqStr,
                        'qr_payload' => $po->no_po . '|' . $detail->part_number . '|' . $seqStr
                    ];
                }

                if ($currentSeq > $this->end) break 2;
            }
        }

        return $qrList;
    }
}
