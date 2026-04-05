<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MStore;
use Yajra\DataTables\Facades\DataTables;
use App\Logs;
use Auth;

class StoreController extends Controller
{
    private function masterLog(string $section, string $content): void
    {
        try {
            (new Logs('Logs_Master_StoreController'))->write($section, $content);
        } catch (\Throwable $e) {
            \Log::error('[StoreController] Gagal menulis log: ' . $e->getMessage());
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
        $this->masterLog('TAMBAH_TOKO', "User: {$this->actor()} | Kode: {$request->code_store} | Nama: {$request->nama_store} | Status: PROCESS");

        try {
            $this->validate($request, [
                'code_store' => 'required|unique:m_stores,code_store',
                'nama_store' => 'required',
                'address' => 'required',
                'phone' => 'required|max:15',
                'email' => 'required|email',
            ], [
                'code_store.required' => 'Kode Toko wajib diisi.',
                'code_store.unique'   => 'Kode Toko telah digunakan.',
                'nama_store.required' => 'Nama Toko wajib diisi.',
                'address.required'    => 'Alamat Toko wajib diisi.',
                'phone.required'      => 'No HP wajib diisi.',
                'email.required'      => 'Alamat Email wajib diisi.',
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
                $this->masterLog('TAMBAH_TOKO', "User: {$this->actor()} | Kode: {$request->code_store} | Nama: {$request->nama_store} | Status: SUCCESS");
                return response()->json([
                    'status' => 'success',
                    'message' => 'Toko telah berhasil ditambahkan.'
                ], 200);
            }

            $this->masterLog('TAMBAH_TOKO', "User: {$this->actor()} | Kode: {$request->code_store} | Status: FAILED");
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan data Toko.'
            ], 500);

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->masterLog('TAMBAH_TOKO', "User: {$this->actor()} | Kode: {$request->code_store} | Status: VALIDATION_ERROR | Error: " . implode(', ', array_merge(...array_values($e->errors()))));
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
            $this->masterLog('TAMBAH_TOKO', "User: {$this->actor()} | Kode: {$request->code_store} | Status: FAILED | Error: {$e->getMessage()}");
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
        $this->masterLog('UBAH_TOKO', "User: {$this->actor()} | ID: {$id} | Kode: {$request->code_store} | Nama: {$request->nama_store} | Status: PROCESS");

        try {
            $this->validate($request, [
                'code_store' => 'required',
                'nama_store' => 'required',
                'address'    => 'required',
                'phone'      => 'required|max:15',
                'email'      => 'required|email',
            ], [
                'code_store.required' => 'Kode Toko wajib diisi.',
                'nama_store.required' => 'Nama Toko wajib diisi.',
                'address.required'    => 'Alamat Toko wajib diisi.',
                'phone.required'      => 'No HP wajib diisi.',
                'email.required'      => 'Alamat Email wajib diisi.',
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
                $this->masterLog('UBAH_TOKO', "User: {$this->actor()} | ID: {$id} | Kode: {$request->code_store} | Nama: {$request->nama_store} | Status: SUCCESS");
                return response()->json([
                    'status' => 'success',
                    'message' => 'Data Toko telah berhasil diubah.'
                ]);
            }

            $this->masterLog('UBAH_TOKO', "User: {$this->actor()} | ID: {$id} | Kode: {$request->code_store} | Status: FAILED");
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengubah data toko.'
            ], 500);

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->masterLog('UBAH_TOKO', "User: {$this->actor()} | ID: {$id} | Kode: {$request->code_store} | Status: VALIDATION_ERROR | Error: " . implode(', ', array_merge(...array_values($e->errors()))));
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
            $this->masterLog('UBAH_TOKO', "User: {$this->actor()} | ID: {$id} | Kode: {$request->code_store} | Status: FAILED | Error: {$e->getMessage()}");
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
            $this->masterLog('HAPUS_TOKO', "User: {$this->actor()} | ID: {$id} | Kode: {$store->code_store} | Nama: {$store->nama_store} | Status: DELETED");
            $store->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Toko telah berhasil dihapus.'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->masterLog('HAPUS_TOKO', "User: {$this->actor()} | ID: {$id} | Status: FAILED | Error: Data tidak ditemukan");
            return response()->json([
                'status' => 'error',
                'message' => 'Toko tidak ditemukan.'
            ], 404);
        } catch (\Exception $e) {
            $this->masterLog('HAPUS_TOKO', "User: {$this->actor()} | ID: {$id} | Status: FAILED | Error: {$e->getMessage()}");
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus data toko.',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}