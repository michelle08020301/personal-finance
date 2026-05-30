<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::forUser(Auth::id())->orderBy('date', 'desc');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('month')) {
            $query->whereMonth('date', $request->month);
        }

        $transactions = $query->paginate(15);
        $categories   = Transaction::CATEGORIES;

        return view('transactions.index', compact('transactions', 'categories'));
    }

    public function create()
    {
        $categories = Transaction::CATEGORIES;
        return view('transactions.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type'        => 'required|in:income,expense',
            'amount'      => 'required|numeric|min:0.01',
            'category'    => 'required|string',
            'description' => 'required|string|max:255',
            'date'        => 'required|date',
            'notes'       => 'nullable|string|max:500',
        ]);

        $validated['user_id'] = Auth::id();
        Transaction::create($validated);

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction saved!');
    }

    public function edit(Transaction $transaction)
    {
        $this->authorize('update', $transaction);
        $categories = Transaction::CATEGORIES;
        return view('transactions.edit', compact('transaction', 'categories'));
    }

    public function update(Request $request, Transaction $transaction)
    {
        $this->authorize('update', $transaction);

        $validated = $request->validate([
            'type'        => 'required|in:income,expense',
            'amount'      => 'required|numeric|min:0.01',
            'category'    => 'required|string',
            'description' => 'required|string|max:255',
            'date'        => 'required|date',
            'notes'       => 'nullable|string|max:500',
        ]);

        $transaction->update($validated);

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction updated!');
    }

    public function destroy(Transaction $transaction)
    {
        $this->authorize('delete', $transaction);
        $transaction->delete();
        return back()->with('success', 'Transaction deleted.');
    }
}