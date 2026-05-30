<?php

namespace App\Http\Controllers;

use App\Models\SavingsGoal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SavingsGoalController extends Controller
{
    public function index()
    {
        $goals = SavingsGoal::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        $totalTarget = $goals->sum('target_amount');
        $totalSaved  = $goals->sum('saved_amount');
        $completed   = $goals->filter->is_completed->count();

        return view('savings.index', compact('goals', 'totalTarget', 'totalSaved', 'completed'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'target_amount' => 'required|numeric|min:1',
            'saved_amount'  => 'nullable|numeric|min:0',
            'icon'          => 'nullable|string|max:10',
            'target_date'   => 'nullable|date',
        ]);

        $validated['user_id']      = Auth::id();
        $validated['saved_amount'] = $validated['saved_amount'] ?? 0;
        $validated['icon']         = $validated['icon'] ?? '🎯';

        SavingsGoal::create($validated);

        return back()->with('success', 'Savings goal added!');
    }

    public function update(Request $request, SavingsGoal $savingsGoal)
    {
        $this->authorize('update', $savingsGoal);

        $validated = $request->validate([
            'saved_amount' => 'required|numeric|min:0',
        ]);

        $savingsGoal->update($validated);

        return back()->with('success', 'Progress updated!');
    }

    public function destroy(SavingsGoal $savingsGoal)
    {
        $this->authorize('delete', $savingsGoal);
        $savingsGoal->delete();
        return back()->with('success', 'Goal removed.');
    }
}