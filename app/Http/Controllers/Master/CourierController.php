<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MCourier;
use Yajra\DataTables\Facades\DataTables;
use App\Logs;
use Auth;

class CourierController extends Controller
{
    private function masterLog(string $section, string $content): void
    {
        try {
            (new Logs('Logs_Master_CourierController'))->write($section, $content);
        } catch (\Throwable $e) {
            \Log::error('[CourierController] Gagal menulis log: ' . $e->getMessage());
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
        return view('pages.master.kurir.kurir_index');
    }

    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $query = MCourier::query();

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $editUrl = route('couriers.edit', $row->id);
                    $deleteUrl = route('couriers.destroy', $row->id);

                    return '
                        <form action="' . $deleteUrl . '" method="POST" class="formDelete" style="display:inline;">
                            ' . csrf_field() . method_field('DELETE') . '
                            <a href="' . $editUrl . '" class="btn btn-link btn-sm"><i class="fa fa-edit"></i></a>
                            <button type="button" class="btn btn-link text-danger btnDeleteCourier">
                                <i class="fa fa-trash"></i>
                            </button>
                        </form>
                    ';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return abort(403);
    }

    public function create()
    {
        return view('pages.master.kurir.kurir_create');
    }

    public function store(Request $request)
    {
        $this->masterLog('TAMBAH_KURIR', "User: {$this->actor()} | Kode: {$request->code_courier} | Nama: {$request->nama_courier} | Status: PROCESS");

        try {
            $this->validate($request, [
                'code_courier' => 'required|unique:m_couriers,code_courier',
                'nama_courier' => 'required',
            ], [
                'code_courier.required' => 'Kode kurir wajib diisi.',
                'nama_courier.required' => 'Nama kurir wajib diisi.',
                'code_courier.unique'   => 'Kode kurir telah digunakan.',
            ]);

            $courier = MCourier::create([
                'code_courier' => $request->code_courier,
                'nama_courier' => $request->nama_courier,
                'created_at' => date_create_immutable(),
                'updated_at' => date_create_immutable(),
            ]);

            if ($courier) {
                $this->masterLog('TAMBAH_KURIR', "User: {$this->actor()} | Kode: {$request->code_courier} | Nama: {$request->nama_courier} | Status: SUCCESS");
                return response()->json([
                    'status' => 'success',
                    'message' => 'Data kurir berhasil ditambahkan.'
                ]);
            }

            $this->masterLog('TAMBAH_KURIR', "User: {$this->actor()} | Kode: {$request->code_courier} | Status: FAILED | Error: Gagal menyimpan data kurir");
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan data kurir.'
            ], 500);

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->masterLog('TAMBAH_KURIR', "User: {$this->actor()} | Kode: {$request->code_courier} | Status: VALIDATION_ERROR | Error: " . json_encode($e->errors()));
            return response()->json([
                'status' => 'validation_error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            $this->masterLog('TAMBAH_KURIR', "User: {$this->actor()} | Kode: {$request->code_courier} | Status: FAILED | Error: {$e->getMessage()}");
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada sistem. Silahkan coba lagi.',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function edit($id)
    {
        $courier = MCourier::findOrFail($id);
        return view('pages.master.kurir.kurir_edit', compact('courier'));
    }

    public function update(Request $request, $id)
    {
        $this->masterLog('UBAH_KURIR', "User: {$this->actor()} | ID: {$id} | Kode: {$request->code_courier} | Nama: {$request->nama_courier} | Status: PROCESS");

        try {
            $this->validate($request, [
                'code_courier' => 'required',
                'nama_courier' => 'required',
            ], [
                'code_courier.required' => 'Kode kurir wajib diisi.',
                'nama_courier.required' => 'Nama kurir wajib diisi.',
            ]);

            $updated = MCourier::whereId($id)->update([
                'code_courier' => $request->code_courier,
                'nama_courier' => $request->nama_courier,
                'updated_at' => date_create_immutable(),
            ]);

            if ($updated) {
                $this->masterLog('UBAH_KURIR', "User: {$this->actor()} | ID: {$id} | Kode: {$request->code_courier} | Nama: {$request->nama_courier} | Status: SUCCESS");
                return response()->json([
                    'status' => 'success',
                    'message' => 'Data kurir berhasil diperbarui.'
                ]);
            }

            $this->masterLog('UBAH_KURIR', "User: {$this->actor()} | ID: {$id} | Status: FAILED | Error: Gagal mengubah data kurir");
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengubah data kurir.'
            ], 500);

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->masterLog('UBAH_KURIR', "User: {$this->actor()} | ID: {$id} | Status: VALIDATION_ERROR | Error: " . json_encode($e->errors()));
            return response()->json([
                'status' => 'validation_error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            $this->masterLog('UBAH_KURIR', "User: {$this->actor()} | ID: {$id} | Status: FAILED | Error: {$e->getMessage()}");
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
            $courier = MCourier::findOrFail($id);
            $this->masterLog('HAPUS_KURIR', "User: {$this->actor()} | ID: {$id} | Kode: {$courier->code_courier} | Nama: {$courier->nama_courier} | Status: DELETED");
            $courier->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Data kurir berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            $this->masterLog('HAPUS_KURIR', "User: {$this->actor()} | ID: {$id} | Status: FAILED | Error: {$e->getMessage()}");
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus Data kurir. Silahkan coba lagi nanti.',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}