<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductInboundController extends Controller
{
    public function index()
    {
        return view('pages.transaction.product_inbound.product_inbound_index');
    }

    public function datatable()
    {
        $data = DB::table('tproduct_inbound as pi')
            ->join('tpos as po', 'po.id', '=', 'pi.id_po')
            ->select(
                DB::raw('DATE(pi.received_at) as tgl_inbound'),
                DB::raw('COUNT(pi.id) as total_barang'),
                DB::raw('COUNT(DISTINCT pi.id_po) as jumlah_po'),
                DB::raw('GROUP_CONCAT(DISTINCT po.no_po ORDER BY po.no_po SEPARATOR ", ") as daftar_po')
            )
            ->groupBy(DB::raw('DATE(pi.received_at)'))
            ->orderByDesc('tgl_inbound')
            ->get();
    
        return response()->json(['data' => $data]);
    }
    
    // public function detail($tgl)
    // {
    //     $rows = DB::table('tproduct_inbound as pi')
    //         ->join('mproduct as p', 'p.id', '=', 'pi.id_product')
    //         ->join('tpo_detail as pd', 'pd.id', '=', 'pi.id_po_detail')
    //         ->join('tpos as po', 'po.id', '=', 'pi.id_po')
    //         ->whereDate('pi.received_at', $tgl)
    //         ->select(
    //             'pi.id',
    //             'pi.qty',
    //             'pi.id_product',
    //             'pi.id_po',
    //             'pi.id_po_detail',
    //             'po.no_po',
    //             'po.status_po',
    //             'p.nama_barang',
    //             'p.SKU'
    //         )
    //         ->orderBy('po.no_po')
    //         ->get()
    //         ->groupBy('id_po');

    //     $warehouses = DB::table('m_warehouses')->get();

    //     return view(
    //         'pages.transaction.product_inbound.product_inbound_detail',
    //         compact('rows', 'warehouses', 'tgl')
    //     );
    // }
    public function detailByDate(Request $request, $tgl)
    {
        // ===============================
        // WAREHOUSE
        // ===============================
        $warehouses = DB::table('m_warehouses')
            ->orderBy('nama_wh')
            ->get();

        // ===============================
        // INBOUND DATA (GROUP BY PO)
        // ===============================
        $rows = DB::table('tproduct_inbound as a')
            ->join('tpos as po', 'a.id_po', '=', 'po.id')
            ->join('mproduct as p', 'a.id_product', '=', 'p.id')
            ->whereDate('a.received_at', $tgl)
            ->select(
                'a.id',
                'a.id_po',
                'po.no_po',
                'a.qr_code',
                'p.sku as SKU',
                'p.nama_barang',
                'a.qty'
            )
            ->orderBy('po.no_po')
            ->get()
            ->groupBy('id_po');

        return view('pages.transaction.product_inbound.product_inbound_detail', compact(
            'tgl',
            'rows',
            'warehouses'
        ));
    }
    /**
     * EDIT
     */
    public function edit($id)
    {
        $inbound = DB::table('tproduct_inbound as pi')
            ->join('mproduct as p', 'p.id', '=', 'pi.id_product')
            ->join('tpo_detail as pd', 'pd.id', '=', 'pi.id_po_detail')
            ->join('tpos as po', 'po.id', '=', 'pi.id_po')
            ->where('pi.id', $id)
            ->select(
                'pi.*',
                'p.nama_barang',
                'p.SKU',
                'pd.qty as po_qty',
                'pd.qty_received',
                'po.no_po'
            )
            ->first();

        $warehouses = DB::table('m_warehouses')->get();

        return view('pages.transaction.product_inbound.product_inbound_edit', compact('inbound', 'warehouses'));
    }

    public function confirm(Request $request)
    {
        $request->validate([
            'id_warehouse' => 'required|integer',
            'items'        => 'required|array|min:1'
        ]);
    
        DB::beginTransaction();
        try {
    
            /** Ambil semua inbound yang dipilih */
            $inbounds = DB::table('tproduct_inbound')
                ->whereIn('id', $request->items)
                ->get();
    
            if ($inbounds->isEmpty()) {
                throw new \Exception('Data inbound tidak ditemukan');
            }
    
            /** Simpan PO yang terlibat (biar update status 1x per PO) */
            $poIds = [];
    
            /** Gabungkan qty inbound per id_product untuk warehouse yang sama */
            $grouped = [];
            foreach ($inbounds as $inbound) {
                $key = $inbound->id_product;
                if (!isset($grouped[$key])) {
                    $grouped[$key] = [
                        'id_product' => $inbound->id_product,
                        'total_qty'  => $inbound->qty,
                        'po_details' => [$inbound->id_po_detail],
                        'po_ids'    => [$inbound->id_po]
                    ];
                } else {
                    $grouped[$key]['total_qty'] += $inbound->qty;
                    $grouped[$key]['po_details'][] = $inbound->id_po_detail;
                    $grouped[$key]['po_ids'][] = $inbound->id_po;
                }
            }
    
            /** Loop setiap product gabungan untuk insert/update stock opname */
            foreach ($grouped as $product) {
    
                // Ambil qty_last terakhir dari stock opname untuk produk & warehouse ini
                $lastStock = DB::table('t_stock_opname')
                    ->where('id_warehouse', $request->id_warehouse)
                    ->where('id_product', $product['id_product'])
                    ->orderBy('id', 'desc')
                    ->limit(1)
                    ->first();
    
                $qty_last_prev = $lastStock ? $lastStock->qty_last : 0;
                $qty_in = $product['total_qty'];
                $qty_last = $qty_last_prev + $qty_in;
    
                // Insert stock opname baru
                DB::table('t_stock_opname')->insert([
                    'id_warehouse' => $request->id_warehouse,
                    'id_product'   => $product['id_product'],
                    'qty_in'       => $qty_in,
                    'qty_last'     => $qty_last,
                    'tgl_opname'   => now()->toDateString(),
                    'created_at'   => now(),
                ]);
    
                // Update qty_received untuk semua po_detail terkait
                DB::table('tpo_detail')
                    ->whereIn('id', $product['po_details'])
                    ->update([
                        'qty_received' => DB::raw("qty_received + {$qty_in}")
                    ]);
    
                $poIds = array_merge($poIds, $product['po_ids']);
            }
    
            /** Update status PO (PER PO, BUKAN PER ITEM) */
            $poIds = array_unique($poIds);
    
            foreach ($poIds as $poId) {
    
                $details = DB::table('tpo_detail')
                    ->where('id_po', $poId)
                    ->get();
    
                $isComplete = true;
    
                foreach ($details as $d) {
                    if ($d->qty_received < $d->qty) {
                        $isComplete = false;
                        break;
                    }
                }
    
                DB::table('tpos')
                    ->where('id', $poId)
                    ->update([
                        'status_po'  => $isComplete ? 3 : 2, // 3 = COMPLETE, 2 = PROGRESS
                        'updated_at' => now()
                    ]);
            }
    
            DB::commit();
    
            return response()->json([
                'message' => 'Inbound berhasil dikonfirmasi'
            ]);
    
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    
    // public function confirm(Request $request)
    // {
    //     $request->validate([
    //         'id_warehouse' => 'required|integer',
    //         'items'        => 'required|array|min:1'
    //     ]);
    
    //     DB::beginTransaction();
    //     try {
    
    //         /** Ambil semua inbound yang dipilih */
    //         $inbounds = DB::table('tproduct_inbound')
    //             ->whereIn('id', $request->items)
    //             ->get();
    
    //         if ($inbounds->isEmpty()) {
    //             throw new \Exception('Data inbound tidak ditemukan');
    //         }
    
    //         /** Simpan PO yang terlibat (biar update status 1x per PO) */
    //         $poIds = [];
    
    //         foreach ($inbounds as $inbound) {
    
    //             /** 1️⃣ INSERT STOCK OPNAME */
    //             DB::table('t_stock_opname')->insert([
    //                 'id_warehouse' => $request->id_warehouse,
    //                 'id_product'   => $inbound->id_product,
    //                 'qty_in'       => $inbound->qty,
    //                 'qty_last'     => $inbound->qty,
    //                 'tgl_opname'   => now()->toDateString(),
    //                 'created_at'   => now(),
    //             ]);
    
    //             /** 2️⃣ UPDATE QTY RECEIVED PO DETAIL */
    //             DB::table('tpo_detail')
    //                 ->where('id', $inbound->id_po_detail)
    //                 ->increment('qty_received', $inbound->qty);
    
    //             $poIds[] = $inbound->id_po;
    //         }
    
    //         /** 3️⃣ UPDATE STATUS PO (PER PO, BUKAN PER ITEM) */
    //         $poIds = array_unique($poIds);
    
    //         foreach ($poIds as $poId) {
    
    //             $details = DB::table('tpo_detail')
    //                 ->where('id_po', $poId)
    //                 ->get();
    
    //             $isComplete = true;
    
    //             foreach ($details as $d) {
    //                 if ($d->qty_received < $d->qty) {
    //                     $isComplete = false;
    //                     break;
    //                 }
    //             }
    
    //             DB::table('tpos')
    //                 ->where('id', $poId)
    //                 ->update([
    //                     'status_po'  => $isComplete ? 3 : 2, // 3 = COMPLETE, 2 = PROGRESS
    //                     'updated_at' => now()
    //                 ]);
    //         }
    
    //         DB::commit();
    
    //         return response()->json([
    //             'message' => 'Inbound berhasil dikonfirmasi'
    //         ]);
    
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'message' => $e->getMessage()
    //         ], 500);
    //     }
    // }    
}