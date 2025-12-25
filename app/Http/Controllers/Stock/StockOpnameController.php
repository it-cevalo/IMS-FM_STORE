<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TStockOpname;
use App\Models\HStockOpname;
use App\Models\Mproduct;
use App\Models\MproductStock;
use App\Models\MWarehouse;
use Auth, DB;
use Yajra\DataTables\Facades\DataTables;

class StockOpnameController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = Mproduct::all();
        return view('pages.stock.stock_opname.stock_opname_index', compact('products'));
    }
    
    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $query = TStockOpname::with(['warehouse', 'product'])->orderBy('t_stock_opname.created_at', 'desc');

            if ($request->product_id) {
                $query->where('id_product', $request->product_id);
            }

            if ($request->fd && $request->td) {
                $query->whereBetween('tgl_opname', [$request->fd, $request->td]);
            }

            $query->orderBy('t_stock_opname.created_at', 'desc');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('warehouse_code', fn($row) => $row->warehouse->code_wh ?? '-')
                ->addColumn('warehouse_name', fn($row) => $row->warehouse->nama_wh ?? '-')
                ->addColumn('product_code', fn($row) => $row->product->sku ?? '-')
                ->addColumn('product_name', fn($row) => $row->product->nama_barang ?? '-')
                ->addColumn('qty_last', fn($row) => $row->qty_last)
                ->addColumn('tgl_opname', fn($row) => $row->tgl_opname)
                ->addColumn('action', function($row) {
                    $edit = route('stock_opname.edit', $row->id);
                    $history = route('stock_opname.history', $row->id);
                    return '
                        <a href="'.$history.'" class="btn btn-success btn-sm"><i class="fa fa-history"></i></a>
                        <a href="'.$edit.'" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i></a>
                    ';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return abort(403);
    }

    public function history($id){
        $stock_opname_his = HStockOpname::where('id_stock_opname', $id)->get(); 
        $products = Mproduct::get();
        return view('pages.stock.stock_opname.stock_opname_his',compact('stock_opname_his','products'))->with('i', (request()->input('page', 1) - 1) * 5);
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $warehouse  = MWarehouse::get();
        $product    = Mproduct::get();
        return view('pages.stock.stock_opname.stock_opname_create', compact('warehouse','product'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {        
        $this->validate($request, [
            'id_product'    => 'required',
            'id_warehouse'  => 'required',
            'qty_in'        => 'required',
            'qty_out'       => 'required',
            'qty_last'      => 'required',
            'tgl_opname'    => 'required'
        ],[
            'id_product.required'   => 'Please Fill Product',
            'id_warehouse.required' => 'Please Fill Warehouse',
            'qty_in.required'       => 'Please Fill QTY in',
            'qty_out.required'      => 'Please Fill QTY out',
            'qty_last.required'     => 'Please Fill Last QTY',
            'tgl_opname.required'   => 'Please Fill Opname Date'
        ]);
        
        $stock_opname = TStockOpname::create([
            'id_product'    => $request->id_product,
            'id_warehouse'  => $request->id_warehouse,
            'qty_in'        => $request->qty_in,
            'qty_out'       => $request->qty_out,
            'qty_last'      => $request->qty_last,
            'tgl_opname'    => $request->tgl_opname
        ]);

        $stock_opn      = TStockOpname::select('id')->latest()->first();
        $id_stock_opn   = $stock_opn->id;

        if($stock_opname){
            $user = Auth::user()->username;
            $date = date('Y-m-d');
            $stock_opname_his = HStockOpname::create([
                'id_stock_opname'   => $id_stock_opn,
                'id_product'        => $request->id_product,
                'id_warehouse'      => $request->id_warehouse,
                'qty_in'            => $request->qty_in,
                'qty_out'           => $request->qty_out,
                'qty_last'          => $request->qty_last,
                'tgl_opname'        => $request->tgl_opname,
                'created_by'        => $user,
                'created_at'        => $date
            ]);

            if($stock_opname_his){
                $product_stock = MproductStock::create([
                    'id_product'    => $request->id_product,
                    'id_warehouse'  => $request->id_warehouse,
                    'qty_last'      => $request->qty_last,
                    'tgl_opname'    => $request->tgl_opname,
                    'tgl_mutasi'    => '1970-01-01'
                ]);
                if($product_stock){
                    return redirect()
                    ->route('stock_opname.index')
                    ->with([
                        'success' => 'Stock Opname has succesfully been added'
                    ]);
                } else {         
                    return redirect()
                    ->back()
                    ->withInput()
                    ->with([
                        'error' => 'Some problem occurred, please try again'
                    ]);
                }
            } else {         
                return redirect()
                ->back()
                ->withInput()
                ->with([
                    'error' => 'Some problem occurred, please try again'
                ]);
            }
        } else {         
            return redirect()
            ->back()
            ->withInput()
            ->with([
                'error' => 'Some problem occurred, please try again'
            ]);
        }
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
        $stock_opname       = TStockOpname::findOrFail($id);
        $warehouse          = MWarehouse::get();
        $product            = Mproduct::get();
        return view('pages.stock.stock_opname.stock_opname_edit', compact('stock_opname','warehouse','product'));
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
        $validatedData = $request->validate([
            'qty_in'        => 'required',
            'qty_out'       => 'required',
            'qty_last'      => 'required',
            'tgl_opname'    => 'required'
        ],[
            'qty_in.required'       => 'Please Fill QTY in',
            'qty_out.required'      => 'Please Fill QTY out',
            'qty_last.required'     => 'Please Fill Last QTY',
            'tgl_opname.required'   => 'Please Fill Opname Date'
        ]);

        DB::beginTransaction();
        try{
            $stock_opname = TStockOpname::whereId($id)->update($validatedData);
            if($stock_opname){      
                $user = Auth::user()->username;
                $date = date('Y-m-d');
                $stock_opn      = TStockOpname::select('id')->whereId($id)->latest()->first();
                $id_stock_opn   = $stock_opn->id;

                $stock_opname_his = HStockOpname::create([
                    'id_stock_opname'  => $id_stock_opn,
                    'id_product'    => $request->id_product,
                    'id_warehouse'  => $request->id_warehouse,
                    'qty_in'        => $request->qty_in,
                    'qty_out'       => $request->qty_out,
                    'qty_last'      => $request->qty_last,
                    'tgl_opname'    => $request->tgl_opname,
                    'created_by'    => $user,
                    'created_at'    => $date
                ]);
                if($stock_opname_his){
                    $product_stockk = MproductStock::where('id_product', $request->id_product)
                    ->where('id_warehouse', $request->id_warehouse)
                    ->first();                    
                    
                    if($product_stockk){
                        $product_stock = $product_stockk->update([
                            'qty_last'      => $request->qty_last,
                            'tgl_opname'    => $request->tgl_opname,
                            'tgl_mutasi'    => '1970-01-01'
                        ]);
                        if($product_stock)
                        {
                            // echo "asup";
                            DB::commit();
                            return redirect()
                            ->route('stock_opname.index')
                            ->with([
                                'success' => 'Stock Opname has succesfully been update'
                            ]);                        
                        } else {                
                            return redirect()
                            ->back()
                            ->withInput()
                            ->with([
                                'error' => 'Some problem occurred, please try again'
                            ]);
                        }
                    } else {    
                        return redirect()
                        ->back()
                        ->withInput()
                        ->with([
                            'error' => 'Failed Find Product, please try again'
                        ]);
                    }
                } else {        
                    return redirect()
                    ->back()
                    ->withInput()
                    ->with([
                        'error' => 'Failed Create History, please try again'
                    ]);
                }
            } else {
                return redirect()
                ->back()
                ->withInput()
                ->with([
                    'error' => 'Failed Update Stock Opname, please try again'
                ]);
            }
        } catch (Exception $e) {
            // echo "gagal";
            DB::rollback();
            return redirect()
            ->back()
            ->withInput()
            ->with([
                'error' => 'Some problem occurred, please try again'
            ]);
        }
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