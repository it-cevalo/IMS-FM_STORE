<?php

namespace App\Imports;

use App\Models\Tpo;
use App\Models\Hpo;
use App\Models\MCustomer;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
// use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;

class PurchaseOrderImport implements ToCollection, WithStartRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    public function startRow(): int
    {
        return 2;
    }


    public function collection(Collection $rows)
    {       
            $po = Tpo::select('id')->latest()->first();
            $validator = Validator::make([], []);
            if (!$po) {
                // Jika tidak ada record dalam database, maka buatkan id baru
                $id_po = 1;
            } else {
                // Jika ada record dalam database, maka tambahkan 1 ke id terakhir
                $id_po = $po->id + 1;
            }

            foreach ($rows as $row) 
            {
                $validator = Validator::make($row->toArray(), [
                    0 => ['required', 'date'],
                    1 => ['required', Rule::exists(MCustomer::class, 'code_cust')],
                    2 => ['required', 'string'],
                    3 => ['nullable', 'string'],
                    4 => ['nullable', 'string'],
                    5 => ['nullable', 'string'],
                    6 => ['nullable', 'string'],
                ]);
    
                if ($validator->fails()) {
                    // Jika validasi gagal, kembalikan pesan kesalahan
                    return redirect()->back()->withInput()
                        ->withErrors($validator->errors())
                        ->with(['fail' => 'There is invalid data in the uploaded file']);
                }

                $mcustomer = MCustomer::where('code_cust', $row[1])->first();

                // MCustomer::updateOrCreate([
                //     'id' => $id,
                //     'tgl_cust'  => trim($row[0],"'"),
                //     'code_cust' => $row[1] ?? '',
                //     'nama_cust' => $row[2] ?? '',
                // ]);

                Tpo::updateOrCreate([
                    'id_cust'   => $mcustomer->id,
                    'tgl_po'    => trim($row[0],"'"),
                    'code_cust' => $mcustomer->code_cust ?? '',
                    'nama_cust' => $mcustomer->nama_cust ?? '',
                    'no_po'     => $row[3] ?? '',
                    'no_so'     => $row[4] ?? '',
                    'status_po' => $row[5] ?? '',
                    'reason_po' => $row[6] ?? ''
                ]);
                
                Hpo::updateOrCreate([
                    'id_cust'   => $mcustomer->id,
                    'id_po'     => $id_po,
                    'tgl_po'    => trim($row[0],"'"),
                    'code_cust' => $mcustomer->code_cust ?? '',
                    'nama_cust' => $mcustomer->nama_cust ?? '',
                    'no_po'     => $row[3] ?? '',
                    'no_so'     => $row[4] ?? '',
                    'reason_po' => $row[6] ?? ''
                ]);
            }
    } 
}
