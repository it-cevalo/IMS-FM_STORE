<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tpo;
use App\Models\Tpo_Detail;
use App\Models\Hpo;
use App\Models\MCustomer;
use App\Models\MSupplier;
use App\Models\Mproduct;
use App\Imports\PurchaseOrderImport;
use Storage, Excel, DB, Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Yajra\DataTables\Facades\DataTables;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PurchaseOrderController extends Controller
{
   /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages.transaction.purchase_order.purchase_order_index');
    }

    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $query = Tpo::orderByDesc('tgl_po');

            return DataTables::of($query)
                ->addColumn('id', fn($row) => $row->id)
                ->addColumn('action', function($row) {
                    $btn = '<a href="'.route('purchase_order.history', $row->id).'" class="btn btn-success btn-sm"><i class="fa fa-history"></i></a> ';
                    if($row->flag_approve === 'Y'){
                        // Tombol Print PDF
                        $btn .= '<a href="'.route('purchase_order.print', $row->id).'" target="_blank" 
                        class="btn btn-secondary btn-sm" title="Print PDF">
                        <i class="fa fa-print"></i>
                        </a> ';
                    }
                    
                    if (Auth::user()->position === 'SUPERADMIN' && ($row->flag_approve === 'N' || empty($row->flag_approve))) {
                        $btn .= '<a href="#" onclick="approveOrder('.$row->id.')" class="btn btn-primary btn-sm" title="Approve Order">
                                    <i class="fa fa-check"></i>
                                 </a> ';
                    }

                    if($row->status_po == ''){
                        $btn .= '
                            <form method="POST" action="'.route('purchase_order.delete', $row->id).'" style="display:inline;">
                                '.csrf_field().method_field('DELETE').'
                                <button type="submit" class="btn btn-danger btn-sm show-alert-delete-box"><i class="fa fa-trash"></i></button>
                            </form>';
                    }

                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return abort(403);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function search(Request $request)
    {
        // $customers      = MCustomer::get();
        $purchase_order = Tpo::with(['customer'])->where('nama_cust', 'LIKE', request()->search.'%')
                                ->orWhere('code_cust', 'LIKE' , request()->search.'%')
                                ->orWhere('tgl_po', 'LIKE' , request()->search.'%')
                                ->orWhere('no_po', 'LIKE' , request()->search.'%')
                                ->orWhere('no_so', 'LIKE' , request()->search.'%')
                                // ->orWhere('status_po', 'LIKE' ,request()->search.'%')
                                ->orWhere('reason_po', 'LIKE' ,request()->search.'%')
                                ->orderBy('tgl_po','desc')
                                ->paginate();
        // $pageTitle = "Register Management";
        $no  = ($purchase_order->currentPage()*$purchase_order->perPage())-$purchase_order->perPage()+1;
        return view('pages.transaction.purchase_order.purchase_order_index',compact('purchase_order','no'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }

    public function history($id){
        $purchase_order_his = Hpo::where('id_po',$id)->get();
        return view('pages.transaction.purchase_order.purchase_order_history',compact('purchase_order_his'));
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

 
	public function upload(Request $request) 
	{
		// validasi
		$this->validate($request, [
			'file' => 'required|mimes:xlsx'
		]);
 
		// menangkap file excel
		$file = $request->file('file');
 
		// membuat nama file unik
		$nama_file = rand().$file->getClientOriginalName();
 
		// upload ke folder file_po di dalam folder public
		$file->move('file_po',$nama_file);
 
		// import data
		Excel::import(new PurchaseOrderImport, public_path('/file_po/'.$nama_file));
 
		// notifikasi dengan session
		// Session::flash('sukses','Data PO Berhasil Diimport!');
 
		// alihkan halaman kembali
		return redirect()->route('purchase_order.index');
	}

    public function create()
    {
        $customers = MCustomer::get();
        $suppliers = MSupplier::get();
        $products  = Mproduct::select('id', 'kode_barang', 'nama_barang', 'harga_beli')->whereNull('deleted_at')->get();

        return view('pages.transaction.purchase_order.purchase_order_create', compact('customers', 'suppliers', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    
     public function store(Request $request)
     {
         try {
             $this->validate($request, [
                 'id_supplier'   => 'required',
                 'no_po'         => 'required',
                 'tgl_po'        => 'required',
                 'reason_po'     => 'required',
                 'grand_total'   => 'required'
             ]);
     
             $supplier = MSupplier::select('code_spl','nama_spl')
                 ->where('id', $request->id_supplier)
                 ->first();
     
             $purchase_order = Tpo::create([
                 'id_cust'       => 0,
                 'id_supplier'   => $request->id_supplier,
                 'code_cust'     => '',
                 'nama_cust'     => '',
                 'code_spl'      => $supplier->code_spl ?? '',
                 'nama_spl'      => $supplier->nama_spl ?? '',
                 'no_po'         => $request->no_po,
                 'no_so'         => 0,
                 'tgl_po'        => $request->tgl_po,
                 'status_po'     => '',
                 'reason_po'     => $request->reason_po,
                 'grand_total'   => $request->grand_total,
                 'flag_approve'  => 'N',
                 'approve_date'  => '1970-01-01',
                 'approve_by'    => ''
             ]);
     
             $id_po = $purchase_order->id;
     
             // Detail PO
             foreach($request->total as $key => $value) {
                 TPO_Detail::create([
                     'id_po'         => $id_po,
                     'part_number'   => $request->kode_barang[$key],
                     'product_name'  => $request->nama_barang[$key],
                     'qty'           => $request->qty[$key],
                     'price'         => $request->harga[$key],
                     'total_price'   => $request->total[$key]
                 ]);
             }
     
             // History PO
             Hpo::create([
                 'id_cust'    => 0,
                 'id_po'      => $id_po,
                 'code_cust'  => '',
                 'nama_cust'  => '',
                 'code_spl'   => $supplier->code_spl ?? '',
                 'nama_spl'   => $supplier->nama_spl ?? '',
                 'no_po'      => $request->no_po,
                 'no_so'      => 0,
                 'tgl_po'     => $request->tgl_po,
                 'reason_po'  => $request->reason_po
             ]);
     
             return response()->json([
                 'status' => 'success',
                 'message' => 'PO has successfully been added'
             ]);
     
         } catch (\Exception $e) {
             return response()->json([
                 'status' => 'error',
                 'message' => 'Failed to create PO: ' . $e->getMessage()
             ], 500);
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
        $purchase_order = Tpo::findOrFail($id);
        $purchase_order_dtl   = Tpo_Detail::where('id_po',$id)->get();
        $customers = MCustomer::get();
        $suppliers = MSupplier::get();
        $status_po = [
            '....' => '....',
            'OK' => 'OK',
            'HOLD' => 'HOLD'
        ];
        return view('pages.transaction.purchase_order.purchase_order_show',compact('purchase_order','customers','suppliers', 'purchase_order_dtl', 'status_po'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $purchase_order       = Tpo::findOrFail($id);
        $purchase_order_dtl   = Tpo_Detail::where('id_po',$id)->get();

        $customers = MCustomer::get();
        $suppliers = MSupplier::get();
        $status_po = [
            '....' => '....',
            'OK' => 'OK',
            'HOLD' => 'HOLD'
        ];
        return view('pages.transaction.purchase_order.purchase_order_edit',compact('purchase_order','customers','suppliers', 'status_po','purchase_order_dtl'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function approve(Request $request, $id)
    {
        $approve_by = Auth::user()->username;
        // dd($approve_by);
        
        $approve = TPo::find($id);
        
        if (!$approve) {
            return response()->json(['error' => 'purchase order not found']);
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
     
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            // 'status_po' => 'required',
            'reason_po' => 'required',
            'grand_total'    => 'required'
        ],[
            // 'status_po.required' => 'Please Fill Status PO',
            'reason_po.required' => 'Please Fill Reason PO',
            'grand_total.required' => 'Grand Total is empty'
        ]);

        DB::beginTransaction();
        try{
            Tpo::whereId($id)->update($validatedData);

            // $customers = MCustomer::select('code_cust','nama_cust')->where('id',$request->id_cust)->first();
            // $code = $customers->code_cust;
            // $name = $customers->nama_cust;
            
            $suppliers = MSupplier::select('code_spl','nama_spl')->where('id',$request->id_supplier)->first();
            $code_spl = $suppliers->code_spl;
            $name_spl = $suppliers->nama_spl;

            $purchase_order = Tpo::select('id')->where('code_cust',$code)->latest()->first();
            $id_po          = $purchase_order->id;
            // dd($id_po);exit();

            $dataUpdatePODetail = TPO_Detail::where('id_po',$id_po)->delete();
            
            $part_number = $request->part_number;

            foreach($request->total_price as $key => $value){
                $po_detail                  = new TPO_Detail;
                $po_detail->id_po           = $id_po;
                $po_detail->part_number     = $request->part_number[$key];
                $po_detail->product_name    = $request->product_name[$key];
                $po_detail->qty             = $request->qty[$key];
                $po_detail->price           = $request->price[$key];
                $po_detail->total_price     = $request->total_price[$key];
                $purchase_order_dtl         = $po_detail->save();
            }

            $purchase_order_his = Hpo::create([
                'id_po'              => $id_po,
                'id_cust'            => /*$request->id_cust*/ 0,
                'code_cust'          => '',
                'nama_cust'          => '',
                'code_spl'           => $code_spl,
                'nama_spl'           => $nama_spl,
                'no_po'              => $request->no_po,
                'no_so'              => $request->no_so,
                'tgl_po'             => $request->tgl_po,
                'reason_po'          => $request->reason_po
            ]);
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
        }

        return redirect('/purchase_order')->with('success', 'Purchase Order is successfully updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    
    public function delete($id)
    {
        $data = Tpo::find($id);
        $data->delete();
        return redirect()->route('purchase_order.index');
    }

    public function bin()
    {
        // dd('ok');
        // $data['pageTitle']      = 'BIN Ticket';
        // $data   ['data']      = Tpo::onlyTrashed()->paginate(5);
        // $data   ['customers'] = MCustomer::get();
        // dd($data);
        return view('pages.transaction.purchase_order.purchase_order_bin');
    }

    public function binData(Request $request)
    {
        if (auth()->user()->position !== 'SUPERADMIN') {
            abort(403);
        }

        $query = Tpo::onlyTrashed()->with('supplier');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('code_spl', fn($row) => $row->supplier->code_spl ?? '-')
            ->addColumn('nama_spl', fn($row) => $row->supplier->nama_spl ?? '-')
            ->addColumn('action', function ($row) {
                return '<button class="btn btn-sm btn-primary show-alert-rollback-box" data-id="' . $row->id . '"><i class="fa fa-undo"></i></button>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }
    
    public function rollback(Request $request)
    {
        try {
            $id = $request->id;
    
            if (!$id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ID tidak ditemukan. Mohon muat ulang halaman.'
                ], 400);
            }
    
            $data = Tpo::onlyTrashed()->where('id', $id)->first();
    
            if (!$data) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data tidak ditemukan atau sudah dipulihkan sebelumnya.'
                ], 404);
            }
    
            $data->restore();
    
            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil dipulihkan.'
            ]);
        } catch (\Exception $e) {
    
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan sistem. Silakan coba beberapa saat lagi.'
            ], 500);
        }
    }
    
    // public function generateQRPDF($id)
    // {
    //     $po = Tpo::with('po_detail')->findOrFail($id);
    
    //     $items = $po->po_detail; // diasumsikan relasi: details
    //     $qrList = [];
    
    //     $counter = 1;
    
    //     foreach ($items as $item) {
    //         $nomorUrut = str_pad($counter, 5, '0', STR_PAD_LEFT);
    
    //         $payload = "{$po->no_po}|{$item->part_number}|{$nomorUrut}";
    
    //         $qrList[] = [
    //             'nama_barang' => $item->product_name,
    //             'kode_barang' => $item->part_number,
    //             'nomor_urut'  => $nomorUrut,
    //             'qr_payload'  => $payload,
    //         ];
    
    //         $counter++;
    //     }
    
    //     $pdf = Pdf::loadView('pages.transaction.purchase_order.purchase_order_pdf', compact('po', 'qrList'))
    //         ->setPaper('A4', 'portrait');
    
    //     return $pdf->stream("QR_PO_{$po->NoPO}.pdf");
    // }
    public function generateQRPDF($id)
{
    $po = Tpo::with('po_detail')->findOrFail($id);

    DB::beginTransaction();

    try {

        $qrList = [];

        foreach ($po->po_detail as $item) {

            $existing = DB::table('tproduct_qr')
                ->where('id_po', $po->id)
                ->where('id_po_detail', $item->id)
                ->get();

            // ==============================
            // 🔹 JIKA SUDAH ADA → GUNAKAN DATA EXISTING
            // ==============================
            if ($existing->count() > 0) {
                foreach ($existing as $ex) {
                    $qrList[] = [
                        'nama_barang' => $ex->nama_barang,
                        'kode_barang' => $ex->sku,
                        'nomor_urut'  => $ex->sequence_no,
                        'qr_payload'  => $ex->qr_code,
                    ];
                }
                continue;
            }

            // ==============================
            // 🔹 Ambil atau buat sequence di mproduct_sequence
            // ==============================
            $seq = DB::table('mproduct_sequence')
                ->where('sku', $item->part_number)
                ->where('id_po', $po->id)
                ->lockForUpdate()
                ->first();

            if (!$seq) {
                $seqId = DB::table('mproduct_sequence')->insertGetId([
                    'id_product'   => $item->id_product ?? 0,
                    'sku'          => $item->part_number,
                    'last_sequence'=> 0,
                    'id_po'        => $po->id,
                ]);

                $seq = DB::table('mproduct_sequence')->find($seqId);
            }

            $start = $seq->last_sequence + 1;
            $end   = $seq->last_sequence + $item->qty;

            // ==============================
            // 🔹 Loop generate QR per sequence
            // ==============================
            for ($s = $start; $s <= $end; $s++) {

                $seqStr = str_pad($s, 4, '0', STR_PAD_LEFT);

                // format QR sama seperti Golang:
                // POxxxx-SKU-0001
                $qrValue = "PO" . str_pad($po->id, 4, '0', STR_PAD_LEFT)
                          . "-" . $item->part_number
                          . "-" . $seqStr;

                // Insert ke tproduct_qr
                DB::table('tproduct_qr')->insert([
                    'id_po'        => $po->id,
                    'id_po_detail' => $item->id,
                    'id_product'   => $item->id_product ?? 0,
                    'sku'          => $item->part_number,
                    'qr_code'      => $qrValue,
                    'sequence_no'  => $seqStr,
                    'nama_barang'  => $item->product_name,
                    'status'       => 'NEW',
                    'used_for'     => 'IN',
                    'printed_at'   => now(),
                ]);

                // Tambahkan ke array untuk PDF
                $qrList[] = [
                    'nama_barang' => $item->product_name,
                    'kode_barang' => $item->part_number,
                    'nomor_urut'  => $seqStr,
                    'qr_payload'  => $qrValue,
                ];
            }

            // Update last_sequence
            DB::table('mproduct_sequence')
                ->where('id', $seq->id)
                ->update(['last_sequence' => $end]);
        }

        DB::commit();

        // ==============================
        // 🔹 CETAK PDF
        // ==============================
        $pdf = Pdf::loadView(
            'pages.transaction.purchase_order.purchase_order_pdf',
            compact('po', 'qrList')
        )->setPaper('A4', 'portrait');

        return $pdf->stream("QR_PO_{$po->no_po}.pdf");

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }
}


    // public function destroy($id)
    // {
    //     $courier = MCourier::findOrFail($id);
    //     $courier->delete();

    //     return redirect('/couriers')->with('success', 'Courier is successfully deleted');
    // }
}