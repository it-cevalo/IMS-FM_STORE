<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StockMutation;
use PDF, DB;

class ReportStockMutationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $stock_mutation = StockMutation::select(
            'stock_mutation.id',
            'm_warehouses.code_wh',
            'm_warehouses.nama_wh',
            'mproduct.SKU as code_prd',
            'mproduct.nama_barang as nama_prd',
            'stock_mutation.qty_start',
            'stock_mutation.qty_in',
            'stock_mutation.qty_out',
            'stock_mutation.qty_last',
            'stock_mutation.tgl_mutasi'
        )
        ->join('m_warehouses', 'stock_mutation.id_warehouse', '=', 'm_warehouses.id')
        ->join('mproduct', 'stock_mutation.id_product', '=', 'mproduct.id')
        ->paginate(5);
        
        return view('pages.report.report_stock_mutation',compact('stock_mutation'))->with('i', (request()->input('page', 1) - 1) * 5);
    }

    public function filter(Request $request){
        $from_date  = $request->fd;
        $to_date    = $request->td;
        
        if($request->opt == 'filter'){
            $stock_mutation = StockMutation::select(
                'stock_mutation.id',
                'm_warehouses.code_wh',
                'm_warehouses.nama_wh',
                'mproduct.SKU as code_prd',
                'mproduct.nama_barang as nama_prd',
                'stock_mutation.qty_start',
                'stock_mutation.qty_in',
                'stock_mutation.qty_out',
                'stock_mutation.qty_last',
                'stock_mutation.tgl_mutasi'
            )
            ->join('m_warehouses', 'stock_mutation.id_warehouse', '=', 'm_warehouses.id')
            ->join('mproduct', 'stock_mutation.id_product', '=', 'mproduct.id')
            ->whereBetween('stock_mutation.tgl_mutasi',[$from_date,$to_date])
            ->paginate(5);            
            
            return view('pages.report.report_stock_mutation',compact('stock_mutation'))->with('i', (request()->input('page', 1) - 1) * 5);
        } else if ($request->opt == 'export'){
            $stock_mutation = StockMutation::select(
                'stock_mutation.id',
                'm_warehouses.code_wh',
                'm_warehouses.nama_wh',
                'mproduct.SKU as code_prd',
                'mproduct.nama_barang as nama_prd',
                'stock_mutation.qty_start',
                'stock_mutation.qty_in',
                'stock_mutation.qty_out',
                'stock_mutation.qty_last',
                'stock_mutation.tgl_mutasi'
            )
            ->join('m_warehouses', 'stock_mutation.id_warehouse', '=', 'm_warehouses.id')
            ->join('mproduct', 'stock_mutation.id_product', '=', 'mproduct.id')
            ->whereBetween('stock_mutation.tgl_mutasi',[$from_date,$to_date])
            ->paginate(5);            

            $pdf = PDF::loadview('pages.report.report_stock_mutation_pdf',['stock_mutation'=>$stock_mutation])->setPaper('A4', 'landscape');
            return $pdf->download('report_stock_mutation_'.date('Y-m-d').'.pdf');
        }
        
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