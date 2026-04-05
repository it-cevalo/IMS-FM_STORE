<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Helpers\Permission;
use Illuminate\Http\Request;
use App\Models\Tpo;
use App\Models\Tpo_Detail;
use App\Models\Hpo;
use App\Models\MCustomer;
use App\Models\MSupplier;
use App\Models\Mproduct;
use App\Imports\PurchaseOrderImport;
use Storage, Excel, DB, Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Yajra\DataTables\Facades\DataTables;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Logs;

use App\Jobs\GenerateQRJob;

class PurchaseOrderController extends Controller
{
    private function poLog(string $section, string $content): void
    {
        try {
            $log = new Logs('Logs_PurchaseOrderController');
            $log->write($section, $content);
        } catch (\Throwable $e) {
            \Log::error('[PurchaseOrderController] Gagal menulis log: ' . $e->getMessage());
        }
    }

    private function actor(): string
    {
        $user = Auth::user();
        if (!$user) return 'Guest';
        return $user->username ?? $user->name ?? "ID:{$user->id}";
    }

    public function generateAllQRBatch(Request $request, $id)
    {
        $po = Tpo::with('po_detail')->findOrFail($id);
        $totalQty = $po->po_detail->sum('qty');

        $this->poLog('CETAK_QR_BATCH', "User: {$this->actor()} | PO: {$po->no_po} (ID:{$id}) | Total QR: {$totalQty} | Status: PROCESS");

        if ($totalQty === 0) {
            $this->poLog('CETAK_QR_BATCH', "User: {$this->actor()} | PO: {$po->no_po} (ID:{$id}) | Status: FAILED | Error: PO tidak memiliki detail item");
            return response()->json(['error' => 'PO tidak memiliki detail item'], 422);
        }

        /*
        |----------------------------------------------------------------------
        | PRE-FLIGHT: Cek apakah ada QR yang sudah dicetak tanpa approved reprint
        | Logika sama persis dengan validateQR() tapi mencakup seluruh PO
        |----------------------------------------------------------------------
        */
        // 1 query: seluruh QR di PO ini yang blocked (ada di tproduct_qr, tanpa approved reprint)
        $blockedRaw = DB::table('tproduct_qr as qr')
            ->leftJoin('tqr_reprint_request as rr', function ($j) {
                $j->on('rr.id_po',       '=', 'qr.id_po')
                  ->on('rr.id_po_detail', '=', 'qr.id_po_detail')
                  ->on(DB::raw('`rr`.`sequence_no` COLLATE utf8mb4_unicode_ci'), '=', DB::raw('`qr`.`sequence_no` COLLATE utf8mb4_unicode_ci'))
                  ->where(DB::raw('`rr`.`status` COLLATE utf8mb4_unicode_ci'), 'APPROVED')
                  ->whereNull('rr.used_at');
            })
            ->where('qr.id_po', $id)
            ->whereNull('rr.id')   // tidak punya approved reprint → blocked
            ->select('qr.id_po_detail', 'qr.sequence_no')
            ->get()
            ->groupBy('id_po_detail');

        $conflictsAll = [];
        foreach ($po->po_detail as $detail) {
            $detailBlocked = $blockedRaw->get($detail->id);
            if (!$detailBlocked || $detailBlocked->isEmpty()) continue;

            $blocked = $detailBlocked
                ->pluck('sequence_no')
                ->map(fn($s) => (int)$s)
                ->sort()->values()->toArray();

            $rangeText      = $this->compressSequenceRange($blocked);
            $conflictsAll[] = [
                'id_po_detail' => $detail->id,
                'sku'          => $detail->part_number,
                'product_name' => $detail->product_name,
                'printed_range'=> $rangeText,
                'sequence'     => $rangeText,
            ];
        }

        if (!empty($conflictsAll)) {
            $conflictSkus = implode(', ', array_column($conflictsAll, 'sku'));
            $this->poLog('CETAK_QR_BATCH', "User: {$this->actor()} | PO: {$po->no_po} (ID:{$id}) | Status: BLOCKED | SKU konflik: {$conflictSkus}");
            return response()->json([
                'code'      => 'QR_ALREADY_PRINTED',
                'message'   => 'Terdapat QR yang sudah pernah dicetak. Ajukan reprint terlebih dahulu.',
                'conflicts' => $conflictsAll,
            ], 409);
        }

        $limitPerBatch = 100;
        $totalBatches  = ceil($totalQty / $limitPerBatch);
        $details       = $po->po_detail;
        $batchList     = [];

        for ($i = 0; $i < $totalBatches; $i++) {
            $batchStart = ($i * $limitPerBatch) + 1;
            $batchEnd   = min(($i + 1) * $limitPerBatch, $totalQty);

            // Hitung summary SKU & sequence
            $summaryItems = [];
            $itemCounter  = 0;
            foreach ($details as $d) {
                $skuStart     = $itemCounter + 1;
                $skuEnd       = $itemCounter + $d->qty;
                $overlapStart = max($batchStart, $skuStart);
                $overlapEnd   = min($batchEnd, $skuEnd);
                if ($overlapStart <= $overlapEnd) {
                    $localStart     = $overlapStart - $itemCounter;
                    $localEnd       = $overlapEnd - $itemCounter;
                    $summaryItems[] = $d->part_number . ' (' .
                        str_pad($localStart, 4, '0', STR_PAD_LEFT) . '-' .
                        str_pad($localEnd, 4, '0', STR_PAD_LEFT) . ')';
                }
                $itemCounter += $d->qty;
            }

            $batchId = DB::table('print_batches')->insertGetId([
                'user_id'         => auth()->id(),
                'id_po'           => $id,
                'batch_name'      => 'Batch ' . ($i + 1),
                'content_summary' => implode(', ', $summaryItems),
                'total_labels'    => ($batchEnd - $batchStart) + 1,
                'batch_start'     => $batchStart,
                'batch_end'       => $batchEnd,
                'status'          => 'Pending',
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            $batchList[] = [
                'id'          => $batchId,
                'batch_name'  => 'Batch ' . ($i + 1),
                'total'       => ($batchEnd - $batchStart) + 1,
                'batch_start' => $batchStart,
                'batch_end'   => $batchEnd,
            ];
        }

        $this->poLog('CETAK_QR_BATCH', "User: {$this->actor()} | PO: {$po->no_po} (ID:{$id}) | Status: BATCH_CREATED | Jumlah batch: {$totalBatches} | Total QR: {$totalQty}");

        return response()->json([
            'batches'       => $batchList,
            'total_batches' => $totalBatches,
            'total_labels'  => $totalQty,
        ]);
    }

    public function processBatch(Request $request, $id, $batchId)
    {
        $batchRecord = DB::table('print_batches')->where('id', $batchId)->first();

        if (!$batchRecord || (int)$batchRecord->id_po !== (int)$id) {
            return response()->json(['error' => 'Batch tidak ditemukan'], 404);
        }

        if ((int)$batchRecord->user_id !== auth()->id()) {
            return response()->json(['error' => 'Tidak diizinkan'], 403);
        }

        $batchStart = (int) $batchRecord->batch_start;
        $batchEnd   = (int) $batchRecord->batch_end;

        $this->poLog('PROSES_BATCH', "User: {$this->actor()} | PO ID: {$id} | Batch ID: {$batchId} | Range: {$batchStart}-{$batchEnd} | Status: PROCESS");

        // Tandai Processing secara atomik — cegah double-process
        $locked = DB::table('print_batches')
            ->where('id', $batchId)
            ->where('status', 'Pending')
            ->update(['status' => 'Processing', 'updated_at' => now()]);

        if (!$locked) {
            $existing = DB::table('print_batches')->where('id', $batchId)->first();
            if ($existing && $existing->status === 'Ready') {
                $this->poLog('PROSES_BATCH', "User: {$this->actor()} | PO ID: {$id} | Batch ID: {$batchId} | Status: ALREADY_READY");
                return response()->json(['status' => 'ready', 'file_path' => $existing->file_path]);
            }
            $this->poLog('PROSES_BATCH', "User: {$this->actor()} | PO ID: {$id} | Batch ID: {$batchId} | Status: SKIPPED | Info: Batch sedang diproses atau sudah selesai");
            return response()->json(['error' => 'Batch sedang diproses atau sudah selesai'], 409);
        }

        try {
            $po       = Tpo::with('po_detail')->findOrFail($id);
            $username = auth()->user()->username ?? auth()->user()->name ?? 'System';

            DB::beginTransaction();
            $qrList = $this->collectQRDataForBatch($po, $batchStart, $batchEnd, $username);
            DB::commit();

            if (empty($qrList)) {
                throw new \Exception("Tidak ada data QR untuk rentang {$batchStart}-{$batchEnd}");
            }

            // Generate SVG
            foreach ($qrList as &$q) {
                $svg          = QrCode::format('svg')->size(220)->margin(0)->generate($q['qr_payload']);
                $q['qr_svg']  = 'data:image/svg+xml;base64,' . base64_encode($svg);
            }
            unset($q);

            // Generate PDF
            $width  = 33 * 2.83465;
            $height = 15 * 2.83465;
            $pdf = Pdf::loadView('pages.transaction.purchase_order.purchase_order_qrcode', [
                'po'     => $po,
                'qrList' => $qrList,
            ])->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => false,
                'defaultFont'          => 'DejaVu Sans',
            ])->setPaper([0, 0, $width, $height], 'portrait');

            $fileName = 'qr_po_' . $id . '_batch_' . $batchId . '.pdf';
            Storage::put('public/temp_prints/' . $fileName, $pdf->output());

            DB::table('print_batches')->where('id', $batchId)->update([
                'status'     => 'Ready',
                'file_path'  => $fileName,
                'updated_at' => now(),
            ]);

            $totalQr = count($qrList);
            $this->poLog('PROSES_BATCH', "User: {$this->actor()} | PO: {$po->no_po} (ID:{$id}) | Batch ID: {$batchId} | File: {$fileName} | Total QR: {$totalQr} | Status: READY");

            return response()->json(['status' => 'ready', 'file_path' => $fileName]);

        } catch (\Exception $e) {
            DB::rollBack();
            DB::table('print_batches')->where('id', $batchId)->update([
                'status'        => 'Failed',
                'error_message' => $e->getMessage(),
                'updated_at'    => now(),
            ]);
            $this->poLog('PROSES_BATCH', "User: {$this->actor()} | PO ID: {$id} | Batch ID: {$batchId} | Status: FAILED | Error: {$e->getMessage()}");
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function collectQRDataForBatch($po, $start, $end, $username)
    {
        $qrList             = [];
        $currentGlobalIndex = 0;

        foreach ($po->po_detail as $detail) {

            $existingQRs = DB::table('tproduct_qr')
                ->where('id_po', $po->id)
                ->where('id_po_detail', $detail->id)
                ->orderBy('id', 'asc')
                ->get();

            // Pre-fetch approved reprints untuk detail ini — 1 query, cek in-memory
            $approvedSeqs = DB::table('tqr_reprint_request')
                ->where('id_po', $po->id)
                ->where('id_po_detail', $detail->id)
                ->where('status', 'APPROVED')
                ->whereNull('used_at')
                ->pluck('sequence_no')
                ->flip() // [sequence_no => index] untuk O(1) lookup
                ->toArray();

            $consumedSeqs = []; // dikumpulkan dulu, bulk update setelah loop

            for ($i = 1; $i <= $detail->qty; $i++) {
                $currentGlobalIndex++;

                if ($currentGlobalIndex >= $start && $currentGlobalIndex <= $end) {

                    if (isset($existingQRs[$i - 1])) {
                        // QR sudah pernah dicetak
                        $qr = $existingQRs[$i - 1];

                        // SAFETY CHECK in-memory (bukan query ke DB)
                        if (!isset($approvedSeqs[$qr->sequence_no])) {
                            throw new \Exception(
                                "QR SKU {$detail->part_number} sequence {$qr->sequence_no} " .
                                "sudah pernah dicetak. Ajukan reprint request terlebih dahulu."
                            );
                        }

                        $consumedSeqs[] = $qr->sequence_no;

                        $qrList[] = [
                            'sku'         => $qr->sku,
                            'nama_barang' => $qr->nama_barang,
                            'nomor_urut'  => $qr->sequence_no,
                            'qr_payload'  => $qr->qr_code,
                        ];
                    } else {
                        // QR baru — generate & simpan
                        $sku     = $detail->part_number;
                        $product = DB::table('mproduct')->where('sku', $sku)->first();
                        if (!$product) {
                            throw new \Exception("SKU {$sku} tidak ditemukan di master product");
                        }

                        $nextSeqInt = DB::table('tproduct_qr')
                            ->where('sku', $sku)
                            ->max(DB::raw('CAST(sequence_no AS UNSIGNED)')) + 1;

                        $seqStr  = str_pad($nextSeqInt, 4, '0', STR_PAD_LEFT);
                        $qrValue = $po->no_po . '|' . $sku . '|' . $seqStr;

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
                            'sku'         => $sku,
                            'nama_barang' => $detail->product_name,
                            'nomor_urut'  => $seqStr,
                            'qr_payload'  => $qrValue,
                        ];
                    }
                }

                if ($currentGlobalIndex > $end) break 2;
            }

            // Bulk consume approval — 1 query per detail, bukan per item
            if (!empty($consumedSeqs)) {
                DB::table('tqr_reprint_request')
                    ->where('id_po', $po->id)
                    ->where('id_po_detail', $detail->id)
                    ->whereIn('sequence_no', $consumedSeqs)
                    ->where('status', 'APPROVED')
                    ->whereNull('used_at')
                    ->update(['used_at' => now()]);
            }
        }

        return $qrList;
    }

    public function printStatus(Request $request, $id)
    {
        $batches = DB::table('print_batches')
            ->where('id_po', $id)
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'asc')
            ->get();

        $po = Tpo::findOrFail($id);

        return view('pages.transaction.purchase_order.print_status', compact('batches', 'id', 'po'));
    }

    public function validateBatchView(Request $request, $id, $batchId)
    {
        $batch = DB::table('print_batches')->where('id', $batchId)->first();

        abort_if(!$batch || (int)$batch->id_po !== (int)$id, 404);
        abort_if((int)$batch->user_id !== auth()->id(), 403);

        // ── Pertama kali dilihat → tandai Printed, izinkan ──
        if ($batch->status === 'Ready') {
            DB::table('print_batches')
                ->where('id', $batchId)
                ->update(['status' => 'Printed', 'updated_at' => now()]);

            $this->poLog('CETAK_QR', "User: {$this->actor()} | PO ID: {$id} | Batch ID: {$batchId} | Status: PRINTED | Info: Pertama kali dicetak");

            return response()->json(['allowed' => true]);
        }

        // ── Sudah pernah dilihat/dicetak → wajib reprint approval ──
        if ($batch->status === 'Printed') {
            $po        = Tpo::with('po_detail')->findOrFail($id);
            $conflicts = $this->findBatchConflictsFromSummary($po, $batch->content_summary);

            if (empty($conflicts)) {
                // Semua item punya approved reprint → konsumsi dan izinkan
                $this->consumeBatchReprintApprovals($po, $batch->content_summary);
                $this->poLog('CETAK_QR', "User: {$this->actor()} | PO: {$po->no_po} (ID:{$id}) | Batch ID: {$batchId} | Status: REPRINT_ALLOWED | Info: Semua sequence sudah di-approve");
                return response()->json(['allowed' => true]);
            }

            $conflictSkus = implode(', ', array_column($conflicts, 'sku'));
            $this->poLog('CETAK_QR', "User: {$this->actor()} | PO: {$po->no_po} (ID:{$id}) | Batch ID: {$batchId} | Status: BLOCKED_REPRINT | SKU konflik: {$conflictSkus}");

            return response()->json([
                'code'      => 'QR_ALREADY_PRINTED',
                'message'   => 'Batch ini sudah pernah dicetak. Ajukan reprint terlebih dahulu.',
                'conflicts' => $conflicts,
            ], 409);
        }

        $this->poLog('CETAK_QR', "User: {$this->actor()} | PO ID: {$id} | Batch ID: {$batchId} | Status: INVALID | Info: Status batch tidak valid ({$batch->status})");

        return response()->json(['allowed' => false, 'error' => 'Status batch tidak valid.'], 400);
    }

    private function findBatchConflictsFromSummary($po, $contentSummary)
    {
        $conflicts = [];
        if (!$contentSummary) return $conflicts;

        foreach (explode(',', $contentSummary) as $part) {
            $part = trim($part);
            if (!preg_match('/^(.+?)\s*\((\d+)-(\d+)\)$/', $part, $m)) continue;

            $sku       = trim($m[1]);
            $localFrom = (int)$m[2];
            $localTo   = (int)$m[3];

            $detail = $po->po_detail->first(fn($d) => $d->part_number === $sku);
            if (!$detail) continue;

            // Query 1: ambil sequence_no dari slice posisi lokal ini
            $seqNos = DB::table('tproduct_qr')
                ->where('id_po', $po->id)
                ->where('id_po_detail', $detail->id)
                ->orderBy('id', 'asc')
                ->skip($localFrom - 1)
                ->take($localTo - $localFrom + 1)
                ->pluck('sequence_no')
                ->toArray();

            if (empty($seqNos)) continue;

            // Query 2: sequence mana yang punya approved reprint
            $approvedSeqs = DB::table('tqr_reprint_request')
                ->where('id_po', $po->id)
                ->where('id_po_detail', $detail->id)
                ->whereIn('sequence_no', $seqNos)
                ->where('status', 'APPROVED')
                ->whereNull('used_at')
                ->pluck('sequence_no')
                ->toArray();

            // Blocked = ada di batch tapi tidak punya approved reprint
            $blocked = array_values(array_diff($seqNos, $approvedSeqs));

            if (!empty($blocked)) {
                $blocked = array_map('intval', $blocked);
                sort($blocked);
                $rangeText   = $this->compressSequenceRange($blocked);
                $conflicts[] = [
                    'id_po_detail' => $detail->id,
                    'sku'          => $sku,
                    'product_name' => $detail->product_name,
                    'printed_range'=> $rangeText,
                    'sequence'     => $rangeText,
                ];
            }
        }

        return $conflicts;
    }

    private function consumeBatchReprintApprovals($po, $contentSummary)
    {
        if (!$contentSummary) return;

        foreach (explode(',', $contentSummary) as $part) {
            $part = trim($part);
            if (!preg_match('/^(.+?)\s*\((\d+)-(\d+)\)$/', $part, $m)) continue;

            $sku       = trim($m[1]);
            $localFrom = (int)$m[2];
            $localTo   = (int)$m[3];

            $detail = $po->po_detail->first(fn($d) => $d->part_number === $sku);
            if (!$detail) continue;

            // Query 1: ambil sequence_no yang masuk batch ini
            $seqNos = DB::table('tproduct_qr')
                ->where('id_po', $po->id)
                ->where('id_po_detail', $detail->id)
                ->orderBy('id', 'asc')
                ->skip($localFrom - 1)
                ->take($localTo - $localFrom + 1)
                ->pluck('sequence_no')
                ->toArray();

            if (empty($seqNos)) continue;

            // Query 2: bulk consume — 1 UPDATE untuk semua sequence sekaligus
            DB::table('tqr_reprint_request')
                ->where('id_po', $po->id)
                ->where('id_po_detail', $detail->id)
                ->whereIn('sequence_no', $seqNos)
                ->where('status', 'APPROVED')
                ->whereNull('used_at')
                ->update(['used_at' => now()]);
        }
    }

    public function showBatch($batchId)
    {
        $batch = DB::table('print_batches')->where('id', $batchId)->first();

        abort_if(!$batch, 404);
        abort_if($batch->user_id !== auth()->id(), 403);

        $po = Tpo::findOrFail($batch->id_po);

        // Parse content_summary menjadi array item untuk ditampilkan di tabel
        $items = [];
        if ($batch->content_summary) {
            foreach (explode(',', $batch->content_summary) as $part) {
                $part = trim($part);
                if (preg_match('/^(.+?)\s*\((\d+)-(\d+)\)$/', $part, $m)) {
                    $items[] = [
                        'sku'      => trim($m[1]),
                        'dari'     => $m[2],
                        'sampai'   => $m[3],
                        'jumlah'   => (int)$m[3] - (int)$m[2] + 1,
                    ];
                }
            }
        }

        return view('print.batch-detail', compact('batch', 'po', 'items'));
    }

   /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages.transaction.purchase_order.purchase_order_index');
    }

    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $query = Tpo::orderByDesc('tgl_po');
            // Filter Date
            if ($request->filled('date_from') && $request->filled('date_to')) {
                $query->whereBetween('tgl_po', [
                    $request->date_from,
                    $request->date_to
                ]);
            }

            // Filter Status
            if ($request->filled('status_po')) {
                $query->where('status_po', $request->status_po);
            }

            return DataTables::of($query)
                ->addColumn('id', fn($row) => $row->id)
                // ->addColumn('action', function($row) {
                //     $btn = '<a href="'.route('purchase_order.show', $row->id).'" class="btn btn-success btn-sm"><i class="fa fa-eye"></i></a> ';
                //     $btn .= '<a 
                //         href="'.route('purchase_order.print_po', $row->id).'" 
                //         target="_blank"
                //         class="btn btn-dark btn-sm"
                //         title="Print PO">
                //         <i class="fa fa-print"></i>
                //     </a> ';
                    
                //     if (Auth::user()->position === 'SUPERADMIN' && ($row->status_po == 0)) {
                //         $btn .= '<a href="javascript:void(0)" 
                //             onclick="confirmOrder('.$row->id.', \''.$row->no_po.'\')" 
                //             class="btn btn-primary btn-sm" 
                //             title="Confirm Order">
                //             <i class="fa fa-check"></i>
                //         </a> ';
                //     }

                //     if (!in_array($row->status_po, [2, 3])) {
                //         $btn .= '
                //             <button 
                //                 type="button"
                //                 class="btn btn-danger btn-sm show-alert-delete-box"
                //                 data-id="'.$row->id.'"
                //                 data-no-po="'.$row->no_po.'">
                //                 <i class="fa fa-times-circle"></i>
                //             </button>
                //         ';
                //     }
                //     // $btn .= '<a 
                //     //     href="'.route('purchase_order.edit', $row->id).'" 
                //     //     class="btn btn-warning btn-sm"
                //     //     title="Edit PO">
                //     //     <i class="fa fa-edit"></i>
                //     //     </a> ';
                        
                //     $req = DB::table('tproduct_qr')
                //         ->where('id_po', $row->id)
                //         ->exists();
                    
                //     if (!empty($row->confirm_by) && $req) {
                //         $btn .= '<a href="'.route('purchase_order.reprint_list', $row->id).'" 
                //             class="btn btn-info btn-sm" 
                //             title="Request Reprint">
                //             <i class="fa fa-file-alt"></i>
                //             Cetak Ulang
                //         </a> ';
                //     }

                //     return $btn;
                // })
                ->addColumn('action', function($row) {

                    $btn = '<a href="'.route('purchase_order.show', $row->id).'" class="btn btn-success btn-sm">
                                <i class="fa fa-eye"></i>
                            </a> ';
                
                    // PRINT PO (sekali)
                    if (Permission::print('MENU-0301')) {
                        $btn .= '<a href="'.route('purchase_order.print_po', $row->id).'" 
                                    target="_blank"
                                    class="btn btn-dark btn-sm">
                                    <i class="fa fa-print"></i>
                                </a> ';
                    }
                
                    // CONFIRM / APPROVE
                    if (Permission::approve('MENU-0301') && $row->status_po == 0) {
                        $btn .= '<button class="btn btn-primary btn-sm"
                                    onclick="confirmOrder('.$row->id.', \''.$row->no_po.'\')">
                                    <i class="fa fa-check"></i>
                                </button> ';
                    }
                
                    // REJECT
                    if (Permission::reject('MENU-0301') && !in_array($row->status_po, [2,3])) {
                        $btn .= '<button class="btn btn-danger btn-sm show-alert-delete-box"
                                    data-id="'.$row->id.'">
                                    <i class="fa fa-times-circle"></i>
                                </button> ';
                    }
                
                    // CETAK ULANG (HARUS PRINT + APPROVE)
                    if (
                        Permission::print('MENU-0301') &&
                        Permission::approve('MENU-0301') &&
                        !empty($row->confirm_by)
                    ) {
                        $btn .= '<a href="'.route('purchase_order.reprint_list', $row->id).'"
                                    class="btn btn-info btn-sm">
                                    Cetak Ulang
                                </a> ';
                    }
                
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return abort(403);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function search(Request $request)
    {
        // $customers      = MCustomer::get();
        $purchase_order = Tpo::with(['customer'])->where('nama_cust', 'LIKE', request()->search.'%')
                                ->orWhere('code_cust', 'LIKE' , request()->search.'%')
                                ->orWhere('tgl_po', 'LIKE' , request()->search.'%')
                                ->orWhere('no_po', 'LIKE' , request()->search.'%')
                                ->orWhere('no_so', 'LIKE' , request()->search.'%')
                                // ->orWhere('status_po', 'LIKE' ,request()->search.'%')
                                ->orWhere('reason_po', 'LIKE' ,request()->search.'%')
                                ->orderBy('tgl_po','desc')
                                ->paginate();
        // $pageTitle = "Register Management";
        $no  = ($purchase_order->currentPage()*$purchase_order->perPage())-$purchase_order->perPage()+1;
        return view('pages.transaction.purchase_order.purchase_order_index',compact('purchase_order','no'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }

    public function history($id)
    {
        $purchase_order_his = Hpo::where('id_po',$id)->get();
        return view('pages.transaction.purchase_order.purchase_order_history',compact('purchase_order_his'));
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

 
	public function upload(Request $request) 
	{
		// validasi
		$this->validate($request, [
			'file' => 'required|mimes:xlsx'
		]);
 
		// menangkap file excel
		$file = $request->file('file');
 
		// membuat nama file unik
		$nama_file = rand().$file->getClientOriginalName();
 
		// upload ke folder file_po di dalam folder public
		$file->move('file_po',$nama_file);
 
		// import data
		Excel::import(new PurchaseOrderImport, public_path('/file_po/'.$nama_file));
 
		// notifikasi dengan session
		// Session::flash('sukses','Data PO Berhasil Diimport!');
 
		// alihkan halaman kembali
		return redirect()->route('purchase_order.index');
	}

    public function create()
    {
        $customers = MCustomer::get();
        $suppliers = MSupplier::get();
        $products  = Mproduct::select('id', 'nama_barang', 'harga_beli', 'sku')
        ->whereNull('deleted_at')
        ->where('flag_active','Y')
        ->get();

        return view('pages.transaction.purchase_order.purchase_order_create', compact('customers', 'suppliers', 'products'));
    }
    
    public function listExistingPO()
    {
        return Tpo::select('id', 'no_po')
            ->where('status_po', '!=', 5) // exclude canceled
            ->orderByDesc('tgl_po')
            ->get();
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    
    public function store(Request $request)
    {
        $this->poLog('BUAT_PO', "User: {$this->actor()} | No PO: {$request->no_po} | Supplier ID: {$request->id_supplier} | Tipe: {$request->po_type} | Tgl: {$request->tgl_po} | Status: PROCESS");

        try {
            $this->validate($request, [
                'id_supplier' => 'required',
                'tgl_po'      => 'required|date',
                'reason_po'   => 'nullable|string',
                'po_type'     => 'required|in:baru,tambahan',
                'no_po'       => 'required'
            ]);

            DB::beginTransaction();

            // ===============================
            // SUPPLIER
            // ===============================
            $supplier = MSupplier::select('code_spl','nama_spl')
                ->where('id', $request->id_supplier)
                ->first();

            if (!$supplier) {
                throw new \Exception('Supplier tidak ditemukan');
            }

            // ===============================
            // NO PO HANDLING
            // ===============================
            $finalNoPo = $request->no_po;
            $exists = Tpo::where('no_po', $finalNoPo)->exists();

            if ($exists) {
                throw new \Exception('Nomor PO sudah digunakan');
            }

            if ($request->po_type === 'tambahan') {
                if (!$request->base_po_id) {
                    throw new \Exception('PO asal wajib dipilih');
                }

                $basePo = Tpo::findOrFail($request->base_po_id);

                // prefix T-
                $finalNoPo = 'T-' . $basePo->no_po;
            }

            // ===============================
            // CREATE PO (SELALU BARU)
            // ===============================
            $purchase_order = Tpo::create([
                'id_cust'       => 0,
                'id_supplier'   => $request->id_supplier,
                'code_cust'     => '',
                'nama_cust'     => '',
                'code_spl'      => $supplier->code_spl,
                'nama_spl'      => $supplier->nama_spl,
                'no_po'         => $finalNoPo,
                'no_so'         => 0,
                'tgl_po'        => $request->tgl_po,
                'status_po'     => 0,
                'reason_po'     => $request->reason_po,
                'grand_total'   => 0,
                'flag_approve'  => 'N',
                'approve_date'  => '1970-01-01',
                'approve_by'    => '',
                'parent_po_id'  => $request->po_type === 'tambahan' ? $request->base_po_id : null
            ]);

            // ===============================
            // DETAIL
            // ===============================
            foreach ($request->sku as $i => $sku) {
                Tpo_Detail::create([
                    'id_po'        => $purchase_order->id,
                    'part_number'  => $sku,
                    'product_name' => $request->nama_barang[$i],
                    'qty'          => $request->qty[$i],
                    'price'        => 0,
                    'total_price'  => 0
                ]);
            }

            // ===============================
            // HISTORY
            // ===============================
            Hpo::create([
                'id_po'     => $purchase_order->id,
                'code_spl'  => $supplier->code_spl,
                'nama_spl'  => $supplier->nama_spl,
                'no_po'     => $finalNoPo,
                'tgl_po'    => $request->tgl_po,
                'reason_po' => $request->reason_po,
            ]);

            DB::commit();

            $this->poLog('BUAT_PO', "User: {$this->actor()} | No PO: {$finalNoPo} | Supplier: {$supplier->nama_spl} | Tipe: {$request->po_type} | PO ID: {$purchase_order->id} | Status: SUCCESS");

            return response()->json([
                'status'  => 'success',
                'message' => 'PO berhasil dibuat'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->poLog('BUAT_PO', "User: {$this->actor()} | No PO: {$request->no_po} | Status: FAILED | Error: {$e->getMessage()}");
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $purchase_order = Tpo::findOrFail($id);
        $purchase_order_dtl   = Tpo_Detail::where('id_po',$id)->get();
        $customers = MCustomer::get();
        $suppliers = MSupplier::get();
        $status_po = [
            '....' => '....',
            'OK' => 'OK',
            'HOLD' => 'HOLD'
        ];
        return view('pages.transaction.purchase_order.purchase_order_show',compact('purchase_order','customers','suppliers', 'purchase_order_dtl', 'status_po'));
    }

    public function printPO($id)
    {
        
        if (!Permission::print('MENU-0301')) {
            abort(403, 'Anda tidak punya hak cetak PO');
        }
        
        $po = Tpo::with(['supplier'])
            ->findOrFail($id);

        $details = Tpo_Detail::where('id_po', $id)
            ->orderBy('id')
            ->get();

        $pdf = Pdf::loadView(
            'pages.transaction.purchase_order.purchase_order_print',
            [
                'po'      => $po,
                'details' => $details
            ]
        )->setPaper('A4', 'portrait');

        return $pdf->stream("PO_{$po->no_po}.pdf");
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $purchase_order       = Tpo::findOrFail($id);
        $purchase_order_dtl   = Tpo_Detail::where('id_po',$id)->get();
        $products  = Mproduct::select('id', 'nama_barang', 'harga_beli', 'sku')->whereNull('deleted_at')->get();

        $customers = MCustomer::get();
        $suppliers = MSupplier::get();
        $status_po = [
            '....' => '....',
            'OK' => 'OK',
            'HOLD' => 'HOLD'
        ];
        return view('pages.transaction.purchase_order.purchase_order_edit',compact('purchase_order','customers','suppliers', 'status_po','purchase_order_dtl','products'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function approve(Request $request, $id)
    {
        if (!Permission::approve('MENU-0301')) {
            $this->poLog('APPROVE_PO', "User: {$this->actor()} | PO ID: {$id} | Status: FORBIDDEN | Error: Tidak punya hak konfirmasi");
            abort(403, 'Anda tidak punya hak konfirmasi');
        }

        $approve_by = Auth::user()->id;

        $approve = TPo::find($id);

        if (!$approve) {
            $this->poLog('APPROVE_PO', "User: {$this->actor()} | PO ID: {$id} | Status: FAILED | Error: Purchase Order tidak ditemukan");
            return response()->json(['error' => 'Purchase Order tidak ditemukan']);
        }

        $this->poLog('APPROVE_PO', "User: {$this->actor()} | PO: {$approve->no_po} (ID:{$id}) | Status: PROCESS");

        try {
            $approve->approve_by = $approve_by;
            $approve->approve_date = date('Y-m-d');
            $approve->flag_approve = "Y";

            $updated = $approve->save();

            if ($updated) {
                $this->poLog('APPROVE_PO', "User: {$this->actor()} | PO: {$approve->no_po} (ID:{$id}) | Status: APPROVED");
                return response()->json(['success' => true]);
            } else {
                $this->poLog('APPROVE_PO', "User: {$this->actor()} | PO: {$approve->no_po} (ID:{$id}) | Status: FAILED | Error: save() returned false");
                return response()->json(['error' => 'Persetujuan gagal']);
            }
        } catch (\Exception $e) {
            $this->poLog('APPROVE_PO', "User: {$this->actor()} | PO: {$approve->no_po} (ID:{$id}) | Status: FAILED | Error: {$e->getMessage()}");
            return response()->json(['error' => 'Persetujuan gagal: ' . $e->getMessage()]);
        }
    }
    
    public function confirm(Request $request, $id)
    {
        if (!Permission::approve('MENU-0301')) {
            $this->poLog('CONFIRM_PO', "User: {$this->actor()} | PO ID: {$id} | Status: FORBIDDEN | Error: Tidak punya hak konfirmasi");
            abort(403, 'Anda tidak punya hak konfirmasi');
        }

        $confirm = Tpo::find($id);

        if (!$confirm) {
            $this->poLog('CONFIRM_PO', "User: {$this->actor()} | PO ID: {$id} | Status: FAILED | Error: PO tidak ditemukan");
            return response()->json([
                'success' => false,
                'error' => 'PO tidak ditemukan'
            ]);
        }

        $this->poLog('CONFIRM_PO', "User: {$this->actor()} | PO: {$confirm->no_po} (ID:{$id}) | Status: PROCESS");

        try {
            $confirm->confirm_by   = Auth::user()->id;
            $confirm->confirm_date = date('Y-m-d');
            $confirm->status_po = '4';
            $confirm->save();

            $this->poLog('CONFIRM_PO', "User: {$this->actor()} | PO: {$confirm->no_po} (ID:{$id}) | Status: CONFIRMED | Status PO: 4");

            return response()->json([
                'success' => true
            ]);
        } catch (\Exception $e) {
            $this->poLog('CONFIRM_PO', "User: {$this->actor()} | PO: {$confirm->no_po} (ID:{$id}) | Status: FAILED | Error: {$e->getMessage()}");
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
     
    public function update(Request $request, $id)
    {
        $po = Tpo::findOrFail($id);
        $status = $po->status_po;

        $this->poLog('UPDATE_PO', "User: {$this->actor()} | PO: {$po->no_po} (ID:{$id}) | Status PO saat ini: {$status} | Status: PROCESS");

        // ===============================
        // STATUS 3 = PARTIAL (LOCK TOTAL)
        // ===============================
        if ($status == 3) {
            $this->poLog('UPDATE_PO', "User: {$this->actor()} | PO: {$po->no_po} (ID:{$id}) | Status: BLOCKED | Info: PO sudah PARTIAL tidak bisa diedit");
            return redirect()
                ->back()
                ->with('error','PO sudah PARTIAL, tidak bisa diedit');
        }

        DB::beginTransaction();
        try {
    
            // ===============================
            // UPDATE HEADER (STATUS 0 & 4)
            // ===============================
            if (in_array($status, [0,4])) {
    
                $request->validate([
                    'id_supplier' => 'required',
                    'tgl_po'      => 'required|date',
                    'reason_po'   => 'nullable|string'
                ]);
    
                $supplier = MSupplier::findOrFail($request->id_supplier);
    
                $po->update([
                    'id_supplier' => $request->id_supplier,
                    'code_spl'    => $supplier->code_spl,
                    'nama_spl'    => $supplier->nama_spl,
                    'tgl_po'      => $request->tgl_po,
                    'reason_po'   => $request->reason_po,
                ]);
            }
    
            // ===============================
            // UPDATE DETAIL (STATUS 0)
            // ===============================
            if ($status == 0) {
    
                $request->validate([
                    'sku.*' => 'required',
                    'qty.*'         => 'required|integer|min:1'
                ]);
    
                // hapus detail lama
                Tpo_Detail::where('id_po', $po->id)->delete();
    
                foreach ($request->sku as $i => $kode) {
    
                    $product = MProduct::where('sku', $kode)->first();
    
                    Tpo_Detail::create([
                        'id_po'        => $po->id,
                        'part_number'  => $kode,
                        'product_name' => $product->nama_barang ?? '',
                        'qty'          => $request->qty[$i],
                        'qty_extra'    => 0
                    ]);
                }
            }
    
            // ===============================
            // UPDATE QTY LEBIHAN (STATUS 2)
            // ===============================
            if ($status == 2) {
    
                $request->validate([
                    'qty_extra.*' => 'nullable|integer|min:0'
                ]);
    
                $details = Tpo_Detail::where('id_po', $po->id)->get();
    
                foreach ($details as $i => $dtl) {
                    $dtl->update([
                        'qty_extra' => $request->qty_extra[$i] ?? 0
                    ]);
                }
            }
    
            // ===============================
            // HISTORY
            // ===============================
            Hpo::create([
                'id_po'     => $po->id,
                'code_spl'  => $po->code_spl,
                'nama_spl'  => $po->nama_spl,
                'no_po'     => $po->no_po,
                'tgl_po'    => $po->tgl_po,
                'reason_po' => $po->reason_po,
                'status_po' => $po->status_po
            ]);
    
            DB::commit();

            $this->poLog('UPDATE_PO', "User: {$this->actor()} | PO: {$po->no_po} (ID:{$id}) | Status PO: {$status} | Status: UPDATED");

            return redirect()
                ->route('purchase_order.index')
                ->with('success','PO berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->poLog('UPDATE_PO', "User: {$this->actor()} | PO: {$po->no_po} (ID:{$id}) | Status: FAILED | Error: {$e->getMessage()}");

            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    
    // public function delete($id)
    // {
    //     $data = Tpo::find($id);
    //     $data->delete();
    //     return redirect()->route('purchase_order.index');
    // }
    public function delete($id)
    {
        if (!Permission::can('MENU-0301','reject')) {
            $this->poLog('REJECT_PO', "User: {$this->actor()} | PO ID: {$id} | Status: FORBIDDEN | Error: Tidak punya hak reject");
            return response()->json(['message'=>'Tidak diizinkan'],403);
        }

        $po = Tpo::findOrFail($id);

        // safety check (backend tetap wajib)
        if (in_array($po->status_po, [2, 3])) {
            $this->poLog('REJECT_PO', "User: {$this->actor()} | PO: {$po->no_po} (ID:{$id}) | Status PO saat ini: {$po->status_po} | Status: BLOCKED | Info: PO tidak bisa dibatalkan");
            return response()->json([
                'success' => false,
                'message' => 'PO tidak bisa dibatalkan'
            ], 422);
        }

        $po->status_po  = 5;
        $po->reason_po  = 'Canceled by user';
        $po->save();

        $this->poLog('REJECT_PO', "User: {$this->actor()} | PO: {$po->no_po} (ID:{$id}) | Status: CANCELED");

        return response()->json([
            'success' => true,
            'message' => 'PO berhasil dibatalkan'
        ]);
    }

    public function bin()
    {
        // dd('ok');
        // $data['pageTitle']      = 'BIN Ticket';
        // $data   ['data']      = Tpo::onlyTrashed()->paginate(5);
        // $data   ['customers'] = MCustomer::get();
        // dd($data);
        return view('pages.transaction.purchase_order.purchase_order_bin');
    }

    public function binData(Request $request)
    {
        if (auth()->user()->position !== 'SUPERADMIN') {
            abort(403);
        }

        $query = Tpo::onlyTrashed()->with('supplier');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('code_spl', fn($row) => $row->supplier->code_spl ?? '-')
            ->addColumn('nama_spl', fn($row) => $row->supplier->nama_spl ?? '-')
            ->addColumn('action', function ($row) {
                return '<button class="btn btn-sm btn-primary show-alert-rollback-box" data-id="' . $row->id . '"><i class="fa fa-undo"></i></button>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }
    
    public function rollback(Request $request)
    {
        try {
            $id = $request->id;
    
            if (!$id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ID tidak ditemukan. Mohon muat ulang halaman.'
                ], 400);
            }
    
            $data = Tpo::onlyTrashed()->where('id', $id)->first();
    
            if (!$data) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data tidak ditemukan atau sudah dipulihkan sebelumnya.'
                ], 404);
            }
    
            $data->restore();
    
            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil dipulihkan.'
            ]);
        } catch (\Exception $e) {
    
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan sistem. Silakan coba beberapa saat lagi.'
            ], 500);
        }
    }

    // public function generateQRPDF(Request $r, $id)
    // {
    //     $po = Tpo::with('po_detail')->findOrFail($id);

    //     // Single product + nomor urut
    //     if ($r->detail && $r->seq) {
    //         return $this->generateSingleQR($po, $r->detail, $r->seq);
    //     }

    //     // Multiple products langsung print
    //     if ($r->multi) {
    //         $ids = explode(",", $r->multi);
    //         return $this->generateMultipleQR($po, $ids);
    //     }

    //     // default all product
    //     return $this->generateAllQR($po);
    // }
    
    // private function generateSingleQR($po, $detailId, $seqText)
    // {
    //     $detail = $po->po_detail->where('id', $detailId)->first();
    //     // if (!$detail) abort(404);

    //     $sequences = $this->parseSequenceInput($seqText);
    //     if (empty($sequences)) {
    //         abort(422, 'Nomor urut tidak valid');
    //     }

    //     $qrList = [];

    //     foreach ($sequences as $num) {

    //         $seqStr = str_pad($num, 4, '0', STR_PAD_LEFT);

    //         /**
    //          * 1️⃣ CEK BOLEH CETAK ATAU TIDAK
    //          */
    //         if (!$this->canPrintQR($po->id, $detail->id, $seqStr)) {
    //             return response()->json([
    //                 'message' => "Sequence {$seqStr} wajib mengajukan request reprint terlebih dahulu"
    //             ], 403);
    //         }

    //         /**
    //          * 2️⃣ BUAT QR (FIXED SEQUENCE)
    //          */
    //         $qrList[] = $this->createQRWithFixedSequence(
    //             $po,
    //             $detail,
    //             $num
    //         );

    //         /**
    //          * 3️⃣ HABISKAN APPROVAL (KALAU ADA)
    //          */
    //         DB::table('tqr_reprint_request')
    //             ->where([
    //                 'id_po'        => $po->id,
    //                 'id_po_detail' => $detail->id,
    //                 'sequence_no'  => $seqStr,
    //                 'status'       => 'APPROVED'
    //             ])
    //             ->whereNull('used_at')
    //             ->update([
    //                 'used_at' => now()
    //             ]);
    //     }

    //     /**
    //      * 4️⃣ CETAK PDF
    //      */
    //     return $this->printPDF($po, $qrList);
    // }

    // private function generateMultipleQR($po, $ids)
    // {
    //     $qrList = [];
    
    //     /**
    //      * =========================================
    //      * 1️⃣ VALIDASI TOTAL (TIDAK BOLEH ADA YANG GAGAL)
    //      * =========================================
    //      */
    //     foreach ($po->po_detail as $detail) {
    
    //         if (!in_array($detail->id, $ids)) {
    //             continue;
    //         }
    
    //         for ($num = 1; $num <= intval($detail->qty); $num++) {
    
    //             $seqStr = str_pad($num, 4, '0', STR_PAD_LEFT);
    
    //             if (!$this->canPrintQR($po->id, $detail->id, $seqStr)) {
    //                 return response()->json([
    //                     'message' => "PO {$po->no_po} - {$detail->product_name} sequence {$seqStr} sudah pernah dicetak. Wajib ajukan request reprint terlebih dahulu."
    //                 ], 403);
    //             }
    //         }
    //     }
    
    //     /**
    //      * =========================================
    //      * 2️⃣ GENERATE QR (SUDAH PASTI AMAN)
    //      * =========================================
    //      */
    //     foreach ($po->po_detail as $detail) {
    
    //         if (!in_array($detail->id, $ids)) {
    //             continue;
    //         }
    
    //         for ($num = 1; $num <= intval($detail->qty); $num++) {
    
    //             $seqStr = str_pad($num, 4, '0', STR_PAD_LEFT);
    
    //             $qrList[] = $this->createQRWithFixedSequence(
    //                 $po,
    //                 $detail,
    //                 $num
    //             );
    
    //             /**
    //              * 3️⃣ HABISKAN APPROVAL REPRINT (JIKA ADA)
    //              */
    //             DB::table('tqr_reprint_request')
    //                 ->where([
    //                     'id_po'        => $po->id,
    //                     'id_po_detail' => $detail->id,
    //                     'sequence_no'  => $seqStr,
    //                     'status'       => 'APPROVED'
    //                 ])
    //                 ->whereNull('used_at')
    //                 ->update([
    //                     'used_at' => now()
    //                 ]);
    //         }
    //     }
    
    //     /**
    //      * =========================================
    //      * 4️⃣ CETAK PDF
    //      * =========================================
    //      */
    //     return $this->printPDF($po, $qrList);
    // }
    
    public function generateQRPDF(Request $r, $id)
    {
        $po = Tpo::with('po_detail')->findOrFail($id);

        // Single product + nomor urut
        if ($r->detail && $r->seq) {
            return $this->generateSingleQR($po, $r->detail, $r->seq);
        }

        // Multiple products langsung print
        if ($r->multi) {
            $ids = explode(",", $r->multi);
            return $this->generateMultipleQR($po, $ids);
        }

        // default all product
        return $this->generateAllQR($po);
    }
    
    // private function generateSingleQR($po, $detailId, $seqText)
    // {
    //     $detail = $po->po_detail->where('id', $detailId)->first();
    
    //     $sequences = $this->parseSequenceInput($seqText);
    //     if (empty($sequences)) {
    //         abort(422, 'Nomor urut tidak valid');
    //     }
    
    //     $qrList    = [];
    //     $conflicts = [];
    
    //     foreach ($sequences as $num) {
    
    //         $seqStr = str_pad($num, 4, '0', STR_PAD_LEFT);
    
    //         if (!$this->canPrintQR($po->id, $detail->id, $seqStr)) {
    //             $conflicts[] = [
    //                 'id_po_detail' => $detail->id,
    //                 'sku'          => $detail->part_number,
    //                 'product_name' => $detail->product_name,
    //                 'sequence'     => $seqStr
    //             ];
    //         }
    //     }
    
    //     if (!empty($conflicts)) {
    //         return response()->json([
    //             'code'      => 'QR_ALREADY_PRINTED',
    //             'message'   => 'Terdapat QR yang sudah pernah dicetak',
    //             'conflicts' => $conflicts
    //         ], 403);
    //     }
    
    //     foreach ($sequences as $num) {
    
    //         $seqStr = str_pad($num, 4, '0', STR_PAD_LEFT);
    
    //         $qrList[] = $this->createQRWithFixedSequence(
    //             $po,
    //             $detail,
    //             $num
    //         );
    
    //         DB::table('tqr_reprint_request')
    //             ->where([
    //                 'id_po'        => $po->id,
    //                 'id_po_detail' => $detail->id,
    //                 'sequence_no'  => $seqStr,
    //                 'status'       => 'APPROVED'
    //             ])
    //             ->whereNull('used_at')
    //             ->update(['used_at' => now()]);
    //     }
    
    //     return $this->printPDF($po, $qrList);
    // }

    private function generateSingleQR($po, $detailId, $seqText)
    {
        $detail = $po->po_detail->where('id', $detailId)->first();
        if (!$detail) {
            abort(404, 'Detail PO tidak ditemukan');
        }

        // parse seq: "1-3,5" → [1,2,3,5]
        $sequences = $this->parseSequenceInput($seqText);
        if (empty($sequences)) {
            abort(422, 'Nomor urut tidak valid');
        }

        // 🔑 FLAG MODE
        // ada parameter seq = REPRINT
        $isReprint = request()->has('seq');

        $qrList    = [];
        $conflicts = [];

        /**
         * =====================================
         * 1️⃣ VALIDASI DULU (SEMUA SEQ)
         * =====================================
         */
        foreach ($sequences as $num) {

            $seqStr = str_pad($num, 4, '0', STR_PAD_LEFT);

            // kalau generate biasa → cek boleh print atau tidak
            if (!$isReprint && !$this->canPrintQR($po->id, $detail->id, $seqStr)) {
                $conflicts[] = [
                    'id_po_detail' => $detail->id,
                    'sku'          => $detail->part_number,
                    'product_name' => $detail->product_name,
                    'sequence'     => $seqStr
                ];
            }

            // kalau reprint → pastikan QR memang ada
            if ($isReprint) {
                $exists = DB::table('tproduct_qr')
                    ->where([
                        'id_po'        => $po->id,
                        'id_po_detail' => $detail->id,
                        'sequence_no'  => $seqStr
                    ])
                    ->exists();

                if (!$exists) {
                    abort(404, "QR tidak ditemukan untuk sequence {$seqStr}");
                }
            }
        }

        if (!empty($conflicts)) {
            return response()->json([
                'code'      => 'QR_ALREADY_PRINTED',
                'message'   => 'Terdapat QR yang sudah pernah dicetak',
                'conflicts' => $conflicts
            ], 403);
        }

        /**
         * =====================================
         * 2️⃣ PROSES CETAK
         * =====================================
         */
        foreach ($sequences as $num) {

            $seqStr = str_pad($num, 4, '0', STR_PAD_LEFT);

            if ($isReprint) {

                // 🔁 REPRINT → AMBIL DATA QR EXISTING (TANPA INSERT)
                $qr = DB::table('tproduct_qr')
                    ->where([
                        'id_po'        => $po->id,
                        'id_po_detail' => $detail->id,
                        'sequence_no'  => $seqStr
                    ])
                    ->orderByDesc('id')
                    ->first();

                if (!$qr) {
                    abort(404, "QR tidak ditemukan untuk sequence {$seqStr}");
                }

                $qrList[] = [
                    'nama_barang' => $qr->nama_barang,
                    'sku'         => $qr->sku,
                    'nomor_urut'  => $qr->sequence_no,
                    'qr_payload'  => $qr->qr_code,
                ];

            } else {

                // 🆕 GENERATE BARU → INSERT
                $qrList[] = $this->createQRWithFixedSequence(
                    $po,
                    $detail,
                    $num
                );
            }

            /**
             * =====================================
             * 3️⃣ HABISKAN APPROVAL REPRINT (JIKA ADA)
             * =====================================
             */
            DB::table('tqr_reprint_request')
                ->where([
                    'id_po'        => $po->id,
                    'id_po_detail' => $detail->id,
                    'sequence_no'  => $seqStr,
                    'status'       => 'APPROVED'
                ])
                ->whereNull('used_at')
                ->update(['used_at' => now()]);
        }

        /**
         * =====================================
         * 4️⃣ CETAK PDF
         * =====================================
         */
        return $this->printPDF($po, $qrList);
    }

    // private function generateMultipleQR($po, $ids)
    // {
    //     $qrList    = [];
    //     $conflicts = [];

    //     foreach ($po->po_detail as $detail) {

    //         if (!in_array($detail->id, $ids)) continue;

    //         for ($num = 1; $num <= intval($detail->qty); $num++) {

    //             $seqStr = str_pad($num, 4, '0', STR_PAD_LEFT);

    //             if (!$this->canPrintQR($po->id, $detail->id, $seqStr)) {
    //                 $conflicts[] = [
    //                     'id_po_detail' => $detail->id,
    //                     'sku'          => $detail->part_number,
    //                     'product_name' => $detail->product_name,
    //                     'sequence'     => $seqStr
    //                 ];
    //             }
    //         }
    //     }

    //     if (!empty($conflicts)) {
    //         return response()->json([
    //             'code'      => 'QR_ALREADY_PRINTED',
    //             'message'   => 'Terdapat QR yang sudah pernah dicetak',
    //             'conflicts' => $conflicts
    //         ], 403);
    //     }

    //     foreach ($po->po_detail as $detail) {

    //         if (!in_array($detail->id, $ids)) continue;

    //         for ($num = 1; $num <= intval($detail->qty); $num++) {

    //             $seqStr = str_pad($num, 4, '0', STR_PAD_LEFT);

    //             $qrList[] = $this->createQRWithFixedSequence(
    //                 $po,
    //                 $detail,
    //                 $num
    //             );

    //             DB::table('tqr_reprint_request')
    //                 ->where([
    //                     'id_po'        => $po->id,
    //                     'id_po_detail' => $detail->id,
    //                     'sequence_no'  => $seqStr,
    //                     'status'       => 'APPROVED'
    //                 ])
    //                 ->whereNull('used_at')
    //                 ->update(['used_at' => now()]);
    //         }
    //     }

    //     return $this->printPDF($po, $qrList);
    // }

    private function generateMultipleQR($po, $ids)
    {
        $qrList    = [];
        $conflicts = [];

        // 🔑 FLAG MODE
        // jika ada parameter seq → REPRINT
        $isReprint = request()->has('seq');

        /**
         * =====================================
         * 1️⃣ VALIDASI SEMUA DETAIL & SEQUENCE
         * =====================================
         */
        foreach ($po->po_detail as $detail) {

            if (!in_array($detail->id, $ids)) continue;

            for ($num = 1; $num <= intval($detail->qty); $num++) {

                $seqStr = str_pad($num, 4, '0', STR_PAD_LEFT);

                // GENERATE MODE → cek boleh print
                if (!$isReprint && !$this->canPrintQR($po->id, $detail->id, $seqStr)) {
                    $conflicts[] = [
                        'id_po_detail' => $detail->id,
                        'sku'          => $detail->part_number,
                        'product_name' => $detail->product_name,
                        'sequence'     => $seqStr
                    ];
                }

                // REPRINT MODE → QR harus sudah ada
                if ($isReprint) {
                    $exists = DB::table('tproduct_qr')
                        ->where([
                            'id_po'        => $po->id,
                            'id_po_detail' => $detail->id,
                            'sequence_no'  => $seqStr
                        ])
                        ->exists();

                    if (!$exists) {
                        abort(
                            404,
                            "QR tidak ditemukan untuk {$detail->product_name} sequence {$seqStr}"
                        );
                    }
                }
            }
        }

        if (!empty($conflicts)) {
            return response()->json([
                'code'      => 'QR_ALREADY_PRINTED',
                'message'   => 'Terdapat QR yang sudah pernah dicetak',
                'conflicts' => $conflicts
            ], 403);
        }

        /**
         * =====================================
         * 2️⃣ PROSES CETAK
         * =====================================
         */
        foreach ($po->po_detail as $detail) {

            if (!in_array($detail->id, $ids)) continue;

            for ($num = 1; $num <= intval($detail->qty); $num++) {

                $seqStr = str_pad($num, 4, '0', STR_PAD_LEFT);

                if ($isReprint) {

                    // 🔁 REPRINT → AMBIL QR EXISTING (TANPA INSERT)
                    $qr = DB::table('tproduct_qr')
                        ->where([
                            'id_po'        => $po->id,
                            'id_po_detail' => $detail->id,
                            'sequence_no'  => $seqStr
                        ])
                        ->orderByDesc('id')
                        ->first();

                    if (!$qr) {
                        abort(
                            404,
                            "QR tidak ditemukan untuk {$detail->product_name} sequence {$seqStr}"
                        );
                    }

                    $qrList[] = [
                        'nama_barang' => $qr->nama_barang,
                        'sku'         => $qr->sku,
                        'nomor_urut'  => $qr->sequence_no,
                        'qr_payload'  => $qr->qr_code,
                    ];

                } else {

                    // 🆕 GENERATE BARU → INSERT
                    $qrList[] = $this->createQRWithFixedSequence(
                        $po,
                        $detail,
                        $num
                    );
                }

                /**
                 * =====================================
                 * 3️⃣ HABISKAN APPROVAL REPRINT (JIKA ADA)
                 * =====================================
                 */
                DB::table('tqr_reprint_request')
                    ->where([
                        'id_po'        => $po->id,
                        'id_po_detail' => $detail->id,
                        'sequence_no'  => $seqStr,
                        'status'       => 'APPROVED'
                    ])
                    ->whereNull('used_at')
                    ->update(['used_at' => now()]);
            }
        }

        /**
         * =====================================
         * 4️⃣ CETAK PDF
         * =====================================
         */
        return $this->printPDF($po, $qrList);
    }

    // private function generateAllQR($po)
    // {
    //     $qrList    = [];
    //     $conflicts = [];

    //     foreach ($po->po_detail as $detail) {

    //         for ($num = 1; $num <= intval($detail->qty); $num++) {

    //             $seqStr = str_pad($num, 4, '0', STR_PAD_LEFT);

    //             if (!$this->canPrintQR($po->id, $detail->id, $seqStr)) {
    //                 $conflicts[] = [
    //                     'id_po_detail' => $detail->id,
    //                     'sku'          => $detail->part_number,
    //                     'product_name' => $detail->product_name,
    //                     'sequence'     => $seqStr
    //                 ];
    //             }
    //         }
    //     }

    //     if (!empty($conflicts)) {
    //         return response()->json([
    //             'code'      => 'QR_ALREADY_PRINTED',
    //             'message'   => 'Terdapat QR yang sudah pernah dicetak',
    //             'conflicts' => $conflicts
    //         ], 403);
    //     }

    //     foreach ($po->po_detail as $detail) {

    //         for ($num = 1; $num <= intval($detail->qty); $num++) {

    //             $seqStr = str_pad($num, 4, '0', STR_PAD_LEFT);

    //             $qrList[] = $this->createQRWithFixedSequence(
    //                 $po,
    //                 $detail,
    //                 $num
    //             );

    //             DB::table('tqr_reprint_request')
    //                 ->where([
    //                     'id_po'        => $po->id,
    //                     'id_po_detail' => $detail->id,
    //                     'sequence_no'  => $seqStr,
    //                     'status'       => 'APPROVED'
    //                 ])
    //                 ->whereNull('used_at')
    //                 ->update(['used_at' => now()]);
    //         }
    //     }

    //     return $this->printPDF($po, $qrList);
    // }

    private function generateAllQR($po)
    {
        $qrList    = [];
        $conflicts = [];

        // 🔑 FLAG MODE
        // default: generate
        // kalau ada seq → treat sebagai reprint (aman, tanpa insert)
        $isReprint = request()->has('seq');

        /**
         * =====================================
         * 1️⃣ VALIDASI SEMUA DETAIL & SEQUENCE
         * =====================================
         */
        foreach ($po->po_detail as $detail) {

            for ($num = 1; $num <= intval($detail->qty); $num++) {

                $seqStr = str_pad($num, 4, '0', STR_PAD_LEFT);

                // GENERATE MODE → cek boleh print
                if (!$isReprint && !$this->canPrintQR($po->id, $detail->id, $seqStr)) {
                    $conflicts[] = [
                        'id_po_detail' => $detail->id,
                        'sku'          => $detail->part_number,
                        'product_name' => $detail->product_name,
                        'sequence'     => $seqStr
                    ];
                }

                // REPRINT MODE → QR harus sudah ada
                if ($isReprint) {
                    $exists = DB::table('tproduct_qr')
                        ->where([
                            'id_po'        => $po->id,
                            'id_po_detail' => $detail->id,
                            'sequence_no'  => $seqStr
                        ])
                        ->exists();

                    if (!$exists) {
                        abort(
                            404,
                            "QR tidak ditemukan untuk {$detail->product_name} sequence {$seqStr}"
                        );
                    }
                }
            }
        }

        if (!empty($conflicts)) {
            return response()->json([
                'code'      => 'QR_ALREADY_PRINTED',
                'message'   => 'Terdapat QR yang sudah pernah dicetak',
                'conflicts' => $conflicts
            ], 403);
        }

        /**
         * =====================================
         * 2️⃣ PROSES CETAK
         * =====================================
         */
        foreach ($po->po_detail as $detail) {

            for ($num = 1; $num <= intval($detail->qty); $num++) {

                $seqStr = str_pad($num, 4, '0', STR_PAD_LEFT);

                if ($isReprint) {

                    // 🔁 REPRINT → AMBIL QR EXISTING (NO INSERT)
                    $qr = DB::table('tproduct_qr')
                        ->where([
                            'id_po'        => $po->id,
                            'id_po_detail' => $detail->id,
                            'sequence_no'  => $seqStr
                        ])
                        ->orderByDesc('id')
                        ->first();

                    if (!$qr) {
                        abort(
                            404,
                            "QR tidak ditemukan untuk {$detail->product_name} sequence {$seqStr}"
                        );
                    }

                    $qrList[] = [
                        'nama_barang' => $qr->nama_barang,
                        'sku'         => $qr->sku,
                        'nomor_urut'  => $qr->sequence_no,
                        'qr_payload'  => $qr->qr_code,
                    ];

                } else {

                    // 🆕 GENERATE BARU → INSERT
                    $qrList[] = $this->createQRWithFixedSequence(
                        $po,
                        $detail,
                        $num
                    );
                }

                /**
                 * =====================================
                 * 3️⃣ HABISKAN APPROVAL REPRINT (JIKA ADA)
                 * =====================================
                 */
                DB::table('tqr_reprint_request')
                    ->where([
                        'id_po'        => $po->id,
                        'id_po_detail' => $detail->id,
                        'sequence_no'  => $seqStr,
                        'status'       => 'APPROVED'
                    ])
                    ->whereNull('used_at')
                    ->update(['used_at' => now()]);
            }
        }

        /**
         * =====================================
         * 4️⃣ CETAK PDF
         * =====================================
         */
        return $this->printPDF($po, $qrList);
    }

    // public function validateQR(Request $r, $id)
    // {
    //     $po = Tpo::with('po_detail')->findOrFail($id);
    //     $conflicts = [];

    //     foreach ($po->po_detail as $detail) {
    //         for ($num = 1; $num <= intval($detail->qty); $num++) {
    //             $seq = str_pad($num, 4, '0', STR_PAD_LEFT);

    //             if (!$this->canPrintQR($po->id, $detail->id, $seq)) {
    //                 $conflicts[] = [
    //                     'id_po_detail' => $detail->id,
    //                     'sku'          => $detail->part_number,
    //                     'product_name' => $detail->product_name,
    //                     'sequence'     => $seq
    //                 ];
    //             }
    //         }
    //     }

    //     return response()->json([
    //         'allowed'   => empty($conflicts),
    //         'conflicts' => $conflicts
    //     ]);
    // }

    // public function validateQR(Request $r, $id)
    // {
    //     $po = Tpo::with('po_detail')->findOrFail($id);
    //     $conflicts = [];

    //     // 🔑 DETAIL YANG DIPILIH USER
    //     $selectedDetailIds = [];

    //     if ($r->filled('details')) {
    //         $selectedDetailIds = array_map(
    //             'intval',
    //             explode(',', $r->details)
    //         );
    //     }

    //     foreach ($po->po_detail as $detail) {

    //         // ⛔ SKIP DETAIL YANG TIDAK DIPILIH
    //         if (!empty($selectedDetailIds) && !in_array($detail->id, $selectedDetailIds)) {
    //             continue;
    //         }

    //         for ($num = 1; $num <= intval($detail->qty); $num++) {

    //             $seq = str_pad($num, 4, '0', STR_PAD_LEFT);

    //             if (!$this->canPrintQR($po->id, $detail->id, $seq)) {
    //                 $conflicts[] = [
    //                     'id_po_detail' => $detail->id,
    //                     'sku'          => $detail->part_number,
    //                     'product_name' => $detail->product_name,
    //                     'sequence'     => $seq
    //                 ];
    //             }
    //         }
    //     }

    //     return response()->json([
    //         'allowed'   => empty($conflicts),
    //         'conflicts' => $conflicts
    //     ]);
    // }

    //     public function validateQR(Request $r, $id)
    // {
    //     $po = Tpo::with('po_detail')->findOrFail($id);

    //     $conflicts = [];

    //     // Ambil detail yang dicentang
    //     $selectedDetailIds = [];

    //     if ($r->filled('details')) {
    //         $selectedDetailIds = array_map(
    //             'intval',
    //             explode(',', $r->details)
    //         );
    //     }

    //     if (empty($selectedDetailIds)) {
    //         return response()->json([
    //             'allowed'   => false,
    //             'message'   => 'Tidak ada barang yang dipilih'
    //         ], 422);
    //     }

    //     foreach ($po->po_detail as $detail) {

    //         if (!in_array($detail->id, $selectedDetailIds)) {
    //             continue;
    //         }

    //         // 🔎 Cek apakah sudah pernah dicetak
    //         $printed = DB::table('tproduct_qr')
    //             ->where('id_po', $po->id)
    //             ->where('id_po_detail', $detail->id)
    //             ->exists();

    //         if ($printed) {

    //             // ambil range sequence untuk info
    //             $minSeq = DB::table('tproduct_qr')
    //                 ->where('id_po', $po->id)
    //                 ->where('id_po_detail', $detail->id)
    //                 ->min(DB::raw('CAST(sequence_no AS UNSIGNED)'));

    //             $maxSeq = DB::table('tproduct_qr')
    //                 ->where('id_po', $po->id)
    //                 ->where('id_po_detail', $detail->id)
    //                 ->max(DB::raw('CAST(sequence_no AS UNSIGNED)'));

    //             $conflicts[] = [
    //                 'id_po_detail' => $detail->id,
    //                 'sku'          => $detail->part_number,
    //                 'product_name' => $detail->product_name,
    //                 'printed_range'=> $minSeq && $maxSeq
    //                     ? str_pad($minSeq,4,'0',STR_PAD_LEFT) .
    //                       ' - ' .
    //                       str_pad($maxSeq,4,'0',STR_PAD_LEFT)
    //                     : null
    //             ];
    //         }
    //     }

    //     return response()->json([
    //         'allowed'   => empty($conflicts),
    //         'conflicts' => $conflicts
    //     ]);
    // }

    public function validateQR(Request $r, $id)
    {
        $po = Tpo::with('po_detail')->findOrFail($id);

        $conflicts = [];

        // Ambil detail yang dicentang
        $selectedDetailIds = [];

        if ($r->filled('details')) {
            $selectedDetailIds = array_filter(
                array_map('intval', explode(',', $r->details))
            );
        }

        if (empty($selectedDetailIds)) {
            return response()->json([
                'allowed' => false,
                'message' => 'Tidak ada barang yang dipilih'
            ], 422);
        }

        foreach ($po->po_detail as $detail) {

            if (!in_array($detail->id, $selectedDetailIds)) {
                continue;
            }

            // Ambil semua sequence yang pernah tercetak
            $sequences = DB::table('tproduct_qr')
                ->where('id_po', $po->id)
                ->where('id_po_detail', $detail->id)
                ->pluck('sequence_no')
                ->toArray();

            if (empty($sequences)) {
                continue; // belum pernah cetak → aman
            }

            $blockedSequences = [];

            foreach ($sequences as $seq) {

                // gunakan canPrintQR
                if (!$this->canPrintQR($po->id, $detail->id, $seq)) {
                    $blockedSequences[] = (int)$seq;
                }
            }

            if (!empty($blockedSequences)) {

                sort($blockedSequences);

                $rangeText = $this->compressSequenceRange($blockedSequences);

                $conflicts[] = [
                    'id_po_detail' => $detail->id,
                    'sku'          => $detail->part_number,
                    'product_name' => $detail->product_name,
                    'printed_range'=> $rangeText,
                    'sequence'     => $rangeText // dikirim untuk reprint
                ];
            }
        }

        return response()->json([
            'allowed'   => empty($conflicts),
            'conflicts' => $conflicts
        ]);
    }
    
    private function compressSequenceRange(array $numbers): string
    {
        if (empty($numbers)) return '';
    
        $ranges = [];
        $start = $numbers[0];
        $prev  = $numbers[0];
    
        for ($i = 1; $i < count($numbers); $i++) {
    
            if ($numbers[$i] == $prev + 1) {
                $prev = $numbers[$i];
                continue;
            }
    
            $ranges[] = $this->formatRange($start, $prev);
            $start = $numbers[$i];
            $prev  = $numbers[$i];
        }
    
        $ranges[] = $this->formatRange($start, $prev);
    
        return implode(', ', $ranges);
    }
    
    private function formatRange($start, $end): string
    {
        if ($start == $end) {
            return str_pad($start, 4, '0', STR_PAD_LEFT);
        }
    
        return str_pad($start, 4, '0', STR_PAD_LEFT)
            . ' - ' .
            str_pad($end, 4, '0', STR_PAD_LEFT);
    }

    private function createQRWithFixedSequence($po, $item, int $seqNumber)
    {
        $sku = $item->part_number;
    
        $product = DB::table('mproduct')->where('sku', $sku)->first();
        if (!$product) abort(422, "SKU {$sku} tidak ditemukan");
    
        // 🔑 GLOBAL SEQUENCE PER SKU
        $seqNumber = $this->getNextGlobalSequenceBySKU($sku);
        $seqStr = str_pad($seqNumber, 4, '0', STR_PAD_LEFT);
    
        $qrValue = $po->no_po . "|" . $sku . "|" . $seqStr;
    
        DB::table('tproduct_qr')->insert([
            'id_po'        => $po->id,
            'id_po_detail' => $item->id,
            'id_product'   => $product->id,
            'sku'          => $sku,
            'qr_code'      => $qrValue,
            'sequence_no'  => $seqStr,
            'nama_barang'  => $item->product_name,
            'status'       => 'NEW',
            'used_for'     => 'IN',
            'printed_at'   => now(),
            'printed_by'   => Auth::user()->username,
        ]);
    
        return [
            'nama_barang' => $item->product_name,
            'sku'         => $sku,
            'nomor_urut'  => $seqStr,
            'qr_payload'  => $qrValue,
        ];
    }

    // private function generateAllQR($po)
    // {
    //     $qrList = [];

    //     /**
    //      * =========================================
    //      * 1️⃣ VALIDASI TOTAL (BLOCK JIKA 1 SAJA GAGAL)
    //      * =========================================
    //      */
    //     foreach ($po->po_detail as $detail) {

    //         for ($num = 1; $num <= intval($detail->qty); $num++) {

    //             $seqStr = str_pad($num, 4, '0', STR_PAD_LEFT);

    //             if (!$this->canPrintQR($po->id, $detail->id, $seqStr)) {
    //                 return response()->json([
    //                     'message' => "PO {$po->po_no} - {$detail->product_name} sequence {$seqStr} sudah pernah dicetak. Wajib ajukan request reprint terlebih dahulu."
    //                 ], 403);
    //             }
    //         }
    //     }

    //     /**
    //      * =========================================
    //      * 2️⃣ GENERATE QR (SUDAH AMAN)
    //      * =========================================
    //      */
    //     foreach ($po->po_detail as $detail) {

    //         for ($num = 1; $num <= intval($detail->qty); $num++) {

    //             $seqStr = str_pad($num, 4, '0', STR_PAD_LEFT);

    //             $qrList[] = $this->createQRWithFixedSequence(
    //                 $po,
    //                 $detail,
    //                 $num
    //             );

    //             /**
    //              * 3️⃣ HABISKAN APPROVAL REPRINT (JIKA ADA)
    //              */
    //             DB::table('tqr_reprint_request')
    //                 ->where([
    //                     'id_po'        => $po->id,
    //                     'id_po_detail' => $detail->id,
    //                     'sequence_no'  => $seqStr,
    //                     'status'       => 'APPROVED'
    //                 ])
    //                 ->whereNull('used_at')
    //                 ->update([
    //                     'used_at' => now()
    //                 ]);
    //         }
    //     }

    //     /**
    //      * =========================================
    //      * 4️⃣ CETAK PDF
    //      * =========================================
    //      */
    //     return $this->printPDF($po, $qrList);
    // }
    
    // private function printPDF($po, $qrList)
    // {
    //     if (!is_array($qrList) || count($qrList) === 0) {
    //         abort(422, 'QR list kosong');
    //     }
        
    //     /*
    //     |--------------------------------------------------------------------------
    //     | UKURAN LABEL (33 x 15 mm)
    //     |--------------------------------------------------------------------------
    //     | DomPDF menggunakan satuan POINT
    //     | 1 mm = 2.83465 pt
    //     */
    //     $width  = 33 * 2.83465;
    //     $height = 15 * 2.83465;

    //     /*
    //     |--------------------------------------------------------------------------
    //     | GENERATE PDF
    //     |--------------------------------------------------------------------------
    //     | PDF adalah sumber kebenaran ukuran
    //     | Browser tidak boleh override
    //     */
    //     $pdf = Pdf::loadView(
    //         'pages.transaction.purchase_order.purchase_order_qrcode',
    //         [
    //             'po'      => $po,
    //             'qrList' => $qrList
    //         ]
    //     )->setPaper([0, 0, $width, $height], 'portrait');


    //     /*
    //     |--------------------------------------------------------------------------
    //     | MODE AKTIF (wajib PRODUKSI)
    //     |--------------------------------------------------------------------------
    //     | Dibuka di NEW TAB sebagai PDF
    //     | Print dilakukan dari PDF viewer
    //     | Ukuran label PRESISI 33x15mm
    //     */
    //     return response($pdf->output(), 200)
    //         ->header('Content-Type', 'application/pdf')
    //         ->header(
    //             'Content-Disposition',
    //             'inline; filename="QR_'.$po->no_po.'.pdf"'
    //         );
    // }

    private function printPDF($po, $qrList)
    {
        if (!is_array($qrList) || count($qrList) === 0) {
            abort(422, 'QR list kosong');
        }

        /*
        |--------------------------------------------------------------------------
        | Generate SVG di Controller (JANGAN DI BLADE)
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

        /*
        |--------------------------------------------------------------------------
        | Generate PDF (Optimized Options)
        |--------------------------------------------------------------------------
        */
        $pdf = Pdf::loadView(
            'pages.transaction.purchase_order.purchase_order_qrcode',
            [
                'po'     => $po,
                'qrList' => $qrList
            ]
        )->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => false,
            'defaultFont'          => 'DejaVu Sans'
        ])
        ->setPaper([0, 0, $width, $height], 'portrait');

        /*
        |--------------------------------------------------------------------------
        | Stream langsung (JANGAN pakai output())
        |--------------------------------------------------------------------------
        */
        return $pdf->stream("QR_{$po->no_po}.pdf");
    }

    private function detectPrintedConflict($poId, $detailId, $seq)
    {
        $qr = DB::table('tproduct_qr')
            ->where([
                'id_po'        => $poId,
                'id_po_detail' => $detailId,
                'sequence_no'  => $seq
            ])->first();
    
        if (!$qr) return null;
    
        $approved = DB::table('tqr_reprint_request')
            ->where([
                'id_po'        => $poId,
                'id_po_detail' => $detailId,
                'sequence_no'  => $seq,
                'status'       => 'APPROVED'
            ])->exists();
    
        if ($approved) return null;
    
        return [
            'id_product_qr' => $qr->id,
            'id_product'    => $qr->id_product,
            'product'       => $qr->nama_barang,
            'sequence'      => $seq,
        ];
    }
    
    private function parseSequenceInput(string $text): array
    {
        // Bersihkan spasi
        $text = str_replace(' ', '', $text);

        if ($text === '') {
            return [];
        }

        $result = [];

        /**
         * Pisahkan berdasarkan koma
         * contoh: "1-3,5,7-10" → ["1-3","5","7-10"]
         */
        $segments = explode(',', $text);

        foreach ($segments as $seg) {

            if ($seg === '') continue;

            /**
             * Case RANGE: x-y
             */
            if (strpos($seg, '-') !== false) {

                // pastikan hanya 1 tanda "-"
                $parts = explode('-', $seg);
                if (count($parts) !== 2) {
                    continue; // skip invalid format
                }

                [$start, $end] = $parts;

                if (!is_numeric($start) || !is_numeric($end)) {
                    continue;
                }

                $start = (int)$start;
                $end   = (int)$end;

                // validasi logis
                if ($start <= 0 || $end <= 0) {
                    continue;
                }

                if ($start > $end) {
                    continue;
                }

                $result = array_merge($result, range($start, $end));
            }

            /**
             * Case SINGLE: x
             */
            else {
                if (!is_numeric($seg)) {
                    continue;
                }

                $num = (int)$seg;
                if ($num <= 0) {
                    continue;
                }

                $result[] = $num;
            }
        }

        // Hilangkan duplikat & urutkan
        $result = array_values(array_unique($result));
        sort($result);

        return $result;
    }   
    public function getSequence($id)
    {
        try {
    
            /**
             * Ambil sequence unik
             * jika ada duplicate (reprint) → ambil yang terbaru
             */
            $sequences = DB::table('tproduct_qr as q')
                ->select('q.sequence_no')
                ->join(
                    DB::raw('
                        (
                            SELECT MAX(id) AS id
                            FROM tproduct_qr
                            WHERE id_po_detail = '.$id.'
                            GROUP BY sequence_no
                        ) latest
                    '),
                    'q.id',
                    '=',
                    'latest.id'
                )
                ->orderBy('q.sequence_no', 'asc')
                ->pluck('q.sequence_no')
                ->toArray();
    
            // "0001" → 1
            $available = array_values(array_map(
                fn($s) => intval($s),
                $sequences
            ));
    
            $lastSequence = count($available) ? max($available) : 0;
    
            return response()->json([
                'available'     => $available,
                'last_sequence' => $lastSequence
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil data sequence.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    
    private function createOrGetQR($po, $item)
    {
        $sku = $item->part_number;

        $product = DB::table('mproduct')->where('sku', $sku)->first();
        if (!$product) {
            abort(422, "SKU {$sku} tidak ditemukan");
        }

        // ===============================
        // GLOBAL SEQUENCE PER SKU
        // ===============================
        $seqNumber = $this->getNextGlobalSequenceBySKU($sku);
        $seqStr    = str_pad($seqNumber, 4, '0', STR_PAD_LEFT);

        // ===============================
        // QR VALUE TETAP CANTUMKAN PO
        // ===============================
        $qrValue = $po->no_po . "|" . $sku . "|" . $seqStr;

        // ===============================
        // SAFETY CHECK (SKU + SEQUENCE)
        // ===============================
        $existing = DB::table('tproduct_qr')
            ->where('sku', $sku)
            ->where('sequence_no', $seqStr)
            ->first();

        if ($existing) {
            abort(409, "Sequence {$seqStr} untuk SKU {$sku} sudah ada");
        }

        DB::table('tproduct_qr')->insert([
            'id_po'        => $po->id,
            'id_po_detail' => $item->id,
            'id_product'   => $product->id,
            'sku'          => $sku,
            'qr_code'      => $qrValue,
            'sequence_no'  => $seqStr,
            'nama_barang'  => $item->product_name,
            'status'       => 'NEW',
            'used_for'     => 'IN',
            'printed_at'   => now(),
            'printed_by'   => Auth::user()->username,
        ]);

        return [
            'nama_barang' => $item->product_name,
            'sku'         => $sku,
            'nomor_urut'  => $seqStr,
            'qr_payload'  => $qrValue,
        ];
    }
    
    public function reprintList($id)
    {

        // Ambil semua request reprint, join ke PO dan detailnya
        $requests = DB::table('tqr_reprint_request as r')
            ->join('tpos as po', 'r.id_po', '=', 'po.id')
            ->join('tpo_detail as d', 'r.id_po_detail', '=', 'd.id')
            ->select(
                'r.id as request_id',
                'po.no_po',
                'po.tgl_po',
                'd.part_number',
                'd.product_name',
                'r.sequence_no',
                'r.reason',
                'r.status',
                'r.requested_at',
                'r.requested_by'
            )
            ->orderBy('po.tgl_po', 'desc')
            ->orderBy('r.id', 'desc')
            ->where('r.id_po',$id)
            ->get();

        // Group per PO
        $requestsGrouped = $requests->groupBy('no_po');
        return view('pages.transaction.purchase_order.purchase_order_reprint', compact('requestsGrouped'));
    }
    
    public function approveReprint(Request $request)
    {
        if (!Permission::approve('MENU-0301')) {
            $this->poLog('APPROVE_REPRINT', "User: {$this->actor()} | Status: FORBIDDEN | Error: Tidak punya hak approve reprint");
            abort(403);
        }

        $reqIds = $request->ids ?? [];

        if (empty($reqIds)) {
            $this->poLog('APPROVE_REPRINT', "User: {$this->actor()} | Status: FAILED | Error: IDs kosong");
            return response()->json(['success' => false]);
        }

        // Ambil semua request yang di-approve
        $requests = DB::table('tqr_reprint_request')
            ->whereIn('id', $reqIds)
            ->get();

        if ($requests->isEmpty()) {
            $this->poLog('APPROVE_REPRINT', "User: {$this->actor()} | IDs: " . implode(',', $reqIds) . " | Status: FAILED | Error: Data tidak ditemukan");
            return response()->json(['success' => false]);
        }

        // Approve semua
        DB::table('tqr_reprint_request')
            ->whereIn('id', $reqIds)
            ->update([
                'status'       => 'APPROVED',
                'approved_by'  => Auth::user()->username,
                'approved_at'  => now()
            ]);

        $poId    = $requests->first()->id_po;
        $seqList = $requests->pluck('sequence_no')->implode(', ');
        $this->poLog('APPROVE_REPRINT', "User: {$this->actor()} | PO ID: {$poId} | Request IDs: " . implode(',', $reqIds) . " | Sequences: {$seqList} | Status: APPROVED");

        /**
         * =========================================
         * BUAT BATCH RECORD — ringan & cepat
         * PDF akan di-generate on-demand saat user
         * membuka preview (tidak pakai job/queue)
         * =========================================
         */
        $batchId = DB::table('print_batches')->insertGetId([
            'user_id'             => Auth::id(),
            'id_po'               => $poId,
            'batch_name'          => 'Cetak Ulang ' . now()->format('d/m/Y H:i'),
            'content_summary'     => 'Reprint: ' . count($reqIds) . ' QR Code',
            'total_labels'        => count($reqIds),
            'batch_type'          => 'reprint',
            'reprint_request_ids' => json_encode(array_map('intval', $reqIds)),
            'status'              => 'Pending',
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);

        $this->poLog('APPROVE_REPRINT', "User: {$this->actor()} | PO ID: {$poId} | Batch ID: {$batchId} | Status: BATCH_CREATED");

        return response()->json([
            'success'  => true,
            'batch_id' => $batchId,
            'po_id'    => $poId,
            'message'  => 'Persetujuan berhasil. Klik "Preview Cetak" untuk mencetak.',
        ]);
    }

    public function reprintBatchPreview($batchId)
    {
        if (!Permission::approve('MENU-0301') && !Permission::print('MENU-0301')) {
            abort(403);
        }

        $batch = DB::table('print_batches')
            ->where('id', $batchId)
            ->where('batch_type', 'reprint')
            ->first();

        abort_if(!$batch, 404, 'Batch tidak ditemukan');

        // Sudah ada file — langsung stream
        if (in_array($batch->status, ['Ready', 'Printed'])) {
            return $this->streamReprintPDF($batch, $batchId);
        }

        // Sedang diproses oleh request lain — coba lagi sebentar
        if ($batch->status === 'Processing') {
            abort(503, 'PDF sedang dibuat, muat ulang halaman beberapa saat lagi.');
        }

        if ($batch->status === 'Failed') {
            abort(500, 'Pembuatan PDF sebelumnya gagal: ' . ($batch->error_message ?? 'unknown error'));
        }

        // Status Pending → generate PDF on-demand (di sini, bukan di job)
        // Lock atomik agar tidak double-generate
        $locked = DB::table('print_batches')
            ->where('id', $batchId)
            ->where('status', 'Pending')
            ->update(['status' => 'Processing', 'updated_at' => now()]);

        if (!$locked) {
            // Race condition — request lain sudah mengambil lock
            $batch = DB::table('print_batches')->where('id', $batchId)->first();
            if (in_array($batch->status, ['Ready', 'Printed'])) {
                return $this->streamReprintPDF($batch, $batchId);
            }
            abort(503, 'PDF sedang dibuat oleh proses lain.');
        }

        try {
            set_time_limit(600);
            ini_set('memory_limit', '512M');

            $requestIds = json_decode($batch->reprint_request_ids ?? '[]', true);

            if (empty($requestIds)) {
                throw new \Exception('Tidak ada request reprint untuk batch ini');
            }

            // Ambil QR data via join reprint_request → tproduct_qr
            $rows = DB::table('tqr_reprint_request as r')
                ->join('tproduct_qr as qr', function ($j) {
                    $j->on('qr.id_po',       '=', 'r.id_po')
                      ->on('qr.id_po_detail', '=', 'r.id_po_detail')
                      ->on(
                          DB::raw('`qr`.`sequence_no` COLLATE utf8mb4_unicode_ci'),
                          '=',
                          DB::raw('`r`.`sequence_no` COLLATE utf8mb4_unicode_ci')
                      );
                })
                ->whereIn('r.id', $requestIds)
                ->where('r.status', 'APPROVED')
                ->whereNull('r.used_at')
                ->select('r.id as request_id', 'qr.sku', 'qr.nama_barang', 'qr.sequence_no', 'qr.qr_code as qr_payload')
                ->get();

            if ($rows->isEmpty()) {
                throw new \Exception('Tidak ada QR yang siap diproses');
            }

            // Generate SVG dalam chunk 50 untuk hindari memory spike
            $qrList = [];
            foreach (array_chunk($rows->all(), 50) as $chunk) {
                foreach ($chunk as $row) {
                    $svg = QrCode::format('svg')->size(220)->margin(0)->generate($row->qr_payload);
                    $qrList[] = [
                        'sku'         => $row->sku,
                        'nama_barang' => $row->nama_barang,
                        'nomor_urut'  => $row->sequence_no,
                        'qr_payload'  => $row->qr_payload,
                        'qr_svg'      => 'data:image/svg+xml;base64,' . base64_encode($svg),
                    ];
                }
                gc_collect_cycles();
            }

            $po     = \App\Models\Tpo::findOrFail($batch->id_po);
            $width  = 33 * 2.83465;
            $height = 15 * 2.83465;

            $pdf = Pdf::loadView('pages.transaction.purchase_order.purchase_order_qrcode', [
                'po'     => $po,
                'qrList' => $qrList,
            ])->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => false,
                'defaultFont'          => 'DejaVu Sans',
            ])->setPaper([0, 0, $width, $height], 'portrait');

            $fileName = 'qr_reprint_po_' . $batch->id_po . '_batch_' . $batchId . '.pdf';
            Storage::put('public/temp_prints/' . $fileName, $pdf->output());

            // Konsumsi approval setelah PDF berhasil dibuat
            DB::table('tqr_reprint_request')
                ->whereIn('id', $requestIds)
                ->where('status', 'APPROVED')
                ->whereNull('used_at')
                ->update(['used_at' => now()]);

            DB::table('print_batches')->where('id', $batchId)->update([
                'status'     => 'Printed',
                'file_path'  => $fileName,
                'updated_at' => now(),
            ]);

            $totalQr = count($qrList);
            $this->poLog('REPRINT_PREVIEW', "User: {$this->actor()} | Batch ID: {$batchId} | File: {$fileName} | Total QR: {$totalQr} | Status: PRINTED");

            $path = storage_path('app/public/temp_prints/' . $fileName);
            return response()->file($path, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="cetak_ulang_' . $batchId . '.pdf"',
                'X-Frame-Options'     => 'SAMEORIGIN',
                'Cache-Control'       => 'no-store',
            ]);

        } catch (\Exception $e) {
            DB::table('print_batches')->where('id', $batchId)->update([
                'status'        => 'Failed',
                'error_message' => $e->getMessage(),
                'updated_at'    => now(),
            ]);
            $this->poLog('REPRINT_PREVIEW', "User: {$this->actor()} | Batch ID: {$batchId} | Status: FAILED | Error: {$e->getMessage()}");
            abort(500, 'Gagal membuat PDF: ' . $e->getMessage());
        }
    }

    private function streamReprintPDF($batch, $batchId): \Symfony\Component\HttpFoundation\Response
    {
        $path = storage_path('app/public/temp_prints/' . $batch->file_path);
        abort_if(!file_exists($path), 404, 'File PDF tidak ditemukan');

        if ($batch->status === 'Ready') {
            DB::table('print_batches')->where('id', $batchId)->update([
                'status'     => 'Printed',
                'updated_at' => now(),
            ]);
        }

        return response()->file($path, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="cetak_ulang_' . $batchId . '.pdf"',
            'X-Frame-Options'     => 'SAMEORIGIN',
            'Cache-Control'       => 'no-store',
        ]);
    }
    
    public function rejectReprint(Request $request)
    {
        if (!Permission::reject('MENU-0301')) {
            $this->poLog('REJECT_REPRINT', "User: {$this->actor()} | Status: FORBIDDEN | Error: Tidak punya hak reject reprint");
            abort(403);
        }

        $reqIds = $request->ids ?? [];

        if (!empty($reqIds)) {
            $requests = DB::table('tqr_reprint_request')->whereIn('id', $reqIds)->get();
            $poId     = $requests->isNotEmpty() ? $requests->first()->id_po : '-';
            $seqList  = $requests->isNotEmpty() ? $requests->pluck('sequence_no')->implode(', ') : '-';

            DB::table('tqr_reprint_request')
                ->whereIn('id', $reqIds)
                ->update(['status' => 'REJECTED', 'approved_by' => Auth::user()->username, 'approved_at' => now()]);

            $this->poLog('REJECT_REPRINT', "User: {$this->actor()} | PO ID: {$poId} | Request IDs: " . implode(',', $reqIds) . " | Sequences: {$seqList} | Status: REJECTED");
        }

        return response()->json(['success' => true]);
    }
    
    // public function requestReprint(Request $r)
    // {
    //     if (!is_array($r->items)) {
    //         return response()->json(['message' => 'Invalid reprint payload'], 422);
    //     }
    
    //     foreach ($r->items as $item) {
    
    //         // =========================
    //         // Ambil id_product dari SKU
    //         // =========================
    //         $product = DB::table('mproduct')
    //             ->where('sku', $item['sku'])
    //             ->first();
    
    //         if (!$product) {
    //             return response()->json([
    //                 'message' => 'Product tidak ditemukan untuk SKU '.$item['sku']
    //             ], 422);
    //         }
    
    //         // =========================
    //         // Proses sequence_no
    //         // =========================
    //         $sequences = [];
    
    //         if (strpos($item['sequence'], '-') !== false) {
    //             [$start, $end] = explode('-', $item['sequence']);
    //             $start = intval($start);
    //             $end   = intval($end);
    //             for ($i = $start; $i <= $end; $i++) {
    //                 $sequences[] = str_pad($i, 4, '0', STR_PAD_LEFT); // 0001, 0002
    //             }
    //         } elseif (strpos($item['sequence'], ',') !== false) {
    //             $nums = explode(',', $item['sequence']);
    //             foreach ($nums as $n) {
    //                 $sequences[] = str_pad(intval($n), 4, '0', STR_PAD_LEFT);
    //             }
    //         } else {
    //             $sequences[] = str_pad(intval($item['sequence']), 4, '0', STR_PAD_LEFT);
    //         }
            
    //         $existsPending = DB::table('tqr_reprint_request')
    //             ->where('id_po', $r->id_po)
    //             ->where('status', 'PENDING')
    //             ->exists();
            
    //         if ($existsPending) {
    //             return response()->json([
    //                 'success' => false,
    //                 'code'    => 'REPRINT_PENDING',
    //                 'message' => 'Masih terdapat pengajuan cetak ulang yang menunggu persetujuan. Silakan tunggu.'
    //             ], 409);
    //         }

    //         // =========================
    //         // Insert untuk setiap sequence
    //         // =========================
    //         foreach ($sequences as $seq_no) {
    //             $productQr = DB::table('tproduct_qr')
    //                 ->where('id_po', $r->id_po)
    //                 ->where('id_po_detail', $item['id_po_detail'])
    //                 ->where('id_product', $product->id) // pakai id_product dari mproduct
    //                 ->where('sequence_no', $seq_no)
    //                 ->first();
    
    //             if (!$productQr) {
    //                 return response()->json([
    //                     'message' => 'QR Product tidak ditemukan untuk SKU '.$item['sku'].' sequence '.$seq_no
    //                 ], 422);
    //             }
    
    //             DB::table('tqr_reprint_request')->insert([
    //                 'id_po'         => $r->id_po,
    //                 'id_po_detail'  => $item['id_po_detail'],
    //                 'id_product'    => $product->id,
    //                 'id_product_qr' => $productQr->id,
    //                 'sequence_no'   => $seq_no,
    //                 'reason'        => $r->reason,
    //                 'status'        => 'PENDING',
    //                 'requested_by'  => Auth::user()->username,
    //                 'requested_at'  => now(),
    //             ]);
    //         }
    //     }
        
    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Pengajuan cetak ulang berhasil dikirim dan menunggu persetujuan.'
    //     ]);
    // }
    
    public function requestReprint(Request $r)
    {
        $itemCount = is_array($r->items) ? count($r->items) : 0;
        $this->poLog('REQUEST_REPRINT', "User: {$this->actor()} | PO ID: {$r->id_po} | Jumlah item: {$itemCount} | Alasan: {$r->reason} | Status: PROCESS");

        if (!is_array($r->items) || empty($r->items)) {
            $this->poLog('REQUEST_REPRINT', "User: {$this->actor()} | PO ID: {$r->id_po} | Status: FAILED | Error: Data reprint tidak valid");
            return response()->json([
                'success' => false,
                'message' => 'Data reprint tidak valid'
            ], 422);
        }

        /**
         * =====================================
         * 1️⃣ GLOBAL GUARD: CEK PENDING DULU
         * =====================================
         */
        $existsPending = DB::table('tqr_reprint_request')
            ->where('id_po', $r->id_po)
            ->where('status', 'PENDING')
            ->exists();

        if ($existsPending) {
            $this->poLog('REQUEST_REPRINT', "User: {$this->actor()} | PO ID: {$r->id_po} | Status: BLOCKED | Info: Masih ada pengajuan PENDING");
            return response()->json([
                'success' => false,
                'code'    => 'REPRINT_PENDING',
                'message' => 'Masih terdapat pengajuan cetak ulang yang menunggu persetujuan. Silakan tunggu hingga disetujui atau ditolak.'
            ], 409);
        }

        /**
         * =====================================
         * 2️⃣ PREPARE DATA (TANPA INSERT)
         * =====================================
         */
        $rowsToInsert = [];

        foreach ($r->items as $item) {

            // Ambil product
            $product = DB::table('mproduct')
                ->where('sku', $item['sku'])
                ->first();

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product tidak ditemukan untuk SKU ' . $item['sku']
                ], 422);
            }

            // Parse sequence
            $sequences = $this->parseSequenceInput($item['sequence']);

            if (empty($sequences)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sequence tidak valid untuk SKU ' . $item['sku']
                ], 422);
            }

            foreach ($sequences as $num) {

                $seq_no = str_pad($num, 4, '0', STR_PAD_LEFT);

                // Validasi QR EXIST
                $productQr = DB::table('tproduct_qr')
                    ->where('id_po', $r->id_po)
                    ->where('id_po_detail', $item['id_po_detail'])
                    ->where('id_product', $product->id)
                    ->where('sequence_no', $seq_no)
                    ->first();

                if (!$productQr) {
                    return response()->json([
                        'success' => false,
                        'message' => "QR tidak ditemukan untuk SKU {$item['sku']} sequence {$seq_no}"
                    ], 422);
                }

                // Siapkan data insert (BELUM INSERT)
                $rowsToInsert[] = [
                    'id_po'         => $r->id_po,
                    'id_po_detail'  => $item['id_po_detail'],
                    'id_product'    => $product->id,
                    'id_product_qr' => $productQr->id,
                    'sequence_no'   => $seq_no,
                    'reason'        => $r->reason,
                    'status'        => 'PENDING',
                    'requested_by'  => Auth::user()->username,
                    'requested_at'  => now(),
                ];
            }
        }

        /**
         * =====================================
         * 3️⃣ INSERT ATOMIC (TRANSACTION)
         * =====================================
         */
        DB::beginTransaction();
        try {

            DB::table('tqr_reprint_request')->insert($rowsToInsert);

            DB::commit();

            $seqSummary = implode(', ', array_column($rowsToInsert, 'sequence_no'));
            $this->poLog('REQUEST_REPRINT', "User: {$this->actor()} | PO ID: {$r->id_po} | Sequences: {$seqSummary} | Jumlah: " . count($rowsToInsert) . " | Status: PENDING");

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan cetak ulang berhasil dikirim dan menunggu persetujuan.'
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            $this->poLog('REQUEST_REPRINT', "User: {$this->actor()} | PO ID: {$r->id_po} | Status: FAILED | Error: {$e->getMessage()}");

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan pengajuan cetak ulang.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    
    private function canPrintQR($poId, $detailId, $seq)
    {
        // QR BELUM PERNAH ADA → PRINT BARU
        $printed = DB::table('tproduct_qr')
            ->where([
                'id_po'        => $poId,
                'id_po_detail' => $detailId,
                'sequence_no'  => $seq
            ])
            ->exists();
    
        if (!$printed) return true;
    
        // QR SUDAH ADA → CEK APPROVAL YANG BELUM DIPAKAI
        return DB::table('tqr_reprint_request')
            ->where([
                'id_po'        => $poId,
                'id_po_detail' => $detailId,
                'sequence_no'  => $seq,
                'status'       => 'APPROVED'
            ])
            ->whereNull('used_at') // 🔐 PENTING
            ->exists();
    }
    
    private function getNextGlobalSequenceBySKU(string $sku): int
    {
        return DB::table('tproduct_qr')
            ->where('sku', $sku)
            ->max(DB::raw('CAST(sequence_no AS UNSIGNED)')) + 1;
    }
    // public function destroy($id)
    // {
    //     $courier = MCourier::findOrFail($id);
    //     $courier->delete();

    //     return redirect('/couriers')->with('success', 'Courier is successfully deleted');
    // }

    private function qrError(string $message, int $code = 403)
    {
        return response()->json([
            'message' => $message
        ], $code);
    }
    
    public function generateNumber(Request $request)
    {
        $date = $request->tgl_po ?? date('Y-m-d');

        $month = date('n', strtotime($date));
        $year  = date('Y', strtotime($date));

        $romanMonths = [
            1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',6=>'VI',
            7=>'VII',8=>'VIII',9=>'IX',10=>'X',11=>'XI',12=>'XII'
        ];

        $roman = $romanMonths[$month];

        $prefix = "PO/{$roman}/{$year}/";

        DB::beginTransaction();

        try {

            // Locking agar aman dari race condition
            $lastNumber = DB::table('tpos')
                ->where('no_po', 'like', $prefix.'%')
                ->lockForUpdate()
                ->orderByDesc('no_po')
                ->value('no_po');

            if ($lastNumber) {
                $lastSeq = (int) substr($lastNumber, -4);
                $nextSeq = $lastSeq + 1;
            } else {
                $nextSeq = 1;
            }

            $newNumber = $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'number' => $newNumber
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal generate nomor'
            ], 500);
        }
    }
}