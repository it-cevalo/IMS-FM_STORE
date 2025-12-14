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
                'code_spl.required'     => 'Supplier code is required.',
                'code_spl.unique'       => 'Supplier code has already been used.',
                'nama_spl.required'     => 'Supplier name is required.',
                'npwp_spl.required'     => 'Tax number is required.',
                'npwp_spl.max'          => 'Tax number cannot exceed 16 characters.',
                'address_spl.required'  => 'Supplier address is required.',
                'address_npwp.required' => 'Tax address is required.',
                'phone.required'        => 'Phone number is required.',
                'phone.max'             => 'Phone number cannot exceed 13 characters.',
                'email.required'        => 'Email is required.',
                'email.email'           => 'Invalid email format.',
                'name_pic.required'     => 'PIC name is required.',
                'phone_pic.required'    => 'PIC phone number is required.',
                'phone_pic.max'         => 'PIC phone number cannot exceed 13 characters.',
                'email_pic.required'    => 'PIC email is required.',
                'email_pic.email'       => 'Invalid PIC email format.',
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
                'message' => 'Supplier has been successfully added.'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Invalid input.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while saving supplier data.',
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
                'code_spl.required'     => 'Supplier code is required.',
                'nama_spl.required'     => 'Supplier name is required.',
                'npwp_spl.required'     => 'Tax number is required.',
                'address_spl.required'  => 'Supplier address is required.',
                'address_npwp.required' => 'Tax address is required.',
                'phone.required'        => 'Phone number is required.',
                'email.required'        => 'Email is required.',
                'email.email'           => 'Invalid email format.',
                'name_pic.required'     => 'PIC name is required.',
                'phone_pic.required'    => 'PIC phone number is required.',
                'email_pic.required'    => 'PIC email is required.',
                'email_pic.email'       => 'Invalid PIC email format.',
            ]);

            MSupplier::whereId($id)->update($validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'Supplier data has been successfully updated.'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Invalid input.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while updating supplier data.',
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
                'message' => 'Supplier has been successfully deleted.'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Supplier data not found.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while deleting supplier data.',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}