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

class SKUController extends Controller
{
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
    
        return abort(403, 'Unauthorized access.');
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
        try {
            $this->validate($request, [
                'nama' => 'required',
                'kode' => 'required|unique:msku,kode',
            ], [
                'nama.required' => 'SKU name is required.',
                'kode.required' => 'SKU is required.',
                'kode.unique'   => 'SKU already exists. Please use another SKU.'
            ]);
    
            $sku = MSku::create([
                'nama' => $request->nama,
                'kode' => $request->kode
            ]);
    
            if ($sku) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'SKU has been successfully added.'
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'An error occurred while saving the data.'
                ], 500);
            }
    
        } catch (\Illuminate\Validation\ValidationException $e) {
            $messages = [];
            foreach ($e->errors() as $field => $errors) {
                foreach ($errors as $msg) {
                    $messages[] = $msg;
                }
            }
    
            return response()->json([
                'status' => 'validation_error',
                'message' => 'Invalid input',
                'errors' => $messages
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'A system error occurred. Please try again later.',
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
        $sheet->setCellValue('A1', 'Kode');
        $sheet->setCellValue('B1', 'Keterangan');

        // Style header
        $headerStyle = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
        ];
        $sheet->getStyle('A1:B1')->applyFromArray($headerStyle);
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(40);

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
                $nama = trim($row[1] ?? '');

                if ($kode == '' || $nama == '') continue;

                // Cek duplikat
                $exists = MSku::where('kode', $kode)->exists();
                if (!$exists) {
                    MSku::create([
                        'kode' => $kode,
                        'nama' => $nama,
                    ]);
                    $inserted++;
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Import berhasil. $inserted data SKU ditambahkan."
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat import data.',
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
        // (You can add your update logic here)
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
            $sku->delete();
    
            return response()->json([
                'status' => 'success',
                'message' => 'SKU data has been successfully deleted.'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'SKU data not found.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while deleting the data.',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }    
}