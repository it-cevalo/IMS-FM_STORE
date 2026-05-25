<?php

namespace App\Exports\Report;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class StockAgingExport implements FromCollection, WithHeadings, WithCustomStartCell, WithEvents
{
    protected $bucket;
    protected string $printedBy;

    public function __construct($bucket, string $printedBy = '')
    {
        $this->bucket    = $bucket;
        $this->printedBy = $printedBy;
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