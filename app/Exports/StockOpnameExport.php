<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StockOpnameExport implements
    FromCollection,
    WithHeadings,
    WithMapping
{
    public function collection()
    {
        return DB::table('t_stock_opname as so')
            ->join('mproduct as p', 'p.id', '=', 'so.id_product')
            ->join('m_warehouses as w', 'w.id', '=', 'so.id_warehouse')
            ->select(
                'w.code_wh',
                'w.nama_wh',
                'p.sku',
                'p.nama_barang',
                'so.qty_last',
                'so.tgl_opname'
            )
            ->orderBy('w.code_wh')
            ->orderBy('p.sku')
            ->get();
    }

    public function map($row): array
    {
        $qty = (int) ($row->qty_last ?? 0);
        return [
            $row->code_wh,
            $row->nama_wh,
            $row->sku,
            $row->nama_barang,
            $qty === 0 ? '-' : $qty,
            $row->tgl_opname,
        ];
    }

    public function headings(): array
    {
        return [
            'Kode Gudang',
            'Nama Gudang',
            'SKU',
            'Nama Barang',
            'Qty Terakhir',
            'Tanggal Opname',
        ];
    }
}
