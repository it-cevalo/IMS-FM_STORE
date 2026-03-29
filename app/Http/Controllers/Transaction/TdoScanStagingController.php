<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TdoScanStaging;
use DB;

class TdoScanStagingController extends Controller
{
    public function index()
    {
        return view('pages.transaction.tdo_scan_staging.tdo_scan_staging_index');
    }

    public function datatable()
    {
        $data = DB::table('tdo_scan_staging')
            ->select(
                DB::raw('DATE(created_at) as tgl_scan'),
                DB::raw('COUNT(DISTINCT session_id) as jumlah_sesi'),
                DB::raw('COUNT(id) as total_scan'),
                DB::raw('COUNT(DISTINCT created_by) as jumlah_user')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderByDesc(DB::raw('DATE(created_at)'))
            ->get();

        return response()->json(['data' => $data]);
    }

    public function detailByDate($tgl)
    {
        $rows = TdoScanStaging::with(['product', 'creator'])
            ->whereDate('created_at', $tgl)
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('session_id');

        return view('pages.transaction.tdo_scan_staging.tdo_scan_staging_detail', compact('tgl', 'rows'));
    }
}
