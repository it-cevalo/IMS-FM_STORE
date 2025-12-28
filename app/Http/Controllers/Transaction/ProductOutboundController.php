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

    // public function confirm(Request $request)
    // {
    //     $request->validate([
    //         'items' => 'required|array|min:1'
    //     ]);
    
    //     DB::beginTransaction();
    //     try {
    
    //         // ===============================
    //         // DEFAULT WAREHOUSE
    //         // ===============================
    //         $warehouseId = 1; // <-- GANTI jika perlu
    
    //         $outbounds = DB::table('tproduct_outbound')
    //             ->whereIn('id', $request->items)
    //             ->get();
    
    //         if ($outbounds->isEmpty()) {
    //             throw new \Exception('Data outbound tidak ditemukan');
    //         }
    
    //         // ===============================
    //         // GROUP QTY PER PRODUCT
    //         // ===============================
    //         $grouped = [];
    //         foreach ($outbounds as $o) {
    //             $grouped[$o->id_product] =
    //                 ($grouped[$o->id_product] ?? 0) + $o->qty;
    //         }
    
    //         // ===============================
    //         // UPDATE STOCK OPNAME (KURANGI)
    //         // ===============================
    //         foreach ($grouped as $idProduct => $qtyOut) {
    
    //             $lastStock = DB::table('t_stock_opname')
    //                 ->where('id_warehouse', $warehouseId)
    //                 ->where('id_product', $idProduct)
    //                 ->orderByDesc('id')
    //                 ->first();
    
    //             if (!$lastStock) {
    //                 throw new \Exception("Stock belum ada untuk product ID {$idProduct}");
    //             }
    
    //             if ($lastStock->qty_last < $qtyOut) {
    //                 throw new \Exception("Stock tidak mencukupi untuk product ID {$idProduct}");
    //             }
    
    //             DB::table('t_stock_opname')->insert([
    //                 'id_warehouse' => $warehouseId,
    //                 'id_product'   => $idProduct,
    //                 'qty_out'      => $qtyOut,
    //                 'qty_last'     => $lastStock->qty_last - $qtyOut,
    //                 'tgl_opname'   => now()->toDateString(),
    //                 'created_at'   => now(),
    //             ]);
    //         }
    
    //         DB::commit();
    
    //         return response()->json([
    //             'message' => 'Outbound berhasil dikonfirmasi'
    //         ]);
    
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'message' => $e->getMessage()
    //         ], 500);
    //     }
    // }    

    public function confirm(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1'
        ]);

        DB::beginTransaction();
        try {

            // ===============================
            // DEFAULT WAREHOUSE
            // ===============================
            $warehouseId = 1; // sesuaikan jika perlu

            $outbounds = DB::table('tproduct_outbound')
                ->whereIn('id', $request->items)
                ->get();

            if ($outbounds->isEmpty()) {
                throw new \Exception('Data outbound tidak ditemukan');
            }

            // ===============================
            // GROUP QTY PER PRODUCT
            // ===============================
            $grouped = [];
            foreach ($outbounds as $o) {
                $grouped[$o->id_product] =
                    ($grouped[$o->id_product] ?? 0) + $o->qty;
            }

            // ===============================
            // UPDATE STOCK OPNAME (PAKAI DATA YANG ADA)
            // ===============================
            foreach ($grouped as $idProduct => $qtyOut) {

                // Ambil stock terakhir
                $stock = DB::table('t_stock_opname')
                    ->where('id_warehouse', $warehouseId)
                    ->where('id_product', $idProduct)
                    ->orderByDesc('id')
                    ->lockForUpdate() // 🔒 penting biar aman race condition
                    ->first();

                if (!$stock) {
                    throw new \Exception("Stock belum tersedia untuk product ID {$idProduct}");
                }

                if ($stock->qty_last < $qtyOut) {
                    throw new \Exception("Stock tidak mencukupi untuk product ID {$idProduct}");
                }

                // Hitung nilai baru
                $newQtyLast = $stock->qty_last - $qtyOut;
                $newQtyOut  = ($stock->qty_out ?? 0) + $qtyOut;

                // UPDATE record stock opname yang sama
                DB::table('t_stock_opname')
                    ->where('id', $stock->id)
                    ->update([
                        'qty_out'    => $newQtyOut,
                        'qty_last'   => $newQtyLast,
                        'updated_at' => now(),
                    ]);
            }
            // ===============================
            // UPDATE OUTBOUND (SYNC INFO)
            // ===============================
            DB::table('tproduct_outbound')
            ->whereIn('id', $request->items)
            ->update([
                'sync_at'   => now(),
                'sync_by'   => Auth::user()->username,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Outbound berhasil dikonfirmasi'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
}