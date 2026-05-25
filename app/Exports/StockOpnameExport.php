<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class StockOpnameExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithCustomStartCell,
    WithEvents
{
    protected string $printedBy;

    public function __construct(string $printedBy = '')
    {
        $this->printedBy = $printedBy;
    }

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

    public function startCell(): string
    {
        return 'A1';
    }

    public function registerEvents(): array
    {
        $version   = config('app.version');
        $printDate = now()->format('d/m/Y H:i');
        $printedBy = $this->printedBy;
        return [
            AfterSheet::class => function (AfterSheet $event) use ($version, $printDate, $printedBy) {
                $sheet   = $event->sheet->getDelegate();
                $metaRow = $sheet->getHighestRow() + 2;
                $sheet->setCellValue('A' . $metaRow, 'Dicetak: ' . $printDate . '   |   Oleh: ' . $printedBy . '   |   Versi: ' . $version);
                $sheet->getStyle('A' . $metaRow)->getFont()->setSize(9);
                $sheet->getStyle('A' . $metaRow)->getFont()->getColor()->setARGB('FF888888');
            },
        ];
    }
}
