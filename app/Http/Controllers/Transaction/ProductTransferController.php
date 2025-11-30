<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TStockOpname;
use App\Models\HStockOpname;
use App\Models\TProductTransferH;
use App\Models\Mproduct;
use App\Models\MproductStock;
use App\Models\MWarehouse;
use Auth, DB;

class ProductTransferController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $product_transfer = TProductTransferH::select('t_product_trf_h.code_trf', 't_product_trf_h.tgl_trf', 't_product_trf_h.id_warehouse_from', 't_product_trf_h.id_warehouse_to', 't_product_trf_h.desc_trf')
        ->join('t_product_trf_d', 't_product_trf_h.id', '=', 't_product_trf_d.id_product_trf_h')
        ->latest()
        ->paginate(5);    
        $products = Mproduct::get();
        $warehouses = MWarehouse::get();
        return view('pages.transaction.product_transfer.product_transfer_index',compact('product_transfer','warehouses'))->with('i', (request()->input('page', 1) - 1) * 5);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $warehouses = MWarehouse::get();
        return view('pages.transaction.product_transfer.product_transfer_create',compact('warehouses'));
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