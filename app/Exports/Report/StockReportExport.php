<?php

namespace App\Exports\Report;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockReportExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithCustomStartCell, WithEvents
{
    protected string $startDate;
    protected string $endDate;
    protected string $printedBy;

    public function __construct(string $startDate, string $endDate, string $printedBy = '')
    {
        $this->startDate = $startDate . ' 00:00:00';
        $this->endDate   = $endDate   . ' 23:59:59';
        $this->printedBy = $printedBy;
    }

    public function collection()
    {
        $beforeIn = DB::table('tproduct_inbound')
            ->select('id_product', DB::raw('SUM(qty) as qty'))
            ->whereNotNull('sync_at')
            ->where('received_at', '<', $this->startDate)
            ->groupBy('id_product');

        $beforeOut = DB::table('tproduct_outbound')
            ->select('id_product', DB::raw('SUM(qty) as qty'))
            ->whereNotNull('sync_at')
            ->where('out_at', '<', $this->startDate)
            ->groupBy('id_product');

        $periodIn = DB::table('tproduct_inbound')
            ->select(
                'id_product',
                DB::raw("SUM(CASE WHEN inbound_source = 'PO' THEN qty ELSE 0 END) as qty_in"),
                DB::raw("SUM(CASE WHEN inbound_source = 'RETUR_CUST' THEN qty ELSE 0 END) as qty_return")
            )
            ->whereNotNull('sync_at')
            ->whereBetween('received_at', [$this->startDate, $this->endDate])
            ->groupBy('id_product');

        $periodOut = DB::table('tproduct_outbound')
            ->select('id_product', DB::raw('SUM(qty) as qty'))
            ->whereNotNull('sync_at')
            ->whereBetween('out_at', [$this->startDate, $this->endDate])
            ->groupBy('id_product');

        return DB::table('mproduct as p')
            ->leftJoinSub($beforeIn,  'bi', 'bi.id_product', '=', 'p.id')
            ->leftJoinSub($beforeOut, 'bo', 'bo.id_product', '=', 'p.id')
            ->leftJoinSub($periodIn,  'pi', 'pi.id_product', '=', 'p.id')
            ->leftJoinSub($periodOut, 'po', 'po.id_product', '=', 'p.id')
            ->where('p.flag_active', 'Y')
            ->select([
                'p.sku',
                'p.nama_barang',
                DB::raw('(COALESCE(bi.qty, 0) - COALESCE(bo.qty, 0)) as last_stock'),
                DB::raw('COALESCE(po.qty, 0) as stock_out'),
                DB::raw('COALESCE(pi.qty_return, 0) as return_qty'),
                DB::raw('
                    (COALESCE(bi.qty, 0) - COALESCE(bo.qty, 0))
                    + COALESCE(pi.qty_in, 0)
                    + COALESCE(pi.qty_return, 0)
                    - COALESCE(po.qty, 0)
                    as remain
                '),
                DB::raw('COALESCE(pi.qty_in, 0) as stock_in'),
            ])
            ->orderBy('p.nama_barang')
            ->get();
    }

    public function headings(): array
    {
        return [
            'SKU',
            'Nama Barang',
            'Last Stock',
            'Stock Out',
            'Return',
            'Remain',
            'Stock In',
        ];
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function registerEvents(): array
    {
        $version     = config('app.version');
        $printDate   = now()->format('d/m/Y H:i');
        $printedBy   = $this->printedBy;
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

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 40,
            'C' => 14,
            'D' => 14,
            'E' => 14,
            'F' => 14,
            'G' => 14,
        ];
    }
}
