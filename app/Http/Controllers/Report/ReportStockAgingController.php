<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\Report\StockAgingExport;
use Maatwebsite\Excel\Facades\Excel;

class ReportStockAgingController extends Controller
{
    public function index()
    {
        return view('pages.report.stock_aging');
    }

    public function data(Request $request)
    {
        $bucket = $request->aging_bucket;

        $query = DB::table('mproduct as p')
            ->leftJoin('tproduct_inbound as i', 'i.id_product', '=', 'p.id')
            ->leftJoin('t_stock_opname as so', 'so.id_product', '=', 'p.id')
            ->where('p.flag_active', 'Y')
            ->groupBy(
                'p.id',
                'p.sku',
                'p.nama_barang',
                'so.qty_in',
                'so.qty_last',
                'so.qty_out'
            )
            ->select([
                'p.sku',
                'p.nama_barang',

                DB::raw('MIN(i.received_at) AS first_in_date'),
                DB::raw('DATEDIFF(CURDATE(), DATE(MIN(i.received_at))) AS aging_days'),
                DB::raw('(COALESCE(so.qty_in,0) + COALESCE(so.qty_last,0) - COALESCE(so.qty_out,0)) AS stock_on_hand'),

                DB::raw("
                    CASE
                        WHEN DATEDIFF(CURDATE(), DATE(MIN(i.received_at))) <= 30 THEN '0-30'
                        WHEN DATEDIFF(CURDATE(), DATE(MIN(i.received_at))) BETWEEN 31 AND 60 THEN '31-60'
                        WHEN DATEDIFF(CURDATE(), DATE(MIN(i.received_at))) BETWEEN 61 AND 90 THEN '61-90'
                        ELSE '>90'
                    END AS aging_bucket
                ")
            ])
            ->havingRaw('stock_on_hand > 0');

        if ($bucket) {
            $query->having('aging_bucket', $bucket);
        }

        return datatables()
            ->of($query)
            ->filter(function ($q) use ($request) {
                if (!empty($request->search['value'])) {
                    $search = $request->search['value'];
                    $q->where(function ($w) use ($search) {
                        $w->where('p.sku', 'LIKE', "%{$search}%")
                          ->orWhere('p.nama_barang', 'LIKE', "%{$search}%");
                    });
                }
            })
            ->addIndexColumn()
            ->editColumn('first_in_date', fn ($r) =>
                $r->first_in_date ? date('d-m-Y', strtotime($r->first_in_date)) : '-'
            )
            ->addColumn('badge', function ($r) {
                $map = [
                    '0-30' => 'success',
                    '31-60' => 'primary',
                    '61-90' => 'warning',
                    '>90'   => 'danger'
                ];
                return '<span class="badge badge-'.$map[$r->aging_bucket].'">'
                        .$r->aging_bucket.' Hari</span>';
            })
            ->rawColumns(['badge'])
            ->make(true);
    }

    public function export(Request $request)
    {
        return Excel::download(
            new StockAgingExport($request->aging_bucket),
            'stock_aging_'.date('Ymd_His').'.xlsx'
        );
    }
}