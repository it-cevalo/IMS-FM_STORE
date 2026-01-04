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
        /* =======================
           DATE RANGE
        ======================= */
        $startDate = $request->fd ?? now()->subDays(30)->toDateString();
        $endDate   = $request->td ?? now()->toDateString();
    
        $days = \Carbon\Carbon::parse($startDate)
                    ->diffInDays(\Carbon\Carbon::parse($endDate)) ?: 30;
    
        /* =======================
           FILTER CATEGORY
        ======================= */
        $category = $request->movement_type; // FAST | MEDIUM | SLOW | DEAD
    
        /* =======================
           BASE QUERY
        ======================= */
        $baseQuery = DB::table('mproduct as p')
            ->leftJoin('tproduct_inbound as i', function ($join) use ($startDate, $endDate) {
                $join->on('i.id_product', '=', 'p.id')
                     ->whereBetween('i.received_at', [$startDate, $endDate]);
            })
            ->leftJoin('tproduct_outbound as o', function ($join) use ($startDate, $endDate) {
                $join->on('o.id_product', '=', 'p.id')
                     ->whereBetween('o.out_at', [$startDate, $endDate]);
            })
            ->where('p.flag_active', 'Y')
            ->groupBy('p.id', 'p.sku', 'p.nama_barang')
            ->select([
                'p.sku',
                'p.nama_barang',
    
                DB::raw('COALESCE(SUM(i.qty),0) AS qty_in'),
                DB::raw('COALESCE(SUM(o.qty),0) AS qty_out'),
                DB::raw('MAX(o.out_at) AS last_out_date'),
    
                DB::raw("ROUND(COALESCE(SUM(o.qty),0)/{$days}, 2) AS movement_rate"),
    
                DB::raw("
                    CASE
                        WHEN COALESCE(SUM(o.qty),0) = 0
                             AND MAX(o.out_at) IS NULL
                            THEN 'DEAD'
    
                        WHEN COALESCE(SUM(o.qty),0) >= 20
                             AND DATEDIFF(CURDATE(), DATE(MAX(o.out_at))) <= 7
                            THEN 'FAST'
    
                        WHEN COALESCE(SUM(o.qty),0) BETWEEN 5 AND 19
                            THEN 'MEDIUM'
    
                        ELSE 'SLOW'
                    END AS movement_category
                ")
            ]);
    
        /* =======================
           FILTER CATEGORY (HAVING)
        ======================= */
        if ($category) {
            $baseQuery->having('movement_category', $category);
        }
    
        /* =======================
           DATATABLES (AMAN SEARCH)
        ======================= */
        return datatables()
            ->of($baseQuery)
    
            // 🔥 CUSTOM SEARCH (HINDARI LOWER() KE ALIAS)
            ->filter(function ($query) use ($request) {
                if (!empty($request->search['value'])) {
                    $search = $request->search['value'];
    
                    $query->where(function ($q) use ($search) {
                        $q->where('p.sku', 'LIKE', "%{$search}%")
                          ->orWhere('p.nama_barang', 'LIKE', "%{$search}%");
                    });
                }
            })
    
            ->addIndexColumn()
    
            ->editColumn('last_out_date', function ($r) {
                return $r->last_out_date
                    ? date('d-m-Y', strtotime($r->last_out_date))
                    : '-';
            })
    
            ->addColumn('badge', function ($r) {
                $color = [
                    'FAST'   => 'success',
                    'MEDIUM' => 'primary',
                    'SLOW'   => 'warning',
                    'DEAD'   => 'danger'
                ];
    
                return '<span class="badge badge-'.$color[$r->movement_category].'">'
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