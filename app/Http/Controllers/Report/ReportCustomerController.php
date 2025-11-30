<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MCustomer;
use PDF,DB;

class ReportCustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $customers = MCustomer::latest()->paginate(5);
        return view('pages.report.report_customer',compact('customers'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function filter(Request $request){
        $from_date  = $request->fd;
        $to_date    = $request->td;
        

        if($request->opt == 'filter'){
            $customers = MCustomer::whereBetween(DB::raw('DATE(created_at)'),[$from_date,$to_date])->paginate(5);
            return view('pages.report.report_customer',compact('customers'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
        } else if ($request->opt == 'export'){
            $customers = MCustomer::whereBetween(DB::raw('DATE(created_at)'),[$from_date,$to_date])->get();
            $pdf = PDF::loadview('pages.report.report_customer_pdf',['customers'=>$customers])->setPaper('A4', 'landscape');
            return $pdf->download('report_customer_'.date('Y-m-d').'.pdf');
        }
        
    }

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