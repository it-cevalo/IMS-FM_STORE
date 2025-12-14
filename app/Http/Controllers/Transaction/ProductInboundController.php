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
            ->join('mproduct as p', 'p.id', '=', 'pi.id_product')
            ->join('tpo_detail as pd', 'pd.id', '=', 'pi.id_po_detail')
            ->join('tpos as po', 'po.id', '=', 'pi.id_po')
            ->select(
                'pi.id',
                'pi.qr_code',
                'p.SKU',
                'p.nama_barang',
                'pi.qty',
                'pd.qty as po_qty',
                'pd.qty_received',
                'po.no_po',
                'po.status_po',
                'pi.created_at'
            )
            ->orderByDesc('pi.id')
            ->get();

        return response()->json(['data' => $data]);
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
    
    public function confirm(Request $request, $id)
    {
        DB::beginTransaction();
        try {

            $inbound = DB::table('tproduct_inbound')->where('id', $id)->first();

            /** 1️⃣ Update / Insert Stock Opname */
            DB::table('t_stock_opname')->insert([
                'id_warehouse' => $request->id_warehouse,
                'id_product'   => $inbound->id_product,
                'qty_in'       => $inbound->qty,
                'qty_last'     => $inbound->qty,
                'tgl_opname'   => now()->toDateString(),
                'created_at'   => now(),
            ]);

            /** 2️⃣ Update qty_received di PO Detail */
            DB::table('tpo_detail')
                ->where('id', $inbound->id_po_detail)
                ->increment('qty_received', $inbound->qty);

            /** 3️⃣ Hitung Status PO */
            $poDetails = DB::table('tpo_detail')
                ->where('id_po', $inbound->id_po)
                ->get();

            $isComplete = true;

            foreach ($poDetails as $d) {
                if ($d->qty_received < $d->qty) {
                    $isComplete = false;
                    break;
                }
            }

            DB::table('tpos')
                ->where('id', $inbound->id_po)
                ->update([
                    'status_po'   => $isComplete ? 3 : 2,
                    'updated_at'  => now()
                ]);

            DB::commit();
            return response()->json(['message' => 'Inbound berhasil dikonfirmasi']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}