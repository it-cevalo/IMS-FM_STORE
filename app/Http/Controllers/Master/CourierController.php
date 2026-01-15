<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MCourier;
use Yajra\DataTables\Facades\DataTables;

class CourierController extends Controller
{
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
        try {
            $this->validate($request, [
                'code_courier' => 'required|unique:m_couriers,code_courier',
                'nama_courier' => 'required',
            ], [
                'code_courier.required' => 'Kode kurir harus diisi.',
                'nama_courier.required' => 'Nama kurir harus diisi.',
                'code_courier.unique'   => 'Kode kurir telah digunakan.',
            ]);

            $courier = MCourier::create([
                'code_courier' => $request->code_courier,
                'nama_courier' => $request->nama_courier,
                'created_at' => date_create_immutable(),
                'updated_at' => date_create_immutable(),
            ]);

            if ($courier) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Data kurir berhasil ditambahkan.'
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan data kurir.'
            ], 500);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'validation_error',
                'errors' => $e->errors(),
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
        $courier = MCourier::findOrFail($id);
        return view('pages.master.kurir.kurir_edit', compact('courier'));
    }

    public function update(Request $request, $id)
    {
        try {
            $this->validate($request, [
                'code_courier' => 'required',
                'nama_courier' => 'required',
            ], [
                'code_courier.required' => 'Kode kurir harus diisi.',
                'nama_courier.required' => 'Nama kurir harus diisi.',
            ]);

            $updated = MCourier::whereId($id)->update([
                'code_courier' => $request->code_courier,
                'nama_courier' => $request->nama_courier,
                'updated_at' => date_create_immutable(),
            ]);

            if ($updated) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Data kurir berhasil diperbarui.'
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengubah data kurir.'
            ], 500);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'validation_error',
                'errors' => $e->errors(),
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
            $courier = MCourier::findOrFail($id);
            $courier->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Data kurir berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus Data kurir. Silahkan coba lagi nanti.',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}