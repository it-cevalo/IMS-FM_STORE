<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MCustomer;
use Yajra\DataTables\Facades\DataTables;

class CustomerController extends Controller
{
    /**
     * Display the customer list page.
     */
    public function index()
    {
        return view('pages.master.customer.customer_index');
    }

    /**
     * Fetch customer data for DataTables.
     */
    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $query = MCustomer::query();

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $editUrl = route('customers.edit', $row->id);
                    $deleteUrl = route('customers.destroy', $row->id);

                    return '
                        <form action="' . $deleteUrl . '" method="POST" class="formDelete" style="display:inline;">
                            ' . csrf_field() . method_field('DELETE') . '
                            <a href="' . $editUrl . '" class="btn btn-link btn-sm" title="Edit">
                                <i class="fa fa-edit"></i>
                            </a>
                            <button type="button" data-id="' . $row->id . '" class="btn btn-link text-danger btnDeleteCustomer" title="Delete">
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
     * Show the create customer form.
     */
    public function create()
    {
        return view('pages.master.customer.customer_create');
    }

    /**
     * Store a newly created customer record.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'code_cust'    => 'required|unique:m_customers,code_cust',
                'nama_cust'    => 'required',
                'npwp_cust'    => 'max:16',
                'type_cust'    => 'required',
                'address_cust' => 'required',
                'phone'        => 'required|max:13',
                'email'        => 'required|email'
            ], [
                'code_cust.required'    => 'Kode Pelanggan wajib diisi.',
                'code_cust.unique'      => 'Kode Pelanggan telah digunakan.',
                'nama_cust.required'    => 'Nama Pelanggan wajib diisi.',
                // 'npwp_cust.required'    => 'NPWP wajib diisi.',
                'npwp_cust.max'         => 'NPWP Pelanggan Tidak boleh lebih dari 16 karakter.',
                'type_cust.required'    => 'Tipe pelanggan wajib diisi.',
                'address_cust.required' => 'Alamat pelanggan wajib diisi.',
                // 'address_npwp.required' => 'Tax address wajib diisi.',
                'phone.required'        => 'No HP Pelanggan wajib diisi.',
                'phone.max'             => 'No HP Pelanggan tidak boleh lebih dari 13 karakter.',
                'email.required'        => 'Email Pelanggan wajib diisi.',
                'email.email'           => 'Format email Pelanggan salah.',
            ]);

            $validatedData['tgl_cust'] = now();

            MCustomer::create($validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'Data Pelanggan telah berhasil ditambahkan.'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Gagal menyimpan data pelanggan.',
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
     * Show the edit form for the specified customer.
     */
    public function edit($id)
    {
        $customers = MCustomer::findOrFail($id);
        $type_cust = [
            '#' => '....',
            'B' => 'Business',
            'C' => 'Non-Business'
        ];

        return view('pages.master.customer.customer_edit', compact('customers', 'type_cust'));
    }

    /**
     * Update an existing customer record.
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'code_cust'    => 'required',
                'nama_cust'    => 'required',
                'npwp_cust'    => 'required',
                'type_cust'    => 'required',
                'address_cust' => 'required',
                'address_npwp' => 'nullable',
                'phone'        => 'required',
                'email'        => 'required|email'
            ], [
                'code_cust.required'    => 'Kode Pelanggan wajib diisi.',
                'nama_cust.required'    => 'Nama Pelanggan wajib diisi.',
                'npwp_cust.required'    => 'NPWP Pelanggan wajib diisi.',
                'type_cust.required'    => 'Tipe pelanggan wajib diisi.',
                'address_cust.required' => 'Alamat Pelanggan wajib diisi.',
                'phone.required'        => 'No HP Pelanggan wajib diisi.',
                'email.required'        => 'Email Pelangga wajib diisi.',
                'email.email'           => 'Format email Pelanggan salah.',
            ]);

            MCustomer::whereId($id)->update($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Data pelanggan berhasil diperbarui.'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Gagal merubah data pelanggan.',
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
     * Delete a customer record.
     */
    public function destroy(Request $request)
    {
        try {
            $id = $request->id;
            $customer = MCustomer::findOrFail($id);
            $customer->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Data pelanggan berhasil dihapus.'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus Data pelanggan.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus Data pelanggan. Silahkan coba lagi nanti.',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}