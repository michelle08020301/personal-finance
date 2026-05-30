<?php

namespace App\Http\Controllers;

use App\Models\Debt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DebtController extends Controller
{
    public function index()
    {
        $debts = Debt::where('user_id', Auth::id())
            ->orderByRaw("status = 'paid' ASC")
            ->orderBy('due_date')
            ->get();

        $totalUnpaid = $debts->where('status', 'unpaid')->sum('amount');

        return view('debts.index', compact('debts', 'totalUnpaid'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'borrowed_from' => 'required|string|max:255',
            'amount'        => 'required|numeric|min:0.01',
            'reason'        => 'nullable|string|max:500',
            'due_date'      => 'nullable|date',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['status']  = 'unpaid';

        Debt::create($validated);

        return back()->with('success', 'Debt recorded.');
    }

    public function markPaid(Debt $debt)
    {
        $this->authorize('update', $debt);
        $debt->update([
            'status' => $debt->status === 'paid' ? 'unpaid' : 'paid'
        ]);
        return back()->with('success', 'Status updated!');
    }

    public function destroy(Debt $debt)
    {
        $this->authorize('delete', $debt);
        $debt->delete();
        return back()->with('success', 'Debt removed.');
    }
}