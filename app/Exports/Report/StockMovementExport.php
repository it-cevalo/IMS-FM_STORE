<?php

namespace App\Exports\Report;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class StockMovementExport implements FromCollection, WithHeadings, WithCustomStartCell, WithEvents
{
    protected $startDate;
    protected $endDate;
    protected $category;
    protected string $printedBy;

    public function __construct($startDate, $endDate, $category, string $printedBy = '')
    {
        $this->startDate = $startDate;
        $this->endDate   = $endDate;
        $this->category  = $category;
        $this->printedBy = $printedBy;
    }

    public function collection()
    {
        $days = now()->parse($this->startDate)
            ->diffInDays(now()->parse($this->endDate)) ?: 30;

        $query = DB::table('mproduct as p')
            ->leftJoin('tproduct_inbound as i', function ($join) {
                $join->on('i.id_product', '=', 'p.id')
                     ->whereBetween('i.received_at', [$this->startDate, $this->endDate]);
            })
            ->leftJoin('tproduct_outbound as o', function ($join) {
                $join->on('o.id_product', '=', 'p.id')
                     ->whereBetween('o.out_at', [$this->startDate, $this->endDate]);
            })
            ->where('p.flag_active', 'Y')
            ->groupBy('p.id', 'p.sku', 'p.nama_barang')
            ->select([
                'p.sku',
                'p.nama_barang',
                DB::raw('COALESCE(SUM(i.qty),0) AS qty_in'),
                DB::raw('COALESCE(SUM(o.qty),0) AS qty_out'),
                DB::raw('MAX(o.out_at) AS last_out_date'),
                DB::raw("ROUND(COALESCE(SUM(o.qty),0)/{$days},2) AS movement_rate"),
                DB::raw("
                    CASE
                        WHEN COALESCE(SUM(o.qty),0)=0 AND MAX(o.out_at) IS NULL THEN 'DEAD'
                        WHEN COALESCE(SUM(o.qty),0)>=20 THEN 'FAST'
                        WHEN COALESCE(SUM(o.qty),0) BETWEEN 5 AND 19 THEN 'MEDIUM'
                        ELSE 'SLOW'
                    END AS status
                ")
            ]);

        if ($this->category) {
            $query->having('status', $this->category);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'SKU',
            'Nama Barang',
            'Qty In',
            'Qty Out',
            'Last Out',
            'Movement Rate',
            'Status'
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