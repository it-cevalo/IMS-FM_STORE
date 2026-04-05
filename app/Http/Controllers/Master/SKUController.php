<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MSku;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\DB;
use App\Logs;
use Auth;

class SKUController extends Controller
{
    private function masterLog(string $section, string $content): void
    {
        try {
            (new Logs('Logs_Master_SKUController'))->write($section, $content);
        } catch (\Throwable $e) {
            \Log::error('[SKUController] Gagal menulis log: ' . $e->getMessage());
        }
    }

    private function actor(): string
    {
        $user = Auth::user();
        if (!$user) return 'Guest';
        return $user->username ?? $user->name ?? "ID:{$user->id}";
    }

    /**
     * Display a listing of the SKU resources.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages.master.product.sku.sku_index');
    }

    /**
     * Fetch data for DataTables (AJAX request).
     */
    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $query = MSku::query();
    
            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $editUrl = route('sku.edit', $row->kode);
                    $deleteUrl = route('sku.destroy', $row->kode);
    
                    return '
                        <div class="d-flex justify-content-center">
                            <a href="' . $editUrl . '" class="btn btn-sm btn-link text-primary" title="Edit">
                                <i class="fa fa-edit"></i>
                            </a>
                            <button type="button"
                                    data-url="' . $deleteUrl . '"
                                    class="btn btn-sm btn-link text-danger btnDeleteSku"
                                    title="Delete">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    ';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    
        return abort(403, 'Akses tidak diizinkan.');
    }
    
    /**
     * Show the form for creating a new SKU.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('pages.master.product.sku.sku_create');
    }

    /**
     * Store a newly created SKU in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->masterLog('TAMBAH_SKU', "User: {$this->actor()} | Kode: {$request->kode} | Status: PROCESS");

        try {
            $this->validate($request, [
                // 'nama' => 'required',
                'kode' => 'required|unique:msku,kode',
            ], [
                // 'nama.required' => 'SKU name wajib diisi.',
                'kode.required' => 'SKU wajib diisi.',
                'kode.unique'   => 'SKU sudah digunakan.'
            ]);
    
            $sku = MSku::create([
                'nama' => $request->nama ?? '-',
                'kode' => $request->kode
            ]);
    
            if ($sku) {
                $this->masterLog('TAMBAH_SKU', "User: {$this->actor()} | Kode: {$request->kode} | Status: SUCCESS");
                return response()->json([
                    'status' => 'success',
                    'message' => 'SKU telah berhasil ditambahkan.'
                ], 200);
            } else {
                $this->masterLog('TAMBAH_SKU', "User: {$this->actor()} | Kode: {$request->kode} | Status: FAILED");
                return response()->json([
                    'status' => 'error',
                    'message' => 'Terjadi kesalahan pada sistem. Silahkan coba lagi.'
                ], 500);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->masterLog('TAMBAH_SKU', "User: {$this->actor()} | Kode: {$request->kode} | Status: VALIDATION_ERROR | Error: " . implode(', ', array_merge(...array_values($e->errors()))));
            $messages = [];
            foreach ($e->errors() as $field => $errors) {
                foreach ($errors as $msg) {
                    $messages[] = $msg;
                }
            }

            return response()->json([
                'status' => 'validation_error',
                'message' => 'Gagal menambahkan data SKU',
                'errors' => $messages
            ], 422);
        } catch (\Exception $e) {
            $this->masterLog('TAMBAH_SKU', "User: {$this->actor()} | Kode: {$request->kode} | Status: FAILED | Error: {$e->getMessage()}");
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
        $sheet->setTitle('Template SKU');

        // Header
        $sheet->setCellValue('A1', 'SKU');
        // $sheet->setCellValue('B1', 'Keterangan');

        // Style header
        $headerStyle = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
        ];
        $sheet->getStyle('A1')->applyFromArray($headerStyle);
        $sheet->getColumnDimension('A')->setWidth(25);
        // $sheet->getColumnDimension('B')->setWidth(40);

        // Output file
        $writer = new Xlsx($spreadsheet);
        $filename = 'Template_SKU.xlsx';
        $tempPath = storage_path('app/public/' . $filename);
        $writer->save($tempPath);

        return response()->download($tempPath)->deleteFileAfterSend(true);
    }

    /**
     * Import data SKU dari file Excel
     */
    public function import(Request $request)
    {
        $this->masterLog('IMPORT_SKU', "User: {$this->actor()} | File: " . ($request->file('file') ? $request->file('file')->getClientOriginalName() : '-') . " | Status: PROCESS");

        $request->validate([
            'file' => 'required|mimes:xlsx|max:10240',
        ]);

        try {
            $path = $request->file('file')->getRealPath();
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            DB::beginTransaction();
            $inserted = 0;

            foreach ($rows as $index => $row) {
                if ($index == 0) continue; // skip header
                $kode = trim($row[0] ?? '');
                // $nama = trim($row[1] ?? '');

                if ($kode == '') continue;

                // Cek duplikat
                $exists = MSku::where('kode', $kode)->exists();
                if (!$exists) {
                    MSku::create([
                        'kode' => $kode,
                        // 'nama' => $nama,
                    ]);
                    $inserted++;
                }
            }

            DB::commit();

            $this->masterLog('IMPORT_SKU', "User: {$this->actor()} | Inserted: {$inserted} | Status: SUCCESS");
            return response()->json([
                'status' => 'success',
                'message' => "Import berhasil. $inserted data SKU ditambahkan."
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->masterLog('IMPORT_SKU', "User: {$this->actor()} | Status: FAILED | Error: {$e->getMessage()}");
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat memasukan data.',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Display the specified SKU resource.
     *
     * @param  string  $kode
     * @return \Illuminate\Http\Response
     */
    public function show($kode)
    {
        //
    }

    /**
     * Show the form for editing the specified SKU.
     *
     * @param  string  $kode
     * @return \Illuminate\Http\Response
     */
    public function edit($kode)
    {
        $sku = MSku::where('kode', $kode)->firstOrFail();
        return view('pages.master.product.sku.sku_edit', compact('sku'));
    }

    /**
     * Update the specified SKU in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $kode
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $kode)
    {
        $this->masterLog('UBAH_SKU', "User: {$this->actor()} | Kode: {$kode} | Status: PROCESS");

        $request->validate(
            [
                'nama' => 'required|string|max:150',
            ],
            [
                'nama.required' => 'Nama SKU wajib diisi',
                'nama.max'      => 'Nama SKU maksimal 150 karakter',
            ]
        );

        try {
            $sku = MSku::findOrFail($kode);

            $sku->update([
                'nama' => $request->nama,
            ]);

            $this->masterLog('UBAH_SKU', "User: {$this->actor()} | Kode: {$kode} | Nama: {$request->nama} | Status: SUCCESS");
            return response()->json([
                'status'  => 'success',
                'message' => 'Data SKU berhasil diperbarui'
            ]);
        } catch (\Exception $e) {
            $this->masterLog('UBAH_SKU', "User: {$this->actor()} | Kode: {$kode} | Status: FAILED | Error: {$e->getMessage()}");
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal memperbarui data SKU'
            ], 500);
        }
    }
    
    /**
     * Remove the specified SKU from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $kode = $request->kode;

        try {
            $sku = MSku::where('kode', $kode)->firstOrFail();
            $this->masterLog('HAPUS_SKU', "User: {$this->actor()} | Kode: {$kode} | Status: DELETED");
            $sku->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Data SKU telah berhasil dihapus.'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->masterLog('HAPUS_SKU', "User: {$this->actor()} | Kode: {$kode} | Status: FAILED | Error: Data tidak ditemukan");
            return response()->json([
                'status' => 'error',
                'message' => 'Data SKU tidak ditemukan.'
            ], 404);
        } catch (\Exception $e) {
            $this->masterLog('HAPUS_SKU', "User: {$this->actor()} | Kode: {$kode} | Status: FAILED | Error: {$e->getMessage()}");
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada sistem. Silahkan coba lagi.',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }    
}