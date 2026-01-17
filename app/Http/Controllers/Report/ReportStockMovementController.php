<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\Report\StockMovementExport;
use Maatwebsite\Excel\Facades\Excel;

class ReportStockMovementController extends Controller
{
    public function index()
    {
        return view('pages.report.stock_movement');
    }
    
    public function data(Request $request)
    {
        $startDate = $request->fd ?? now()->subDays(30)->toDateString();
        $endDate   = $request->td ?? now()->toDateString();
    
        $days = \Carbon\Carbon::parse($startDate)
            ->diffInDays(\Carbon\Carbon::parse($endDate)) ?: 30;
    
        $category = $request->movement_type;
    
        /**
         * =======================
         * SUBQUERY INBOUND
         * =======================
         */
        $inbound = DB::table('tproduct_inbound')
            ->select(
                'id_product',
                DB::raw('SUM(qty) as qty_in')
            )
            ->whereBetween('received_at', [$startDate, $endDate])
            ->groupBy('id_product');
    
        /**
         * =======================
         * SUBQUERY OUTBOUND
         * =======================
         */
        $outbound = DB::table('tproduct_outbound')
            ->select(
                'id_product',
                DB::raw('SUM(qty) as qty_out'),
                DB::raw('MAX(out_at) as last_out_date')
            )
            ->whereBetween('out_at', [$startDate, $endDate])
            ->groupBy('id_product');
    
        /**
         * =======================
         * MAIN QUERY
         * =======================
         */
        $query = DB::table('mproduct as p')
            ->leftJoinSub($inbound, 'i', 'i.id_product', '=', 'p.id')
            ->leftJoinSub($outbound, 'o', 'o.id_product', '=', 'p.id')
            ->where('p.flag_active', 'Y')
            ->select([
                'p.sku',
                'p.nama_barang',
    
                DB::raw('COALESCE(i.qty_in, 0) as qty_in'),
                DB::raw('COALESCE(o.qty_out, 0) as qty_out'),
                DB::raw('o.last_out_date'),
    
                DB::raw("ROUND(COALESCE(o.qty_out,0)/{$days}, 2) as movement_rate"),
    
                DB::raw("
                    CASE
                        WHEN COALESCE(o.qty_out,0) = 0
                             AND o.last_out_date IS NULL
                            THEN 'DEAD'
    
                        WHEN COALESCE(o.qty_out,0) >= 20
                             AND DATEDIFF(CURDATE(), DATE(o.last_out_date)) <= 7
                            THEN 'FAST'
    
                        WHEN COALESCE(o.qty_out,0) BETWEEN 5 AND 19
                            THEN 'MEDIUM'
    
                        ELSE 'SLOW'
                    END as movement_category
                ")
            ]);
    
        if ($category) {
            $query->having('movement_category', $category);
        }
    
        return datatables()
            ->of($query)
            ->addIndexColumn()
            ->editColumn('last_out_date', function ($r) {
                return $r->last_out_date
                    ? date('d-m-Y', strtotime($r->last_out_date))
                    : '-';
            })
            ->addColumn('badge', function ($r) {
                $map = [
                    'FAST'   => 'success',
                    'MEDIUM' => 'primary',
                    'SLOW'   => 'warning',
                    'DEAD'   => 'danger',
                ];
                return '<span class="badge badge-'.$map[$r->movement_category].'">'
                    .$r->movement_category.
                    '</span>';
            })
            ->rawColumns(['badge'])
            ->make(true);
    }
    

    public function export(Request $request)
    {
        $startDate = $request->fd ?? now()->subDays(30)->toDateString();
        $endDate   = $request->td ?? now()->toDateString();
        $category  = $request->movement_type;

        return Excel::download(
            new StockMovementExport($startDate, $endDate, $category),
            'stock_movement_'.date('Ymd_His').'.xlsx'
        );
    }
    
    // public function data(Request $request)
    // {
    //     $startDate = $request->fd ?? now()->subDays(30)->toDateString();
    //     $endDate   = $request->td ?? now()->toDateString();
    //     $days      = now()->diffInDays($startDate) ?: 30;

    //     $query = DB::table('mproduct as p')
    //         ->leftJoin('tproduct_inbound as i', function ($join) use ($startDate, $endDate) {
    //             $join->on('i.id_product', '=', 'p.id')
    //                  ->whereBetween('i.received_at', [$startDate, $endDate]);
    //         })
    //         ->leftJoin('tproduct_outbound as o', function ($join) use ($startDate, $endDate) {
    //             $join->on('o.id_product', '=', 'p.id')
    //                  ->whereBetween('o.out_at', [$startDate, $endDate]);
    //         })
    //         ->where('p.flag_active', 'Y')
    //         ->groupBy('p.id', 'p.sku', 'p.nama_barang')
    //         ->select([
    //             'p.sku',
    //             'p.nama_barang',
    //             DB::raw('COALESCE(SUM(i.qty),0) as qty_in'),
    //             DB::raw('COALESCE(SUM(o.qty),0) as qty_out'),
    //             DB::raw('MAX(o.out_at) as last_out_date'),
    //             DB::raw("ROUND(COALESCE(SUM(o.qty),0)/{$days},2) as movement_rate"),
    //             DB::raw("
    //                 CASE
    //                     WHEN COALESCE(SUM(o.qty),0)=0
    //                          AND DATEDIFF(CURDATE(), DATE(MAX(o.out_at))) >= 90
    //                         THEN 'DEAD'
    //                     WHEN COALESCE(SUM(o.qty),0) >= 20
    //                          AND DATEDIFF(CURDATE(), DATE(MAX(o.out_at))) <= 7
    //                         THEN 'FAST'
    //                     WHEN COALESCE(SUM(o.qty),0) BETWEEN 5 AND 19
    //                         THEN 'MEDIUM'
    //                     ELSE 'SLOW'
    //                 END as movement_category
    //             ")
    //         ]);

    //     return datatables()->of($query)
    //         ->addIndexColumn()
    //         ->editColumn('last_out_date', function ($row) {
    //             return $row->last_out_date
    //                 ? date('d-m-Y', strtotime($row->last_out_date))
    //                 : '-';
    //         })
    //         ->addColumn('badge', function ($row) {
    //             $map = [
    //                 'FAST'   => 'success',
    //                 'MEDIUM' => 'primary',
    //                 'SLOW'   => 'warning',
    //                 'DEAD'   => 'danger',
    //             ];
    //             return '<span class="badge badge-'.$map[$row->movement_category].'">'
    //                     .$row->movement_category.'</span>';
    //         })
    //         ->rawColumns(['badge'])
    //         ->make(true);
    // }
}