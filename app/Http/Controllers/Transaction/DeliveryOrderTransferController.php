<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TStockOpname;
use App\Models\HStockOpname;
use App\Models\TDoTransferH;
use App\Models\TDoTransferD;
use App\Models\Mproduct;
use App\Models\MproductStock;
use App\Models\MWarehouse;
use App\Models\StockMutation;
use Auth, DB;
use App\Logs;
use Yajra\DataTables\Facades\DataTables;

class DeliveryOrderTransferController extends Controller
{
    
    public function __construct()
    {
        // $this->middleware('auth');

        $this->logs = new Logs( 'Logs_DeliveryOrderTransferController' );
        // $this->isPrinciple = Libraries::isPrinciple();
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $warehouses = MWarehouse::get();
        return view('pages.transaction.delivery_order_transfer.delivery_order_transfer_index', compact('warehouses'));
    }
    
    public function getData(Request $request)
    {
        if (Auth::user()->position !== 'SUPERADMIN') {
            return response()->json([
                'error' => 'Anda tidak memiliki izin untuk mengakses data ini.'
            ], 403);
        }

        $query = TDoTransferH::select(
            'do_transfer_h.tgl_trf',
            'mproduct.kode_barang',
            'mproduct.nama_barang',
            'from_warehouse.code_wh',
            'from_warehouse.nama_wh',
            DB::raw('to_warehouse.code_wh AS to_code_wh'),
            DB::raw('to_warehouse.nama_wh AS to_nama_wh'),
            'do_transfer_d.qty_prd'
        )
        ->join('do_transfer_d', 'do_transfer_h.id', '=', 'do_transfer_d.id_product_trf_h')
        ->join('mproduct','do_transfer_d.id_product','=','mproduct.id')
        ->join('m_warehouses as from_warehouse', 'do_transfer_h.id_warehouse_from', '=', 'from_warehouse.id')
        ->join('m_warehouses as to_warehouse', 'do_transfer_h.id_warehouse_to', '=', 'to_warehouse.id');

        return DataTables::of($query)->make(true);
    }

    public function filter(Request $request)
    {
        $do_transfer = TDoTransferH::where('id_warehouse_from', 'LIKE', request()->search.'%')
        ->where('id_warehouse_to', 'LIKE', request()->search.'%')
        ->whereBetween('tgl_trf', [request()->fd, request()->td])
        ->paginate(10); // contoh paginasi dengan 10 item per halaman
        // dd($stock_opname);
        $warehouses = MWarehouse::get();
        $no = ($do_transfer->currentPage() * $do_transfer->perPage()) - $do_transfer->perPage() + 1;
        return view('pages.transaction.delivery_order_transfer.delivery_order_transfer_index',compact('do_transfer','warehouses'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $warehouses = MWarehouse::get();
        return view('pages.transaction.delivery_order_transfer.delivery_order_transfer_create',compact('warehouses'));
    }

    public function product(){
        $product = Mproduct::get();

        if($product){
            return response()->json($product);
        } else {
            return response()->json([
                'error'=>'Product not found'
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request->all());
        try{
            DB::beginTransaction();
            $this->validate($request, [
                'id_warehouse_from' => 'required',
                'id_warehouse_to'   => 'required',
                'code_trf'          => 'required|unique:do_transfer_h,code_trf',
                'tgl_trf'           => 'required',
                'desc_trf'          => 'required',
                'qty_trf'           => 'required',
                'kode_barang'               => 'required' 
            ],[
                'kode_barang.required'                  => 'Please Fill Kode Barang',
                'id_warehouse_from.required'    => 'Please Fill Warehouse From',
                'id_warehouse_to.required'      => 'Please Fill Warehouse To',
                'code_trf.required'             => 'Please Fill Number',
                'tgl_trf.required'              => 'Please Fill Date',
                'desc_trf.required'             => 'Please Fill Desc',
                'qty_trf.required'              => 'Please Fill Qty'
            ]);

            $do_transfer_h = TDoTransferH::create([
                'id_warehouse_from' => $request->id_warehouse_from,
                'id_warehouse_to'   => $request->id_warehouse_to,
                'code_trf'          => $request->code_trf,
                'tgl_trf'           => $request->tgl_trf,
                'desc_trf'          => $request->desc_trf,
                'total_qty_trf'     => $request->qty_trf
            ]);
            if($do_transfer_h){
                $doth = TDoTransferH::select('id')->latest()->first();
                $id_doth = $doth->id;

                $stm = '';
                $stm2 = '';
                foreach($request->qty as $key => $value){
                    $dot_d                      = new TDoTransferD;
                    $dot_d->id_product_trf_h    = $id_doth;
                    $product                    = Mproduct::select('id')->where('kode_barang',$request->kode_barang[$key])->first();
                    $id_product                 = $product->id;
                    $dot_d->id_product          = $id_product;
                    $dot_d->qty_prd             = $request->qty[$key];
                    $dot_d->desc_prd            = $request->desc_prd[$key];
                    $dotdtl                     = $dot_d->save();
                    
                    //From Process    
                        //Select Opname
                            $stock_opname = TStockOpname::select('qty_last','id')
                            ->where([
                                ['id_product', $product->id],
                                ['id_warehouse', $request->id_warehouse_from]
                            ])
                            ->latest()
                            ->first();
                            $qty_last_opn   = $stock_opname->qty_last;
                            $id_opn         = $stock_opname->id;
                        //Select Opname
                        
                        //Insert New
                            $stm                = new StockMutation;
                            $stm->id_product    = $product->id;
                            $stm->id_warehouse  = $request->id_warehouse_from;
                            $stm->qty_start     = $qty_last_opn;
                            $stm->qty_in        = 0;
                            $stm->qty_out       = $request->qty[$key];
                            $stm->qty_last      = $qty_last_opn - $request->qty[$key];
                            $stm->tgl_mutasi    = date('Y-m-d');
                            $stock_mut          = $stm->save();
                        //Insert New

                        //Update Opname
                            if($stock_mut) {
                                $sopn = TStockOpname::where([
                                    ['id_product', $product->id],
                                    ['id_warehouse', $request->id_warehouse_from]
                                ])->first();
                                
                                if ($sopn) {
                                    $stock_opm = $sopn->update([
                                        'qty_out'   => $request->qty[$key],
                                        'qty_last'  => $qty_last_opn - $request->qty[$key],
                                    ]);

                                    
                                    if($stock_opm){
                                        $user = Auth::user()->username;
                                        $date = date('Y-m-d'); // Menggunakan fungsi now() untuk mendapatkan waktu sekarang
                                    
                                        // Menggunakan create() untuk membuat entri baru pada HStockOpname
                                        $stock_opname_his = HStockOpname::create([
                                            'id_stock_opname'   => $id_opn,
                                            'id_product'        => $product->id,
                                            'id_warehouse'      => $request->id_warehouse_from,
                                            'qty_in'            => 0,
                                            'qty_out'           => $request->qty[$key],
                                            'qty_last'          => $qty_last_opn - $request->qty[$key],
                                            'tgl_opname'        => $date,
                                            'created_by'        => $user,
                                            'created_at'        => $date,
                                        ]);
                                        $this->logs->write("QUERY INPUT FROM HIS ", $stock_opname_his);
                                        if($stock_opname_his){
                                            $this->logs->write("SUCCESS INPUT FROM HIS",$stock_opname_his);
                                        }

                                    } 
                                } 
                            }
                        //Update Opname
                    //From Process
                    
                    //To Process    
                        //Select Opname
                            $stock_opname = TStockOpname::select('qty_last','id')
                            ->where([
                                ['id_product', $product->id],
                                ['id_warehouse', $request->id_warehouse_to]
                            ])
                            ->latest()
                            ->first();
                            $qty_last_opn   = $stock_opname->qty_last;
                            $id_opn         = $stock_opname->id;
                        //Select Opname
                        
                        //Insert New
                            $stm2                = new StockMutation;
                            $stm2->id_product    = $product->id;
                            $stm2->id_warehouse  = $request->id_warehouse_to;
                            $stm2->qty_start     = $qty_last_opn;
                            $stm2->qty_in        = $request->qty[$key];
                            $stm2->qty_out       = 0;
                            $stm2->qty_last      = $qty_last_opn + $request->qty[$key];
                            $stm2->tgl_mutasi    = date('Y-m-d');
                            $stock_mut2          = $stm2->save();
                        //Insert New

                        //Update Opname
                            if($stock_mut2){
                                $sopnt = TStockOpname::where([
                                    ['id_product', $product->id],
                                    ['id_warehouse', $request->id_warehouse_to]
                                ])->first();
                                
                                if ($sopnt) {
                                    $stock_opmt = $sopnt->update([
                                        'qty_out'   => 0,
                                        'qty_last'  => $qty_last_opn + $request->qty[$key],
                                    ]);
                                    
                                    if($stock_opmt){
                                        $user = Auth::user()->username;
                                        $date = date('Y-m-d'); // Menggunakan fungsi now() untuk mendapatkan waktu sekarang
                                    
                                        // Menggunakan create() untuk membuat entri baru pada HStockOpname
                                        $stock_opname_hist = HStockOpname::create([
                                            'id_stock_opname'   => $id_opn,
                                            'id_product'        => $product->id,
                                            'id_warehouse'      => $request->id_warehouse_to,
                                            'qty_in'            => $request->qty[$key],
                                            'qty_out'           => 0,
                                            'qty_last'          => $qty_last_opn + $request->qty[$key],
                                            'tgl_opname'        => $date,
                                            'created_by'        => $user,
                                            'created_at'        => $date,
                                        ]);
                                        $this->logs->write("QUERY INPUT TO HIS ", $stock_opname_hist);
                                        
                                        if($stock_opname_hist){
                                            $this->logs->write("SUCCESS INPUT TO HIS",$stock_opname_hist);
                                        }
                                    } 
                                } 
                            }
                        //Update Opname
                    //To Process
                }
                
                // If everything is successful, commit the transaction
                DB::commit();

                return redirect()
                    ->route('product_transfer.index')
                    ->with([
                        'success' => 'Product Transfer has successfully been added'
                ]);
            }
        } catch (\Exception $e) {
            DB::rollback();
        
            // $errorMessage = 'An error occurred';
            $errorMessage = 'An error occurred: ' . $e->getMessage();
            $errorDetails = 'File: ' . $e->getFile() . ', Line: ' . $e->getLine();
        
            return redirect()
                ->back()
                ->withInput()
                ->with([
                    'error' => $errorMessage
                    // 'error_details' => $errorDetails
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