<?php

namespace App\Exports;

use App\Models\Debt;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class DebtsExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithColumnWidths,
    WithTitle
{
    public function title(): string
    {
        return '💳 Debts';
    }

    public function collection()
    {
        return Debt::where('user_id', Auth::id())
            ->orderByRaw("status = 'paid' ASC")
            ->orderBy('due_date')
            ->get();
    }

    public function headings(): array
    {
        return ['Borrowed From', 'Amount (₱)', 'Reason', 'Due Date', 'Status'];
    }

    public function map($debt): array
    {
        return [
            $debt->borrowed_from,
            $debt->amount,
            $debt->reason ?? '—',
            $debt->due_date ? $debt->due_date->format('M d, Y') : '—',
            ucfirst($debt->status),
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 22,
            'B' => 16,
            'C' => 28,
            'D' => 16,
            'E' => 14,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();

        $sheet->getStyle('A1:E1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'BF360C']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        for ($row = 2; $row <= $lastRow; $row++) {
            $status = $sheet->getCell("E{$row}")->getValue();
            $bg = $status === 'Paid' ? 'EAF3DE' : 'FAEEDA';
            $fg = $status === 'Paid' ? '27500A' : '854F0B';
            $sheet->getStyle("A{$row}:E{$row}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bg]],
            ]);
            $sheet->getStyle("E{$row}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => $fg]],
            ]);
        }

        $sheet->getStyle("B2:B{$lastRow}")->getNumberFormat()->setFormatCode('₱#,##0.00');
        $sheet->getStyle("B2:E{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return [];
    }
}