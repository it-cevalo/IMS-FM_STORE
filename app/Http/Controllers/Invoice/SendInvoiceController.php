<?php

namespace App\Http\Controllers\Invoice;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tdo;
use App\Models\Tpo;
use App\Models\Tinv;
use App\Models\Tinv_rcp;
use App\Models\Tinv_send;
use App\Models\Hinv;
use App\Models\Hinv_rcp;
use App\Models\MCourier;
use App\Models\MCustomer;
use App\Logs;

class SendInvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function __construct()
    {
        // $this->middleware('auth');

        $this->logs = new Logs( 'Logs_SendInvoiceController' );
        // $this->isPrinciple = Libraries::isPrinciple();
    }
    
    public function index()
    {
        $send_invoice = Tinv_send::with(['inv','customer','courier','inv_rcp'])->latest('id')->paginate(5);

		// $this->logs->write("QUERY ", $reciept_invoice->toSql());

        // $this->logs->write(__FUNCTION__, "STOP\r\n");


        return view('pages.invoice.send_invoice.send_invoice_index',compact('send_invoice'))
            ->with('i', (request()->input('page', 1) - 1) * 5);

        // return view('pages.invoice.send_invoice.send_invoice_index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $inv_rcp = Tinv_rcp::get();
        $courier = MCourier::get();
        // $customer = MCustomer::get();
        return view('pages.invoice.send_invoice.send_invoice_create',compact('inv_rcp','courier'));
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
            'id_inv_rcp'                 => 'required',
            'no_resi'                    => 'required|unique:tinv_send,no_resi|regex:/(^([a-zA-Z0-9_\-\s]+)(\d+)?$)/u',
			'bukti_tanda_terima'         => 'required|mimes:jpg,jpeg,png'
		],[
            'id_inv_rcp.required'           => 'Please Select Receipt Invoice',
            'no_resi.required'              => 'Please Fill Resi Number',
            'no_resi.unique'                => 'This Number has been taken! Please Input with another Resi Number',
            'no_resi.regex'                 => 'Character input is not allowed! Please input without symbol ',
            'bukti_tanda_terima.required'   => 'Please Upload Image',
            'bukti_tanda_terima.mimes'      => 'File Must JPG,JPEG,PNG'
        ]);
 
        //start file
            // // menangkap file
            // $file = $request->file('bukti_tanda_terima');

            // // membuat nama file unik
            // $nama_file = $file->getClientOriginalName();
            
            // // upload ke folder file_do di dalam folder public
            // $path = $request->file('bukti_tanda_terima')->storeAs('file_send_invoice',$nama_file);

            // // $file->move('file_send_invoice',$nama_file);

            // $bukti_tanda_terima = "http://localhost:8000"."/storage/".$path;
        //end file

        if($request->bukti_tanda_terima!=''){
            $filename = 'img-bukti-'.$request->no_resi.'-'.time().'.'.$request->bukti_tanda_terima->extension();
            $request->bukti_tanda_terima->move(public_path('file_send_invoice'),$filename);
            $bukti_tanda_terima = '/file_send_invoice/'.$filename;
        } else {
            $bukti_tanda_terima = '';
        }

        // dd($bukti_tanda_terima);exit;
        $inv_rcp = Tinv_rcp::select(['no_inv','no_tti','code_courier','code_cust','tgl_inv_rcp'])->where('id',$request->id_inv_rcp)->first();
        $no_inv         = $inv_rcp->no_inv;
        $no_tti         = $inv_rcp->no_tti;
        $tgl_inv_rcp    = $inv_rcp->tgl_inv_rcp;
        // $tgl_inv        = $inv_rcp->tgl_inv;
        $code_courier   = $inv_rcp->code_courier;
        $code_cust      = $inv_rcp->code_cust;
        
        // dd($inv_rcp);
        $inv_send = Tinv_send::create([
            'id_inv_rcp'           => $request->id_inv_rcp,
            'no_inv'               => $no_inv,
            'no_tti'               => $no_tti,
            'code_cust'            => $code_cust,
            'code_courier'         => $code_courier,
            'no_resi'              => $request->no_resi,
            'bukti_tanda_terima'   => $bukti_tanda_terima,
            'tgl_inv_rcp'          => $tgl_inv_rcp
        ]);

        // dd($inv_send);

        if ($inv_send) {
            return redirect()
                ->route('send_invoice.index')
                ->with([
                    'success' => 'New Send Invoice has been created successfully'
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
        
        $send_invoice = Tinv_send::findOrFail($id);
        $inv_rcp = Tinv_rcp::get();
        $courier = MCourier::get();
        
        return view('pages.invoice.send_invoice.send_invoice_show',compact('send_invoice','inv_rcp','courier'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function edit($id)
    // {   
    //     $send_invoice = Tinv_send::findOrFail($id);
    //     $inv_rcp = Tinv_rcp::get();
    //     $courier = MCourier::get();
        
    //     return view('pages.invoice.send_invoice.send_invoice_edit',compact('send_invoice','inv_rcp','courier'));
    // }

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
