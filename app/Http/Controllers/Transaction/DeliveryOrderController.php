<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Helpers\Permission;
use Illuminate\Http\Request;
use App\Models\Tdo;
use App\Models\Tpo;
use App\Models\Mproduct;
use App\Models\Tpo_Detail;
use App\Models\Hdo;
use App\Imports\DeliveryOrderImport;
use Storage, Excel, Response, Auth, DB;
use Yajra\DataTables\Facades\DataTables;
use App\Logs;

class DeliveryOrderController extends Controller
{
    private function doLog(string $section, string $content): void
    {
        try {
            $log = new Logs('Logs_DeliveryOrderController');
            $log->write($section, $content);
        } catch (\Throwable $e) {
            \Log::error('[DeliveryOrderController] Gagal menulis log: ' . $e->getMessage());
        }
    }

    private function actor(): string
    {
        $user = Auth::user();
        if (!$user) return 'Guest';
        return $user->username ?? $user->name ?? "ID:{$user->id}";
    }

    public function index()
    {
        $delivery_order = Tdo::with(['po'])->latest('id')->paginate(5);
        return view('pages.transaction.delivery_order.delivery_order_index',compact('delivery_order'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }
    
    public function data(Request $request)
    {
        $query = Tdo::with(['po'])->select('tdos.*');

        return DataTables::eloquent($query)
            ->addColumn('po_id', fn($d) => $d->po->id ?? null)
            ->addColumn('code_spl', fn($d) => $d->po->code_spl ?? '-')
            ->addColumn('nama_spl', fn($d) => $d->po->nama_spl ?? '-')
            ->addColumn('file', fn($d) => $d->file ? '<a href="'.route('delivery_order.downloadDO', $d->id).'">'.$d->file.'</a>' : 'No File')
            ->addColumn('action', function ($d) {

                $btn = '
                    <a href="'.route('delivery_order.show', $d->id).'" 
                       class="btn btn-sm btn-success">
                       <i class="fa fa-eye"></i>
                    </a>
                ';
                
                if (Permission::approve('MENU-0302') && $d->flag_approve == 'N') {
                    $btn .= '
                        <a href="'.route('delivery_order.edit', $d->id).'"
                        class="btn btn-sm btn-warning"
                        title="Edit DO">
                        <i class="fa fa-edit"></i>
                        </a>
                    ';
                }
            
                // APPROVE (final)
                if (Permission::approve('MENU-0302') && $d->flag_approve == 'N') {
                    $btn .= '
                        <a href="javascript:void(0)"
                           onclick="approveDO('.$d->id.', \''.$d->no_do.'\')"
                           class="btn btn-sm btn-primary"
                           title="Setujui Pengiriman">
                           <i class="fa fa-check"></i>
                        </a>
                    ';
                }
            
                // Cancel hanya jika belum approve
                if (Permission::reject('MENU-0302') && $d->flag_approve == 'N') {
                    $btn .= '
                        <button 
                            class="btn btn-sm btn-danger"
                            onclick="deleteDO('.$d->id.', \''.$d->no_do.'\')"
                            title="Batalkan Pengiriman">
                            <i class="fa fa-times-circle"></i>
                        </button>
                    ';
                }
            
                return $btn;
            })
            ->rawColumns(['file', 'action'])
            ->make(true);
    }
    
    public function search(Request $request)
    {
        $delivery_order = Tdo::with(['po'])->where('nama_cust', 'LIKE', request()->search.'%')
                                ->orWhere('code_cust', 'LIKE' , request()->search.'%')
                                ->orWhere('tgl_po', 'LIKE' , request()->search.'%')
                                ->orWhere('tgl_do', 'LIKE' , request()->search.'%')
                                ->orWhere('no_po', 'LIKE' , request()->search.'%')
                                ->orWhere('no_so', 'LIKE' , request()->search.'%')
                                ->orWhere('no_do', 'LIKE' , request()->search.'%')
                                ->orWhere('status_lmpr_do', 'LIKE' ,request()->search.'%')
                                ->orWhere('reason_do', 'LIKE' ,request()->search.'%')
                                ->orderBy('tgl_do','desc')
                                ->paginate();
        $no  = ($delivery_order->currentPage()*$delivery_order->perPage())-$delivery_order->perPage()+1;
        return view('pages.transaction.delivery_order.delivery_order_index',compact('delivery_order','no'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }

    public function history($id){
        $delivery_order_his = Hdo::where('id_do',$id)->get();
        // dd($delivery_order_his);
        return view('pages.transaction.delivery_order.delivery_order_history',compact('delivery_order_his'));
    }

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
 
		// upload ke folder file_do di dalam folder public
		$file->move('file_do',$nama_file);
 
		// import data
		Excel::import(new DeliveryOrderImport, public_path('/file_do/'.$nama_file));
 
		// notifikasi dengan session
		// Session::flash('sukses','Data PO Berhasil Diimport!');
 
		// alihkan halaman kembali
		return redirect()->route('delivery_order.index');
	}

    public function uploadDO(Request $request)
	{
		$this->validate($request, [
			'file'  => 'required|mimes:pdf',
            'id_do' => 'required'
		],[
            'file.required' => 'File DO wajib diunggah',
            'id_do.required' => 'DO wajib dipilih',
        ]);

        $do = Tdo::find($request->id_do);
        $doNo = $do ? $do->no_do : '-';

        $this->doLog('UPLOAD_FILE_DO', "User: {$this->actor()} | DO: {$doNo} (ID:{$request->id_do}) | Status: PROCESS");

		$file      = $request->file('file');
		$nama_file = rand().$file->getClientOriginalName();
		$file->move('file_do/berkas_do', $nama_file);

        Tdo::where('id', $request->id_do)->update([
			'file'           => $nama_file,
            'upload_date_at' => date("Y-m-d H:i:s")
		]);

        $this->doLog('UPLOAD_FILE_DO', "User: {$this->actor()} | DO: {$doNo} (ID:{$request->id_do}) | File: {$nama_file} | Status: SUCCESS");

		return redirect()->back();
	}

    public function downloadDO($id)
    {
        $DO = Tdo::select(['file'])->where('id',$id)->first();
        $filename = $DO->file;
        // dd($filename);exit; 
        $filepath = public_path('file_do/berkas_do/'.$filename);
        return Response::download($filepath); 
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $products = \DB::table('mproduct')
            ->select(['SKU','sku','nama_barang'])
            ->where('flag_active','Y')
            ->get();

        $shipping_via = ['....'=>'....','HANDCARRY'=>'HANDCARRY','EKSPEDISI'=>'EKSPEDISI'];

        return view('pages.transaction.delivery_order.delivery_order_create', compact('products','shipping_via'));
    }
    
    // Endpoint AJAX untuk qty tersedia
    public function getStock(Request $request)
    {
        $kode = $request->sku;
        // dd($kode);

        $stock = \DB::table('t_stock_opname as s')
            ->join('mproduct as p','p.id','=','s.id_product')
            ->where('p.sku',$kode)
            ->selectRaw('qty_last as qty_tersedia')
            ->first();

        return response()->json([
            'qty_tersedia' => $stock->qty_tersedia ?? 0
        ]);
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $skuCount = is_array($request->sku) ? count($request->sku) : 0;
        $this->doLog('BUAT_DO', "User: {$this->actor()} | No DO: {$request->no_do} | Tgl: {$request->tgl_do} | Via: {$request->shipping_via} | Jumlah SKU: {$skuCount} | Status: PROCESS");

        $request->validate([
            'tgl_do'        => 'required',
            'no_do'         => 'required|unique:tdos,no_do',
            'reason_do'     => 'nullable|string',
            'shipping_via'  => 'required'
        ]);

        DB::beginTransaction();
        try {
            // Buat DO header
            $do = Tdo::create([
                'no_do'        => $request->no_do,
                'tgl_do'       => $request->tgl_do,
                'reason_do'    => $request->reason_do,
                'shipping_via' => $request->shipping_via,
                'flag_approve' => 'N',
                'approve_date' => '1970-01-01',
                'approve_by'   => ''
            ]);

            // FIFO sequence untuk detail DO (format 1,2,3,...)
            $sequence = 1;
            foreach($request->sku as $i => $sku){
                \DB::table('tdo_detail')->insert([
                    'id_do'         => $do->id,
                    'sku'           => $request->sku[$i] ?? '',
                    'sku'   => $sku,
                    'qty'           => $request->qty[$i],
                    'seq'           => str_pad($sequence++, 4, '0', STR_PAD_LEFT),
                    'created_at'    => now(),
                    'updated_at'    => now()
                ]);
            }

            DB::commit();

            $this->doLog('BUAT_DO', "User: {$this->actor()} | No DO: {$request->no_do} | DO ID: {$do->id} | Via: {$request->shipping_via} | Status: SUCCESS");

            return response()->json([
                'status'  => 'success',
                'message' => 'Delivery Order berhasil dibuat'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            $this->doLog('BUAT_DO', "User: {$this->actor()} | No DO: {$request->no_do} | Status: FAILED | Error: {$e->getMessage()}");
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
    
    public function approve(Request $request, $id)
    {
        $this->doLog('APPROVE_DO', "User: {$this->actor()} | DO ID: {$id} | Status: PROCESS");

        DB::beginTransaction();

        try {

            $do = Tdo::with('do_detail')->find($id);

            if (!$do) {
                $this->doLog('APPROVE_DO', "User: {$this->actor()} | DO ID: {$id} | Status: FAILED | Error: Delivery Order tidak ditemukan");
                return response()->json([
                    'error' => 'Delivery Order tidak ditemukan'
                ], 404);
            }

            if ($do->flag_approve === 'Y') {
                $this->doLog('APPROVE_DO', "User: {$this->actor()} | DO: {$do->no_do} (ID:{$id}) | Status: BLOCKED | Info: DO sudah di-approve sebelumnya");
                return response()->json([
                    'error' => 'Delivery Order sudah di-approve'
                ], 422);
            }

            // 1. Update header DO
            $do->update([
                'approve_by'   => Auth::user()->username,
                'approve_date'=> now(),
                'flag_approve'=> 'Y',
            ]);
    
            // 2. Loop detail DO
            foreach ($do->do_detail as $detail) {
    
                $qtyNeeded = (int) $detail->qty;
    
                $product = Mproduct::where('sku', $detail->sku)->first();
                
                if (!$product) {
                    throw new \Exception("Product dengan kode {$detail->sku} tidak ditemukan.");
                }
                
                $idProduct   = $product->id;
                $sequence_no = str_pad($detail->seq, 4, '0', STR_PAD_LEFT);
    
                // 3. Ambil QR FIFO (limit sesuai qty needed)
                
                // $productQRs = DB::table('tproduct_qr')
                // ->where('id_product', $idProduct)
                // ->whereNull('id_do')
                // ->orderBy('id', 'asc') // FIFO real
                // ->limit($qtyNeeded)
                // ->get();
                $productQRs = DB::table('tproduct_qr as q')
                ->join(
                    DB::raw('
                        (
                            SELECT MAX(id) AS id
                            FROM tproduct_qr
                            WHERE id_product = '.$idProduct.'
                            AND id_do IS NULL
                            GROUP BY sequence_no
                        ) latest
                    '),
                    'q.id',
                    '=',
                    'latest.id'
                )
                ->orderBy('q.id', 'asc') // FIFO antar sequence
                ->limit($qtyNeeded)
                ->get();

    
                $available = (int) $productQRs->count();

    
                if ($available < $qtyNeeded) {
                    throw new \Exception(
                        "Stock tidak cukup untuk produk {$detail->sku}. 
                         Dibutuhkan {$qtyNeeded}, tersedia {$available}."
                    );
                }
    
                // 4. Assign QR ke DO
                foreach ($productQRs as $qr) {
                    DB::table('tproduct_qr')
                        ->where('id', $qr->id)
                        ->update([
                            'id_do'        => $do->id,
                            'id_do_detail' => $detail->id,
                            'used_for'     => 'OUT',
                            'out_at'       => now(),
                        ]);
                }
            }
    
            DB::commit();

            $totalDetail = $do->do_detail->count();
            $this->doLog('APPROVE_DO', "User: {$this->actor()} | DO: {$do->no_do} (ID:{$id}) | Via: {$do->shipping_via} | Jumlah SKU: {$totalDetail} | Status: APPROVED");

            return response()->json([
                'success' => true,
                'message' => 'Delivery Order berhasil di-approve'
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            $noDoInfo = isset($do) ? $do->no_do : '-';
            $this->doLog('APPROVE_DO', "User: {$this->actor()} | DO: {$noDoInfo} (ID:{$id}) | Status: FAILED | Error: {$e->getMessage()}");

            return response()->json([
                'error' => $e->getMessage()
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
        $delivery_order = Tdo::findOrFail($id);
    
        $tdo_detail = \DB::table('tdo_detail as d')
        ->leftJoin('mproduct as p', function($join) {
            $join->on(\DB::raw("d.sku COLLATE utf8mb4_unicode_ci"), '=', 'p.sku');
        })
        ->where('d.id_do', $id)
        ->orderBy('d.seq', 'asc')
        ->select('d.*', 'p.nama_barang', 'p.SKU')
        ->get();
    
        $shipping_via = [
            '....'      => '....',
            'HANDCARRY' => 'HANDCARRY',
            'EKSPEDISI' => 'EKSPEDISI'
        ];
    
        return view(
            'pages.transaction.delivery_order.delivery_order_show',
            compact('delivery_order', 'tdo_detail', 'shipping_via')
        );
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    
    public function edit($id)
    {
        $delivery_order = Tdo::findOrFail($id);

        // DETAIL PRODUK DO
        $tdo_detail = DB::table('tdo_detail as d')
            ->leftJoin('mproduct as p', function($join) {
                $join->on(
                    DB::raw("d.sku COLLATE utf8mb4_unicode_ci"),
                    '=',
                    'p.sku'
                );
            })
            ->where('d.id_do', $id)
            ->orderBy('d.seq', 'asc')
            ->select('d.*', 'p.nama_barang', 'p.SKU')
            ->get();

        // MASTER PRODUCT (untuk ganti product)
        $products = DB::table('mproduct')
            ->select(['SKU','sku','nama_barang'])
            ->get();

        $shipping_via = [
            '....'      => '....',
            'HANDCARRY' => 'HANDCARRY',
            'EKSPEDISI' => 'EKSPEDISI'
        ];

        return view(
            'pages.transaction.delivery_order.delivery_order_edit',
            compact('delivery_order','tdo_detail','products','shipping_via')
        );
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
        $do = Tdo::findOrFail($id);

        $this->doLog('UPDATE_DO', "User: {$this->actor()} | DO: {$do->no_do} (ID:{$id}) | Flag Approve: {$do->flag_approve} | Status: PROCESS");

        /**
         * ==================================
         * CASE 1: SUDAH APPROVED
         * ==================================
         * HANYA BOLEH UPDATE NO_RESI
         */
        if ($do->flag_approve === 'Y') {

            $request->validate([
                'no_resi' => 'required|string|max:100'
            ]);

            $do->update([
                'no_resi' => $request->no_resi
            ]);

            $this->doLog('UPDATE_DO', "User: {$this->actor()} | DO: {$do->no_do} (ID:{$id}) | No Resi: {$request->no_resi} | Status: RESI_UPDATED");

            return redirect()
                ->back()
                ->with('success', 'No Resi berhasil diperbarui');
        }

        /**
         * ==================================
         * CASE 2: BELUM APPROVED
         * ==================================
         * BOLEH UPDATE SEMUA + DETAIL PRODUK
         */
        $request->validate([
            'shipping_via' => 'required',
            'reason_do'    => 'nullable|string',
            'sku'  => 'required|array',
            'qty'          => 'required|array'
        ]);

        DB::beginTransaction();
        try {

            // UPDATE HEADER
            $do->update([
                'shipping_via' => $request->shipping_via,
                'reason_do'    => $request->reason_do
            ]);

            // HAPUS DETAIL LAMA
            DB::table('tdo_detail')->where('id_do', $id)->delete();

            // INSERT DETAIL BARU
            $seq = 1;
            foreach ($request->sku as $i => $kode) {
                DB::table('tdo_detail')->insert([
                    'id_do'         => $id,
                    'sku'           => $kode,
                    'qty'           => $request->qty[$i],
                    'seq'           => str_pad($seq++, 4, '0', STR_PAD_LEFT),
                    'created_at'    => now(),
                    'updated_at'    => now()
                ]);
            }

            DB::commit();

            $skuCount = count($request->sku);
            $this->doLog('UPDATE_DO', "User: {$this->actor()} | DO: {$do->no_do} (ID:{$id}) | Via: {$request->shipping_via} | Jumlah SKU: {$skuCount} | Status: UPDATED");

            return redirect()
                ->route('delivery_order.index')
                ->with('success', 'Delivery Order berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->doLog('UPDATE_DO', "User: {$this->actor()} | DO: {$do->no_do} (ID:{$id}) | Status: FAILED | Error: {$e->getMessage()}");

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
    
    public function delete2($id)
    {
        $data = Tdo::with(['po'])->find($id);

        if (!$data) {
            $this->doLog('DELETE_DO', "User: {$this->actor()} | DO ID: {$id} | Status: FAILED | Error: Data tidak ditemukan");
            return redirect()->route('delivery_order.index');
        }

        $this->doLog('DELETE_DO', "User: {$this->actor()} | DO: {$data->no_do} (ID:{$id}) | Status: PROCESS");

        $data->delete();

        $this->doLog('DELETE_DO', "User: {$this->actor()} | DO: {$data->no_do} (ID:{$id}) | Status: DELETED");

        return redirect()->route('delivery_order.index');
    }

    public function bin2()
    {
        $data   ['data']      = Tdo::with(['po'])->onlyTrashed()->paginate(5);
        return view('pages.transaction.delivery_order.delivery_order_bin', $data)->with('i', (request()->input('page', 1) - 1) * 5);
    }

    public function bin2Data(Request $request)
    {
        if ($request->ajax()) {
            $data = Tdo::onlyTrashed()->with(['po']);
            return DataTables::of($data)
                ->addColumn('code_spl', fn($row) => $row->po->code_spl ?? '-')
                ->addColumn('nama_spl', fn($row) => $row->po->nama_spl ?? '-')
                ->addColumn('action', function ($row) {
                    return '<button data-id="' . $row->id . '" class="btn btn-sm btn-primary btn-rollback"><i class="fa fa-undo"></i></button>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }
    
    public function rollbackPost(Request $request)
    {
        $id = $request->id;
        $this->doLog('ROLLBACK_DO', "User: {$this->actor()} | DO ID: {$id} | Status: PROCESS");

        try {
            $tdo = Tdo::onlyTrashed()->where('id', $id)->first();

            if (!$tdo) {
                $this->doLog('ROLLBACK_DO', "User: {$this->actor()} | DO ID: {$id} | Status: FAILED | Error: Data tidak ditemukan atau sudah aktif");
                return response()->json(['message' => 'Data tidak ditemukan atau sudah aktif.'], 404);
            }

            $tdo->restore();

            $this->doLog('ROLLBACK_DO', "User: {$this->actor()} | DO: {$tdo->no_do} (ID:{$id}) | Status: RESTORED");

            return response()->json(['message' => 'Data berhasil dipulihkan.']);
        } catch (\Exception $e) {
            $this->doLog('ROLLBACK_DO', "User: {$this->actor()} | DO ID: {$id} | Status: FAILED | Error: {$e->getMessage()}");
            return response()->json(['message' => 'Gagal memulihkan data.'], 500);
        }
    }
    
    public function autoGenerate(Request $request)
    {
        $tgl = $request->tgl_do; // format YYYY-MM-DD
        $date = \Carbon\Carbon::parse($tgl);

        $yy   = $date->format('y');
        $day  = $date->format('d');

        // Konversi bulan ke romawi
        $romawi = [
            1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',6=>'VI',
            7=>'VII',8=>'VIII',9=>'IX',10=>'X',11=>'XI',12=>'XII'
        ];
        $bln_romawi = $romawi[$date->format('n')];

        // Cari nomor terakhir
        $prefix = "DO/{$yy}/{$bln_romawi}/{$day}/";

        $last = Tdo::where('no_do', 'LIKE', $prefix . '%')
                    ->orderBy('no_do', 'desc')
                    ->first();

        if ($last) {
            // Ambil 4 digit nomor urut di belakang
            $lastNumber = intval(substr($last->no_do, -4));
            $next = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $next = "0001";
        }

        $generated = $prefix . $next;

        return response()->json([
            'no_do' => $generated
        ]);
    }

    // public function destroy($id)
    // {
    //     $courier = MCourier::findOrFail($id);
    //     $courier->delete();

    //     return redirect('/couriers')->with('success', 'Courier is successfully deleted');
    // }
}