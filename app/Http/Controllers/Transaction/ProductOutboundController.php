<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth, DB;

class ProductOutboundController extends Controller
{
    public function index()
    {
        return view('pages.transaction.product_outbound.product_outbound_index');
    }

    public function datatable()
    {
        /**
         * =====================================================
         * TOTAL BARANG = SUM QTY DO DETAIL (TANPA DUPLIKASI)
         * =====================================================
         */
        $data = DB::table(DB::raw("
            (
                SELECT
                    DATE(po.out_at) AS tgl_outbound,
                    d.id AS id_do,
                    d.no_do
                FROM tproduct_outbound po
                JOIN tdos d ON d.id = po.id_do
                GROUP BY DATE(po.out_at), d.id, d.no_do
            ) x
        "))
        ->join('tdo_detail as dd', 'dd.id_do', '=', 'x.id_do')
        ->select(
            'x.tgl_outbound',
    
            // 🔹 jumlah DO unik per hari
            DB::raw('COUNT(DISTINCT x.id_do) AS jumlah_do'),
    
            // 🔹 total barang = qty DO (TIDAK TERKALI)
            DB::raw('SUM(dd.qty) AS total_barang'),
    
            DB::raw('GROUP_CONCAT(DISTINCT x.no_do ORDER BY x.no_do SEPARATOR ", ") AS daftar_do')
        )
        ->groupBy('x.tgl_outbound')
        ->orderByDesc('x.tgl_outbound')
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
                    dd.qty AS qty_do,
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
                WHERE DATE(a.out_at) = ?
            ) x
            WHERE x.rn <= x.qty_do
            ORDER BY x.no_do, x.out_at
        ", [$tgl]);
        
        // GROUP BY DO
        $rows = collect($rows)->groupBy('id_do');

        return view(
            'pages.transaction.product_outbound.product_outbound_detail',
            compact('tgl', 'rows', 'warehouses')
        );
    }
    
    public function confirm(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1'
        ]);
    
        DB::beginTransaction();
        try {
    
            $warehouseId = 1;
    
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
                        'confirm_by'   => Auth::user()->username,
                        'confirm_date' => now()->toDateString(),
                        'updated_at'   => now(),
                    ]);
            }
    
            DB::commit();
    
            return response()->json([
                'message' => 'Pengiriman Barang berhasil dikonfirmasi'
            ]);
    
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }    
}