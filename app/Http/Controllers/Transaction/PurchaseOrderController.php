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
            // Filter Date
            if ($request->filled('date_from') && $request->filled('date_to')) {
                $query->whereBetween('tgl_po', [
                    $request->date_from,
                    $request->date_to
                ]);
            }

            // Filter Status
            if ($request->filled('status_po')) {
                $query->where('status_po', $request->status_po);
            }

            return DataTables::of($query)
                ->addColumn('id', fn($row) => $row->id)
                ->addColumn('action', function($row) {
                    $btn = '<a href="'.route('purchase_order.show', $row->id).'" class="btn btn-success btn-sm"><i class="fa fa-eye"></i></a> ';
                    $btn .= '<a 
                        href="'.route('purchase_order.print_po', $row->id).'" 
                        target="_blank"
                        class="btn btn-dark btn-sm"
                        title="Print PO">
                        <i class="fa fa-print"></i>
                    </a> ';
                    
                    if (Auth::user()->position === 'SUPERADMIN' && ($row->status_po == 0)) {
                        $btn .= '<a href="javascript:void(0)" 
                            onclick="confirmOrder('.$row->id.', \''.$row->no_po.'\')" 
                            class="btn btn-primary btn-sm" 
                            title="Confirm Order">
                            <i class="fa fa-check"></i>
                        </a> ';
                    }

                    if (!in_array($row->status_po, [2, 3])) {
                        $btn .= '
                            <button 
                                type="button"
                                class="btn btn-danger btn-sm show-alert-delete-box"
                                data-id="'.$row->id.'"
                                data-no-po="'.$row->no_po.'">
                                <i class="fa fa-times-circle"></i>
                            </button>
                        ';
                    }
                    $btn .= '<a 
                        href="'.route('purchase_order.edit', $row->id).'" 
                        class="btn btn-warning btn-sm"
                        title="Edit PO">
                        <i class="fa fa-edit"></i>
                        </a> ';
                        
                    $req = DB::table('tproduct_qr')
                        ->where('id_po', $row->id)
                        ->exists();
                    
                    if (!empty($row->confirm_by) && $req) {
                        $btn .= '<a href="'.route('purchase_order.reprint_list', $row->id).'" 
                            class="btn btn-info btn-sm" 
                            title="Request Reprint">
                            <i class="fa fa-file-alt"></i>
                            Reprint
                        </a> ';
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

    public function history($id)
    {
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
        $products  = Mproduct::select('id', 'nama_barang', 'harga_beli', 'sku')->whereNull('deleted_at')->get();

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
                 'reason_po'     => 'required'
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
                 'status_po'     => '0',
                 'reason_po'     => $request->reason_po,
                 'grand_total'   => 0,
                 'flag_approve'  => 'N',
                 'approve_date'  => '1970-01-01',
                 'approve_by'    => ''
             ]);
             $id_po = $purchase_order->id;
     
             // Detail PO
            //  foreach($request->total as $key => $value) {
            //      TPO_Detail::create([
            //          'id_po'         => $id_po,
            //          'part_number'   => $request->kode_barang[$key],
            //          'product_name'  => $request->nama_barang[$key],
            //          'qty'           => $request->qty[$key],
            //          'price'         => /*$request->harga[$key]*/0,
            //          'total_price'   => /*$request->total[$key]*/0
            //      ]);
            //  }
            foreach($request->sku as $key => $kode) {
                TPO_Detail::create([
                    'id_po'         => $id_po,
                    'part_number'   => $kode,
                    'product_name'  => $request->nama_barang[$key],
                    'qty'           => $request->qty[$key],
                    'price'         => 0,
                    'total_price'   => 0
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

    public function printPO($id)
    {
        $po = Tpo::with(['supplier'])
            ->findOrFail($id);

        $details = Tpo_Detail::where('id_po', $id)
            ->orderBy('id')
            ->get();

        $pdf = Pdf::loadView(
            'pages.transaction.purchase_order.purchase_order_print',
            [
                'po'      => $po,
                'details' => $details
            ]
        )->setPaper('A4', 'portrait');

        return $pdf->stream("PO_{$po->no_po}.pdf");
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
        $products  = Mproduct::select('id', 'kode_barang', 'nama_barang', 'harga_beli', 'sku')->whereNull('deleted_at')->get();

        $customers = MCustomer::get();
        $suppliers = MSupplier::get();
        $status_po = [
            '....' => '....',
            'OK' => 'OK',
            'HOLD' => 'HOLD'
        ];
        return view('pages.transaction.purchase_order.purchase_order_edit',compact('purchase_order','customers','suppliers', 'status_po','purchase_order_dtl','products'));
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
    
    public function confirm(Request $request, $id)
    {
        $confirm = Tpo::find($id);
    
        if (!$confirm) {
            return response()->json([
                'success' => false,
                'error' => 'Purchase Order tidak ditemukan'
            ]);
        }
    
        try {
            $confirm->confirm_by   = Auth::user()->username;
            $confirm->confirm_date = date('Y-m-d');
            $confirm->status_po = '4';
            $confirm->save();
    
            return response()->json([
                'success' => true
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
     
    public function update(Request $request, $id)
    {
        $po = Tpo::findOrFail($id);
        $status = $po->status_po;
    
        // ===============================
        // STATUS 3 = PARTIAL (LOCK TOTAL)
        // ===============================
        if ($status == 3) {
            return redirect()
                ->back()
                ->with('error','PO sudah PARTIAL, tidak bisa diedit');
        }
    
        DB::beginTransaction();
        try {
    
            // ===============================
            // UPDATE HEADER (STATUS 0 & 4)
            // ===============================
            if (in_array($status, [0,4])) {
    
                $request->validate([
                    'id_supplier' => 'required',
                    'tgl_po'      => 'required|date',
                    'reason_po'   => 'required'
                ]);
    
                $supplier = MSupplier::findOrFail($request->id_supplier);
    
                $po->update([
                    'id_supplier' => $request->id_supplier,
                    'code_spl'    => $supplier->code_spl,
                    'nama_spl'    => $supplier->nama_spl,
                    'tgl_po'      => $request->tgl_po,
                    'reason_po'   => $request->reason_po,
                ]);
            }
    
            // ===============================
            // UPDATE DETAIL (STATUS 0)
            // ===============================
            if ($status == 0) {
    
                $request->validate([
                    'kode_barang.*' => 'required',
                    'qty.*'         => 'required|integer|min:1'
                ]);
    
                // hapus detail lama
                Tpo_Detail::where('id_po', $po->id)->delete();
    
                foreach ($request->kode_barang as $i => $kode) {
    
                    $product = MProduct::where('kode_barang', $kode)->first();
    
                    Tpo_Detail::create([
                        'id_po'        => $po->id,
                        'part_number'  => $kode,
                        'product_name' => $product->nama_barang ?? '',
                        'qty'          => $request->qty[$i],
                        'qty_extra'    => 0
                    ]);
                }
            }
    
            // ===============================
            // UPDATE QTY LEBIHAN (STATUS 2)
            // ===============================
            if ($status == 2) {
    
                $request->validate([
                    'qty_extra.*' => 'nullable|integer|min:0'
                ]);
    
                $details = Tpo_Detail::where('id_po', $po->id)->get();
    
                foreach ($details as $i => $dtl) {
                    $dtl->update([
                        'qty_extra' => $request->qty_extra[$i] ?? 0
                    ]);
                }
            }
    
            // ===============================
            // HISTORY
            // ===============================
            Hpo::create([
                'id_po'     => $po->id,
                'code_spl'  => $po->code_spl,
                'nama_spl'  => $po->nama_spl,
                'no_po'     => $po->no_po,
                'tgl_po'    => $po->tgl_po,
                'reason_po' => $po->reason_po,
                'status_po' => $po->status_po
            ]);
    
            DB::commit();
    
            return redirect()
                ->route('purchase_order.index')
                ->with('success','Purchase Order berhasil diupdate');
    
        } catch (\Exception $e) {
            DB::rollBack();
    
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    
    // public function delete($id)
    // {
    //     $data = Tpo::find($id);
    //     $data->delete();
    //     return redirect()->route('purchase_order.index');
    // }
    public function delete($id)
    {
        $po = Tpo::findOrFail($id);

        // safety check (backend tetap wajib)
        if (in_array($po->status_po, [2, 3])) {
            return response()->json([
                'success' => false,
                'message' => 'PO tidak bisa dibatalkan'
            ], 422);
        }

        // contoh: status_po = 9 artinya CANCEL
        $po->status_po  = 5;
        $po->reason_po  = 'Canceled by user'; // opsional
        $po->save();

        return response()->json([
            'success' => true,
            'message' => 'PO berhasil dibatalkan'
        ]);
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

    public function generateQRPDF(Request $r, $id)
    {
        $po = Tpo::with('po_detail')->findOrFail($id);

        // Single product + nomor urut
        if ($r->detail && $r->seq) {
            return $this->generateSingleQR($po, $r->detail, $r->seq);
        }

        // Multiple products langsung print
        if ($r->multi) {
            $ids = explode(",", $r->multi);
            return $this->generateMultipleQR($po, $ids);
        }

        // default all product
        return $this->generateAllQR($po);
    }
    
    private function generateSingleQR($po, $detailId, $seqText)
    {
        $detail = $po->po_detail->where('id', $detailId)->first();
        if (!$detail) abort(404);
    
        $range = $this->parseSequenceInput($seqText);
    
        $qrList = [];
        
        foreach ($range as $num) {

            $seq = str_pad($num, 4, '0', STR_PAD_LEFT);
            $conflict = $this->detectPrintedConflict($po->id, $detail->id, $seq);
        
            if ($conflict) {
                return response()->json([
                    'type' => 'SINGLE',
                    'conflicts' => [$conflict]
                ], 403);
            }
        
            $qrList[] = $this->createOrGetQR($po, $detail, $num);
        }
        
        return $this->printPDF($po, $qrList);
    }
    
    private function generateMultipleQR($po, $ids)
    {
        foreach ($po->po_detail as $item) {

            if (!in_array($item->id, $ids)) continue;

            $existing = DB::table('tproduct_qr')
                ->where('id_po', $po->id)
                ->where('id_po_detail', $item->id)
                ->first();

            if ($existing) {
                return response()->json([
                    'message' => "Gagal print QR. Barang {$item->product_name} nomor {$existing->sequence_no} sudah pernah dicetak."
                ], 409);
            }
        }

        // ===============================
        // AMAN → GENERATE SEMUA
        // ===============================
        $qrList = [];

        foreach ($po->po_detail as $item) {

            if (!in_array($item->id, $ids)) continue;

            for ($seq = 1; $seq <= intval($item->qty); $seq++) {
                $qrList[] = $this->createOrGetQR($po, $item, $seq);
            }
        }

        return $this->printPDF($po, $qrList);
    }
    
    private function generateAllQR($po)
    {
        $existing = DB::table('tproduct_qr')
            ->where('id_po', $po->id)
            ->orderBy('id_po_detail')
            ->orderBy('sequence_no')
            ->first();

        // ❌ JIKA ADA SATU SAJA → GAGAL TOTAL
        if ($existing) {
            return response()->json([
                'message' => "Gagal print QR. Barang sudah pernah dicetak (No {$existing->sequence_no})."
            ], 409);
        }

        // ===============================
        // BELUM ADA → PRINT SEMUA
        // ===============================
        $qrList = [];

        foreach ($po->po_detail as $item) {
            for ($seq = 1; $seq <= intval($item->qty); $seq++) {
                $qrList[] = $this->createOrGetQR($po, $item, $seq);
            }
        }

        return $this->printPDF($po, $qrList);
    }
    
    private function printPDF($po, $qrList)
    {
        if (!is_array($qrList) || count($qrList) === 0) {
            abort(422, 'QR list kosong');
        }
        
        /*
        |--------------------------------------------------------------------------
        | UKURAN LABEL (33 x 15 mm)
        |--------------------------------------------------------------------------
        | DomPDF menggunakan satuan POINT
        | 1 mm = 2.83465 pt
        */
        $width  = 33 * 2.83465;
        $height = 15 * 2.83465;

        /*
        |--------------------------------------------------------------------------
        | GENERATE PDF
        |--------------------------------------------------------------------------
        | PDF adalah sumber kebenaran ukuran
        | Browser tidak boleh override
        */
        $pdf = Pdf::loadView(
            'pages.transaction.purchase_order.purchase_order_pdf',
            [
                'po'      => $po,
                'qrList' => $qrList
            ]
        )->setPaper([0, 0, $width, $height], 'portrait');


        /*
        |--------------------------------------------------------------------------
        | MODE AKTIF (WAJIB PRODUKSI)
        |--------------------------------------------------------------------------
        | Dibuka di NEW TAB sebagai PDF
        | Print dilakukan dari PDF viewer
        | Ukuran label PRESISI 33x15mm
        */
        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header(
                'Content-Disposition',
                'inline; filename="QR_'.$po->no_po.'.pdf"'
            );

        // $pdf = Pdf::loadView('pages.transaction.purchase_order.purchase_order_pdf', [
        //     'po' => $po,
        //     'qrList' => $qrList
        // ])->setPaper('A4', 'portrait');

        // return $pdf->stream("QR_{$po->no_po}.pdf");
    }

    private function detectPrintedConflict($poId, $detailId, $seq)
    {
        $qr = DB::table('tproduct_qr')
            ->where([
                'id_po'        => $poId,
                'id_po_detail' => $detailId,
                'sequence_no'  => $seq
            ])->first();
    
        if (!$qr) return null;
    
        $approved = DB::table('tqr_reprint_request')
            ->where([
                'id_po'        => $poId,
                'id_po_detail' => $detailId,
                'sequence_no'  => $seq,
                'status'       => 'APPROVED'
            ])->exists();
    
        if ($approved) return null;
    
        return [
            'id_product_qr' => $qr->id,
            'id_product'    => $qr->id_product,
            'product'       => $qr->nama_barang,
            'sequence'      => $seq,
        ];
    }
    
    private function parseSequenceInput(string $text): array
    {
        // Bersihkan spasi
        $text = str_replace(' ', '', $text);

        if (empty($text)) return [];

        $result = [];

        // Pisahkan berdasarkan koma (jika ada)
        $segments = explode(',', $text);

        foreach ($segments as $seg) {

            // Case range: "x-y"
            if (strpos($seg, '-') !== false) {
                [$start, $end] = explode('-', $seg);

                // Validasi angka
                if (is_numeric($start) && is_numeric($end)) {
                    $start = (int)$start;
                    $end   = (int)$end;

                    if ($start <= $end) {
                        $result = array_merge($result, range($start, $end));
                    }
                }
            }

            // Case single: "7"
            else {
                if (is_numeric($seg)) {
                    $result[] = (int)$seg;
                }
            }
        }

        // Hilangkan duplikat & urutkan
        $result = array_unique($result);
        sort($result);

        return array_values($result);
    }
    
    public function getSequence($id)
    {
        try {
            // Ambil semua sequence_no untuk id_po_detail
            $sequences = DB::table('tproduct_qr')
                ->where('id_po_detail', $id)
                ->orderBy('sequence_no', 'asc')
                ->pluck('sequence_no')
                ->toArray();
    
            // ubah "0001" => int 1
            $available = array_values(array_map(function($s){
                return intval($s);
            }, $sequences));
    
            // optional: tambahkan info last_sequence (maksimum yang ada)
            $lastSequence = count($available) ? max($available) : 0;
    
            return response()->json([
                'available' => $available,
                'last_sequence' => $lastSequence
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil data sequence.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    private function createOrGetQR($po, $item, $seqNumber)
    {
        $seqStr = str_pad($seqNumber, 4, '0', STR_PAD_LEFT);

        $qrValue = $po->no_po
                . "|" . $item->part_number
                . "|" . $seqStr;

        $sku = $item->part_number;
        
        $id_product = DB::table('mproduct')
                        ->where('sku', $sku)
                        ->value('id');
                        
        $sku = DB::table('mproduct')
        ->where('sku', $sku)
        ->value('sku');

        // ===========================
        // 1. CEK APAKAH QR SUDAH ADA
        // ===========================
        $existing = DB::table('tproduct_qr')
            ->where('qr_code', $qrValue)
            ->first();

        if ($existing) {

            // update printed_at
            DB::table('tproduct_qr')
                ->where('id', $existing->id)
                ->update(['printed_at' => now()]);

            // update mproduct_sequence
            // DB::table('mproduct_sequence')
            //     ->updateOrInsert(
            //         ['sku' => $sku],
            //         [
            //             'id_product'   => $id_product,
            //             'last_sequence'=> $seqNumber,
            //             'id_po'        => $po->id,
            //             'updated_at'   => now()
            //         ]
            //     );

            return [
                'nama_barang' => $item->product_name,
                'sku' => $item->part_number,
                'nomor_urut'  => $seqStr,
                'qr_payload'  => $qrValue,
            ];
        }

        // ===========================
        // 2. INSERT BARU
        // ===========================
        DB::table('tproduct_qr')->insert([
            'id_po'        => $po->id,
            'id_po_detail' => $item->id,
            'id_product'   => $id_product,
            'sku'          => $sku,
            'qr_code'      => $qrValue,
            'sequence_no'  => $seqStr,
            'nama_barang'  => $item->product_name,
            'status'       => 'NEW',
            'used_for'     => 'IN',
            'printed_at'   => now(),
        ]);

        // update mproduct_sequence
        // DB::table('mproduct_sequence')
        //     ->updateOrInsert(
        //         ['sku' => $sku],
        //         [
        //             'id_product'   => $id_product,
        //             'last_sequence'=> $seqNumber,
        //             'id_po'        => $po->id,
        //             'updated_at'   => now()
        //         ]
        //     );

        return [
            'nama_barang' => $item->product_name,
            'sku' => $item->part_number,
            'nomor_urut'  => $seqStr,
            'qr_payload'  => $qrValue,
        ];
    }

    // public function listReprint()
    // {

    //     return view('pages.transaction.purchase_order.purchase_order_reprint', compact('requestsGrouped'));
    // }
    
    public function reprintList($id)
    {

        // Ambil semua request reprint, join ke PO dan detailnya
        $requests = DB::table('tqr_reprint_request as r')
            ->join('tpos as po', 'r.id_po', '=', 'po.id')
            ->join('tpo_detail as d', 'r.id_po_detail', '=', 'd.id')
            ->select(
                'r.id as request_id',
                'po.no_po',
                'po.tgl_po',
                'd.part_number',
                'd.product_name',
                'r.sequence_no',
                'r.reason',
                'r.status'
            )
            ->orderBy('po.tgl_po', 'desc')
            ->orderBy('r.id', 'asc')
            ->where('r.id_po',$id)
            ->get();

        // Group per PO
        $requestsGrouped = $requests->groupBy('no_po');
        return view('pages.transaction.purchase_order.purchase_order_reprint', compact('requestsGrouped'));
    }

    public function approveReprint(Request $request)
    {
        $reqIds = $request->ids ?? [];
        DB::table('tqr_reprint_request')
            ->whereIn('id', $reqIds)
            ->update(['status' => 'APPROVED', 'approved_by' => Auth::user()->username, 'approved_at' => now()]);
    
        return response()->json(['success' => true]);
    }
    
    public function rejectReprint(Request $request)
    {
        $reqIds = $request->ids ?? [];
        DB::table('tqr_reprint_request')
            ->whereIn('id', $reqIds)
            ->update(['status' => 'REJECTED', 'approved_by' => Auth::user()->username, 'approved_at' => now()]);
    
        return response()->json(['success' => true]);
    }
    
    public function requestReprint(Request $r)
    {
        if (!is_array($r->items)) {
            return response()->json(['message' => 'Invalid reprint payload'], 422);
        }
    
        foreach ($r->items as $item) {
    
            // =========================
            // Ambil id_product dari SKU
            // =========================
            $product = DB::table('mproduct')
                ->where('kode_barang', $item['sku'])
                ->first();
    
            if (!$product) {
                return response()->json([
                    'message' => 'Product tidak ditemukan untuk SKU '.$item['sku']
                ], 422);
            }
    
            // =========================
            // Proses sequence_no
            // =========================
            $sequences = [];
    
            if (strpos($item['sequence'], '-') !== false) {
                [$start, $end] = explode('-', $item['sequence']);
                $start = intval($start);
                $end   = intval($end);
                for ($i = $start; $i <= $end; $i++) {
                    $sequences[] = str_pad($i, 4, '0', STR_PAD_LEFT); // 0001, 0002
                }
            } elseif (strpos($item['sequence'], ',') !== false) {
                $nums = explode(',', $item['sequence']);
                foreach ($nums as $n) {
                    $sequences[] = str_pad(intval($n), 4, '0', STR_PAD_LEFT);
                }
            } else {
                $sequences[] = str_pad(intval($item['sequence']), 4, '0', STR_PAD_LEFT);
            }
    
            // =========================
            // Insert untuk setiap sequence
            // =========================
            foreach ($sequences as $seq_no) {
                $productQr = DB::table('tproduct_qr')
                    ->where('id_po', $r->id_po)
                    ->where('id_po_detail', $item['id_po_detail'])
                    ->where('id_product', $product->id) // pakai id_product dari mproduct
                    ->where('sequence_no', $seq_no)
                    ->first();
    
                if (!$productQr) {
                    return response()->json([
                        'message' => 'QR Product tidak ditemukan untuk SKU '.$item['sku'].' sequence '.$seq_no
                    ], 422);
                }
    
                DB::table('tqr_reprint_request')->insert([
                    'id_po'         => $r->id_po,
                    'id_po_detail'  => $item['id_po_detail'],
                    'id_product'    => $product->id,
                    'id_product_qr' => $productQr->id,
                    'sequence_no'   => $seq_no,
                    'reason'        => $r->reason,
                    'status'        => 'PENDING',
                    'requested_by'  => Auth::user()->username,
                    'requested_at'  => now(),
                ]);
            }
        }
    
        return response()->json(['success'=>true]);
    }
    
    
    // public function requestReprint(Request $r)
    // {
    //     foreach ($r->items as $item) {
    //         DB::table('tqr_reprint_request')->insert([
    //             'id_po'         => $r->id_po,
    //             'id_po_detail'  => $item['id_po_detail'],
    //             'id_product'    => $item['id_product'],
    //             'id_product_qr' => $item['id_product_qr'],
    //             'sequence_no'   => $item['sequence'],
    //             'reason'        => $r->reason,
    //             'status'        => 'PENDING',
    //             'requested_by'  => Auth::user()->username,
    //             'requested_at'  => now(),
    //         ]);
    //     }
    
    //     return response()->json(['success'=>true]);
    // }

    private function canPrintQR($poId, $detailId, $seq)
    {
        $printed = DB::table('tproduct_qr')
            ->where('id_po', $poId)
            ->where('id_po_detail', $detailId)
            ->where('sequence_no', $seq)
            ->exists();
    
        if (!$printed) return true;
    
        return DB::table('tqr_reprint_request')
            ->where([
                'id_po' => $poId,
                'id_po_detail' => $detailId,
                'sequence_no' => $seq,
                'status' => 'APPROVED'
            ])->exists();
    }
    
    // public function destroy($id)
    // {
    //     $courier = MCourier::findOrFail($id);
    //     $courier->delete();

    //     return redirect('/couriers')->with('success', 'Courier is successfully deleted');
    // }
}