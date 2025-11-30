<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MWarehouse;
use App\Models\MStore;
use Yajra\DataTables\Facades\DataTables;

class WarehouseController extends Controller
{
    public function index()
    {
        return view('pages.master.warehouse.warehouse_index');
    }

    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $query = MWarehouse::with('store');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('store_name', function ($row) {
                    return $row->store->nama_store ?? '-';
                })
                ->addColumn('action', function ($row) {
                    $editUrl = route('warehouses.edit', $row->id);
                    $deleteUrl = route('warehouses.destroy', $row->id);

                    return '
                        <div class="d-flex justify-content-center">
                            <a href="' . $editUrl . '" class="btn btn-sm btn-link text-primary" title="Edit">
                                <i class="fa fa-edit"></i>
                            </a>
                            <button type="button"
                                    data-url="' . $deleteUrl . '"
                                    class="btn btn-sm btn-link text-danger btnDeleteWarehouse"
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
        $stores = MStore::all();
        return view('pages.master.warehouse.warehouse_create', compact('stores'));
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'id_store' => 'required|exists:m_stores,id',
                'code_wh'  => 'required|unique:m_warehouses,code_wh',
                'nama_wh'  => 'required',
                'address'  => 'required',
                'phone'    => 'required|max:15',
                'email'    => 'required|email',
            ], [
                'id_store.required' => 'Store must be selected.',
                'id_store.exists'   => 'Selected store was not found.',
                'code_wh.required'  => 'Warehouse code is required.',
                'code_wh.unique'    => 'Warehouse code must be unique.',
                'nama_wh.required'  => 'Warehouse name is required.',
                'address.required'  => 'Address is required.',
                'phone.required'    => 'Phone number is required.',
                'email.required'    => 'Email address is required.',
                'email.email'       => 'Invalid email format.',
            ]);

            $store = MStore::findOrFail($request->id_store);

            MWarehouse::create([
                'id_store'   => $store->id,
                'code_store' => $store->code_store,
                'code_wh'    => $request->code_wh,
                'nama_wh'    => $request->nama_wh,
                'address'    => $request->address,
                'phone'      => $request->phone,
                'email'      => $request->email,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Warehouse has been successfully added.'
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            $messages = [];
            foreach ($e->errors() as $field => $errors) {
                foreach ($errors as $msg) {
                    $messages[] = $msg;
                }
            }

            return response()->json([
                'status'  => 'validation_error',
                'message' => 'Invalid input.',
                'errors'  => $messages
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Something went wrong while saving data.',
                'debug'   => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function edit($id)
    {
        $warehouses = MWarehouse::findOrFail($id);
        $stores = MStore::all();
        return view('pages.master.warehouse.warehouse_edit', compact('warehouses', 'stores'));
    }

    public function update(Request $request, $id)
    {
        try {
            $this->validate($request, [
                'id_store' => 'required|exists:m_stores,id',
                'code_wh'  => 'required',
                'nama_wh'  => 'required',
                'address'  => 'required',
                'phone'    => 'required|max:15',
                'email'    => 'required|email',
            ], [
                'id_store.required' => 'Store must be selected.',
                'id_store.exists'   => 'Selected store was not found.',
                'code_wh.required'  => 'Warehouse code is required.',
                'nama_wh.required'  => 'Warehouse name is required.',
                'address.required'  => 'Address is required.',
                'phone.required'    => 'Phone number is required.',
                'email.required'    => 'Email address is required.',
                'email.email'       => 'Invalid email format.',
            ]);

            $store = MStore::findOrFail($request->id_store);

            MWarehouse::whereId($id)->update([
                'id_store'   => $store->id,
                'code_store' => $store->code_store,
                'code_wh'    => $request->code_wh,
                'nama_wh'    => $request->nama_wh,
                'address'    => $request->address,
                'phone'      => $request->phone,
                'email'      => $request->email,
                'updated_at' => now(),
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Warehouse data has been successfully updated.'
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            $messages = [];
            foreach ($e->errors() as $field => $errors) {
                foreach ($errors as $msg) {
                    $messages[] = $msg;
                }
            }

            return response()->json([
                'status'  => 'validation_error',
                'message' => 'Invalid input.',
                'errors'  => $messages
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Something went wrong while updating data.',
                'debug'   => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $warehouse = MWarehouse::findOrFail($id);
            $warehouse->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'Warehouse has been successfully deleted.'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Warehouse not found.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Something went wrong while deleting data.',
                'debug'   => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}