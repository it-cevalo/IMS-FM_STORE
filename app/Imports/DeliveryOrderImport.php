<?php

namespace App\Imports;

use App\Models\Tpo;
use App\Models\Tdo;
use App\Models\Hpo;
use App\Models\Hdo;
use App\Models\MCustomer;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;

class DeliveryOrderImport implements ToCollection, WithStartRow
{
    /**
    * @param Collection $collection
    */
    public function startRow(): int
    {
        return 2;
    }

    public function collection(Collection $rows)
    {

            $do = Tdo::select('id')->latest('id')->first();
            $errors = [];
            $validator = Validator::make([], []);
            if (!$do) {
                // Jika tidak ada record dalam database, maka buatkan id baru
                $id_do = 1;
            } else {
                // Jika ada record dalam database, maka tambahkan 1 ke id terakhir
                $id_do = $do->id + 1;
            }

            foreach ($rows as $row) 
            {
                
                $validator = Validator::make($row->toArray(), [
                    0   => ['required', 'date'],
                    1   => ['required', Rule::exists(Tpo::class, 'no_po')],
                    2   => ['required', 'string'],
                    3   => ['nullable', 'string'],
                    4   => ['nullable', 'string'],
                    5   => ['nullable', 'date'],
                    6   => ['nullable', 'string'],
                    7   => ['nullable', 'string'],
                    8   => ['nullable', 'string'],
                    9   => ['nullable', 'string'],
                ]);
    
                if ($validator->fails()) {
                    // Jika validasi gagal, kembalikan pesan kesalahan
                    
                    $errors[] = [
                        'row' => json_encode($row->toArray()),
                        'errors' => json_encode($validator->errors()->toArray())
                    ];
                } else {
                    // $mcustomer = MCustomer::where('code_cust', $row[1])->first();
                    $Tpo = Tpo::where('no_po', $row[1])->first();
                    
                    Tdo::updateOrCreate([
                        'id_po'          => $Tpo->id,
                        'id_supplier'    => $Tpo->id_supplier,
                        'tgl_po'         => trim($row[0],"'"),
                        'tgl_do'         => trim($row[5],"'"),
                        'code_cust'      => '',
                        'nama_cust'      => '',
                        'no_po'          => $Tpo->no_po ?? '',
                        'no_so'          => 0,
                        'no_do'          => $row[5] ?? '',
                        'status_lmpr_do' => $row[6] ?? '',
                        'reason_do'      => $row[7] ?? '',
                        'shipping_via'   => $row[8] ?? ''
                    ]);
                    
                    Hdo::create([
                        'id_do'     => $id_do,
                        'id_po'     => $Tpo->id,
                        'tgl_do'    => trim($row[5],"'"),
                        'code_cust' => '',
                        'nama_cust' => '',
                        'no_do'     => $row[5] ?? '',
                        'reason_do' => $row[7] ?? ''
                    ]);
                }
            }
            
            if (count($errors) > 0) {
                // Jika terdapat error, kembalikan ke halaman sebelumnya beserta pesan errornya
                return redirect()->back()->withInput()->withErrors($errors)->with(['fail' => 'There is invalid data in the uploaded file']);
            }
    } 
}
