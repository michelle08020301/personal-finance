<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Debt;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $totalIncome   = Transaction::forUser($userId)->thisMonth()->income()->sum('amount');
        $totalExpenses = Transaction::forUser($userId)->thisMonth()->expense()->sum('amount');
        $balance       = $totalIncome - $totalExpenses;

        $recentTransactions = Transaction::forUser($userId)
            ->orderBy('date', 'desc')
            ->limit(5)
            ->get();

        $budgets = Budget::where('user_id', $userId)->currentMonth()->get();

        $spentByCategory = Transaction::forUser($userId)
            ->thisMonth()
            ->expense()
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        $totalUnpaidDebt = Debt::where('user_id', $userId)->unpaid()->sum('amount');
        $activeDebts     = Debt::where('user_id', $userId)->unpaid()->orderBy('due_date')->limit(5)->get();

        // Monthly chart data — last 6 months
        $chartLabels  = [];
        $chartIncome  = [];
        $chartExpense = [];

        for ($i = 5; $i >= 0; $i--) {
            $date  = now()->subMonths($i);
            $month = $date->month;
            $year  = $date->year;

            $chartLabels[]  = $date->format('M Y');
            $chartIncome[]  = (float) Transaction::forUser($userId)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->income()
                ->sum('amount');
            $chartExpense[] = (float) Transaction::forUser($userId)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->expense()
                ->sum('amount');
        }

        return view('dashboard.index', compact(
            'totalIncome', 'totalExpenses', 'balance',
            'recentTransactions', 'budgets', 'spentByCategory',
            'totalUnpaidDebt', 'activeDebts',
            'chartLabels', 'chartIncome', 'chartExpense'
        ));
    }
}