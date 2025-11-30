<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TInvoiceH;
use App\Models\TInvoiceD;
use PDF,DB;

class ReportInvoicingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        
            // $receipt_invoice = Tinv_rcp::with(['inv','customer','courier'])->latest('id')->paginate(5);

            $invoice = TInvoiceH::select(
                't_invoice_h.id',
                't_invoice_h.no_inv',
                't_invoice_h.tgl_inv',
                't_invoice_h.code_cust',
                'm_customers.nama_cust',
                't_invoice_h.grand_total'
            )
            ->join('m_customers', 't_invoice_h.id_cust', '=', 'm_customers.id')
            ->paginate(5);
            
    
            return view('pages.report.report_invoicing',compact('invoice'))->with('i', (request()->input('page', 1) - 1) * 5);
    }

    public function filter(Request $request){
        $stat       = $request->status;
        $status     = ($stat!='') ? " = '$stat'" : "LIKE '%'";
        $from_date  = $request->fd;
        $to_date    = $request->td;
        
        // $sql = "SELECT
        //             a.code_cust,
        //             b.nama_cust,
        //             a.tgl_inv,
        //             a.no_inv,
        //             a.no_seri_pajak,
        //             a.term,
        //             a.grand_total,
        //             a.shipping_via,
        //             b.status_po,
        //             c.status_lmpr_do,
        //             a.status_inv 
        //         FROM
        //             tinvs a
        //             INNER JOIN tpos b ON a.id_po = b.id
        //             INNER JOIN tdos c ON a.id_do = c.id
        //         WHERE
        //             a.tgl_inv BETWEEN '$from_date' AND '$to_date'
        //             AND (b.status_po $status OR c.status_lmpr_do $status OR a.status_inv $status)
        // ";
        // // echo nl2br($sql);exit;
        // $receipt_invoice    =   DB::select($sql);
        
        $invoice = TInvoiceH::select(
            't_invoice_h.id',
            't_invoice_h.no_inv',
            't_invoice_h.tgl_inv',
            't_invoice_h.code_cust',
            'm_customers.nama_cust',
            't_invoice_h.grand_total'
        )
        ->join('m_customers', 't_invoice_h.id_cust', '=', 'm_customers.id')
        ->whereBetween('t_invoice_h.tgl_inv',[$from_date,$to_date])
        ->paginate(5);
        
        if($request->opt == 'filter'){
            return view('pages.report.report_invoicing',compact('invoice'))->with('i', (request()->input('page', 1) - 1) * 5);
        } else if ($request->opt == 'export'){
            $pdf = PDF::loadview('pages.report.report_invoicing_pdf',['invoice'=>$invoice])->setPaper('A4', 'landscape');
            return $pdf->download('report_invoicing_'.date('Y-m-d').'.pdf');
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