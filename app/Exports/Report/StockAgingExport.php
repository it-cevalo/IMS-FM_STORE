<?php

namespace App\Exports\Report;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StockAgingExport implements FromCollection, WithHeadings
{
    protected $bucket;

    public function __construct($bucket)
    {
        $this->bucket = $bucket;
    }

    public function collection()
    {
        $query = DB::table('mproduct as p')
            ->leftJoin('tproduct_inbound as i', 'i.id_product', '=', 'p.id')
            ->leftJoin('t_stock_opname as so', 'so.id_product', '=', 'p.id')
            ->where('p.flag_active', 'Y')
            ->groupBy(
                'p.id',
                'p.sku',
                'p.nama_barang',
                'so.qty_in',
                'so.qty_last',
                'so.qty_out'
            )
            ->select([
                'p.sku',
                'p.nama_barang',
                DB::raw('(COALESCE(so.qty_in,0) + COALESCE(so.qty_last,0) - COALESCE(so.qty_out,0)) AS stock_on_hand'),
                DB::raw('MIN(i.received_at) AS first_in_date'),
                DB::raw('DATEDIFF(CURDATE(), DATE(MIN(i.received_at))) AS aging_days'),
                DB::raw("
                    CASE
                        WHEN DATEDIFF(CURDATE(), DATE(MIN(i.received_at))) <= 30 THEN '0-30'
                        WHEN DATEDIFF(CURDATE(), DATE(MIN(i.received_at))) BETWEEN 31 AND 60 THEN '31-60'
                        WHEN DATEDIFF(CURDATE(), DATE(MIN(i.received_at))) BETWEEN 61 AND 90 THEN '61-90'
                        ELSE '>90'
                    END AS aging_bucket
                ")
            ])
            ->havingRaw('stock_on_hand > 0');

        if ($this->bucket) {
            $query->having('aging_bucket', $this->bucket);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'SKU',
            'Nama Barang',
            'Stok Saat Ini',
            'Tanggal Masuk Pertama',
            'Umur (Hari)',
            'Kategori Aging'
        ];
    }
}