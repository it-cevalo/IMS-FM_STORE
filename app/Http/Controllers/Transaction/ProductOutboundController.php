<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Logs;
use Auth, DB;

class ProductOutboundController extends Controller
{
    private function outboundLog(string $section, string $content): void
    {
        try {
            $log = new Logs('Logs_ProductOutboundController');
            $log->write($section, $content);
        } catch (\Throwable $e) {
            \Log::error('[ProductOutboundController] Gagal menulis log: ' . $e->getMessage());
        }
    }

    private function actor(): string
    {
        $user = Auth::user();
        if (!$user) return 'Guest';
        return $user->username ?? $user->name ?? "ID:{$user->id}";
    }

    public function index()
    {
        return view('pages.transaction.product_outbound.product_outbound_index');
    }

    public function datatable()
    {
        $data = DB::table(DB::raw("
            (
                SELECT
                    DATE(po.out_at) AS tgl_outbound,
                    d.id            AS id_do,
                    d.no_do,
                    po.sku,
                    COUNT(po.id)    AS qty_out
                FROM tproduct_outbound po
                JOIN tdos d ON d.id = po.id_do
                GROUP BY DATE(po.out_at), d.id, d.no_do, po.sku
            ) o
        "))
        ->join('tdo_detail as dd', function ($join) {
            $join->on('dd.id_do', '=', 'o.id_do')
                 ->on('dd.sku',   '=', 'o.sku');
        })
        ->select(
            'o.tgl_outbound',
    
            // jumlah DO unik
            DB::raw('COUNT(DISTINCT o.id_do) AS jumlah_do'),
    
            // 🔥 TOTAL BARANG = OUTBOUND AKTUAL (DIBATASI QTY DO)
            DB::raw('SUM(LEAST(o.qty_out, dd.qty)) AS total_barang'),
    
            DB::raw('GROUP_CONCAT(DISTINCT o.no_do ORDER BY o.no_do SEPARATOR ", ") AS daftar_do')
        )
        ->groupBy('o.tgl_outbound')
        ->orderByDesc('o.tgl_outbound')
        ->get();
    
        return response()->json(['data' => $data]);
    }    

    public function detailByDate(Request $request, $tgl)
    {
        $warehouses = DB::table('m_warehouses')
            ->orderBy('nama_wh')
            ->get();

        $rows = DB::select("
            SELECT *
            FROM (
                SELECT
                    a.id,
                    a.id_do,
                    d.no_do,
                    a.qr_code,
                    d.nama_cust,
                    d.do_source,
                    p.sku AS SKU,
                    p.nama_barang,
                    a.out_at,
                    a.qty,
                    a.sync_at,
                    a.sync_by,
                    a.rejected_at,
                    a.rejected_by,
                    a.reject_reason,
                    dd.qty AS qty_do,
                    u.username AS created_by_name,
                    ROW_NUMBER() OVER (
                        PARTITION BY a.id_do, a.sku
                        ORDER BY a.out_at ASC
                    ) AS rn
                FROM tproduct_outbound a
                JOIN tdos d ON a.id_do = d.id
                JOIN mproduct p ON a.id_product = p.id
                JOIN tdo_detail dd
                    ON dd.id_do = d.id
                   AND dd.sku = a.sku
                LEFT JOIN users u ON u.id = a.created_by
                WHERE DATE(a.out_at) = ?
                  AND a.rejected_at IS NULL
            ) x
            WHERE x.rn <= x.qty_do
            ORDER BY x.no_do, x.out_at
        ", [$tgl]);

        // Detect duplicate QR codes within the same DO (mark 2nd+ occurrence)
        $rows = collect($rows);
        $seenQRsByDo = [];
        foreach ($rows as $item) {
            $key = $item->id_do . '_' . $item->qr_code;
            if (isset($seenQRsByDo[$key])) {
                $item->is_duplicate_qr = true;
            } else {
                $seenQRsByDo[$key] = true;
                $item->is_duplicate_qr = false;
            }
        }

        // Rejected items for display reference (shown separately in view)
        $rejectedRows = DB::select("
            SELECT
                a.id,
                a.id_do,
                d.no_do,
                a.qr_code,
                p.sku AS SKU,
                p.nama_barang,
                a.out_at,
                a.rejected_at,
                a.reject_reason,
                u.username AS rejected_by_name
            FROM tproduct_outbound a
            JOIN tdos d ON a.id_do = d.id
            JOIN mproduct p ON a.id_product = p.id
            LEFT JOIN users u ON u.id = a.rejected_by
            WHERE DATE(a.out_at) = ?
              AND a.rejected_at IS NOT NULL
            ORDER BY a.rejected_at DESC
        ", [$tgl]);

        $rows = $rows->groupBy('id_do');
        $rejectedRows = collect($rejectedRows);

        return view(
            'pages.transaction.product_outbound.product_outbound_detail',
            compact('tgl', 'rows', 'warehouses', 'rejectedRows')
        );
    }

    public function reject(Request $request)
    {
        $request->validate([
            'items'         => 'required|array|min:1',
            'reject_reason' => 'required|string|min:3|max:500',
        ]);

        $updated = DB::table('tproduct_outbound')
            ->whereIn('id', $request->items)
            ->whereNull('sync_at')
            ->whereNull('rejected_at')
            ->update([
                'rejected_at'   => now(),
                'rejected_by'   => Auth::user()->id,
                'reject_reason' => trim($request->reject_reason),
            ]);

        $this->outboundLog('REJECT_OUTBOUND',
            "User: {$this->actor()} | Item IDs: " . implode(',', $request->items) .
            " | Jumlah: {$updated} | Alasan: {$request->reject_reason}"
        );

        return response()->json([
            'message' => "{$updated} item berhasil ditolak"
        ]);
    }
    
    public function confirm(Request $request)
    {
        $itemCount   = is_array($request->items) ? count($request->items) : 0;
        
        $warehouseId = !empty($request->id_warehouse) ? $request->id_warehouse : '1';
        
        $warehouseName = DB::table('m_warehouses')->where('id', $warehouseId)->value('nama_wh') ?? "ID:{$warehouseId}";

        $this->outboundLog('CONFIRM_OUTBOUND', "User: {$this->actor()} | Warehouse: {$warehouseName} (ID:{$warehouseId}) | Jumlah item: {$itemCount} | Status: PROCESS");

        $request->validate([
            'items'        => 'required|array|min:1'
        ]);

        DB::beginTransaction();
        try {

            $outbounds = DB::table('tproduct_outbound')
                ->whereIn('id', $request->items)
                ->get();

            if ($outbounds->isEmpty()) {
                throw new \Exception('Data outbound tidak ditemukan');
            }
    
            /**
             * =================================================
             * GROUP QTY OUT PER PRODUCT (UNTUK STOCK)
             * =================================================
             */
            $groupedProduct = [];
            foreach ($outbounds as $o) {
                $groupedProduct[$o->id_product] =
                    ($groupedProduct[$o->id_product] ?? 0) + $o->qty;
            }
    
            /**
             * =================================================
             * UPDATE STOCK OPNAME
             * =================================================
             */
            foreach ($groupedProduct as $idProduct => $qtyOut) {
    
                $stock = DB::table('t_stock_opname')
                    ->where('id_warehouse', $warehouseId)
                    ->where('id_product', $idProduct)
                    ->orderByDesc('id')
                    ->lockForUpdate()
                    ->first();
    
                if (!$stock) {
                    throw new \Exception("Stock belum tersedia untuk product ID {$idProduct}");
                }
    
                if ($stock->qty_last < $qtyOut) {
                    throw new \Exception("Stock tidak mencukupi untuk product ID {$idProduct}");
                }
    
                DB::table('t_stock_opname')
                    ->where('id', $stock->id)
                    ->update([
                        'qty_out'    => ($stock->qty_out ?? 0) + $qtyOut,
                        'qty_last'   => $stock->qty_last - $qtyOut,
                        'updated_at' => now(),
                        'updated_by' => Auth::user()->id,
                    ]);
            }
    
            /**
             * =================================================
             * UPDATE OUTBOUND SYNC
             * =================================================
             */
            DB::table('tproduct_outbound')
                ->whereIn('id', $request->items)
                ->update([
                    'sync_at' => now(),
                    'sync_by' => Auth::user()->id,
                ]);
    
            /**
             * =================================================
             * UPDATE STATUS DO (VALIDASI SKU + QTY)
             * =================================================
             */
            $doIds = $outbounds->pluck('id_do')->unique();
    
            foreach ($doIds as $doId) {
    
                // 🔹 DO DETAIL (SKU wajib)
                $doDetails = DB::table('tdo_detail')
                    ->where('id_do', $doId)
                    ->select('sku', 'qty')
                    ->get();
    
                // 🔹 OUTBOUND PER SKU
                $outboundPerSku = DB::table('tproduct_outbound')
                ->where('id_do', $doId)
                ->whereNotNull('sync_at') // 🔒 PENTING
                ->select('sku', DB::raw('SUM(qty) as qty_out'))
                ->groupBy('sku')
                ->pluck('qty_out', 'sku');
    
                $isComplete = true;
    
                foreach ($doDetails as $detail) {
                    $outQty = $outboundPerSku[$detail->sku] ?? 0;
    
                    if ($outQty < $detail->qty) {
                        $isComplete = false;
                        break;
                    }
                }
    
                DB::table('tdos')
                    ->where('id', $doId)
                    ->update([
                        'status_do'    => $isComplete ? 3 : 2,
                        'confirm_by'   => Auth::user()->id,
                        'confirm_date' => now()->toDateString(),
                        'updated_at'   => now(),
                    ]);
            }
    
            DB::commit();

            // Rangkum DO yang terdampak untuk log
            $doIdsUnique = $outbounds->pluck('id_do')->unique()->values();
            $doNos       = DB::table('tdos')->whereIn('id', $doIdsUnique)->pluck('no_do')->implode(', ');
            $totalQty    = $outbounds->sum('qty');

            $doStatusSummary = [];
            foreach ($doIdsUnique as $doId) {
                $statusDo = DB::table('tdos')->where('id', $doId)->value('status_do');
                $noDo     = DB::table('tdos')->where('id', $doId)->value('no_do');
                $doStatusSummary[] = "{$noDo}=>" . ($statusDo == 3 ? 'COMPLETE' : 'PARTIAL');
            }

            $this->outboundLog('CONFIRM_OUTBOUND',
                "User: {$this->actor()} | Warehouse: {$warehouseName} (ID:{$warehouseId}) | " .
                "Total item: {$itemCount} | Total qty: {$totalQty} | " .
                "DO terlibat: {$doNos} | Status DO: " . implode(', ', $doStatusSummary) . " | " .
                "Status: SUCCESS"
            );

            return response()->json([
                'message' => 'Pengiriman Barang berhasil dikonfirmasi'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->outboundLog('CONFIRM_OUTBOUND',
                "User: {$this->actor()} | Warehouse: {$warehouseName} (ID:{$warehouseId}) | Jumlah item: {$itemCount} | " .
                "Status: FAILED | Error: {$e->getMessage()}"
            );
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
}