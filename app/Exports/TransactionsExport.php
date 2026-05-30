<?php

namespace App\Exports;

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

class TransactionsExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithColumnWidths,
    WithTitle
{
    public function title(): string
    {
        return '📥 Transactions';
    }

    public function collection()
    {
        return Transaction::forUser(Auth::id())
            ->orderBy('date', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return ['Date', 'Description', 'Category', 'Type', 'Amount (₱)', 'Notes'];
    }

    public function map($transaction): array
    {
        return [
            $transaction->date->format('M d, Y'),
            $transaction->description,
            $transaction->category,
            ucfirst($transaction->type),
            $transaction->amount,
            $transaction->notes ?? '',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 16,
            'B' => 30,
            'C' => 16,
            'D' => 12,
            'E' => 16,
            'F' => 24,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();

        // Header row style
        $sheet->getStyle('A1:F1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0D1B2A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Data rows — alternate colors
        for ($row = 2; $row <= $lastRow; $row++) {
            $bg = $row % 2 === 0 ? 'F5F5F5' : 'FFFFFF';
            $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bg]],
            ]);

            // Color type column
            $type = $sheet->getCell("D{$row}")->getValue();
            if ($type === 'Income') {
                $sheet->getStyle("E{$row}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '27500A']],
                ]);
            } else {
                $sheet->getStyle("E{$row}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'A32D2D']],
                ]);
            }
        }

        // Amount column — peso format
        $sheet->getStyle("E2:E{$lastRow}")
              ->getNumberFormat()
              ->setFormatCode('₱#,##0.00');

        // Center align certain columns
        $sheet->getStyle("A2:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("C2:D{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("E2:E{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        return [];
    }
}