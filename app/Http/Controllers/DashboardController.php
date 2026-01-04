<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Tpo;
use App\Models\TProductInbound;
use App\Models\TProductOutbound;
use App\Models\Tdo;
use App\Models\TInvoiceH;
use App\Models\TStockOpname;
use DB;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $total_po = Tpo::count();
        $total_do = Tdo::count();
        $total_inb = TProductInbound::count();
        $total_outb = TProductOutbound::count();
        return view('pages.dashboard',compact('total_po','total_do','total_inb','total_outb'));
    }
    
    public function chartInOut(Request $request)
    {
        $month = (int) $request->month;
        $year  = (int) $request->year;

        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        // init array tanggal 1 - akhir bulan
        $labels = [];
        $inbound = array_fill(1, $daysInMonth, 0);
        $outbound = array_fill(1, $daysInMonth, 0);

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $labels[] = (string) $i;
        }

        // ================= INBOUND =================
        $inData = TProductInbound::select(
                DB::raw('DAY(received_at) as day'),
                DB::raw('SUM(qty) as total')
            )
            ->whereMonth('received_at', $month)
            ->whereYear('received_at', $year)
            ->groupBy(DB::raw('DAY(received_at)'))
            ->get();

        foreach ($inData as $row) {
            $inbound[(int)$row->day] = (int)$row->total;
        }

        // ================= OUTBOUND =================
        $outData = TProductOutbound::select(
                DB::raw('DAY(out_at) as day'),
                DB::raw('SUM(qty) as total')
            )
            ->whereMonth('out_at', $month)
            ->whereYear('out_at', $year)
            ->groupBy(DB::raw('DAY(out_at)'))
            ->get();

        foreach ($outData as $row) {
            $outbound[(int)$row->day] = (int)$row->total;
        }

        return response()->json([
            'labels'   => $labels,
            'inbound'  => array_values($inbound),
            'outbound' => array_values($outbound),
            'summary'  => [
                'inbound'  => array_sum($inbound),
                'outbound' => array_sum($outbound),
                'balance'  => array_sum($inbound) - array_sum($outbound)
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}