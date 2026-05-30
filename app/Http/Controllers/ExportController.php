<?php

namespace App\Http\Controllers;

use App\Exports\TransactionsExport;
use App\Exports\BudgetExport;
use App\Exports\DebtsExport;
use App\Models\Transaction;
use App\Models\Budget;
use App\Models\Debt;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ExportController extends Controller
{
    public function exportExcel()
    {
        $userId   = Auth::id();
        $userName = Auth::user()->name;
        $month    = now()->format('F Y');
        $filename = 'PersonalFinance_' . Auth::user()->name . '_' . now()->format('MY') . '.xlsx';

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        // ── Sheet 1: Summary Dashboard ──────────────────────────────────────
        $ws1 = $spreadsheet->createSheet(0);
        $ws1->setTitle('📊 Dashboard');
        
        $totalIncome   = Transaction::forUser($userId)->thisMonth()->income()->sum('amount');
        $totalExpenses = Transaction::forUser($userId)->thisMonth()->expense()->sum('amount');
        $balance       = $totalIncome - $totalExpenses;
        $totalUnpaid   = Debt::where('user_id', $userId)->unpaid()->sum('amount');

        $cols = ['A'=>3,'B'=>28,'C'=>20,'D'=>20,'E'=>20,'F'=>3];
        foreach ($cols as $col => $width) {
            $ws1->getColumnDimension($col)->setWidth($width);
        }

        // Title
        $ws1->mergeCells('B2:E2');
        $ws1->setCellValue('B2', "Personal Finance — Dashboard Report");
        $ws1->getStyle('B2')->applyFromArray([
            'font' => ['bold'=>true,'color'=>['rgb'=>'FFFFFF'],'size'=>16,'name'=>'Arial'],
            'fill' => ['fillType'=>Fill::FILL_SOLID,'startColor'=>['rgb'=>'0D1B2A']],
            'alignment' => ['horizontal'=>Alignment::HORIZONTAL_LEFT,'vertical'=>Alignment::VERTICAL_CENTER],
        ]);
        $ws1->getRowDimension(2)->setRowHeight(44);

        // Meta
        $ws1->getRowDimension(3)->setRowHeight(8);
        $ws1->getRowDimension(4)->setRowHeight(22);
        foreach ([
            ['B4', 'User:', 'C4', $userName],
            ['D4', 'Month:', 'E4', $month],
        ] as [$lc, $lv, $vc, $vv]) {
            $ws1->setCellValue($lc, $lv);
            $ws1->getStyle($lc)->getFont()->setBold(true)->setColor((new \PhpOffice\PhpSpreadsheet\Style\Color('FF888888')));
            $ws1->getStyle($lc)->getFont()->setName('Arial')->setSize(10);
            $ws1->setCellValue($vc, $vv);
            $ws1->getStyle($vc)->getFont()->setName('Arial')->setSize(10);
        }

        // Metric cards
        $ws1->getRowDimension(5)->setRowHeight(10);
        $ws1->getRowDimension(6)->setRowHeight(22);
        $ws1->getRowDimension(7)->setRowHeight(38);
        $ws1->getRowDimension(8)->setRowHeight(10);

        $metrics = [
            ['B', 'TOTAL INCOME', '₱'.number_format($totalIncome,2), 'EAF3DE', '27500A'],
            ['C', 'TOTAL EXPENSES', '₱'.number_format($totalExpenses,2), 'FCEBEB', 'A32D2D'],
            ['D', 'NET BALANCE', '₱'.number_format($balance,2), 'E6F1FB', '185FA5'],
            ['E', 'TOTAL UNPAID DEBT', '₱'.number_format($totalUnpaid,2), 'FAEEDA', '854F0B'],
        ];
        foreach ($metrics as [$col, $label, $value, $bg, $fg]) {
            $ws1->setCellValue("{$col}6", $label);
            $ws1->getStyle("{$col}6")->applyFromArray([
                'font' => ['bold'=>true,'color'=>['rgb'=>'FFFFFF'],'size'=>9,'name'=>'Arial'],
                'fill' => ['fillType'=>Fill::FILL_SOLID,'startColor'=>['rgb'=>'0D1B2A']],
                'alignment' => ['horizontal'=>Alignment::HORIZONTAL_CENTER,'vertical'=>Alignment::VERTICAL_CENTER],
            ]);
            $ws1->setCellValue("{$col}7", $value);
            $ws1->getStyle("{$col}7")->applyFromArray([
                'font' => ['bold'=>true,'color'=>['rgb'=>$fg],'size'=>16,'name'=>'Arial'],
                'fill' => ['fillType'=>Fill::FILL_SOLID,'startColor'=>['rgb'=>$bg]],
                'alignment' => ['horizontal'=>Alignment::HORIZONTAL_CENTER,'vertical'=>Alignment::VERTICAL_CENTER],
            ]);
        }

        // Budget status
        $ws1->getRowDimension(9)->setRowHeight(26);
        $ws1->mergeCells('B9:E9');
        $ws1->setCellValue('B9', 'BUDGET STATUS — ' . strtoupper($month));
        $ws1->getStyle('B9')->applyFromArray([
            'font' => ['bold'=>true,'color'=>['rgb'=>'FFFFFF'],'size'=>10,'name'=>'Arial'],
            'fill' => ['fillType'=>Fill::FILL_SOLID,'startColor'=>['rgb'=>'27500A']],
            'alignment' => ['horizontal'=>Alignment::HORIZONTAL_CENTER,'vertical'=>Alignment::VERTICAL_CENTER],
        ]);

        $ws1->getRowDimension(10)->setRowHeight(22);
        foreach ([['B10','Category'],['C10','Budget Limit'],['D10','Spent'],['E10','Remaining']] as [$cell,$val]) {
            $ws1->setCellValue($cell, $val);
            $ws1->getStyle($cell)->applyFromArray([
                'font' => ['bold'=>true,'color'=>['rgb'=>'FFFFFF'],'size'=>10,'name'=>'Arial'],
                'fill' => ['fillType'=>Fill::FILL_SOLID,'startColor'=>['rgb'=>'0D1B2A']],
                'alignment' => ['horizontal'=>Alignment::HORIZONTAL_CENTER,'vertical'=>Alignment::VERTICAL_CENTER],
            ]);
        }

        $budgets = Budget::where('user_id', $userId)->currentMonth()->get();
        $budgetRow = 11;
        foreach ($budgets as $i => $budget) {
            $spent     = Transaction::forUser($userId)->thisMonth()->expense()->where('category',$budget->category)->sum('amount');
            $remaining = $budget->limit_amount - $spent;
            $pct       = $budget->limit_amount > 0 ? ($spent/$budget->limit_amount)*100 : 0;
            $bg        = $pct >= 100 ? 'FCEBEB' : ($pct >= 70 ? 'FAEEDA' : 'EAF3DE');

            $ws1->getRowDimension($budgetRow)->setRowHeight(20);
            $ws1->setCellValue("B{$budgetRow}", $budget->category);
            $ws1->setCellValue("C{$budgetRow}", $budget->limit_amount);
            $ws1->setCellValue("D{$budgetRow}", $spent);
            $ws1->setCellValue("E{$budgetRow}", $remaining);

            $ws1->getStyle("B{$budgetRow}:E{$budgetRow}")->applyFromArray([
                'fill' => ['fillType'=>Fill::FILL_SOLID,'startColor'=>['rgb'=>$bg]],
                'alignment' => ['horizontal'=>Alignment::HORIZONTAL_CENTER,'vertical'=>Alignment::VERTICAL_CENTER],
            ]);
            $ws1->getStyle("B{$budgetRow}")->getFont()->setName('Arial')->setSize(10)->setBold(true);
            $ws1->getStyle("C{$budgetRow}:E{$budgetRow}")->getNumberFormat()->setFormatCode('₱#,##0.00');
            $budgetRow++;
        }

        // ── Sheet 2: Transactions ────────────────────────────────────────────
        $txExport = new TransactionsExport();
        $ws2 = $spreadsheet->createSheet(1);
        $ws2->setTitle('📥 Transactions');
               $transactions = Transaction::forUser($userId)->orderBy('date','desc')->get();
        $headers = ['Date','Description','Category','Type','Amount (₱)','Notes'];
        $ws2->getRowDimension(1)->setRowHeight(30);

        foreach ($headers as $i => $h) {
            $col = chr(65+$i);
            $ws2->setCellValue("{$col}1", $h);
            $ws2->getStyle("{$col}1")->applyFromArray([
                'font' => ['bold'=>true,'color'=>['rgb'=>'FFFFFF'],'size'=>10,'name'=>'Arial'],
                'fill' => ['fillType'=>Fill::FILL_SOLID,'startColor'=>['rgb'=>'0D1B2A']],
                'alignment' => ['horizontal'=>Alignment::HORIZONTAL_CENTER,'vertical'=>Alignment::VERTICAL_CENTER],
            ]);
        }
        foreach (['A'=>16,'B'=>30,'C'=>16,'D'=>12,'E'=>16,'F'=>24] as $col=>$w) {
            $ws2->getColumnDimension($col)->setWidth($w);
        }

        foreach ($transactions as $i => $tx) {
            $row = $i + 2;
            $bg  = $i%2===0 ? 'FFFFFF' : 'F5F5F5';
            $ws2->getRowDimension($row)->setRowHeight(20);
            $ws2->setCellValue("A{$row}", $tx->date->format('M d, Y'));
            $ws2->setCellValue("B{$row}", $tx->description);
            $ws2->setCellValue("C{$row}", $tx->category);
            $ws2->setCellValue("D{$row}", ucfirst($tx->type));
            $ws2->setCellValue("E{$row}", $tx->amount);
            $ws2->setCellValue("F{$row}", $tx->notes ?? '');

            $ws2->getStyle("A{$row}:F{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($bg);
            $amtColor = $tx->type === 'income' ? '27500A' : 'A32D2D';
            $ws2->getStyle("E{$row}")->getFont()->setBold(true)->setName('Arial')->setSize(10);
            $ws2->getStyle("E{$row}")->getFont()->getColor()->setRGB($amtColor);
            $ws2->getStyle("E{$row}")->getNumberFormat()->setFormatCode('₱#,##0.00');
            $ws2->getStyle("A{$row}:F{$row}")->getFont()->setName('Arial')->setSize(10);
            $ws2->getStyle("A{$row}:D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // ── Sheet 3: Budget ──────────────────────────────────────────────────
        $ws3 = $spreadsheet->createSheet(2);
        $ws3->setTitle('📋 Budget');
        
        foreach (['A'=>18,'B'=>18,'C'=>16,'D'=>18,'E'=>12,'F'=>16] as $col=>$w) {
            $ws3->getColumnDimension($col)->setWidth($w);
        }
        $ws3->getRowDimension(1)->setRowHeight(30);
        foreach ([['A','Category'],['B','Budget Limit (₱)'],['C','Spent (₱)'],['D','Remaining (₱)'],['E','% Used'],['F','Status']] as [$col,$h]) {
            $ws3->setCellValue("{$col}1", $h);
            $ws3->getStyle("{$col}1")->applyFromArray([
                'font' => ['bold'=>true,'color'=>['rgb'=>'FFFFFF'],'size'=>10,'name'=>'Arial'],
                'fill' => ['fillType'=>Fill::FILL_SOLID,'startColor'=>['rgb'=>'0D1B2A']],
                'alignment' => ['horizontal'=>Alignment::HORIZONTAL_CENTER,'vertical'=>Alignment::VERTICAL_CENTER],
            ]);
        }

        foreach ($budgets as $i => $budget) {
            $row   = $i + 2;
            $spent = Transaction::forUser($userId)->thisMonth()->expense()->where('category',$budget->category)->sum('amount');
            $rem   = $budget->limit_amount - $spent;
            $pct   = $budget->limit_amount > 0 ? $spent/$budget->limit_amount : 0;
            $status= $pct >= 1 ? 'Over Budget!' : ($pct >= 0.7 ? 'Warning' : 'On Track');
            $bg    = $pct >= 1 ? 'FCEBEB' : ($pct >= 0.7 ? 'FAEEDA' : 'EAF3DE');
            $fg    = $pct >= 1 ? 'A32D2D' : ($pct >= 0.7 ? '854F0B' : '27500A');

            $ws3->getRowDimension($row)->setRowHeight(22);
            $ws3->setCellValue("A{$row}", $budget->category);
            $ws3->setCellValue("B{$row}", $budget->limit_amount);
            $ws3->setCellValue("C{$row}", $spent);
            $ws3->setCellValue("D{$row}", $rem);
            $ws3->setCellValue("E{$row}", $pct);
            $ws3->setCellValue("F{$row}", $status);

            $ws3->getStyle("A{$row}:F{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($bg);
            $ws3->getStyle("F{$row}")->getFont()->setBold(true)->setName('Arial')->setSize(10)->getColor()->setRGB($fg);
            $ws3->getStyle("A{$row}:F{$row}")->getFont()->setName('Arial')->setSize(10);
            $ws3->getStyle("B{$row}:D{$row}")->getNumberFormat()->setFormatCode('₱#,##0.00');
            $ws3->getStyle("E{$row}")->getNumberFormat()->setFormatCode('0.0%');
            $ws3->getStyle("A{$row}:F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // ── Sheet 4: Debts ───────────────────────────────────────────────────
        $ws4 = $spreadsheet->createSheet(3);
        $ws4->setTitle('💳 Debts');
        

        foreach (['A'=>22,'B'=>16,'C'=>28,'D'=>16,'E'=>14] as $col=>$w) {
            $ws4->getColumnDimension($col)->setWidth($w);
        }
        $ws4->getRowDimension(1)->setRowHeight(30);
        foreach ([['A','Borrowed From'],['B','Amount (₱)'],['C','Reason'],['D','Due Date'],['E','Status']] as [$col,$h]) {
            $ws4->setCellValue("{$col}1", $h);
            $ws4->getStyle("{$col}1")->applyFromArray([
                'font' => ['bold'=>true,'color'=>['rgb'=>'FFFFFF'],'size'=>10,'name'=>'Arial'],
                'fill' => ['fillType'=>Fill::FILL_SOLID,'startColor'=>['rgb'=>'BF360C']],
                'alignment' => ['horizontal'=>Alignment::HORIZONTAL_CENTER,'vertical'=>Alignment::VERTICAL_CENTER],
            ]);
        }

        $debts = Debt::where('user_id',$userId)->orderByRaw("status='paid' ASC")->orderBy('due_date')->get();
        foreach ($debts as $i => $debt) {
            $row = $i + 2;
            $bg  = $debt->status === 'paid' ? 'EAF3DE' : 'FAEEDA';
            $fg  = $debt->status === 'paid' ? '27500A' : '854F0B';

            $ws4->getRowDimension($row)->setRowHeight(22);
            $ws4->setCellValue("A{$row}", $debt->borrowed_from);
            $ws4->setCellValue("B{$row}", $debt->amount);
            $ws4->setCellValue("C{$row}", $debt->reason ?? '—');
            $ws4->setCellValue("D{$row}", $debt->due_date ? $debt->due_date->format('M d, Y') : '—');
            $ws4->setCellValue("E{$row}", ucfirst($debt->status));

            $ws4->getStyle("A{$row}:E{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($bg);
            $ws4->getStyle("E{$row}")->getFont()->setBold(true)->setName('Arial')->setSize(10)->getColor()->setRGB($fg);
            $ws4->getStyle("A{$row}:E{$row}")->getFont()->setName('Arial')->setSize(10);
            $ws4->getStyle("B{$row}")->getNumberFormat()->setFormatCode('₱#,##0.00');
            $ws4->getStyle("B{$row}:E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // ── Output ───────────────────────────────────────────────────────────
        $spreadsheet->setActiveSheetIndex(0);

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }
}