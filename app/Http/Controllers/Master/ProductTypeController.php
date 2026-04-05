<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MproductType;
use Yajra\DataTables\Facades\DataTables;
use App\Logs;
use Auth;

class ProductTypeController extends Controller
{
    private function masterLog(string $section, string $content): void
    {
        try {
            (new Logs('Logs_Master_ProductTypeController'))->write($section, $content);
        } catch (\Throwable $e) {
            \Log::error('[ProductTypeController] Gagal menulis log: ' . $e->getMessage());
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
        return view('pages.master.product.product_type.product_type_index');
    }

    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $query = MproductType::query();

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $editUrl   = route('product_type.edit', $row->id);
                    $deleteUrl = route('product_type.destroy', $row->id);
                
                    return '
                        <a href="'.$editUrl.'" class="btn btn-link btn-sm">
                            <i class="fa fa-edit"></i>
                        </a>
                        <button 
                            type="button"
                            class="btn btn-link text-danger btnDelete"
                            data-url="'.$deleteUrl.'">
                            <i class="fa fa-trash"></i>
                        </button>
                    ';
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
        return view('pages.master.product.product_type.product_type_create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->masterLog('TAMBAH_TIPE_PRODUK', "User: {$this->actor()} | Nama Tipe: {$request->nama_tipe} | Status: PROCESS");

        try {
            $this->validate($request, [
                'nama_tipe' => 'required|unique:mproduct_type,nama_tipe'
            ], [
                'nama_tipe.required' => 'Nama Tipe Produk wajib diisi.',
                'nama_tipe.unique'   => 'Tipe Produk sudah digunakan.'
            ]);
    
            $product_type = MproductType::create([
                'nama_tipe' => $request->nama_tipe
            ]);
    
            if ($product_type) {
                $this->masterLog('TAMBAH_TIPE_PRODUK', "User: {$this->actor()} | Nama Tipe: {$request->nama_tipe} | Status: SUCCESS");
                return response()->json([
                    'status' => 'success',
                    'message' => 'Tipe Poduk telah berhasil ditambahkan!'
                ], 200);
            } else {
                $this->masterLog('TAMBAH_TIPE_PRODUK', "User: {$this->actor()} | Nama Tipe: {$request->nama_tipe} | Status: FAILED");
                return response()->json([
                    'status' => 'error',
                    'message' => 'Terjadi kesalahan pada sistem. Silahkan coba lagi.'
                ], 500);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->masterLog('TAMBAH_TIPE_PRODUK', "User: {$this->actor()} | Nama Tipe: {$request->nama_tipe} | Status: VALIDATION_ERROR | Error: " . implode(', ', array_merge(...array_values($e->errors()))));
            $messages = [];
            foreach ($e->errors() as $field => $errors) {
                foreach ($errors as $msg) {
                    $messages[] = $msg;
                }
            }

            return response()->json([
                'status' => 'validation_error',
                'message' => 'Gagal menambahkan data tipe produk.',
                'errors' => $messages
            ], 422);
        } catch (\Exception $e) {
            $this->masterLog('TAMBAH_TIPE_PRODUK', "User: {$this->actor()} | Nama Tipe: {$request->nama_tipe} | Status: FAILED | Error: {$e->getMessage()}");
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada sistem. Silahkan coba lagi. Silahkan coba lagi nanti.',
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
        $product_type = MproductType::findOrFail($id);

        return view('pages.master.product.product_type.product_type_edit',compact('product_type'));
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
        $this->masterLog('UBAH_TIPE_PRODUK', "User: {$this->actor()} | ID: {$id} | Nama Tipe: {$request->nama_tipe} | Status: PROCESS");

        try {
            $this->validate($request, [
                'nama_tipe' => 'required|unique:mproduct_type,nama_tipe'
            ], [
                'nama_tipe.required' => 'Nama Tipe Produk wajib diisi.',
                'nama_tipe.unique'   => 'Tipe Produk wajib diisi.'
            ]);
    
            $updated = MproductType::whereId($id)->update([
                'nama_tipe' => $request->nama_tipe
            ]);
    
            if ($updated) {
                $this->masterLog('UBAH_TIPE_PRODUK', "User: {$this->actor()} | ID: {$id} | Nama Tipe: {$request->nama_tipe} | Status: SUCCESS");
                return response()->json([
                    'status' => 'success',
                    'message' => 'Tipe Produk telah berhasil diubah!'
                ]);
            } else {
                $this->masterLog('UBAH_TIPE_PRODUK', "User: {$this->actor()} | ID: {$id} | Nama Tipe: {$request->nama_tipe} | Status: FAILED");
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal mengubah Tipe Produk.'
                ], 500);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->masterLog('UBAH_TIPE_PRODUK', "User: {$this->actor()} | ID: {$id} | Nama Tipe: {$request->nama_tipe} | Status: VALIDATION_ERROR | Error: " . implode(', ', array_merge(...array_values($e->errors()))));
            $messages = [];
            foreach ($e->errors() as $field => $errors) {
                foreach ($errors as $msg) {
                    $messages[] = $msg;
                }
            }

            return response()->json([
                'status' => 'validation_error',
                'message' => 'Gagal menambahkan data tipe produk.',
                'errors' => $messages
            ], 422);
        } catch (\Exception $e) {
            $this->masterLog('UBAH_TIPE_PRODUK', "User: {$this->actor()} | ID: {$id} | Nama Tipe: {$request->nama_tipe} | Status: FAILED | Error: {$e->getMessage()}");
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada sistem. Silahkan coba lagi. Silahkan coba lagi nanti.',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
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
        try {
            $product_type = MproductType::findOrFail($id);
            $this->masterLog('HAPUS_TIPE_PRODUK', "User: {$this->actor()} | ID: {$id} | Nama Tipe: {$product_type->nama_tipe} | Status: DELETED");
            $product_type->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Tipe Produk telah berhasil dihapus!'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->masterLog('HAPUS_TIPE_PRODUK', "User: {$this->actor()} | ID: {$id} | Status: FAILED | Error: Data tidak ditemukan");
            return response()->json([
                'status' => 'error',
                'message' => 'Tipe Produk tidak ditemukan.'
            ], 404);

        } catch (\Exception $e) {
            $this->masterLog('HAPUS_TIPE_PRODUK', "User: {$this->actor()} | ID: {$id} | Status: FAILED | Error: {$e->getMessage()}");
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada sistem. Silahkan coba lagi. Silahkan coba lagi nanti.',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}