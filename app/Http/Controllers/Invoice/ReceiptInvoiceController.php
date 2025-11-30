<?php

namespace App\Http\Controllers\Invoice;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tdo;
use App\Models\Tpo;
use App\Models\Tinv;
use App\Models\Tinv_rcp;
use App\Models\Hinv;
use App\Models\Hinv_rcp;
use App\Models\MCourier;
use App\Models\MCustomer;
use App\Logs;
use PDF;

class ReceiptInvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    
    public function __construct()
    {
        // $this->middleware('auth');

        $this->logs = new Logs( 'Logs_ReceiptInvoiceController' );
        // $this->isPrinciple = Libraries::isPrinciple();
    }

    public function index()
    {
        
    	// $this->logs->write(__FUNCTION__, "START");
        
        $receipt_invoice = Tinv_rcp::with(['inv','customer','courier'])->latest('id')->paginate(5);

		// $this->logs->write("QUERY ", $reciept_invoice->toSql());

        // $this->logs->write(__FUNCTION__, "STOP\r\n");


        return view('pages.invoice.receipt_invoice.receipt_invoice_index',compact('receipt_invoice'))
            ->with('i', (request()->input('page', 1) - 1) * 5);

        // return view('pages.invoice.receipt_invoice.receipt_invoice_index');
    }

    public function history($id){
        $receipt_invoice_his = Hinv_rcp::where('id_inv_rcp',$id)->get();
        return view('pages.invoice.receipt_invoice.receipt_invoice_history',compact('receipt_invoice_his'));
    }

        
    public function Export2PDF($id){
        // dd('Masuk');
        $receipt_invoice = Tinv_rcp::with(['inv','customer','courier'])->where('id',$id)->get();
        $rcp_inv = Tinv_rcp::select(['grand_total'])->where('id',$id)->first();
        $gt = $rcp_inv->grand_total;
        $grand_total = number_format($gt, 2, '.', ',');
        
        $pdf = PDF::loadview('pages.invoice.receipt_invoice.receipt_invoice_pdf',['receipt_invoice'=>$receipt_invoice, 'grand_total'=>$grand_total])->setPaper('A4', 'landscape');
    	return $pdf->download('receipt_invoice_'.date('Y-m-d').'.pdf');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $inv = Tinv::get();
        $courier = MCourier::get();
        // $customer = MCustomer::get();
        return view('pages.invoice.receipt_invoice.receipt_invoice_create',compact('inv','courier'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->logs->write(__FUNCTION__, "START");

        $this->validate($request, [
            'id_inv'                     => 'required',
            'no_tti'                     => 'required|unique:tinv_rcp,no_tti',
            'code_courier'               => 'required'
        ],[
            'id_inv.required'               => 'Please Select Tax Invoice',
            'no_tti.required'               => 'Please Fill Receipt Invoice Number',
            'no_tti.unique'                 => 'This Number has been taken! Please Input with another Receipt Invoice Number',
            'code_courier.required'         => 'Please Select Courier'
        ]);
        
        $inv = Tinv::select(['no_inv','code_cust','tgl_inv','no_seri_pajak','grand_total','term','shipping_via'])->where('id',$request->id_inv)->first();
        $no_inv         = $inv->no_inv;
        $code_cust      = $inv->code_cust;
        $tgl_inv        = $inv->tgl_inv;
        $no_seri_pajak  = $inv->no_seri_pajak;
        $grand_total    = $inv->grand_total;
        $term           = $inv->term;
        $shipping_via   = $inv->shipping_via;

        // $courier = MCourier::select(['code_courier'])->where('id',$request->code_courier)->first();
        // $code_courier = $courier->code_courier;

		$this->logs->write("QUERY INV ", $inv);
        
        $inv_rcp = Tinv_rcp::create([
            'id_inv'               => $request->id_inv,
            'tgl_inv'              => $tgl_inv,
            'tgl_inv_rcp'          => date("Y-m-d"),
            'no_inv'               => $no_inv,
            'no_tti'               => $request->no_tti,
            'no_seri_pajak'        => $no_seri_pajak,
            'code_cust'            => $code_cust,
            'code_courier'         => $request->code_courier,
            'shipping_via'         => $shipping_via,
            'grand_total'          => $grand_total,
            'term'                 => $term
        ]);

        
		$this->logs->write("QUERY INPUT INV ", $inv);

        $invoice = Tinv_rcp::select('id')->first();
        $id_inv_rcp = $invoice->id;

        // dd($invoice);

		$this->logs->write("QUERY INV RCP ", $invoice);

        $inv_rcp_his = Hinv_rcp::create([
            'id_inv'                => $request->id_inv,
            'id_inv_rcp'            => $id_inv_rcp,
            'no_tti'                => $request->no_tti,
            'no_inv'                => $no_inv,
            'tgl_inv'               => $tgl_inv,
            'no_seri_pajak'         => $no_seri_pajak,
            'shipping_via'          => $shipping_via,
            'grand_total'           => $grand_total,
            'code_courier'          => $request->code_courier,
            'term'                  => $term
        ]);

        
		$this->logs->write("QUERY INPUT INV RCP HIS ", $inv_rcp_his);

        
        $this->logs->write(__FUNCTION__, "STOP\r\n");

        if ($inv_rcp && $inv_rcp_his) {
            return redirect()
                ->route('receipt_invoice.index')
                ->with([
                    'success' => 'New Receipt Invoice has been created successfully'
                ]);
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
        $receipt_invoice = Tinv_rcp::findOrFail($id);
        $inv = Tinv::get();
        $courier = MCourier::get();
        return view('pages.invoice.receipt_invoice.receipt_invoice_show',compact('receipt_invoice', 'inv','courier'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $receipt_invoice = Tinv_rcp::findOrFail($id);
        $inv = Tinv::get();
        $courier = MCourier::get();
        return view('pages.invoice.receipt_invoice.receipt_invoice_edit',compact('receipt_invoice', 'inv','courier'));
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
            'code_courier'   => 'required',
        ],[
            'code_courier.required' => 'Please Select Courier',
        ]);

        Tinv_rcp::whereId($id)->update($validatedData);
        
        $invoice = Tinv_rcp::select('id','id_inv','no_tti','no_seri_pajak','grand_total','term','shipping_via')->where('id',$id)->latest()->first();
        $id_inv         = $invoice->id_inv;
        $id_inv_rcp     = $invoice->id;
        $no_tti         = $invoice->no_tti;
        $no_seri_pajak  = $invoice->no_seri_pajak;
        $grand_total    = $invoice->grand_total;
        $term           = $invoice->term;
        $shipping_via   = $invoice->shipping_via;

        $inv_his = Hinv_rcp::create([
            'id_inv'                => $id_inv,
            'id_inv_rcp'            => $id_inv_rcp,
            'no_tti'                => $no_tti,
            'no_seri_pajak'         => $no_seri_pajak,
            'grand_total'           => $grand_total,
            'term'                  => $term,
            'code_courier'          => $request->code_courier,
            'shipping_via'          => $shipping_via 
        ]);

        return redirect('/receipt_invoice')->with('success', 'Receipt Invoice is successfully updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    
        
    public function delete4($id)
    {
        $data = Tinv_rcp::with(['inv'])->find($id);
        $data->delete();
        return redirect()->route('receipt_invoice.index');
    }

    public function bin4()
    {
        $data   ['data']      = Tinv_rcp::with(['inv'])->onlyTrashed()->paginate(5);
        return view('pages.invoice.receipt_invoice.receipt_invoice_bin', $data)->with('i', (request()->input('page', 1) - 1) * 5);
    }

    public function rollback4($id)
    {
        $data = Tinv_rcp::onlyTrashed()->where('id',$id);
        $data->restore();
        return redirect()->route('receipt_invoice.index');
    }

    public function destroy($id)
    {
        //
    }
}
