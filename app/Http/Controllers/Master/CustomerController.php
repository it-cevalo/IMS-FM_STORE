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
                'npwp_cust'    => 'required|max:16',
                'type_cust'    => 'required',
                'address_cust' => 'required',
                'address_npwp' => 'required',
                'phone'        => 'required|max:13',
                'email'        => 'required|email'
            ], [
                'code_cust.required'    => 'Customer code is required.',
                'code_cust.unique'      => 'Customer code has already been used.',
                'nama_cust.required'    => 'Customer name is required.',
                'npwp_cust.required'    => 'Tax number is required.',
                'npwp_cust.max'         => 'Tax number cannot exceed 16 characters.',
                'type_cust.required'    => 'Customer type is required.',
                'address_cust.required' => 'Customer address is required.',
                'address_npwp.required' => 'Tax address is required.',
                'phone.required'        => 'Phone number is required.',
                'phone.max'             => 'Phone number cannot exceed 13 characters.',
                'email.required'        => 'Email is required.',
                'email.email'           => 'Invalid email format.',
            ]);

            $validatedData['tgl_cust'] = now();

            MCustomer::create($validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'Customer has been successfully added.'
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
                'message' => 'An error occurred while saving the customer.',
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
                'code_cust.required'    => 'Customer code is required.',
                'nama_cust.required'    => 'Customer name is required.',
                'npwp_cust.required'    => 'Tax number is required.',
                'type_cust.required'    => 'Customer type is required.',
                'address_cust.required' => 'Customer address is required.',
                'phone.required'        => 'Phone number is required.',
                'email.required'        => 'Email is required.',
                'email.email'           => 'Invalid email format.',
            ]);

            MCustomer::whereId($id)->update($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Customer data has been successfully updated.'
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
                'message' => 'An error occurred while updating the customer data.',
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
                'message' => 'Customer has been successfully deleted.'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Customer data not found.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while deleting the customer.',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}