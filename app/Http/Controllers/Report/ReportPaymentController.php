<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tinv;
use App\Models\Tpayment;
use App\Models\Hpayment;
use App\Models\MCustomer;
use PDF, DB;

class ReportPaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $this->logs->write(__FUNCTION__, "START");

        $sql = "SELECT
                    a.id, 
                    c.code_cust,
                    c.nama_cust,
                    b.tgl_inv,
                    b.no_inv,
                    a.payment_via,
                    a.bank_account,
                    b.term,
                    a.invoice_paid,
                    a.amount_paid
                FROM
                    tpayment a
                    INNER JOIN tinvs b ON a.id_tax_inv = b.id
                    INNER JOIN m_customers c ON a.id_cust = c.id
        ";
        
        $payment = DB::select($sql);
        // $payment = Tpayment::with(['do','po','tax_inv'])->latest('id')->paginate(5);

        $customer = MCustomer::get();

        return view('pages.report.report_payment',compact('payment','customer'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }

    
    public function filter(Request $request){
        $stat       = $request->status;
        $status     = ($stat!='') ? " = '$stat'" : "LIKE '%'";
        $from_date  = $request->fd;
        $to_date    = $request->td;
        $cust       = $request->id_cust;
        $id_cust    = ($cust!='') ? " = '$cust'" : "LIKE '%'";
        
        $sql = "SELECT 
                    a.id,
                    c.code_cust,
                    c.nama_cust,
                    b.tgl_inv,
                    b.no_inv,
                    a.payment_via,
                    a.bank_account,
                    b.term,
                    a.invoice_paid,
                    a.amount_paid
                FROM
                    tpayment a
                    INNER JOIN tinvs b ON a.id_tax_inv = b.id
                    INNER JOIN m_customers c ON a.id_cust = c.id 
                WHERE
                    b.tgl_inv BETWEEN '$from_date' AND '$to_date'
                    AND (CASE WHEN a.invoice_paid > a.amount_paid THEN 'Belum' WHEN a.invoice_paid = a.amount_paid THEN 'Lunas' ELSE '' END $status)
                    AND c.id $id_cust
        ";
        
        $payment    =   DB::select($sql);
        if($request->opt == 'filter'){
            $customer = MCustomer::get();
            return view('pages.report.report_payment',compact('payment','customer'))->with('i', (request()->input('page', 1) - 1) * 5);
        } else if ($request->opt == 'export'){
            $pdf = PDF::loadview('pages.report.report_payment_pdf',['payment'=>$payment])->setPaper('A4', 'landscape');
            return $pdf->download('report_payment_'.date('Y-m-d').'.pdf');
        }
    }

    // public function Export2PDF(Request $request){
        
    //     $stat       = $request->status;
    //     $status     = ($stat!='') ? " = '$stat'" : "LIKE '%'";
    //     $from_date  = $request->fd;
    //     $to_date    = $request->td;
    //     $cust       = $request->id_cust;
    //     $id_cust    = ($cust!='') ? " = '$cust'" : "LIKE '%'";
        
    //     $sql = "SELECT 
    //                 a.id,
    //                 c.code_cust,
    //                 c.nama_cust,
    //                 b.tgl_inv,
    //                 b.no_inv,
    //                 a.payment_via,
    //                 a.bank_account,
    //                 b.term,
    //                 a.invoice_paid,
    //                 a.amount_paid
    //             FROM
    //                 tpayment a
    //                 INNER JOIN tinvs b ON a.id_tax_inv = b.id
    //                 INNER JOIN m_customers c ON a.id_cust = c.id 
    //             WHERE
    //                 b.tgl_inv BETWEEN '$from_date' AND '$to_date'
    //                 AND (CASE WHEN a.invoice_paid > a.amount_paid THEN 'Belum' WHEN a.invoice_paid = a.amount_paid THEN 'Lunas' ELSE '' END $status)
    //                 AND c.id $id_cust
    //     ";

    //     $payment =  DB::select($sql);
    //     $pdf = PDF::loadview('pages.report.report_payment_pdf',['payment'=>$payment])->setPaper('A4', 'landscape');
    // 	return $pdf->download('report_payment_'.date('Y-m-d').'.pdf');
    // }

    
    public function Export2PDFHis(Request $request, $id){
        $sql = "SELECT 
                            a.id,
                            a.payment_date,
                            a.payment_via,
                            a.invoice_paid,
                            a.amount_paid
                        FROM
                            tpayment a
                        WHERE
                            a.id = '$id'
        ";
        // dd($sql);
        $payment_his = DB::select($sql);
        $pdf = PDF::loadview('pages.report.report_payment_hispdf',['payment_his'=>$payment_his])->setPaper('A4', 'landscape');
    	return $pdf->download('report_payment_his_'.date('Y-m-d').'.pdf');
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
