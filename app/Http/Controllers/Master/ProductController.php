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

class ProductController extends Controller
{
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

                // 👉 CODE = kode
                ->addColumn('kode', function ($row) {
                    $url = config('app.url') . '/product/' . $row->id;
                    return '<a href="'.$url.'" class="text-primary fw-bold">'.$row->kode.'</a>';
                })

                // 👉 SKU = sku
                ->addColumn('sku', fn($row) => $row->sku ?? '-')
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
                ->rawColumns(['kode','action'])
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
                'sku.required'              => 'SKU is required.',
                'sku.unique'                => 'SKU already exists. Please use another code.',
                'nama_barang.required'      => 'Product name is required.',
                'id_type.required'          => 'Product type must be selected.',
                'id_unit.required'          => 'Product unit must be selected.',
                'stock_minimum.required'    => 'Minimum stock is required.',
                'flag_active.required'      => 'Active status must be selected.'
            ]);

            // Save product to the database
            $product = Mproduct::create([
                'sku'               => $request->SKU,
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
                return response()->json([
                    'status' => 'success',
                    'message' => 'New product has been successfully added.'
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'An error occurred while saving the data. Please try again.'
                ], 500);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Collect all validation messages as an array
            $messages = [];
            foreach ($e->errors() as $field => $errorArray) {
                foreach ($errorArray as $errorMessage) {
                    $messages[] = $errorMessage;
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
        $sheet->setTitle('Template Product');

        // Header
        $sheet->setCellValue('A1', 'SKU');
        $sheet->setCellValue('B1', 'NAMA PRODUK');

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
                $SKU   = trim($row[0] ?? '');
                $nama   = trim($row[1] ?? '');
                // $SKU    = substr($kode, 4); // mulai dari index ke-4

                // if ($SKU == '' || $nama == '') continue;

                // Cek duplikat
                // $exists = Mproduct::where('sku', $SKU)->exists();
                // if (!$exists) {
                    Mproduct::create([
                        'nama_barang'   => $nama,
                        'id_unit'       => '1',
                        'id_type'       => '1',
                        'sku'           => $SKU,
                        'flag_active'   => 'Y',
                        'stock_minimum' => '1'
                    ]);
                    $inserted++;
                // }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Import berhasil. $inserted data Product ditambahkan."
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
        try {
            // Validate user input
            $this->validate($request, [
                'id_type'           => 'required',
                'id_unit'           => 'required',
                'stock_minimum'     => 'required',
                'flag_active'       => 'required'
            ], [
                'id_type.required'          => 'Product type must be selected.',
                'id_unit.required'          => 'Product unit must be selected.',
                'stock_minimum.required'    => 'Minimum stock is required.',
                'flag_active.required'      => 'Active status must be selected.'
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

            return response()->json([
                'status' => 'success',
                'message' => 'Product data has been successfully updated.'
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            $messages = [];
            foreach ($e->errors() as $field => $errors) {
                foreach ($errors as $msg) {
                    $messages[] = $msg;
                }
            }

            return response()->json([
                'status' => 'validation_error',
                'message' => 'There are errors in the submitted data.',
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