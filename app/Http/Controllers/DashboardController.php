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
    
    public function chartFastSlow(Request $request)
    {
        $mode  = $request->mode; // monthly | yearly
        $month = $request->month;
        $year  = $request->year;
        
        /* =============================
        FILTER TANGGAL
        ============================== */
        if ($mode === 'monthly') {
            $dateFilterInbound  = "YEAR(tpi.received_at) = $year AND MONTH(tpi.received_at) = $month";
            $dateFilterOutbound = "YEAR(tpo.out_at) = $year AND MONTH(tpo.out_at) = $month";
        } else {
            $dateFilterInbound  = "YEAR(tpi.received_at) = $year";
            $dateFilterOutbound = "YEAR(tpo.out_at) = $year";
        }
    
        /* =============================
           FAST MOVING (OUT TERBESAR)
        ============================== */
        $fast = DB::select("
            SELECT 
                mp.nama_barang,
                IFNULL(inb.inbound, 0)   AS inbound,
                IFNULL(outb.outbound, 0) AS outbound
            FROM mproduct mp
        
            LEFT JOIN (
                SELECT 
                    tpi.id_product,
                    COUNT(*) AS inbound
                FROM tproduct_inbound tpi
                WHERE tpi.sync_by IS NOT NULL
                AND tpi.sync_by != ''
                AND $dateFilterInbound
                GROUP BY tpi.id_product
            ) inb ON mp.id = inb.id_product
        
            LEFT JOIN (
                SELECT 
                    tpo.id_product,
                    COUNT(*) AS outbound
                FROM tproduct_outbound tpo
                WHERE tpo.sync_by IS NOT NULL
                AND tpo.sync_by != ''
                AND $dateFilterOutbound
                GROUP BY tpo.id_product
            ) outb ON mp.id = outb.id_product
        
            -- 🔴 FILTER BERDASARKAN OUT
            WHERE IFNULL(outb.outbound, 0) > 0
        
            ORDER BY outb.outbound DESC
            LIMIT 10
        ");
    
        /* =============================
           SLOW MOVING (OUT TERKECIL)
        ============================== */
        $slow = DB::select("
            SELECT 
                mp.nama_barang,
                IFNULL(inb.inbound, 0)   AS inbound,
                IFNULL(outb.outbound, 0) AS outbound
            FROM mproduct mp

            LEFT JOIN (
                SELECT 
                    tpi.id_product,
                    COUNT(*) AS inbound
                FROM tproduct_inbound tpi
                WHERE tpi.sync_by IS NOT NULL
                AND tpi.sync_by != ''
                AND $dateFilterInbound
                GROUP BY tpi.id_product
            ) inb ON mp.id = inb.id_product

            LEFT JOIN (
                SELECT 
                    tpo.id_product,
                    COUNT(*) AS outbound
                FROM tproduct_outbound tpo
                WHERE tpo.sync_by IS NOT NULL
                AND tpo.sync_by != ''
                AND $dateFilterOutbound
                GROUP BY tpo.id_product
            ) outb ON mp.id = outb.id_product

            -- 🔴 PENTING: FILTER OUT, BUKAN IN
            WHERE IFNULL(outb.outbound, 0) > 0

            ORDER BY outb.outbound ASC
            LIMIT 10
        ");
    
        return response()->json([
            'fast' => [
                'labels'   => array_column($fast, 'nama_barang'),
                'inbound'  => array_map('intval', array_column($fast, 'inbound')),
                'outbound' => array_map('intval', array_column($fast, 'outbound')),
            ],
            'slow' => [
                'labels'   => array_column($slow, 'nama_barang'),
                'inbound'  => array_map('intval', array_column($slow, 'inbound')),
                'outbound' => array_map('intval', array_column($slow, 'outbound')),
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