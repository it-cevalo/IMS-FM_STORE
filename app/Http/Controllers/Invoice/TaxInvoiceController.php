<?php

namespace App\Http\Controllers\Invoice;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tdo;
use App\Models\Tpo;
use App\Models\Tpo_Detail;
use App\Models\Tinv;
use App\Models\Hinv;
use Auth, PDF;

use App\Logs;

class TaxInvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    
	// protected $logs;
    
    public function __construct()
    {
        // $this->middleware('auth');

        $this->logs = new Logs( 'Logs_TaxInvoiceController' );
        // $this->isPrinciple = Libraries::isPrinciple();
    }

    public function index()
    {
        
    	// $this->logs->write(__FUNCTION__, "START");
        
        $tax_invoice = Tinv::with(['po','do'])->latest('id')->paginate(5);

        // dd($tax_invoice);

		// $this->logs->write("QUERY ", $tax_invoice->toSql());

        // $this->logs->write(__FUNCTION__, "STOP\r\n");


        return view('pages.invoice.tax_invoice.tax_invoice_index',compact('tax_invoice'))
            ->with('i', (request()->input('page', 1) - 1) * 5);

        // return view('pages.invoice.tax_invoice.tax_invoice_index');
    }

    
    public function search(Request $request)
    {
        // dd('Mantab');exit;
        $tax_invoice = Tinv::with(['po','do','customer'])
                                ->Where('code_cust', 'LIKE' , request()->search.'%')
                                ->orWhere('tgl_inv', 'LIKE' , request()->search.'%')
                                ->orWhere('no_inv', 'LIKE' , request()->search.'%')
                                ->orWhere('no_seri_pajak', 'LIKE' , request()->search.'%')
                                ->orWhere('reason_inv', 'LIKE' , request()->search.'%')
                                ->orWhere('status_faktur_pajak', 'LIKE' ,request()->search.'%')
                                ->orWhere('reason_faktur_pajak', 'LIKE' ,request()->search.'%')
                                ->orWhere('grand_total', 'LIKE' ,request()->search.'%')
                                ->orWhere('term', 'LIKE' ,request()->search.'%')
                                ->orWhere('shipping_via', 'LIKE' ,request()->search.'%')
                                ->orWhere('status_inv', 'LIKE' ,request()->search.'%')
                                ->orderBy('tgl_inv','desc')
                                ->paginate();
        $no  = ($tax_invoice->currentPage()*$tax_invoice->perPage())-$tax_invoice->perPage()+1;
        return view('pages.invoice.tax_invoice.tax_invoice_index',compact('tax_invoice','no'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }
    
    public function history($id){
        $tax_invoice_his = Hinv::where('id_inv',$id)->get();
        return view('pages.invoice.tax_invoice.tax_invoice_history',compact('tax_invoice_his'));
    }

    public function Export2PDF($id){
        // dd('Masuk');
        $tax_invoice = Tinv::with(['po','do','customer'])->where('id',$id)->get();
        $tax_inv = Tinv::select(['grand_total','id_po','signed'])->where('id',$id)->first();
        $id_po = $tax_inv->id_po;
        $gt = $tax_inv->grand_total;
        $sign = $tax_inv->signed;
        if($sign!=''){
            $signed = $sign;
        } else {
            $signed = '';
        }
        $po_dtl = Tpo_Detail::where('id_po',$id_po)->get();
        $grand_total = number_format($gt, 2, '.', ',');
        
        $pdf = PDF::loadview('pages.invoice.tax_invoice.tax_invoice_pdf',['tax_invoice'=>$tax_invoice, 'grand_total'=>$grand_total, 'po_dtl'=>$po_dtl, 'signed'=>$signed])->setPaper('F4', 'potrait');
    	return $pdf->download('tax_invoice_'.date('Y-m-d').'.pdf');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        
        $po = Tpo::get();
        $do = Tdo::get();
        
        return view('pages.invoice.tax_invoice.tax_invoice_create',compact('po','do'));
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
            'id_po'                      => 'required',
            'id_do'                      => 'required',
            'status_faktur_pajak'        => 'required',
            'status_inv'                 => 'required',       
            'tgl_inv'                    => 'required',
            'no_inv'                     => 'required|unique:tinvs,no_inv|regex:/(^([a-zA-Z0-9_\-\s]+)(\d+)?$)/u',
            'no_seri_pajak'              => 'required|unique:tinvs,no_seri_pajak',
            'reason_faktur_pajak'        => 'required|regex:/(^([a-zA-Z0-9_\-\s]+)(\d+)?$)/u',
            'reason_inv'                 => 'required|regex:/(^([a-zA-Z0-9_\-\s]+)(\d+)?$)/u',
            'term'                       => 'required|regex:/(^([a-zA-Z0-9_\-\s]+)(\d+)?$)/u',
            'shipping_via'               => 'required'
        ],[
            'id_po.required'                => 'Please Select PO',
            'id_do.required'                => 'Please Select DO',
            'status_faktur_pajak.required'  => 'Please Select Tax Invoice Status',
            'status_inv.required'           => 'Please Select Invoice Status',
            'tgl_inv.required'              => 'Please Select Date',
            'no_inv.required'               => 'Please Fill Invoice Number',
            'no_inv.unique'                 => 'This Number has been taken! Please Input with another Invoice Number',
            'no_inv.regex'                  => 'Character input is not allowed! Please input without symbol ',
            'no_seri_pajak.required'        => 'Please Fill Invoice Number',
            'no_seri_pajak.unique'          => 'This Number has been taken! Please Input with another Invoice Number',
            'no_seri_pajak.regex'           => 'Character input is not allowed! Please input without symbol ',
            'reason_inv.required'           => 'Please Input Invoice Reason',
            'reason_inv.regex'              => 'Character input is not allowed! Please input without symbol ',
            'reason_faktur_pajak.required'  => 'Please Input Reason',
            'reason_faktur_pajak.regex'     => 'Character input is not allowed! Please input without symbol ',
            'term.required'                 => 'Please Input Term',
            'term.regex'                    => 'Character input is not allowed! Please input without symbol ',
            'shipping_via.required'         => 'Please Select Shipping Via'
        ]);
        
        $do = Tdo::select(['code_cust'])->where('id',$request->id_do)->first();
        $code_cust  = $do->code_cust;

        $po = Tpo::select(['grand_total'])->where('id',$request->id_po)->first();
        $grand_total = $po->grand_total;

        
		$this->logs->write("QUERY DO ", $do);
        
        $inv = Tinv::create([
            'id_po'                => $request->id_po,
            'id_do'                => $request->id_do,
            'tgl_inv'              => $request->tgl_inv,
            'no_inv'               => $request->no_inv,
            'no_seri_pajak'        => $request->no_seri_pajak,
            'code_cust'            => $code_cust,
            'shipping_via'         => $request->shipping_via,
            'status_inv'           => $request->status_inv,
            'status_faktur_pajak'  => $request->status_faktur_pajak,
            'reason_inv'           => $request->reason_inv,
            'reason_faktur_pajak'  => $request->reason_faktur_pajak,
            'grand_total'          => $grand_total,
            'term'                 => $request->term
        ]);

        
		$this->logs->write("QUERY INPUT INV ", $inv);

        $invoice = Tinv::select('id')->first();
        $id_inv = $invoice->id;

		$this->logs->write("QUERY INV ", $invoice);

        $inv_his = Hinv::create([
            'id_inv'                => $id_inv,
            'tgl_inv'               => $request->tgl_inv,
            'code_cust'             => $code_cust,
            'no_inv'                => $request->no_inv,
            'no_seri_pajak'         => $request->no_seri_pajak,
            'reason_inv'            => $request->reason_inv,
            'status_inv'            => $request->status_inv,
            'reason_faktur_pajak'   => $request->reason_faktur_pajak,
            'status_faktur_pajak'   => $request->status_faktur_pajak,
            'shipping_via'          => $request->shipping_via,
            'grand_total'           => $grand_total,
            'term'                  => $request->term
        ]);

        
		$this->logs->write("QUERY INPUT INV HIS ", $inv_his);

        
        $this->logs->write(__FUNCTION__, "STOP\r\n");

        if ($inv && $inv_his) {
            return redirect()
                ->route('tax_invoice.index')
                ->with([
                    'success' => 'New Invoice has been created successfully'
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
        $this->logs->write(__FUNCTION__, "START");

        $tax_invoice = Tinv::findOrFail($id);
        $this->logs->write("QUERY INV ", $tax_invoice);
        
        $po = Tpo::get();
        $this->logs->write("QUERY PO ", $po);

        $do = Tdo::get();
		$this->logs->write("QUERY DO ", $do);

        $status_inv = [
            '....' => '....',
            'OK' => 'OK',
            'HOLD' => 'HOLD'
        ];
        $status_faktur_pajak = [
            '....' => '....',
            'OK' => 'OK',
            'HOLD' => 'HOLD'
        ];
        
        $shipping_via = [
            '....' => '....',
            'HANDCARRY' => 'HANDCARRY',
            'EKSPEDISI' => 'EKSPEDISI'
        ];
        return view('pages.invoice.tax_invoice.tax_invoice_show',compact('tax_invoice','po','do','status_inv', 'status_faktur_pajak', 'shipping_via'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $tax_invoice = Tinv::findOrFail($id);
        $po          = Tpo::get();
        $do          = Tdo::get();
        $status_inv = [
            '....' => '....',
            'OK' => 'OK',
            'HOLD' => 'HOLD'
        ];
        $status_faktur_pajak = [
            '....' => '....',
            'OK' => 'OK',
            'HOLD' => 'HOLD'
        ];
        $shipping_via = [
            '....' => '....',
            'HANDCARRY' => 'HANDCARRY',
            'EKSPEDISI' => 'EKSPEDISI'
        ];
        return view('pages.invoice.tax_invoice.tax_invoice_edit',compact('tax_invoice','po','do','status_inv', 'status_faktur_pajak', 'shipping_via'));
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
        if(Auth::user()->position=='DIRECTOR'){
            
            $validatedData = $request->validate([
                'signed'=>'required'
            ],[
                'signed.required'=>'Please Signed'
            ]);

            //Start E-Sign
                $folderPath = public_path('/file_signed/');
                
                $image_parts = explode(";base64,", $request->signed);
                    
                $image_type_aux = explode("image/", $image_parts[0]);
                
                $image_type = $image_type_aux[1];
                
                $image_base64 = base64_decode($image_parts[1]);
    
                $signature = uniqid() . '.'.$image_type;

                $ttd = "/file_signed/".$signature;
                
                $file = $folderPath . $signature;
    
                file_put_contents($file, $image_base64);
            //End E-Sign

                $po = Tpo::select(['grand_total'])->where('id',$request->id_po)->first();
                $grand_total = $po->grand_total;

                $validatedData['signed'] = $ttd;
                $validatedData ['grand_total'] = $grand_total;
 
                Tinv::whereId($id)->update($validatedData);
        } else {
            $validatedData = $request->validate([
                'status_faktur_pajak'   => 'required',
                'reason_faktur_pajak'   => 'required',
                'shipping_via'          => 'required',
                'status_inv'            => 'required',
                'reason_inv'            => 'required',
                'term'                  => 'required',
            ],[
                'status_faktur_pajak.required' => 'Please Fill Attachment Status',
                'reason_faktur_pajak.required' => 'Please Fill Reason Tax Invoice',
                'status_inv.required'          => 'Please Fill Status Invoice',
                'reason_inv.required'          => 'Please Fill Reason Invoice',
                'shipping_via.required'        => 'Please Select Shipping Via',
                'term.required'                => 'Please Fill Term',
            ]);

            $po = Tpo::select(['grand_total'])->where('id',$request->id_po)->first();
            $grand_total = $po->grand_total;

            $validatedData ['grand_total'] = $grand_total;
            Tinv::whereId($id)->update($validatedData);
        }

        // dd($validatedData);

        
        
        $invoice = Tinv::select('no_inv')->where('id',$id)->latest()->first();
        $no_inv  = $invoice->no_inv;
        // dd($id_po);exit();

        $inv_his = Hinv::create([
            'no_seri_pajak'         => $request->no_seri_pajak,
            'reason_inv'            => $request->reason_inv,
            'status_inv'            => $request->status_inv,
            'reason_faktur_pajak'   => $request->reason_faktur_pajak,
            'status_faktur_pajak'   => $request->status_faktur_pajak,
            'grand_total'           => $request->grand_total,
            'term'                  => $request->term
        ]);

        return redirect('/tax_invoice')->with('success', 'Tax Invoice is successfully updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */    
    
    public function delete3($id)
    {
        $data = Tinv::with(['po','do'])->find($id);
        $data->delete();
        return redirect()->route('tax_invoice.index');
    }

    public function bin3()
    {
        $data   ['data']      = Tinv::with(['po','do'])->onlyTrashed()->paginate(5);
        return view('pages.invoice.tax_invoice.tax_invoice_bin', $data)->with('i', (request()->input('page', 1) - 1) * 5);
    }

    public function rollback3($id)
    {
        $data = Tinv::onlyTrashed()->where('id',$id);
        $data->restore();
        return redirect()->route('tax_invoice.index');
    }
}