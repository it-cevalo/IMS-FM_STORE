<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mproduct;
use App\Models\MproductType;
use App\Models\MproductUnit;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use DB;
use App\Logs;
use Auth;

class ProductController extends Controller
{
    private function masterLog(string $section, string $content): void
    {
        try {
            (new Logs('Logs_Master_ProductController'))->write($section, $content);
        } catch (\Throwable $e) {
            \Log::error('[ProductController] Gagal menulis log: ' . $e->getMessage());
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
    public function index()
    {
        return view('pages.master.product.product_master.product_index');
    }
    
    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $query = Mproduct::with(['product_type', 'product_unit'])->select('mproduct.*');

            return DataTables::of($query)
            ->addIndexColumn()

            ->editColumn('sku', function ($row) {
                return $row->sku ?: '-';
            })
            ->addColumn('type', fn($row) => $row->product_type->nama_tipe ?? '-')
            ->addColumn('uom', fn($row) => $row->product_unit->nama_unit ?? '-')

            ->addColumn('action', function ($row) {
                $role = auth()->user()->position;
                $btn = '';

                if (in_array($role, ['MANAGER', 'SUPERADMIN', 'PURCHASING'])) {
                    $btn .= '<a href="'.route('product.edit', $row->id).'" class="btn btn-warning btn-sm">
                                <i class="fa fa-edit"></i>
                            </a>';
                }
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
        }

        abort(403);
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $product_type = MproductType::get();
        $product_unit = MproductUnit::get();
        return view('pages.master.product.product_master.product_create', compact('product_type','product_unit'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->masterLog('TAMBAH_PRODUK', "User: {$this->actor()} | SKU: {$request->sku} | Nama: {$request->nama_barang} | Status: PROCESS");

        try {
            // Validate user input
            $this->validate($request, [
                'sku'               => 'required|unique:mproduct,sku',
                'nama_barang'       => 'required',
                'id_type'           => 'required',
                'id_unit'           => 'required',
                'stock_minimum'     => 'required',
                'flag_active'       => 'required'
            ],[
                'sku.required'              => 'SKU wajib diisi.',
                'sku.unique'                => 'SKU telah digunakan',
                'nama_barang.required'      => 'Nama produk wajib diisi.',
                'id_type.required'          => 'Tipe produk wajib diisi.',
                'id_unit.required'          => 'Satuan produk wajib diisi',
                'stock_minimum.required'    => 'Stok minimal wajib diisi.',
                'flag_active.required'      => 'Status aktif wajib diisi.'
            ]);

            // Save product to the database
            $product = Mproduct::create([
                'sku'               => $request->sku,
                'nama_barang'       => $request->nama_barang,
                'id_type'           => $request->id_type,
                'id_unit'           => $request->id_unit,
                'harga_beli'        => 0,
                'harga_jual'        => 0,
                'stock_minimum'     => $request->stock_minimum,
                'harga_rata_rata'   => 0,
                'flag_active'       => $request->flag_active
            ]);

            if ($product) {
                $this->masterLog('TAMBAH_PRODUK', "User: {$this->actor()} | SKU: {$request->sku} | Nama: {$request->nama_barang} | Status: SUCCESS");
                return response()->json([
                    'status' => 'success',
                    'message' => 'Data produk telah berhasil ditambahkan.'
                ], 200);
            } else {
                $this->masterLog('TAMBAH_PRODUK', "User: {$this->actor()} | SKU: {$request->sku} | Status: FAILED");
                return response()->json([
                    'status' => 'error',
                    'message' => 'Terjadi kesalahan pada sistem. Silahkan coba lagi.'
                ], 500);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->masterLog('TAMBAH_PRODUK', "User: {$this->actor()} | SKU: {$request->sku} | Status: VALIDATION_ERROR | Error: " . implode(', ', array_merge(...array_values($e->errors()))));
            $messages = [];
            foreach ($e->errors() as $field => $errorArray) {
                foreach ($errorArray as $errorMessage) {
                    $messages[] = $errorMessage;
                }
            }

            return response()->json([
                'status' => 'validation_error',
                'message' => 'Gagal menyimpan data produk',
                'errors' => $messages
            ], 422);
        } catch (\Exception $e) {
            $this->masterLog('TAMBAH_PRODUK', "User: {$this->actor()} | SKU: {$request->sku} | Status: FAILED | Error: {$e->getMessage()}");
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada sistem. Silahkan coba lagi.',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
    
    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Product');
    
        // =========================
        // HEADER
        // =========================
        $headers = [
            'A1' => 'SKU (wajib)',
            'B1' => 'NAMA BARANG (wajib)',
            'C1' => 'TIPE BARANG',
            'D1' => 'SATUAN BARANG',
            'E1' => 'STOK MINIMAL',
            'F1' => 'STATUS AKTIF (Y/N)',
        ];
    
        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }
    
        // =========================
        // STYLE HEADER
        // =========================
        $headerStyle = [
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                ]
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'EAF1FF']
            ]
        ];
    
        $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);
    
        // =========================
        // COLUMN WIDTH
        // =========================
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(25);
    
        // =========================
        // OUTPUT FILE
        // =========================
        $writer = new Xlsx($spreadsheet);
        $filename = 'Template_Product.xlsx';
        $tempPath = storage_path('app/public/' . $filename);
        $writer->save($tempPath);
    
        return response()->download($tempPath)->deleteFileAfterSend(true);
    }    

    /**
     * Import data SKU dari file Excel
     */
    public function import(Request $request)
    {
        $this->masterLog('IMPORT_PRODUK', "User: {$this->actor()} | File: " . ($request->file('file') ? $request->file('file')->getClientOriginalName() : '-') . " | Status: PROCESS");

        $request->validate([
            'file' => 'required|mimes:xlsx|max:10240',
        ]);

        try {
            $path = $request->file('file')->getRealPath();
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
    
            DB::beginTransaction();
    
            $total       = 0;
            $inserted    = 0;
            $duplicated  = 0;
    
            foreach ($rows as $index => $row) {
                if ($index === 0) continue; // skip header
                $total++;
    
                $rowNumber = $index + 1;
    
                $SKU        = trim($row[0] ?? '');
                $nama       = trim($row[1] ?? '');
                $typeName   = trim($row[2] ?? '');
                $unitName   = trim($row[3] ?? '');
                $stockMin   = is_numeric($row[4] ?? null) ? $row[4] : 1;
                $flagActive = strtoupper(trim($row[5] ?? 'Y'));
    
                // =========================
                // VALIDASI SKU
                // =========================
                if ($SKU === '') {
                    DB::rollBack();
                    return response()->json([
                        'status'  => 'error',
                        'message' => "SKU wajib diisi. Cek baris ke-{$rowNumber}."
                    ], 422);
                }
    
                // =========================
                // CEK DUPLIKAT SKU
                // =========================
                if (Mproduct::where('sku', $SKU)->exists()) {
                    $duplicated++;
                    continue; // skip tapi lanjut import
                }
    
                // =========================
                // HANDLE PRODUCT TYPE
                // =========================
                $typeId = null;
                if ($typeName !== '') {
                    $type = MproductType::firstOrCreate(
                        ['nama_tipe' => $typeName],
                        ['created_at' => now(), 'updated_at' => now()]
                    );
                    $typeId = $type->id;
                }
    
                // =========================
                // HANDLE PRODUCT UNIT
                // =========================
                $unitId = null;
                if ($unitName !== '') {
                    $unit = MproductUnit::firstOrCreate(
                        ['nama_unit' => $unitName],
                        ['created_at' => now(), 'updated_at' => now()]
                    );
                    $unitId = $unit->id;
                }
    
                // =========================
                // INSERT PRODUCT
                // =========================
                Mproduct::create([
                    'sku'           => $SKU,
                    'nama_barang'   => $nama,
                    'id_type'       => $typeId,
                    'id_unit'       => $unitId,
                    'flag_active'   => in_array($flagActive, ['Y', 'N']) ? $flagActive : 'Y',
                    'stock_minimum' => $stockMin,
                ]);
    
                $inserted++;
            }
    
            DB::commit();
    
            // =========================
            // RESPONSE (UNTUK SWAL)
            // =========================
            $this->masterLog('IMPORT_PRODUK', "User: {$this->actor()} | Total: {$total} | Inserted: {$inserted} | Duplicated: {$duplicated} | Status: SUCCESS");
            return response()->json([
                'status'      => 'success',
                'message'     => 'Import selesai',
                'total'       => $total,
                'inserted'    => $inserted,
                'failed'      => $duplicated,
                'duplicated'  => $duplicated
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->masterLog('IMPORT_PRODUK', "User: {$this->actor()} | Status: FAILED | Error: {$e->getMessage()}");
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan saat import data.',
                'debug'   => config('app.debug') ? $e->getMessage() : null
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
        $products = Mproduct::findOrFail($id);

        $product_type = MproductType::get();
        $product_unit = MproductUnit::get();
        
        $flag_active = [
            '#' => '....',
            'Y' => 'Yes',
            'N' => 'No'
        ];
        return view('pages.master.product.product_master.product_show', compact('products','product_type','product_unit', 'flag_active'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $products = Mproduct::findOrFail($id);
    
        $product_type = MproductType::get();
        $product_unit = MproductUnit::get();
    
        // Get all SKU data from table msku (code + name)
        $msku = DB::table('msku')
            ->select('kode', 'nama')
            ->orderBy('nama', 'asc')
            ->get();
    
        $flag_active = [
            '#' => '....',
            'Y' => 'Yes',
            'N' => 'No'
        ];
    
        return view('pages.master.product.product_master.product_edit', compact(
            'products', 'product_type', 'product_unit', 'flag_active', 'msku'
        ));
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
        $this->masterLog('UBAH_PRODUK', "User: {$this->actor()} | ID: {$id} | Status: PROCESS");

        try {
            // Validate user input
            $this->validate($request, [
                'id_type'           => 'required',
                'id_unit'           => 'required',
                'stock_minimum'     => 'required',
                'flag_active'       => 'required'
            ], [
                'id_type.required'          => 'Tipe produk wajib diisi.',
                'id_unit.required'          => 'Satuan produk wajib diisi.',
                'stock_minimum.required'    => 'Stok minimal wajib diisi.',
                'flag_active.required'      => 'Status aktif wajib diisi.'
            ]);

            $product = Mproduct::findOrFail($id);

            $product->update([
                'id_type'           => $request->id_type,
                'id_unit'           => $request->id_unit,
                'harga_beli'        => 0,
                'harga_jual'        => 0,
                'stock_minimum'     => $request->stock_minimum,
                'flag_active'       => $request->flag_active
            ]);

            $this->masterLog('UBAH_PRODUK', "User: {$this->actor()} | ID: {$id} | SKU: {$product->sku} | Nama: {$product->nama_barang} | Status: SUCCESS");
            return response()->json([
                'status' => 'success',
                'message' => 'Data produk telah berhasil diubah.'
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->masterLog('UBAH_PRODUK', "User: {$this->actor()} | ID: {$id} | Status: VALIDATION_ERROR | Error: " . implode(', ', array_merge(...array_values($e->errors()))));
            $messages = [];
            foreach ($e->errors() as $field => $errors) {
                foreach ($errors as $msg) {
                    $messages[] = $msg;
                }
            }

            return response()->json([
                'status' => 'validation_error',
                'message' => 'Gagal menyimpan data produk.',
                'errors' => $messages
            ], 422);

        } catch (\Exception $e) {
            $this->masterLog('UBAH_PRODUK', "User: {$this->actor()} | ID: {$id} | Status: FAILED | Error: {$e->getMessage()}");
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada sistem. Silahkan coba lagi.',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
    
    public function getSku(Request $request)
    {
        $query = DB::table('msku')
            ->select('kode', 'nama');
    
        if ($request->filled('search')) {
            $query->where('kode', 'like', '%' . $request->search . '%')
                  ->orWhere('nama', 'like', '%' . $request->search . '%');
        } else {
            // If no search input, return first 10 data
            $query->limit(10);
        }
    
        $skuList = $query->get();
    
        return response()->json($skuList);
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