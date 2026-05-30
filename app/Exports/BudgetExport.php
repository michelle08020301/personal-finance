<?php

namespace App\Exports;

use App\Models\Budget;
use App\Models\Transaction;
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

class BudgetExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithColumnWidths,
    WithTitle
{
    public function title(): string
    {
        return '📋 Budget';
    }

    public function collection()
    {
        return Budget::where('user_id', Auth::id())
            ->currentMonth()
            ->get();
    }

    public function headings(): array
    {
        return ['Category', 'Budget Limit (₱)', 'Spent (₱)', 'Remaining (₱)', '% Used', 'Status'];
    }

    public function map($budget): array
    {
        $spent     = Transaction::forUser(Auth::id())
            ->thisMonth()
            ->expense()
            ->where('category', $budget->category)
            ->sum('amount');
        $remaining = $budget->limit_amount - $spent;
        $pct       = $budget->limit_amount > 0
            ? round(($spent / $budget->limit_amount) * 100, 1)
            : 0;
        $status    = $pct >= 100 ? 'Over Budget!' : ($pct >= 70 ? 'Warning' : 'On Track');

        return [
            $budget->category,
            $budget->limit_amount,
            $spent,
            $remaining,
            $pct / 100,
            $status,
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 18,
            'B' => 18,
            'C' => 16,
            'D' => 18,
            'E' => 12,
            'F' => 16,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();

        $sheet->getStyle('A1:F1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0D1B2A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        for ($row = 2; $row <= $lastRow; $row++) {
            $status = $sheet->getCell("F{$row}")->getValue();
            $bg = match($status) {
                'Over Budget!' => 'FCEBEB',
                'Warning'      => 'FAEEDA',
                default        => 'EAF3DE',
            };
            $fg = match($status) {
                'Over Budget!' => 'A32D2D',
                'Warning'      => '854F0B',
                default        => '27500A',
            };
            $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bg]],
            ]);
            $sheet->getStyle("F{$row}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => $fg]],
            ]);
        }

        $pesoFmt = '₱#,##0.00';
        $sheet->getStyle("B2:D{$lastRow}")->getNumberFormat()->setFormatCode($pesoFmt);
        $sheet->getStyle("E2:E{$lastRow}")->getNumberFormat()->setFormatCode('0.0%');
        $sheet->getStyle("B2:F{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return [];
    }
}