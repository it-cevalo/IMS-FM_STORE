<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\TStockOpname;
use App\Models\HStockOpname;
use App\Models\TPurchaseRequest;
use App\Models\TPurchaseRequestD;
use App\Models\Mproduct;
use App\Models\MWarehouse;
use App\Models\StockMutation;
use Auth, DB;
use App\Logs;
use Yajra\DataTables\Facades\DataTables;

class PurchaseRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    
    public function __construct()
    {
        // $this->middleware('auth');
 
        $this->logs = new Logs( 'Logs_PurchaseRequestController' );
        // $this->isPrinciple = Libraries::isPrinciple();
    }
     
    public function index()
    {
        $warehouses = MWarehouse::get();
        return view('pages.transaction.purchase_request.purchase_request_index',compact('warehouses'));
    }
    
    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $data = DB::table('purchase_request as a')
                ->select(
                    'a.id', 'a.code_pr', 'b.code_wh', 'b.nama_wh', 'a.total_qty_req',
                    'a.desc_req', 'req.name as request_name', 'a.request_date',
                    'app.name as approve_name', 'a.approve_date'
                )
                ->join('m_warehouses as b', 'a.id_warehouse', '=', 'b.id')
                ->leftJoin('users as req', 'a.id_user_request', '=', 'req.id')
                ->leftJoin('users as app', 'a.approve_by', '=', 'app.id')
                ->orderByDesc('a.id');

            return DataTables::of($data)
                ->addColumn('action', function ($row) {
                    $btn = '<a href="'.route('purchase_request.show', $row->id).'" class="btn btn-sm btn-primary"><i class="fa fa-eye"></i></a> ';
                    if (auth()->user()->position == 'MANAGER' || auth()->user()->position == 'SUPERADMIN') {
                        $btn .= '<button class="btn btn-sm btn-success btn-approve" data-id="'.$row->id.'"><i class="fa fa-check"></i></button> ';
                        $btn .= '<a href="'.route('purchase_request.edit', $row->id).'" class="btn btn-sm btn-warning"><i class="fa fa-edit"></i></a>';
                    } elseif (auth()->user()->position == 'OPERATIONAL') {
                        $btn .= '<a href="'.route('purchase_request.edit', $row->id).'" class="btn btn-sm btn-warning"><i class="fa fa-edit"></i></a>';
                    }
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $warehouses = MWarehouse::get();
        return view('pages.transaction.purchase_request.purchase_request_create',compact('warehouses'));
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
      
        $validator = Validator::make($request->all(), [
            'code_pr'               => 'required',
            'request_date'          => 'required',
            'id_warehouse_from'     => 'required',
            'desc_req'              => 'required',
            'qty_trf'               => 'required',
        ], [
            'code_pr.required'              => 'Please provide a code.',
            'request_date.required'         => 'Please provide a request_date.',
            'id_warehouse_from.required'    => 'Please provide a warehouse ID.',
            'desc_req.required'             => 'Please provide a description.',
            'qty_trf.required'              => 'Please provide total quantity for all items.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                        ->withErrors($validator)
                        ->withInput();
        }
        
        $id_user_request    = Auth::user()->id;
        $purchase_request   = TPurchaseRequest::create([
            'code_pr'           => $request->code_pr,
            'id_warehouse'      => $request->id_warehouse_from,
            'total_qty_req'     => $request->qty_trf,
            'desc_req'          => $request->desc_req,
            'id_user_request'   => $id_user_request,
            'request_date'      => $request->request_date,
            'flag_approve'      => 'N',
            'approve_date'      => '1970-01-01',
            'approve_by'        => ''
        ]);

        $pr = TPurchaseRequest::select('id')->latest()->first();
        $id_pr = $pr->id;

        foreach($request->qty as $key => $value){
            $pr_detail                  = new TPurchaseRequestD;
            $pr_detail->pr_id           = $id_pr;
            $product                    = Mproduct::select('id')->where('SKU',$request->SKU[$key])->first();
            $id_product                 = $product->id;
            $pr_detail->code_pr         = $request->code_pr;
            $pr_detail->id_product      = $id_product;
            $pr_detail->SKU             = $request->SKU[$key];
            $pr_detail->qty_prd         = $request->qty[$key];
            $pr_detail->desc_prd        = $request->desc_prd[$key];
            $pr_detail->request_date    = $request->request_date;
            $purchase_request_dtl       = $pr_detail->save();
        }

        if($purchase_request && $purchase_request_dtl){
            return redirect()
                ->route('purchase_request.index')
                ->with([
                    'success'=>'Request saved successfully.'
            ]);
        } else {
            return redirect()
                    ->back()
                    ->withInput()
                    ->with([
                    'error'=>'Request failed save'
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
        $purchase_request = TPurchaseRequest::findOrFail($id);
        $purchase_request_dtl = TPurchaseRequestD::where('pr_id',$id)->get();
        $warehouses = MWarehouse::get();
        return view('pages.transaction.purchase_request.purchase_request_show',compact('purchase_request','warehouses', 'purchase_request_dtl'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $purchase_request = TPurchaseRequest::findOrFail($id);
        $purchase_request_dtl = TPurchaseRequestD::where('pr_id',$id)->get();
        $warehouses = MWarehouse::get();
        return view('pages.transaction.purchase_request.purchase_request_edit',compact('purchase_request','warehouses', 'purchase_request_dtl'));
    }

    public function approve(Request $request, $id)
    {
        $approve_by = Auth::user()->id;
        // dd($approve_by);
        
        $approve = TPurchaseRequest::find($id);
        
        if (!$approve) {
            return response()->json(['error' => 'delivery order not found']);
        }

        try {
            $approve->approve_by = $approve_by;
            $approve->approve_date = date('Y-m-d');
            $approve->flag_approve = "Y";
            
            $updated = $approve->save();
            
            if ($updated) {
                return response()->json(['success' => true]);
            } else {
                return response()->json(['error' => 'approval failed']);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'approval failed: ' . $e->getMessage()]);
        }
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
            'code_pr'               => 'required',
            'request_date'          => 'required',
            'desc_req'              => 'required',
            'qty_trf'               => 'required',
        ], [
            'code_pr.required'              => 'Please provide a code.',
            'request_date.required'         => 'Please provide a request_date.',
            'desc_req.required'             => 'Please provide a description.',
            'qty_trf.required'              => 'Please provide total quantity for all items.',
        ]);

        $id_user_request    = Auth::user()->id;
        $validatedData['id_user_request']   = $id_user_request;
        $validatedData['id_warehouse']      = $request->id_warehouse_form;

        DB::beginTransaction();
        try{
            TPurchaseRequest::whereId($id)->update($validatedData);

            $pr = TPurchaseRequest::select('id')->where('code_pr',$request->code_pr)->latest()->first();
            $id_pr = $pr->id;

            $dataUpdatePODetail = TPurchaseRequestD::where('pr_id',$id_pr)->delete();
            
            $part_number = $request->part_number;

            foreach($request->total_price as $key => $value){
                $pr_detail                  = new TPurchaseRequestD;
                $pr_detail->pr_id           = $id_pr;
                $product                    = Mproduct::select('id')->where('SKU',$request->SKU[$key])->first();
                $id_product                 = $product->id;
                $pr_detail->code_pr         = $request->code_pr;
                $pr_detail->id_product      = $id_product;
                $pr_detail->SKU             = $request->SKU[$key];
                $pr_detail->qty_prd         = $request->qty_prd[$key];
                $pr_detail->desc_prd        = $request->desc_prd[$key];
                $pr_detail->request_date    = $request->request_date;
                $purchase_request_dtl       = $pr_detail->save();
            }
            
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
        }

        return redirect('/purchase_request')->with('success', 'Purchase Request is successfully updated');
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