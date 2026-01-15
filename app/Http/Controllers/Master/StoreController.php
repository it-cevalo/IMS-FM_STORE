<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MStore;
use Yajra\DataTables\Facades\DataTables;

class StoreController extends Controller
{
    public function index()
    {
        return view('pages.master.store.store_index');
    }

    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $query = MStore::query();

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $editUrl = route('stores.edit', $row->id);
                    $deleteUrl = route('stores.destroy', $row->id);

                    return '
                        <div class="d-flex justify-content-center">
                            <a href="' . $editUrl . '" class="btn btn-sm btn-link text-primary" title="Edit">
                                <i class="fa fa-edit"></i>
                            </a>
                            <button type="button"
                                    data-url="' . $deleteUrl . '"
                                    class="btn btn-sm btn-link text-danger btnDeleteStore"
                                    title="Delete">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    ';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return abort(403);
    }

    public function create()
    {
        return view('pages.master.store.store_create');
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'code_store' => 'required|unique:m_stores,code_store',
                'nama_store' => 'required',
                'address' => 'required',
                'phone' => 'required|max:15',
                'email' => 'required|email',
            ], [
                'code_store.required' => 'Kode Toko harus diisi.',
                'code_store.unique'   => 'Kode Toko telah digunakan.',
                'nama_store.required' => 'Nama Toko harus diisi.',
                'address.required'    => 'Alamat Toko harus diisi.',
                'phone.required'      => 'No HP harus diisi.',
                'email.required'      => 'Alamat Email harus diisi.',
                'email.email'         => 'Format Email Toko salah.',
            ]);

            $store = MStore::create([
                'code_store' => $request->code_store,
                'nama_store' => $request->nama_store,
                'address'    => $request->address,
                'phone'      => $request->phone,
                'email'      => $request->email,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($store) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Toko telah berhasil ditambahkan.'
                ], 200);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan data Toko.'
            ], 500);

        } catch (\Illuminate\Validation\ValidationException $e) {
            $messages = [];
            foreach ($e->errors() as $field => $errors) {
                foreach ($errors as $msg) {
                    $messages[] = $msg;
                }
            }

            return response()->json([
                'status' => 'validation_error',
                'message' => 'Gagal menambahkan data.',
                'errors' => $messages
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada sistem. Silahkan coba lagi.',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function edit($id)
    {
        $store = MStore::findOrFail($id);
        return view('pages.master.store.store_edit', compact('store'));
    }

    public function update(Request $request, $id)
    {
        try {
            $this->validate($request, [
                'code_store' => 'required',
                'nama_store' => 'required',
                'address'    => 'required',
                'phone'      => 'required|max:15',
                'email'      => 'required|email',
            ], [
                'code_store.required' => 'Kode Toko harus diisi.',
                'nama_store.required' => 'Nama Toko harus diisi.',
                'address.required'    => 'Alamat Toko harus diisi.',
                'phone.required'      => 'No HP harus diisi.',
                'email.required'      => 'Alamat Email harus diisi.',
                'email.email'         => 'Format Email Toko salah.',
            ]);

            $updated = MStore::whereId($id)->update([
                'code_store' => $request->code_store,
                'nama_store' => $request->nama_store,
                'address'    => $request->address,
                'phone'      => $request->phone,
                'email'      => $request->email,
                'updated_at' => now(),
            ]);

            if ($updated) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Data Toko telah berhasil diubah.'
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengubah data toko.'
            ], 500);

        } catch (\Illuminate\Validation\ValidationException $e) {
            $messages = [];
            foreach ($e->errors() as $field => $errors) {
                foreach ($errors as $msg) {
                    $messages[] = $msg;
                }
            }

            return response()->json([
                'status' => 'validation_error',
                'message' => 'Gagal menambahkan data.',
                'errors' => $messages
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada sistem. Silahkan coba lagi.',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $store = MStore::findOrFail($id);
            $store->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Toko telah berhasil dihapus.'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Toko tidak ditemukan.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus data toko.',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}