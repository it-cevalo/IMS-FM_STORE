<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MproductUnit;
use Yajra\DataTables\Facades\DataTables;

class ProductUnitController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages.master.product.product_unit.product_unit_index');
    }

    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $query = MproductUnit::query();

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $editUrl = route('product_unit.edit', $row->id);
                    $deleteUrl = route('product_unit.destroy', $row->id);

                    return '
                        <form action="' . $deleteUrl . '" method="POST" class="formDelete" style="display:inline;">
                            ' . csrf_field() . method_field('DELETE') . '
                            <a href="' . $editUrl . '" class="btn btn-link btn-sm"><i class="fa fa-edit"></i></a>
                            <button type="button" class="btn btn-link text-danger btnDeleteProductUnit">
                                <i class="fa fa-trash"></i>
                            </button>
                        </form>';
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
    public function create()
    {
        return view('pages.master.product.product_unit.product_unit_create');
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
            // Validate input
            $this->validate($request, [
                'nama_unit' => 'required|unique:mproduct_unit,nama_unit'
            ], [
                'nama_unit.required' => 'Satuan Produk harus diisi.',
                'nama_unit.unique'   => 'Satuan Produk telah digunakan.',
            ]);
    
            // Attempt to create new product unit
            $product_unit = MproductUnit::create([
                'nama_unit' => $request->nama_unit
            ]);
    
            // Success response
            if ($product_unit) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Satuan Produk telah berhasil ditambahkan!'
                ], 200);
            }
    
            // Failed to create record (unexpected reason)
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menambahkan Satuan Produk. Silahkan coba lagi nanti.'
            ], 500);
    
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors
            $messages = [];
            foreach ($e->errors() as $field => $errors) {
                foreach ($errors as $msg) {
                    $messages[] = $msg;
                }
            }
    
            return response()->json([
                'status' => 'validation_error',
                'message' => 'Gagal menambahkan Satuan Produk. Slahkan coba lagi nanti.',
                'errors' => $messages
            ], 422);
    
        } catch (\Exception $e) {
            // Handle any unexpected system errors
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada sistem. Silahkan coba lagi nanti.',
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
        $product_unit = MproductUnit::findOrFail($id);

        return view('pages.master.product.product_unit.product_unit_edit',compact('product_unit'));
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
            // Validate input
            $this->validate($request, [
                'nama_unit' => 'required|unique:mproduct_unit,nama_unit'
            ], [
                'nama_unit.required' => 'Satuan Produk harus diisi.',
                'nama_unit.unique'   => 'Satuan Produk sudah digunakan.'
            ]);
            
            // Attempt to update record
            $updated = MproductUnit::whereId($id)->update([
                'nama_unit' => $request->nama_unit
            ]);

            if ($updated) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Satuan Produk telah berhasil diubah!'
                ], 200);
            }

            // No rows affected (possibly invalid ID)
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengubah Satuan Produk. Silahkan coba lagi nanti.'
            ], 500);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation errors
            $messages = [];
            foreach ($e->errors() as $field => $errors) {
                foreach ($errors as $msg) {
                    $messages[] = $msg;
                }
            }

            return response()->json([
                'status' => 'validation_error',
                'message' => 'Gagal menampilkan Satuan Produk.',
                'errors' => $messages
            ], 422);

        } catch (\Exception $e) {
            // System error
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada sistem. Silahkan coba lagi. Silahkan coba lagi nanti.',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $product_unit = MproductUnit::findOrFail($id);
            $product_unit->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Satuan Produk telah berhasil dihapus!'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Satuan Produk tidak ditemukan.'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada sistem. Silahkan coba lagi. Silahkan coba lagi nanti.',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

}