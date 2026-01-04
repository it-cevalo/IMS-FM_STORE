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
            ->leftJoin('tpos as po', 'po.id', '=', 'pi.id_po')
            ->select(
                DB::raw('DATE(pi.received_at) as tgl_inbound'),

                // JUMLAH PO (hanya inbound_source = PO)
                DB::raw("
                    COUNT(DISTINCT CASE
                        WHEN pi.inbound_source = 'PO' THEN pi.id_po
                    END) as jumlah_po
                "),

                // JUMLAH RETUR
                DB::raw("
                    COUNT(CASE
                        WHEN pi.inbound_source = 'RETUR_CUST' THEN 1
                    END) as jumlah_retur
                "),

                // TOTAL BARANG (SEMUA)
                DB::raw('COUNT(pi.id) as total_barang')
            )
            ->groupBy(DB::raw('DATE(pi.received_at)'))
            ->orderByDesc('tgl_inbound')
            ->get();

        return response()->json(['data' => $data]);
    }

    
    // public function datatable()
    // {
    //     $data = DB::table('tproduct_inbound as pi')
    //         ->join('tpos as po', 'po.id', '=', 'pi.id_po')
    //         ->select(
    //             DB::raw('DATE(pi.received_at) as tgl_inbound'),
    //             DB::raw('COUNT(pi.id) as total_barang'),
    //             DB::raw('COUNT(DISTINCT pi.id_po) as jumlah_po'),
    //             DB::raw('GROUP_CONCAT(DISTINCT po.no_po ORDER BY po.no_po SEPARATOR ", ") as daftar_po')
    //         )
    //         ->groupBy(DB::raw('DATE(pi.received_at)'))
    //         ->orderByDesc('tgl_inbound')
    //         ->get();
    
    //     return response()->json(['data' => $data]);
    // }
    
    // public function detailByDate(Request $request, $tgl)
    // {
    //     // ===============================
    //     // WAREHOUSE
    //     // ===============================
    //     $warehouses = DB::table('m_warehouses')
    //         ->orderBy('nama_wh')
    //         ->get();

    //     // ===============================
    //     // INBOUND DATA (GROUP BY PO)
    //     // ===============================
    //     $rows = DB::table('tproduct_inbound as a')
    //     ->join('tpos as po', 'a.id_po', '=', 'po.id')
    //     ->join('mproduct as p', 'a.id_product', '=', 'p.id')
    //     ->whereDate('a.received_at', $tgl)
    //     ->select(
    //         'a.id',
    //         'a.id_po',
    //         'po.no_po',
    //         'a.qr_code',
    //         'a.id_warehouse',
    //         'p.sku as SKU',
    //         'p.nama_barang',
    //         'a.received_at',
    //         'a.qty'
    //     )
    //     ->orderBy('po.no_po')
    //     ->get()
    //     ->groupBy('id_po');

    //     return view('pages.transaction.product_inbound.product_inbound_detail', compact(
    //         'tgl',
    //         'rows',
    //         'warehouses'
    //     ));
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
        // INBOUND DATA (PO + RETUR)
        // ===============================
        $rows = DB::table('tproduct_inbound as a')
            ->leftJoin('tpos as po', 'a.id_po', '=', 'po.id')
            ->join('mproduct as p', 'a.id_product', '=', 'p.id')
            ->whereDate('a.received_at', $tgl)
            ->select(
                'a.id',
                'a.id_po',
                'a.inbound_source',
                'po.no_po',
                'a.qr_code',
                'a.id_warehouse',
                'p.sku as SKU',
                'p.nama_barang',
                'a.received_at',
                'a.qty'
            )
            ->orderByRaw("
                CASE 
                    WHEN a.inbound_source = 'RETUR_CUST' THEN 1
                    ELSE 0
                END
            ")
            ->orderBy('po.no_po')
            ->orderBy('a.received_at')
            ->get()
            ->groupBy(function ($row) {
                /**
                 * ===============================
                 * GROUPING RULE
                 * ===============================
                 * - PO        → group by id_po
                 * - RETUR     → group under 'RETUR'
                 */
                return $row->inbound_source === 'RETUR_CUST'
                    ? 'RETUR'
                    : $row->id_po;
            });

        return view(
            'pages.transaction.product_inbound.product_inbound_detail',
            compact(
                'tgl',
                'rows',
                'warehouses'
            )
        );
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

            /** Ambil inbound yang dipilih */
            $inbounds = DB::table('tproduct_inbound')
                ->whereIn('id', $request->items)
                ->get();

            if ($inbounds->isEmpty()) {
                throw new \Exception('Data inbound tidak ditemukan');
            }

            /** Simpan PO yang terlibat */
            $poIds = [];

            /** Gabungkan qty inbound per product */
            $grouped = [];
            foreach ($inbounds as $inbound) {
                $key = $inbound->id_product;

                if (!isset($grouped[$key])) {
                    $grouped[$key] = [
                        'id_product' => $inbound->id_product,
                        'total_qty'  => $inbound->qty,
                        'po_details' => [$inbound->id_po_detail],
                        'po_ids'     => [$inbound->id_po]
                    ];
                } else {
                    $grouped[$key]['total_qty'] += $inbound->qty;
                    $grouped[$key]['po_details'][] = $inbound->id_po_detail;
                    $grouped[$key]['po_ids'][]     = $inbound->id_po;
                }
            }

            /** Proses stock opname */
            foreach ($grouped as $product) {

                // 🔒 Lock saldo terakhir
                $lastStock = DB::table('t_stock_opname')
                    ->where('id_warehouse', $request->id_warehouse)
                    ->where('id_product', $product['id_product'])
                    ->orderBy('id', 'desc')
                    ->lockForUpdate()
                    ->first();

                $qty_last_prev = $lastStock ? $lastStock->qty_last : 0;
                $qty_in        = $product['total_qty'];
                $qty_last      = $qty_last_prev + $qty_in;

                /**
                 * 🔍 Ambil OPNAME PERTAMA (tanggal paling awal)
                 * 👉 BUKAN per hari
                 */
                $firstOpname = DB::table('t_stock_opname')
                    ->where('id_warehouse', $request->id_warehouse)
                    ->where('id_product', $product['id_product'])
                    ->orderBy('tgl_opname', 'asc')
                    ->first();

                if ($firstOpname) {
                    // 🔁 UPDATE KE TANGGAL PERTAMA
                    DB::table('t_stock_opname')
                        ->where('id', $firstOpname->id)
                        ->update([
                            'qty_in'     => DB::raw("qty_in + {$qty_in}"),
                            'qty_last'   => $qty_last,
                            'updated_at' => now(),
                        ]);
                } else {
                    // ➕ INSERT SEKALI SEBAGAI OPNAME PERTAMA
                    DB::table('t_stock_opname')->insert([
                        'id_warehouse' => $request->id_warehouse,
                        'id_product'   => $product['id_product'],
                        'qty_in'       => $qty_in,
                        'qty_last'     => $qty_last,
                        'tgl_opname'   => now()->toDateString(), // tanggal pertama
                        'created_at'   => now(),
                    ]);
                }

                $latestQR = DB::table('tproduct_qr')
                ->select(DB::raw('MAX(id) as id'))
                ->where('id_product', $product['id_product'])
                ->groupBy('sequence_no');
                
                // Update warehouse di inbound via QR
                DB::table('tproduct_inbound as i')
                ->join('tproduct_qr as qr', function ($join) use ($latestQR) {
                    $join->on('qr.qr_code', '=', 'i.qr_code')
                         ->whereIn('qr.id', $latestQR);
                })
                ->whereIn('i.id', $request->items)
                ->where('i.id_product', $product['id_product'])
                ->update([
                    'i.id_warehouse' => $request->id_warehouse
                ]);

                // Update qty_received PO detail
                DB::table('tpo_detail')
                    ->whereIn('id', $product['po_details'])
                    ->update([
                        'qty_received' => DB::raw("qty_received + {$qty_in}")
                    ]);

                $poIds = array_merge($poIds, $product['po_ids']);
            }

            /** Update status PO */
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
                        'status_po'  => $isComplete ? 3 : 2,
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
    
    //         /** Ambil inbound yang dipilih */
    //         $inbounds = DB::table('tproduct_inbound')
    //             ->whereIn('id', $request->items)
    //             ->get();
    
    //         if ($inbounds->isEmpty()) {
    //             throw new \Exception('Data inbound tidak ditemukan');
    //         }
    
    //         /** Simpan PO yang terlibat */
    //         $poIds = [];
    
    //         /** Gabungkan qty inbound per product */
    //         $grouped = [];
    //         foreach ($inbounds as $inbound) {
    //             $key = $inbound->id_product;
    
    //             if (!isset($grouped[$key])) {
    //                 $grouped[$key] = [
    //                     'id_product' => $inbound->id_product,
    //                     'total_qty'  => $inbound->qty,
    //                     'po_details' => [$inbound->id_po_detail],
    //                     'po_ids'     => [$inbound->id_po]
    //                 ];
    //             } else {
    //                 $grouped[$key]['total_qty'] += $inbound->qty;
    //                 $grouped[$key]['po_details'][] = $inbound->id_po_detail;
    //                 $grouped[$key]['po_ids'][]     = $inbound->id_po;
    //             }
    //         }
    
    //         /** Proses stock opname */
    //         foreach ($grouped as $product) {
    
    //             // 🔒 Lock saldo terakhir
    //             $lastStock = DB::table('t_stock_opname')
    //                 ->where('id_warehouse', $request->id_warehouse)
    //                 ->where('id_product', $product['id_product'])
    //                 ->orderBy('id', 'desc')
    //                 ->lockForUpdate()
    //                 ->first();
    
    //             $qty_last_prev = $lastStock ? $lastStock->qty_last : 0;
    //             $qty_in        = $product['total_qty'];
    //             $qty_last      = $qty_last_prev + $qty_in;
    
    //             // 🔍 Cek apakah sudah ada opname HARI INI
    //             $todayOpname = DB::table('t_stock_opname')
    //                 ->where('id_warehouse', $request->id_warehouse)
    //                 ->where('id_product', $product['id_product'])
    //                 ->where('tgl_opname', now()->toDateString())
    //                 ->first();
    
    //             if ($todayOpname) {
    //                 // 🔁 UPDATE (bukan insert baru)
    //                 DB::table('t_stock_opname')
    //                     ->where('id', $todayOpname->id)
    //                     ->update([
    //                         'qty_in'     => DB::raw("qty_in + {$qty_in}"),
    //                         'qty_last'   => $qty_last,
    //                         'updated_at' => now(),
    //                     ]);
    //             } else {
    //                 // ➕ INSERT BARU (hari pertama)
    //                 DB::table('t_stock_opname')->insert([
    //                     'id_warehouse' => $request->id_warehouse,
    //                     'id_product'   => $product['id_product'],
    //                     'qty_in'       => $qty_in,
    //                     'qty_last'     => $qty_last,
    //                     'tgl_opname'   => now()->toDateString(),
    //                     'created_at'   => now(),
    //                 ]);
    //             }
    
    //             // Update warehouse di inbound via QR
    //             DB::table('tproduct_qr as qr')
    //                 ->join('tproduct_inbound as i', function ($join) {
    //                     $join->on(
    //                         DB::raw('i.qr_code COLLATE utf8mb4_unicode_ci'),
    //                         '=',
    //                         DB::raw('qr.qr_code COLLATE utf8mb4_unicode_ci')
    //                     );
    //                 })
    //                 ->whereIn('i.id', $request->items)
    //                 ->where('i.id_product', $product['id_product'])
    //                 ->update([
    //                     'i.id_warehouse' => $request->id_warehouse
    //                 ]);
    
    //             // Update qty_received PO detail
    //             DB::table('tpo_detail')
    //                 ->whereIn('id', $product['po_details'])
    //                 ->update([
    //                     'qty_received' => DB::raw("qty_received + {$qty_in}")
    //                 ]);
    
    //             $poIds = array_merge($poIds, $product['po_ids']);
    //         }
    
    //         /** Update status PO */
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
    //                     'status_po'  => $isComplete ? 3 : 2,
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