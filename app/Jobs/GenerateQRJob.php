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
            // Gunakan Transaction agar nomor urut tidak loncat atau bentrok
            DB::beginTransaction();

            $po = Tpo::with('po_detail')->findOrFail($this->poId);
            
            // Logika pengumpulan data QR untuk batch ini
            $qrList = $this->collectQRData($po, $this->start, $this->end);

            DB::commit();

            if (empty($qrList)) {
                throw new \Exception("Tidak ada data QR yang diproses untuk batch ini ({$this->start}-{$this->end})");
            }

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
            DB::rollBack();

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
        $currentGlobalIndex = 0; // Index akumulasi seluruh label di PO

        // Ambil info user pembuat batch
        $user = DB::table('users')->where('id', $this->batchRecord->user_id)->first();
        $username = $user ? $user->username : 'System';

        foreach ($po->po_detail as $detail) {
            
            // Ambil data QR yang SUDAH ADA untuk detail ini (berdasarkan urutan ID)
            $existingQRs = DB::table('tproduct_qr')
                ->where('id_po', $po->id)
                ->where('id_po_detail', $detail->id)
                ->orderBy('id', 'asc')
                ->get();
            
            for ($i = 1; $i <= $detail->qty; $i++) {
                $currentGlobalIndex++;

                // Hanya ambil yang masuk dalam range batch ini
                if ($currentGlobalIndex >= $this->start && $currentGlobalIndex <= $this->end) {
                    
                    // Case 1: SUDAH PERNAH DICETAK (Ambil dari DB)
                    // Kita asumsikan urutan kemunculan di DB = urutan sequence item
                    if (isset($existingQRs[$i-1])) {
                        
                        $qr = $existingQRs[$i-1];
                        
                        $qrList[] = [
                            'sku' => $qr->sku,
                            'nama_barang' => $qr->nama_barang,
                            'nomor_urut' => $qr->sequence_no,
                            'qr_payload' => $qr->qr_code
                        ];

                    } else {
                        // Case 2: BELUM PERNAH DICETAK (Insert Baru)
                        $sku = $detail->part_number;
                        
                        $product = DB::table('mproduct')->where('sku', $sku)->first();
                        if (!$product) {
                            throw new \Exception("SKU {$sku} tidak ditemukan di master product");
                        }

                        // Get Next Global Sequence (Logic sama dengan Controller)
                        $nextSeqInt = DB::table('tproduct_qr')
                            ->where('sku', $sku)
                            ->max(DB::raw('CAST(sequence_no AS UNSIGNED)')) + 1;
                        
                        $seqStr = str_pad($nextSeqInt, 4, '0', STR_PAD_LEFT);
                        $qrValue = $po->no_po . '|' . $sku . '|' . $seqStr;

                        // Simpan ke tproduct_qr agar bisa di-scan WMS
                        DB::table('tproduct_qr')->insert([
                            'id_po'        => $po->id,
                            'id_po_detail' => $detail->id,
                            'id_product'   => $product->id,
                            'sku'          => $sku,
                            'qr_code'      => $qrValue,
                            'sequence_no'  => $seqStr,
                            'nama_barang'  => $detail->product_name,
                            'status'       => 'NEW',
                            'used_for'     => 'IN',
                            'printed_at'   => now(),
                            'printed_by'   => $username,
                        ]);

                        $qrList[] = [
                            'sku' => $sku,
                            'nama_barang' => $detail->product_name,
                            'nomor_urut' => $seqStr,
                            'qr_payload' => $qrValue
                        ];
                    }
                }

                // Optimalisasi: Jika sudah melewati batas akhir batch, stop
                if ($currentGlobalIndex > $this->end) break 2;
            }
        }

        return $qrList;
    }
}
