<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mproduct;
use App\Models\MproductUnit;
use App\Models\MproductType;
use PDF, DB;

class ReportProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $mproduct = MProduct::select(
            'mproduct.id',
            'mproduct_type.nama_tipe as nama_tipe',
            'mproduct_unit.nama_unit as nama_unit',
            'mproduct.SKU',
            'mproduct.nama_barang',
            'mproduct.id_unit',
            'mproduct.id_type',
            'mproduct.harga_beli',
            'mproduct.harga_jual',
            'mproduct.harga_rata_rata',
            'mproduct.flag_active'
        )
        ->join('mproduct_type', 'mproduct.id_type', '=', 'mproduct_type.id')
        ->join('mproduct_unit', 'mproduct.id_unit', '=', 'mproduct_unit.id')
        ->paginate(5);
        
        return view('pages.report.report_product',compact('mproduct'))->with('i', (request()->input('page', 1) - 1) * 5);
    }

    public function filter(Request $request){
        $from_date  = $request->fd;
        $to_date    = $request->td;
        
        if($request->opt == 'filter'){
            $product = MProduct::select(
                'mproduct.id',
                'mproduct_type.nama_tipe as nama_tipe',
                'mproduct_unit.nama_unit as nama_unit',
                'mproduct.SKU',
                'mproduct.nama_barang',
                'mproduct.id_unit',
                'mproduct.id_type',
                'mproduct.harga_beli',
                'mproduct.harga_jual',
                'mproduct.harga_rata_rata',
                'mproduct.flag_active'
            )
            ->join('mproduct_type', 'mproduct.id_type', '=', 'mproduct_type.id')
            ->join('mproduct_unit', 'mproduct.id_unit', '=', 'mproduct_unit.id')
            // ->whereBetween('product.tgl_mutasi',[$from_date,$to_date])
            ->paginate(5);
            
            return view('pages.report.report_product',compact('mproduct'))->with('i', (request()->input('page', 1) - 1) * 5);
    
        } else if ($request->opt == 'export'){
            $mproduct = MProduct::select(
                'mproduct.id',
                'mproduct_type.nama_tipe as nama_tipe',
                'mproduct_unit.nama_unit as nama_unit',
                'mproduct.SKU',
                'mproduct.nama_barang',
                'mproduct.id_unit',
                'mproduct.id_type',
                'mproduct.harga_beli',
                'mproduct.harga_jual',
                'mproduct.harga_rata_rata',
                'mproduct.flag_active'
            )
            ->join('mproduct_type', 'mproduct.id_type', '=', 'mproduct_type.id')
            ->join('mproduct_unit', 'mproduct.id_unit', '=', 'mproduct_unit.id')
            // ->whereBetween('mproduct.tgl_mutasi',[$from_date,$to_date])
            ->paginate(5);
            $pdf = PDF::loadview('pages.report.report_product_pdf',['mproduct'=>$mproduct])->setPaper('A4', 'landscape');
            return $pdf->download('report_product_'.date('Y-m-d').'.pdf');
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