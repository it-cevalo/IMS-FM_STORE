<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MSupplier;
use Yajra\DataTables\Facades\DataTables;

class SupplierController extends Controller
{
    /**
     * Display the supplier list page.
     */
    public function index()
    {
        return view('pages.master.supplier.supplier_index');
    }

    /**
     * Fetch supplier data for DataTables.
     */
    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $query = MSupplier::query();

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $editUrl = route('suppliers.edit', $row->id);
                    $deleteUrl = route('suppliers.destroy', $row->id);

                    return '
                        <form action="' . $deleteUrl . '" method="POST" class="formDelete" style="display:inline;">
                            ' . csrf_field() . method_field('DELETE') . '
                            <a href="' . $editUrl . '" class="btn btn-link btn-sm" title="Edit">
                                <i class="fa fa-edit"></i>
                            </a>
                            <button type="button" class="btn btn-link text-danger btnDeleteSupplier" title="Delete">
                                <i class="fa fa-trash"></i>
                            </button>
                        </form>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return abort(403, 'Unauthorized access.');
    }

    /**
     * Show the create supplier form.
     */
    public function create()
    {
        return view('pages.master.supplier.supplier_create');
    }

    /**
     * Store a newly created supplier record.
     */
    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'code_spl'      => 'required|unique:m_suppliers,code_spl',
                'nama_spl'      => 'required',
                'npwp_spl'      => 'required|max:20',
                'address_spl'   => 'required',
                'address_npwp'  => 'required',
                'phone'         => 'required|max:13',
                'email'         => 'required|email',
                'name_pic'      => 'required',
                'phone_pic'     => 'required|max:13',
                'email_pic'     => 'required|email',
            ], [
                'code_spl.required'     => 'Kode Pemasok harus diisi.',
                'code_spl.unique'       => 'Kode Pemasok telah digunakan.',
                'nama_spl.required'     => 'Nama Pemasok harus diisi.',
                'npwp_spl.required'     => 'Nomor Pajak harus diisi.',
                'npwp_spl.max'          => 'Nomor Pajak tidak lebih dari 16 karakter.',
                'address_spl.required'  => 'Alamat Pemasok harus diisi.',
                'address_npwp.required' => 'Alamat Pajak harus diisi.',
                'phone.required'        => 'No HP harus diisi.',
                'phone.max'             => 'No HP tidak lebih dari 13 karakter.',
                'email.required'        => 'Email harus diisi.',
                'email.email'           => 'Format Email Pemasok salah.',
                'name_pic.required'     => 'Nama PIC harus diisi.',
                'phone_pic.required'    => 'HP PIC number harus diisi.',
                'phone_pic.max'         => 'No HP PIC tidak lebih dari 13 karakter.',
                'email_pic.required'    => 'Email PIC harus diisi.',
                'email_pic.email'       => 'Format Email PIC salah.',
            ]);

            MSupplier::create([
                'code_spl'     => $request->code_spl,
                'nama_spl'     => $request->nama_spl,
                'npwp_spl'     => $request->npwp_spl,
                'address_spl'  => $request->address_spl,
                'address_npwp' => $request->address_npwp,
                'phone'        => $request->phone,
                'email'        => $request->email,
                'name_pic'     => $request->name_pic,
                'phone_pic'    => $request->phone_pic,
                'email_pic'    => $request->email_pic,
                'tgl_spl'      => now(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Pemasok telah berhasil ditambahkan.'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Terjadi kesalahan pada sistem. Silahkan coba lagi.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada sistem. Silahkan coba lagi.',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Show the edit form for the specified supplier.
     */
    public function edit($id)
    {
        $suppliers = MSupplier::findOrFail($id);
        return view('pages.master.supplier.supplier_edit', compact('suppliers'));
    }

    /**
     * Update an existing supplier record.
     */
    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'code_spl'      => 'required',
                'nama_spl'      => 'required',
                'npwp_spl'      => 'required',
                'address_spl'   => 'required',
                'address_npwp'  => 'required',
                'phone'         => 'required',
                'email'         => 'required|email',
                'name_pic'      => 'required',
                'phone_pic'     => 'required',
                'email_pic'     => 'required|email',
            ], [
                'code_spl.required'     => 'Kode Pemasok harus diisi.',
                'nama_spl.required'     => 'Nama Pemasok harus diisi.',
                'npwp_spl.required'     => 'Nomor Pajak harus diisi.',
                'address_spl.required'  => 'Alamat Pemasok harus diisi.',
                'address_npwp.required' => 'Alamat Pajak harus diisi.',
                'phone.required'        => 'No HP harus diisi.',
                'email.required'        => 'Email harus diisi.',
                'email.email'           => 'Format Email Pemasok salah.',
                'name_pic.required'     => 'Nama PIC harus diisi.',
                'phone_pic.required'    => 'No HP PIC harus diisi.',
                'email_pic.required'    => 'Email PIC harus diisi.',
                'email_pic.email'       => 'Format Email PIC Salah.',
            ]);

            MSupplier::whereId($id)->update($validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'Pemasok data telah berhasil diubah.'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Terjadi kesalahan pada sistem. Silahkan coba lagi.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada sistem. Silahkan coba lagi.',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Delete a supplier record.
     */
    public function destroy($id)
    {
        try {
            $supplier = MSupplier::findOrFail($id);
            $supplier->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Pemasok telah berhasil dihapus.'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pemasok tidak ditemukan.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada sistem. Silahkan coba lagi.',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}