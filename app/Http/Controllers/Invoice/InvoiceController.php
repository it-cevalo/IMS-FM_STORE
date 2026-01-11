<?php

namespace App\Http\Controllers\Invoice;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MCustomer;
use App\Models\MBank;
use App\Models\TInvoiceH;
use App\Models\TInvoiceD;
use App\Models\TStockOpname;
use App\Models\HStockOpname;
use App\Models\TDoTransferH;
use App\Models\TDoTransferD;
use App\Models\Mproduct;
use App\Models\MWarehouse;
use App\Models\StockMutation;
use Auth, PDF, DB;
use App\Logs;
use Yajra\DataTables\DataTables;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    
    public function __construct()
    {
        // $this->middleware('auth');

        $this->logs = new Logs( 'Logs_InvoiceController' );
        // $this->isPrinciple = Libraries::isPrinciple();
    }
     
    public function index()
    {
        return view('pages.invoice.invoice.invoice_index');
    }
    
    public function getData(Request $request)
    {
        if (Auth::user()->position !== 'SUPERADMIN') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $query = TinvoiceH::with('customer','bank')->latest();

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                return '
                    <button onclick="printInvoicePDF('.$row->id.')" class="btn btn-info btn-sm" data-id="'.$row->id.'">
                        <i class="fa fa-print"></i> Print
                    </button>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function Export2PDF($id)
    {
        $SQL = "
            SELECT
                a.no_inv,
                a.tgl_inv,
                d.nama_cust,
                d.address_cust,
                d.npwp_cust,
                b.SKU,
                c.nama_barang,
                b.qty,
                b.price,
                (b.qty * b.price) as total_price,
                a.grand_total,
                e.norek_bank,
                e.atasnama_bank,
                e.nama_bank,
                a.ppn,
                a.diskon,
                d.type_cust
            FROM t_invoice_h as a
                INNER JOIN t_invoice_d as b ON a.id = b.hid 
                INNER JOIN mproduct as c ON b.SKU = c.SKU 
                INNER JOIN m_customers as d ON a.code_cust = d.code_cust 
                INNER JOIN mbank as e ON a.id_bank = e.id
            WHERE 
                a.id = '{$id}'
        ";
        
        $data = DB::select($SQL);
        
        if (empty($data)) {
            abort(404, 'Data invoice tidak ditemukan');
        }

        // Ambil informasi umum dari baris pertama
        $header = $data[0];
        // Fungsi terbilang di dalam controller
        function terbilang($angka)
        {
            $angka = abs($angka);
            $bilangan = [
                "", "satu", "dua", "tiga", "empat", "lima",
                "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas"
            ];
            if ($angka < 12) {
                return $bilangan[$angka];
            } elseif ($angka < 20) {
                return terbilang($angka - 10) . " belas";
            } elseif ($angka < 100) {
                return terbilang($angka / 10) . " puluh " . terbilang($angka % 10);
            } elseif ($angka < 200) {
                return "seratus " . terbilang($angka - 100);
            } elseif ($angka < 1000) {
                return terbilang($angka / 100) . " ratus " . terbilang($angka % 100);
            } elseif ($angka < 2000) {
                return "seribu " . terbilang($angka - 1000);
            } elseif ($angka < 1000000) {
                return terbilang($angka / 1000) . " ribu " . terbilang($angka % 1000);
            } elseif ($angka < 1000000000) {
                return terbilang($angka / 1000000) . " juta " . terbilang($angka % 1000000);
            } else {
                return "angka terlalu besar";
            }
        }

        $terbilang = ucwords(terbilang($header->grand_total));

        $grand_total = number_format($header->grand_total, 2, ',', '.');

        if($header->type_cust == 'B'){
            //Invoice for B2B
                $pdf = PDF::loadView('pages.invoice.invoice.invoice_pdf', [
                    'invoice' => $data,
                    'grand_total' => $grand_total,
                    'header' => $header,
                    'signed' => '', // jika belum ada tanda tangan
                ])->setPaper('A4', 'potrait');

                return $pdf->download('invoice_'.$header->no_inv.'_'.$header->nama_cust.'_'. date('Y-m-d') . '.pdf');
            //Invoice for B2B
        } else {
            // Kuitansi for B2C
                $pdf = PDF::loadView('pages.invoice.invoice.receipt_pdf', [
                    'invoice' => $data,
                    'header' => $header,
                    'signed' => '', // atau path tanda tangan
                    'terbilang' => $terbilang
                ])->setPaper('A5', 'potrait'); // A5 agar terlihat seperti kwitansi
            
                return $pdf->download('kwitansi_'.$header->no_inv.'_'.$header->nama_cust.'_'. date('Y-m-d') .'.pdf');
            // Kuitansi for B2C
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $customers  = MCustomer::get();
        $products   = Mproduct::get();
        $bank       = MBank::get();
        
        return view('pages.invoice.invoice.invoice_create',compact('customers','products', 'bank'));
    }
    
    public function product()
    {
        $product = Mproduct::get();

        if($product){
            return response()->json($product);
        } else {
            return response()->json([
                'error'=>'Produk tidak ditemukan'
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
        try{
            DB::beginTransaction();
            
            $validasi = $this->validate($request, [
                'id_cust'           => 'required',
                'no_inv'            => 'required|unique:t_invoice_h,no_inv',
                'tgl_inv'           => 'required',
                'grand_total'       => 'required',
                'id_bank'           => 'required',
                'ppn'               => 'nullable',
                'diskon'            => 'nullable'
            ],[
                'id_cust.required'            => 'Customer harus dipilih',
                'no_inv.required'             => 'No Invoice harus dipilih',
                'tgl_inv.required'            => 'Tanggal harus dipilih',
                'grand_total.required'        => 'Grand total harus dipilih',
                'id_bank.required'            => 'Bank harus dipilih',
            ]);

            if($validasi){
                // $this->logs->write("SUCCESS VALIDATION",$validasi);
                $customer = MCustomer::select('code_cust')->where('id',$request->id_cust)->first();
                $code_cust = $customer->code_cust;
                                
                $invoice_h = TInvoiceH::create([
                    'id_cust'       => $request->id_cust,
                    'no_inv'        => $request->no_inv,
                    'code_cust'     => $code_cust,
                    'tgl_inv'       => $request->tgl_inv,
                    'grand_total'   => $request->grand_total,
                    'id_bank'       => $request->id_bank,
                    'ppn'           => $request->ppn,
                    'diskon'        => $request->diskon
                ]);

                if($invoice_h){
                    // $this->logs->write("SUCCESS INPUT INV HEADER",$invoice_h);
                    $invh = TInvoiceH::select('id')->latest()->first();
                    $id_invh = $invh->id;

                    $invd = '';
                    foreach($request->qty as $key => $value){
                        $invd                   = new TInvoiceD;
                        $invd->hid              = $id_invh;
                        $product                = Mproduct::select('id')->where('SKU',$request->SKU[$key])->first();
                        $id_product             = $product->id;
                        $invd->id_product       = $id_product;
                        $invd->SKU              = $request->SKU[$key];
                        $invd->no_inv           = $request->no_inv;
                        $invd->tgl_inv          = $request->tgl_inv;
                        $invd->qty              = $request->qty[$key];
                        $invd->price            = $request->price[$key];
                        $invoice_d              = $invd->save();
                        
                        if($invoice_d){
                            // $this->logs->write("SUCCESS INPUT INV DETAIL",$invoice_d);
                            //From Process    
                                //Select Opname
                                    $stock_opname = TStockOpname::select('qty_last','id')
                                    ->where([
                                        ['id_product', $product->id],
                                        ['id_warehouse', 1]
                                    ])
                                    ->latest()
                                    ->first();
                                    $qty_last_opn = $stock_opname->qty_last;
                                    $id_opn       = $stock_opname->id;
                                //Select Opname
                                
                                //Insert New
                                    $stm                = new StockMutation;
                                    $stm->id_product    = $product->id;
                                    $stm->id_warehouse  = 1;
                                    $stm->qty_start     = $qty_last_opn;
                                    $stm->qty_in        = 0;
                                    $stm->qty_out       = $request->qty[$key];
                                    $stm->qty_last      = $qty_last_opn - $request->qty[$key];
                                    $stm->tgl_mutasi    = date('Y-m-d');
                                    $stock_mut          = $stm->save();
                                //Insert New

                                //Update Opname
                                    if($stock_mut){
                                        $sopn = TStockOpname::where([
                                            ['id_product', $product->id],
                                            ['id_warehouse', 1]
                                        ])->first();

                                        $stock_opm = $sopn->update([
                                            'qty_out'   => $request->qty[$key],
                                            'qty_last'  => $qty_last_opn - $request->qty[$key],
                                        ]);
                                        if($stock_opm){
                                            $user = Auth::user()->id;
                                            $date = date('Y-m-d'); // Menggunakan fungsi now() untuk mendapatkan waktu sekarang
                                        
                                            // Menggunakan create() untuk membuat entri baru pada HStockOpname
                                            $stock_opname_his = HStockOpname::create([
                                                'id_stock_opname'   => $id_opn,
                                                'id_product'        => $product->id,
                                                'id_warehouse'      => 1,
                                                'qty_in'            => 0,
                                                'qty_out'           => $request->qty[$key],
                                                'qty_last'          => $qty_last_opn - $request->qty[$key],
                                                'tgl_opname'        => $date,
                                                'created_by'        => $user,
                                                'created_at'        => $date,
                                            ]);
                                            // $this->logs->write("QUERY INPUT FROM HIS ", $stock_opname_his);
                                            if($stock_opname_his){
                                                // $this->logs->write("SUCCESS INPUT FROM HIS",$stock_opname_his);
                                            }
                                        }
                                    }
                                //Update Opname
                            //From Process
                        } else {
                            // $this->logs->write("FAILED INPUT INV DETAIL",$invoice_d);
                        }
                    }
                    
                    // If everything is successful, commit the transaction
                    DB::commit();

                    return redirect()
                        ->route('invoice.index')
                        ->with([
                            'success' => 'Invoice has successfully been added'
                    ]);
                } else {
                    // $this->logs->write("FAILED INPUT INV HEADER",$invoice_h);
                }
            } else {
                // $this->logs->write("FAILED VALIDATION");
            }
            DB::commit();

            return redirect()
                ->route('invoice.index')
                ->with([
                    'success' => 'Invoice has successfully been added'
            ]);
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