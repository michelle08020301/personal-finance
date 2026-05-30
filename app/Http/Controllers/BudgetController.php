<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BudgetController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $budgets = Budget::where('user_id', $userId)->currentMonth()->get();

        $spentByCategory = Transaction::forUser($userId)
            ->thisMonth()
            ->expense()
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        $categories = Transaction::CATEGORIES;

        return view('budgets.index', compact('budgets', 'spentByCategory', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category'     => 'required|string',
            'limit_amount' => 'required|numeric|min:1',
        ]);

        Budget::updateOrCreate(
            [
                'user_id'  => Auth::id(),
                'category' => $validated['category'],
                'month'    => now()->month,
                'year'     => now()->year,
            ],
            ['limit_amount' => $validated['limit_amount']]
        );

        return back()->with('success', 'Budget saved!');
    }

    public function destroy(Budget $budget)
    {
        $this->authorize('delete', $budget);
        $budget->delete();
        return back()->with('success', 'Budget removed.');
    }
}