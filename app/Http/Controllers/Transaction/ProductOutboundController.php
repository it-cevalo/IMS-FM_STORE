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
        $data = DB::table('tproduct_outbound as po')
            ->join('tdos as d', 'd.id', '=', 'po.id_do')
            ->select(
                DB::raw('DATE(po.out_at) as tgl_outbound'),
                DB::raw('COUNT(po.id) as total_barang'),
                DB::raw('COUNT(DISTINCT po.id_do) as jumlah_do'),
                DB::raw('GROUP_CONCAT(DISTINCT d.no_do ORDER BY d.no_do SEPARATOR ", ") as daftar_do')
            )
            ->groupBy(DB::raw('DATE(po.out_at)'))
            ->orderByDesc('tgl_outbound')
            ->get();

        return response()->json(['data' => $data]);
    }

    public function detailByDate(Request $request, $tgl)
    {
        $warehouses = DB::table('m_warehouses')
            ->orderBy('nama_wh')
            ->get();

        $rows = DB::table('tproduct_outbound as a')
            ->join('tdos as d', 'a.id_do', '=', 'd.id')
            ->join('mproduct as p', 'a.id_product', '=', 'p.id')
            ->whereDate('a.out_at', $tgl)
            ->select(
                'a.id',
                'a.id_do',
                'd.no_do',
                'a.qr_code',
                'd.nama_cust',
                'd.do_source',
                'p.sku as SKU',
                'a.sync_at',
                'a.sync_by',
                'p.nama_barang',
                'a.out_at',
                'a.qty'
            )
            ->orderBy('d.no_do')
            ->get()
            ->groupBy('id_do');

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
    
                // 🔹 DO DETAIL (SKU harus)
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
                'message' => 'Peniriman Baran berhasil dikonfirmasi'
            ]);
    
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }    
}