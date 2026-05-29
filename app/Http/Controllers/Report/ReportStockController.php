<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\Report\StockReportExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Helpers\Permission;
use App\Logs;
use Auth;

class ReportStockController extends Controller
{
    private function activityLog(string $section, string $content): void
    {
        try {
            (new Logs('Logs_ReportStockController'))->write($section, $content);
        } catch (\Throwable $e) {
            \Log::error('[ReportStockController] Gagal menulis log: ' . $e->getMessage());
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
        if (!Permission::view('MENU-0503')) {
            abort(403);
        }

        return view('pages.report.stock_report');
    }

    public function data(Request $request)
    {
        $startDate = $request->fd
            ? $request->fd . ' 00:00:00'
            : now()->startOfMonth()->toDateTimeString();

        $endDate = $request->td
            ? $request->td . ' 23:59:59'
            : now()->endOfDay()->toDateTimeString();

        /*
         * Subquery: all confirmed inbound BEFORE the period (opening balance base)
         */
        $beforeIn = DB::table('tproduct_inbound')
            ->select('id_product', DB::raw('SUM(qty) as qty'))
            ->whereNotNull('sync_at')
            ->where('received_at', '<', $startDate)
            ->groupBy('id_product');

        /*
         * Subquery: all confirmed outbound BEFORE the period
         */
        $beforeOut = DB::table('tproduct_outbound')
            ->select('id_product', DB::raw('SUM(qty) as qty'))
            ->whereNotNull('sync_at')
            ->where('out_at', '<', $startDate)
            ->groupBy('id_product');

        /*
         * Subquery: inbound DURING the period split by source
         */
        $periodIn = DB::table('tproduct_inbound')
            ->select(
                'id_product',
                DB::raw("SUM(CASE WHEN inbound_source = 'PO' THEN qty ELSE 0 END) as qty_in"),
                DB::raw("SUM(CASE WHEN inbound_source = 'RETUR_CUST' THEN qty ELSE 0 END) as qty_return")
            )
            ->whereNotNull('sync_at')
            ->whereBetween('received_at', [$startDate, $endDate])
            ->groupBy('id_product');

        /*
         * Subquery: outbound DURING the period
         */
        $periodOut = DB::table('tproduct_outbound')
            ->select('id_product', DB::raw('SUM(qty) as qty'))
            ->whereNotNull('sync_at')
            ->whereBetween('out_at', [$startDate, $endDate])
            ->groupBy('id_product');

        $query = DB::table('mproduct as p')
            ->leftJoinSub($beforeIn, 'bi', 'bi.id_product', '=', 'p.id')
            ->leftJoinSub($beforeOut, 'bo', 'bo.id_product', '=', 'p.id')
            ->leftJoinSub($periodIn, 'pi', 'pi.id_product', '=', 'p.id')
            ->leftJoinSub($periodOut, 'po', 'po.id_product', '=', 'p.id')
            ->where('p.flag_active', 'Y')
            ->select([
                'p.sku',
                'p.nama_barang',
                DB::raw('(COALESCE(bi.qty, 0) - COALESCE(bo.qty, 0)) as last_stock'),
                DB::raw('COALESCE(po.qty, 0) as stock_out'),
                DB::raw('COALESCE(pi.qty_return, 0) as return_qty'),
                DB::raw('COALESCE(pi.qty_in, 0) as stock_in'),
                DB::raw('
                    (COALESCE(bi.qty, 0) - COALESCE(bo.qty, 0))
                    + COALESCE(pi.qty_in, 0)
                    + COALESCE(pi.qty_return, 0)
                    - COALESCE(po.qty, 0)
                    as remain
                '),
            ]);

        $query->orderBy('p.sku');

        return datatables()
            ->of($query)
            ->addIndexColumn()
            ->make(true);
    }

    public function export(Request $request)
    {
        $startDate = $request->fd ?? now()->startOfMonth()->toDateString();
        $endDate   = $request->td ?? now()->toDateString();

        $this->activityLog('EXPORT_STOCK', "User: {$this->actor()} | Periode: {$startDate} s/d {$endDate} | Status: PROCESS");

        try {
            $result = Excel::download(
                new StockReportExport($startDate, $endDate, Auth::user()->name),
                'laporan_stock_' . date('Ymd_His') . '.xlsx'
            );

            $this->activityLog('EXPORT_STOCK', "User: {$this->actor()} | Periode: {$startDate} s/d {$endDate} | Status: SUCCESS");

            return $result;

        } catch (\Throwable $e) {
            $this->activityLog('EXPORT_STOCK', "User: {$this->actor()} | Periode: {$startDate} s/d {$endDate} | Status: FAILED | Error: {$e->getMessage()}");
            return back()->with('error', 'Terjadi kesalahan saat export. Silakan coba lagi.');
        }
    }
}
