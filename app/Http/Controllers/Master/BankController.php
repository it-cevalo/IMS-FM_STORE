<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MBank;
use Yajra\DataTables\Facades\DataTables;

class BankController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        return view('pages.master.bank.bank_index');
    }
  
    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $query = MBank::query();

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $editUrl = route('bank.edit', $row->id);
                    $deleteUrl = route('bank.destroy', $row->id);
                    return '
                        <form action="' . $deleteUrl . '" method="POST" class="formDelete" style="display:inline;">
                            ' . csrf_field() . method_field('DELETE') . '
                            <a href="' . $editUrl . '" class="btn btn-link btn-sm"><i class="fa fa-edit"></i></a>
                            <button type="submit" class="btn btn-link text-danger"><i class="fa fa-trash"></i></button>
                        </form>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return abort(403, 'Unauthorized access.');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('pages.master.bank.bank_create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'code_bank'         => 'required',
                'nama_bank'         => 'required',
                'norek_bank'        => 'required',
                'atasnama_bank'     => 'required',
            ], [
                'code_bank.required'        => 'Kode bank wajib diisi.',
                'nama_bank.required'        => 'Nama bank wajib diisi.',
                'norek_bank.required'       => 'Nomor rekening bank wajib diisi.',
                // 'norek_bank.unique'         => 'Nomor rekening bank sudah digunakan.',
                'atasnama_bank.required'    => 'Atas Nama bank wajib diisi.',
            ]);

            MBank::create($validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'Bank berhasil ditambahkan.'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'fail',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menyimpan Bank.',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $bank = MBank::findOrFail($id);

        return view('pages.master.bank.bank_edit',compact('bank'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'code_bank'         => 'required',
                'nama_bank'         => 'required',
                'norek_bank'        => 'required',
                'atasnama_bank'     => 'required',
            ], [
                'code_bank.required'        => 'Kode bank harus diisi.',
                'nama_bank.required'        => 'Nama bank harus diisi.',
                'norek_bank.required'       => 'Nomor rekening bank harus diisi.',
                'atasnama_bank.required'    => 'Atas Nama bank harus diisi.',
            ]);

            MBank::whereId($id)->update($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Data bank berhasil diperbarui.'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'fail',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat memperbarui data bank.',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $bank = MBank::findOrFail($id);
        $bank->delete();

        return redirect('/bank')->with('success', 'Bank berhasil dihapus');
    }
}