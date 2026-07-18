<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Helpers\Permission;
use Illuminate\Http\Request;
use App\Models\TStockOpname;
use App\Models\HStockOpname;
use App\Models\Mproduct;
use App\Models\MproductStock;
use App\Models\MWarehouse;
use Auth, DB;
use App\Logs;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Exports\StockOpnameExport;
use Maatwebsite\Excel\Facades\Excel;

class StockOpnameController extends Controller
{
    private function activityLog(string $section, string $content): void
    {
        try {
            (new Logs('Logs_StockOpnameController'))->write($section, $content);
        } catch (\Throwable $e) {
            \Log::error('[StockOpnameController] Gagal menulis log: ' . $e->getMessage());
        }
    }

    private function actor(): string
    {
        $user = Auth::user();
        if (!$user) return 'Guest';
        return $user->username ?? $user->name ?? "ID:{$user->id}";
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    private function isOwner(): bool
    {
        $user = Auth::user();
        return $user && $user->role && strtolower($user->role->name) === 'owner';
    }

    public function index()
    {
        $products  = Mproduct::all();
        $canEdit   = $this->isOwner();
        return view('pages.stock.stock_opname.stock_opname_index', compact('products', 'canEdit'));
    }
    
    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $query = TStockOpname::with(['warehouse', 'product', 'creator', 'updater'])->orderBy('t_stock_opname.created_at', 'desc');

            if ($request->product_id) {
                $query->where('id_product', $request->product_id);
            }

            if ($request->fd && $request->td) {
                $query->whereBetween('tgl_opname', [$request->fd, $request->td]);
            }

            $query->orderBy('t_stock_opname.created_at', 'desc');

            return DataTables::of($query)
                ->addColumn('opname_id', fn($row) => $row->getKey())
                ->addColumn('warehouse_code', fn($row) => $row->warehouse->code_wh ?? '-')
                ->addColumn('warehouse_name', fn($row) => $row->warehouse->nama_wh ?? '-')
                ->addColumn('product_code', fn($row) => $row->product->sku ?? '-')
                ->addColumn('product_name', fn($row) => $row->product->nama_barang ?? '-')
                ->addColumn('qty_last', fn($row) => $row->qty_last)
                ->addColumn('tgl_opname', fn($row) => $row->tgl_opname)
                // created_by/updated_by NULL = perubahan dari sistem WMS, bukan user aplikasi
                ->addColumn('created_by_name', fn($row) => $row->creator->username ?? 'SYSTEM')
                ->addColumn('updated_by_name', fn($row) => $row->updater->username ?? 'SYSTEM')
                ->make(true);
        }

        return abort(403);
    }

    public function history($id){
        $stock_opname_his = HStockOpname::where('id_stock_opname', $id)->get(); 
        $products = Mproduct::get();
        return view('pages.stock.stock_opname.stock_opname_his',compact('stock_opname_his','products'))->with('i', (request()->input('page', 1) - 1) * 5);
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        abort(403, 'Penambahan stock opname tidak diizinkan.');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        abort(403, 'Penambahan stock opname tidak diizinkan.');
        $product = Mproduct::select('kode_barang', 'nama_barang')->find($request->id_product);
        $kode = $product->kode_barang ?? '-';
        $nama = $product->nama_barang ?? '-';

        $this->activityLog('TAMBAH_OPNAME', "User: {$this->actor()} | Kode: {$kode} | Nama: {$nama} | Gudang ID: {$request->id_warehouse} | Qty Last: {$request->qty_last} | Status: PROCESS");

        try {
            $this->validate($request, [
                'id_product'   => 'required',
                'id_warehouse' => 'required',
                'qty_in'       => 'required',
                'qty_out'      => 'required',
                'qty_last'     => 'required',
                'tgl_opname'   => 'required',
            ], [
                'id_product.required'   => 'Produk wajib dipilih',
                'id_warehouse.required' => 'Gudang wajib dipilih',
                'qty_in.required'       => 'Qty In wajib diisi',
                'qty_out.required'      => 'Qty Out wajib diisi',
                'qty_last.required'     => 'Qty Last wajib diisi',
                'tgl_opname.required'   => 'Tanggal opname wajib diisi',
            ]);

            DB::beginTransaction();

            $userId = Auth::user()->id;

            $stock_opname = TStockOpname::create([
                'id_product'   => $request->id_product,
                'id_warehouse' => $request->id_warehouse,
                'qty_in'       => $request->qty_in,
                'qty_out'      => $request->qty_out,
                'qty_last'     => $request->qty_last,
                'tgl_opname'   => $request->tgl_opname,
                'created_by'   => $userId,
            ]);

            $id_stock_opn = TStockOpname::select('id')->latest()->first()->id;
            $date         = date('Y-m-d');

            HStockOpname::create([
                'id_stock_opname' => $id_stock_opn,
                'id_product'      => $request->id_product,
                'id_warehouse'    => $request->id_warehouse,
                'qty_in'          => $request->qty_in,
                'qty_out'         => $request->qty_out,
                'qty_last'        => $request->qty_last,
                'tgl_opname'      => $request->tgl_opname,
                'created_by'      => $userId,
                'created_at'      => $date,
            ]);

            MproductStock::create([
                'id_product'   => $request->id_product,
                'id_warehouse' => $request->id_warehouse,
                'qty_last'     => $request->qty_last,
                'tgl_opname'   => $request->tgl_opname,
                'tgl_mutasi'   => '1970-01-01',
            ]);

            DB::commit();

            $this->activityLog('TAMBAH_OPNAME', "User: {$this->actor()} | Kode: {$kode} | Nama: {$nama} | Gudang ID: {$request->id_warehouse} | Qty Last: {$request->qty_last} | Status: SUCCESS");

            return redirect()->route('stock_opname.index')->with('success', 'Stock Opname berhasil ditambahkan');

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->activityLog('TAMBAH_OPNAME', "User: {$this->actor()} | Kode: {$kode} | Nama: {$nama} | Status: VALIDATION_ERROR | Error: " . json_encode($e->errors()));
            throw $e;

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->activityLog('TAMBAH_OPNAME', "User: {$this->actor()} | Kode: {$kode} | Nama: {$nama} | Status: FAILED | Error: {$e->getMessage()} | File: {$e->getFile()}:{$e->getLine()}");
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan sistem. Silakan coba lagi.');
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
        if (!$this->isOwner()) {
            abort(403, 'Akses ditolak. Hanya Owner yang dapat mengedit Stock Opname.');
        }

        $stock_opname = TStockOpname::with(['product', 'creator', 'updater'])->findOrFail($id);
        $warehouse    = MWarehouse::get();
        $product      = Mproduct::get();

        $sku      = $stock_opname->product->sku ?? null;
        $qrCount  = $sku
            ? DB::table('tproduct_qr')->where('sku', $sku)->where('status', '!=', 'OUT')->count()
            : 0;

        return view('pages.stock.stock_opname.stock_opname_edit', compact('stock_opname', 'warehouse', 'product', 'qrCount'));
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
        if (!$this->isOwner()) {
            abort(403, 'Akses ditolak. Hanya Owner yang dapat mengedit Stock Opname.');
        }

        $existing = TStockOpname::with('product')->find($id);
        $kode = $existing->product->kode_barang ?? '-';
        $nama = $existing->product->nama_barang ?? '-';

        $this->activityLog('UBAH_OPNAME', "User: {$this->actor()} | ID: {$id} | Kode: {$kode} | Nama: {$nama} | Qty Last: {$request->qty_last} | Status: PROCESS");

        try {
            $validatedData = $request->validate([
                'qty_in'     => 'required',
                'qty_out'    => 'required',
                'qty_last'   => 'required',
                'tgl_opname' => 'required',
            ], [
                'qty_in.required'     => 'Qty In wajib diisi',
                'qty_out.required'    => 'Qty Out wajib diisi',
                'qty_last.required'   => 'Qty Last wajib diisi',
                'tgl_opname.required' => 'Tanggal opname wajib diisi',
            ]);

            DB::beginTransaction();

            $userId = Auth::user()->id;
            $date   = date('Y-m-d');

            $validatedData['updated_by'] = $userId;

            TStockOpname::whereId($id)->update($validatedData);

            $id_stock_opn = TStockOpname::select('id')->whereId($id)->latest()->first()->id;

            HStockOpname::create([
                'id_stock_opname' => $id_stock_opn,
                'id_product'      => $request->id_product,
                'id_warehouse'    => $request->id_warehouse,
                'qty_in'          => $request->qty_in,
                'qty_out'         => $request->qty_out,
                'qty_last'        => $request->qty_last,
                'tgl_opname'      => $request->tgl_opname,
                'created_by'      => $userId,
                'created_at'      => $date,
                'updated_by'      => $userId,
            ]);

            MproductStock::updateOrCreate(
                [
                    'id_product'   => $request->id_product,
                    'id_warehouse' => $request->id_warehouse,
                ],
                [
                    'qty_last'   => $request->qty_last,
                    'tgl_opname' => $request->tgl_opname,
                    'tgl_mutasi' => '1970-01-01',
                ]
            );

            DB::commit();

            $this->activityLog('UBAH_OPNAME', "User: {$this->actor()} | ID: {$id} | Kode: {$kode} | Nama: {$nama} | Qty Last: {$request->qty_last} | Status: SUCCESS");

            return redirect()->route('stock_opname.index')->with('success', 'Stock Opname berhasil diperbarui');

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->activityLog('UBAH_OPNAME', "User: {$this->actor()} | ID: {$id} | Kode: {$kode} | Nama: {$nama} | Status: VALIDATION_ERROR | Error: " . json_encode($e->errors()));
            throw $e;

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->activityLog('UBAH_OPNAME', "User: {$this->actor()} | ID: {$id} | Kode: {$kode} | Nama: {$nama} | Status: FAILED | Error: {$e->getMessage()} | File: {$e->getFile()}:{$e->getLine()}");
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan sistem. Silakan coba lagi.');
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
    
    public function qrHistory(Request $request)
    {
        $sku = $request->sku;
        if (!$sku) {
            return response()->json(['error' => 'SKU tidak ditemukan'], 422);
        }

        $rows = DB::table('tproduct_qr as q')
            // inbound utama (PO / SALDO_AWAL)
            ->leftJoin(
                DB::raw('(SELECT qr_code, received_at, id_po, inbound_source, sync_at FROM tproduct_inbound WHERE inbound_source != "RETUR_CUST" OR inbound_source IS NULL) as i'),
                DB::raw('i.qr_code COLLATE utf8mb4_unicode_ci'), '=', DB::raw('q.qr_code COLLATE utf8mb4_unicode_ci')
            )
            // PO header
            ->leftJoin('tpos as po', 'po.id', '=', 'i.id_po')
            // inbound retur dari customer
            ->leftJoin(
                DB::raw('(SELECT qr_code, received_at, id_po, sync_at FROM tproduct_inbound WHERE inbound_source = "RETUR_CUST") as rc'),
                DB::raw('rc.qr_code COLLATE utf8mb4_unicode_ci'), '=', DB::raw('q.qr_code COLLATE utf8mb4_unicode_ci')
            )
            // outbound
            ->leftJoin(
                DB::raw('tproduct_outbound as o'),
                DB::raw('o.qr_code COLLATE utf8mb4_unicode_ci'), '=', DB::raw('q.qr_code COLLATE utf8mb4_unicode_ci')
            )
            // DO header
            ->leftJoin('tdos as d', 'd.id', '=', 'o.id_do')
            // supplier dari PO
            ->leftJoin('m_suppliers as po_spl', 'po_spl.id', '=', 'po.id_supplier')
            // supplier dari DO (untuk retur ke supplier)
            ->leftJoin('m_suppliers as do_spl', 'do_spl.id', '=', 'd.id_supplier')
            ->where(DB::raw('q.sku COLLATE utf8mb4_unicode_ci'), $sku)
            ->select([
                'q.qr_code',
                'q.sequence_no',
                'q.status',
                // inbound
                'i.received_at',
                'i.inbound_source',
                'po.no_po',
                'po.tgl_po',
                'po.nama_spl as po_supplier_name',
                // retur dari customer
                'rc.received_at as retur_cust_at',
                // outbound
                'o.out_at',
                'd.no_do',
                'd.tgl_do',
                'd.do_source',
                'd.nama_cust',
                // retur ke supplier (DO dengan prefix RT-DO)
                'do_spl.nama_spl as do_supplier_name',
                'd.approve_date as do_approve_date',
            ])
            ->orderByRaw('CAST(q.sequence_no AS UNSIGNED)')
            ->get();

        return response()->json($rows);
    }

    public function printQRAwalByProductRange(Request $request)
    {
        $request->validate([
            'from' => 'required|integer|min:1',
            'to'   => 'required|integer|gte:from',
        ]);
    
        $from = $request->from;
        $to   = $request->to;
    
        /**
         * ===============================
         * AMBIL QR SALDO AWAL BY RANGE PRODUCT
         * ===============================
         */
        $rows = DB::table('tproduct_qr as q')
            ->join(
                'tproduct_inbound as i',
                DB::raw('i.qr_code COLLATE utf8mb4_unicode_ci'),
                '=',
                DB::raw('q.qr_code COLLATE utf8mb4_unicode_ci')
            )
            ->where('i.inbound_source', 'SALDO_AWAL')
            ->whereNotNull('q.sequence_no')
            ->where('q.sequence_no', '!=', '')
            ->whereBetween('q.id_product', [$from, $to])
            ->orderBy('q.id_product')
            ->orderByRaw('CAST(q.sequence_no AS UNSIGNED)')
            ->select(
                'q.sku',
                'q.nama_barang',
                'q.sequence_no',
                'q.qr_code'
            )
            ->get();
    
        if ($rows->isEmpty()) {
            abort(404, 'QR Saldo Awal tidak ditemukan pada range tersebut');
        }
    
        /**
         * ===============================
         * FORMAT DATA
         * ===============================
         */
        $qrList = $rows->map(fn ($r) => [
            'sku'          => $r->sku,
            'nama_barang' => $r->nama_barang,
            'nomor_urut'  => $r->sequence_no,
            'qr_payload'  => $r->qr_code,
        ])->toArray();
    
        /**
         * ===============================
         * LABEL SIZE (33 x 15 mm)
         * ===============================
         */
        $width  = 33 * 2.83465;
        $height = 15 * 2.83465;
    
        $pdf = Pdf::loadView(
            'pages.stock.stock_opname.stock_opname_qrcode',
            [
                'qrList' => $qrList
            ]
        )->setPaper([0, 0, $width, $height], 'portrait');
    
        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header(
                'Content-Disposition',
                "inline; filename=QR_SALDO_AWAL_{$from}_TO_{$to}.pdf"
            );
    }

    public function printQRAwalBySKU(Request $request)
    {
        $request->validate([
            'sku' => 'required|string',
        ]);

        $sku = $request->sku;

        /**
         * ===============================
         * AMBIL QR SALDO AWAL BY RANGE SKU
         * ===============================
         */
        $rows = DB::table('tproduct_qr as q')
            ->join(
                'tproduct_inbound as i',
                DB::raw('i.qr_code COLLATE utf8mb4_unicode_ci'),
                '=',
                DB::raw('q.qr_code COLLATE utf8mb4_unicode_ci')
            )
            ->where('i.inbound_source', 'SALDO_AWAL')
            ->whereNotNull('q.sequence_no')
            ->where('q.sequence_no', '!=', '')
            ->where('q.sku', $sku)
            ->orderBy('q.sku')
            ->orderByRaw('CAST(q.sequence_no AS UNSIGNED)')
            ->select(
                'q.sku',
                'q.nama_barang',
                'q.sequence_no',
                'q.qr_code'
            )
            ->get();

        if ($rows->isEmpty()) {
            abort(404, 'QR Saldo Awal tidak ditemukan pada range SKU tersebut');
        }

        /**
         * ===============================
         * FORMAT DATA
         * ===============================
         */
        $qrList = $rows->map(fn ($r) => [
            'sku'         => $r->sku,
            'nama_barang' => $r->nama_barang,
            'nomor_urut'  => $r->sequence_no,
            'qr_payload'  => $r->qr_code,
        ])->toArray();

        /**
         * ===============================
         * LABEL SIZE (33 x 15 mm)
         * ===============================
         */
        $width  = 33 * 2.83465;
        $height = 15 * 2.83465;

        $pdf = Pdf::loadView(
            'pages.stock.stock_opname.stock_opname_qrcode',
            [
                'qrList' => $qrList
            ]
        )->setPaper([0, 0, $width, $height], 'portrait');

        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header(
                'Content-Disposition',
                "inline; filename=QR_SALDO_AWAL_SKU_{$sku}.pdf"
            );
    }

    public function exportExcel()
    {
        return Excel::download(
            new StockOpnameExport(Auth::user()->name),
            'stock_opname_' . date('Ymd_His') . '.xlsx'
        );
    }
}