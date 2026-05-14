<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MSupplier;
use Yajra\DataTables\Facades\DataTables;
use App\Logs;
use Auth;

class SupplierController extends Controller
{
    private function masterLog(string $section, string $content): void
    {
        try {
            (new Logs('Logs_Master_SupplierController'))->write($section, $content);
        } catch (\Throwable $e) {
            \Log::error('[SupplierController] Gagal menulis log: ' . $e->getMessage());
        }
    }

    private function actor(): string
    {
        $user = Auth::user();
        if (!$user) return 'Guest';
        return $user->username ?? $user->name ?? "ID:{$user->id}";
    }

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

        return abort(403, 'Akses tidak diizinkan.');
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
        $this->masterLog('TAMBAH_PEMASOK', "User: {$this->actor()} | Kode: {$request->code_spl} | Nama: {$request->nama_spl} | Status: PROCESS");

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
                'code_spl.required'     => 'Kode Pemasok wajib diisi.',
                'code_spl.unique'       => 'Kode Pemasok telah digunakan.',
                'nama_spl.required'     => 'Nama Pemasok wajib diisi.',
                'npwp_spl.required'     => 'Nomor Pajak wajib diisi.',
                'npwp_spl.max'          => 'Nomor Pajak tidak lebih dari 16 karakter.',
                'address_spl.required'  => 'Alamat Pemasok wajib diisi.',
                'address_npwp.required' => 'Alamat Pajak wajib diisi.',
                'phone.required'        => 'No HP wajib diisi.',
                'phone.max'             => 'No HP tidak lebih dari 13 karakter.',
                'email.required'        => 'Email wajib diisi.',
                'email.email'           => 'Format Email Pemasok salah.',
                'name_pic.required'     => 'Nama PIC wajib diisi.',
                'phone_pic.required'    => 'HP PIC number wajib diisi.',
                'phone_pic.max'         => 'No HP PIC tidak lebih dari 13 karakter.',
                'email_pic.required'    => 'Email PIC wajib diisi.',
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

            $this->masterLog('TAMBAH_PEMASOK', "User: {$this->actor()} | Kode: {$request->code_spl} | Nama: {$request->nama_spl} | Status: SUCCESS");
            return response()->json([
                'status' => 'success',
                'message' => 'Pemasok telah berhasil ditambahkan.'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->masterLog('TAMBAH_PEMASOK', "User: {$this->actor()} | Kode: {$request->code_spl} | Status: VALIDATION_ERROR | Error: " . json_encode($e->errors()));
            return response()->json([
                'status' => 'fail',
                'message' => 'Terjadi kesalahan pada sistem. Silahkan coba lagi.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            $this->masterLog('TAMBAH_PEMASOK', "User: {$this->actor()} | Kode: {$request->code_spl} | Status: FAILED | Error: {$e->getMessage()}");
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
        $this->masterLog('UBAH_PEMASOK', "User: {$this->actor()} | ID: {$id} | Kode: {$request->code_spl} | Nama: {$request->nama_spl} | Status: PROCESS");

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
                'code_spl.required'     => 'Kode Pemasok wajib diisi.',
                'nama_spl.required'     => 'Nama Pemasok wajib diisi.',
                'npwp_spl.required'     => 'Nomor Pajak wajib diisi.',
                'address_spl.required'  => 'Alamat Pemasok wajib diisi.',
                'address_npwp.required' => 'Alamat Pajak wajib diisi.',
                'phone.required'        => 'No HP wajib diisi.',
                'email.required'        => 'Email wajib diisi.',
                'email.email'           => 'Format Email Pemasok salah.',
                'name_pic.required'     => 'Nama PIC wajib diisi.',
                'phone_pic.required'    => 'No HP PIC wajib diisi.',
                'email_pic.required'    => 'Email PIC wajib diisi.',
                'email_pic.email'       => 'Format Email PIC Salah.',
            ]);

            MSupplier::whereId($id)->update($validatedData);

            $this->masterLog('UBAH_PEMASOK', "User: {$this->actor()} | ID: {$id} | Kode: {$request->code_spl} | Nama: {$request->nama_spl} | Status: SUCCESS");
            return response()->json([
                'status' => 'success',
                'message' => 'Pemasok data telah berhasil diubah.'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->masterLog('UBAH_PEMASOK', "User: {$this->actor()} | ID: {$id} | Kode: {$request->code_spl} | Status: VALIDATION_ERROR | Error: " . json_encode($e->errors()));
            return response()->json([
                'status' => 'fail',
                'message' => 'Terjadi kesalahan pada sistem. Silahkan coba lagi.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            $this->masterLog('UBAH_PEMASOK', "User: {$this->actor()} | ID: {$id} | Kode: {$request->code_spl} | Status: FAILED | Error: {$e->getMessage()}");
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
            $this->masterLog('HAPUS_PEMASOK', "User: {$this->actor()} | ID: {$id} | Kode: {$supplier->code_spl} | Nama: {$supplier->nama_spl} | Status: DELETED");
            $supplier->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Pemasok telah berhasil dihapus.'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->masterLog('HAPUS_PEMASOK', "User: {$this->actor()} | ID: {$id} | Status: FAILED | Error: Data tidak ditemukan");
            return response()->json([
                'status' => 'error',
                'message' => 'Pemasok tidak ditemukan.'
            ], 404);
        } catch (\Exception $e) {
            $this->masterLog('HAPUS_PEMASOK', "User: {$this->actor()} | ID: {$id} | Status: FAILED | Error: {$e->getMessage()}");
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada sistem. Silahkan coba lagi.',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}