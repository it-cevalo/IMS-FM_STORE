<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MCustomer;
use App\Models\Tinv;
use App\Models\Tpo;
use App\Models\Tpayment;
use App\Models\Hpayment;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $payment = Tpayment::with(['po','tax_inv','customer'])->latest('id')->paginate(5);

        return view('pages.transaction.payment.payment_index',compact('payment'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
      
    public function history($id){
        $payment_his = Hpayment::where('id_payment',$id)->get();
        return view('pages.transaction.payment.payment_history',compact('payment_his'));
    }


    public function create()
    {
        $tax_inv = Tinv::get();
        $po = Tpo::get();
        return view('pages.transaction.payment.payment_create',compact('tax_inv','po'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd("OK");

        $this->validate($request, [
            'id_tax_inv'                 => 'required',
            'id_po'                      => 'required',
            'payment_via'                => 'required',
            'payment_date'               => 'required',
            'bank_account'               => '',       
            'invoice_paid'               => 'required|regex:/(^([a-zA-Z0-9_\-\s]+)(\d+)?$)/u',
            'amount_paid'                => 'required|regex:/(^([a-zA-Z0-9_\-\s]+)(\d+)?$)/u'
        ],[
            'id_tax_inv.required'        => 'Please Select Tax Invoice',
            'id_po.required'             => 'Please Select Purchase Order',
            'invoice_paid.required'      => 'Please Fill Invoice Number',
            'invoice_paid.regex'         => 'Character input is not allowed! Please input without symbol ',
            'amount_paid.required'       => 'Please Fill Invoice Number',
            'amount_paid.regex'          => 'Character input is not allowed! Please input without symbol ',
            'payment_via.required'       => 'Please Select Payment Via',
            'payment_date.required'      => 'Please Select Payment Date'
        ]);

        // dd($validate);exit;

        // dd($request->all());
        
        $inv = Tinv::select(['code_cust','no_inv','tgl_inv'])->where('id',$request->id_tax_inv)->first();
        $code_cust  = $inv->code_cust;
        $no_inv     = $inv->no_inv;
        $tgl_inv    = $inv->tgl_inv;
        $tgl_inv    = date('Y-m-d', strtotime($tgl_inv));


        $cust    = MCustomer::select(['id'])->where('code_cust',$code_cust)->first();
        // $id_cust = $cust->id;
        // dd($id_cust);

        $po      = Tpo::select(['no_po'])->where('id',$request->id_po)->first();
        // $no_po   = $po->no_po;

        
		// $this->logs->write("QUERY DO ", $do);
        
        $payment = Tpayment::create([
            'id_tax_inv'           => $request->id_tax_inv,
            'id_cust'              => $cust->id,
            'id_po'                => $request->id_po,
            'invoice_date'         => $tgl_inv,
            'payment_date'         => $request->payment_date,
            'invoice_paid'         => $request->invoice_paid,
            'amount_paid'          => $request->amount_paid,
            'no_tax_inv'           => $no_inv,
            'no_po'                => $po->no_po,
            'code_cust'            => $code_cust,
            'payment_via'          => $request->payment_via,
            'bank_account'         => $request->bank_account
        ]);

        // dd($payment);exit;


        $pay = Tpayment::select('id')->latest()->first();
        $id_payment = $pay->id;

        // dd($invoice);

		// $this->logs->write("QUERY INV ", $invoice);

        $pay_his = Hpayment::create([
            'id_payment'            => $id_payment,
            'invoice_paid'          => $request->invoice_paid,
            'amount_paid'           => $request->amount_paid,
            'invoice_date'          => $tgl_inv,
            'payment_date'          => $request->payment_date
        ]);

        
		// $this->logs->write("QUERY INPUT INV HIS ", $inv_his);

        
        // $this->logs->write(__FUNCTION__, "STOP\r\n");

        if ($pay && $pay_his) {
            return redirect()
                ->route('payment.index')
                ->with([
                    'success' => 'New Payment has been created successfully'
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
        $payment = Tpayment::findOrFail($id);
        $po      = Tpo::get();
        $tax_inv     = Tinv::get();
        $payment_via = [
            '....' => '....',
            'TF' => 'TRANSFER',
            'CASH' => 'CASH'
        ];
        $bank_account = [
            '....'    => '....',
            'BCA'     => 'BCA',
            'PANIN'   => 'PANIN',
            'MANDIRI' => 'Mandiri'
        ];
        return view('pages.transaction.payment.payment_edit',compact('payment','po','tax_inv','payment_via', 'bank_account'));
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
            'amount_paid'   => 'required',
            'payment_date'  => 'required'
        ],[
            'amount_paid.required' => 'Please Fill Amount Paid',
            'payment_date.required' => 'Please Select Payment Date',
        ]);

        $pay                            = Tpayment::select('amount_paid')->where('id',$id)->latest()->first();
        $req_amt_paid                   = $request->amount_paid;
        $amt_paid                       = $pay->amount_paid;
        $validatedData['amount_paid']   = $req_amt_paid + $amt_paid;

        Tpayment::whereId($id)->update($validatedData);
        
        $payment = Tpayment::select('*')->where('id',$id)->latest()->first();

        $payment_his = Hpayment::create([
            'amount_paid'           => $request->amount_paid,
            'payment_date'          => $request->payment_date,
            'id_payment'            => $payment->id,
            'invoice_paid'          => $payment->invoice_paid,
            'invoice_date'          => $payment->invoice_date
        ]);

        if($payment && $payment_his){
            return redirect('/payment')->with('success', 'Payment is successfully updated');
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