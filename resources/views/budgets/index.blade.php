@extends('layouts.app')
@section('title', 'Budget')

@section('content')

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="page-title">Budget</h1>
        <p class="text-xs mt-0.5" style="color:rgba(255,255,255,0.3)">{{ now()->format('F Y') }}</p>
    </div>
</div>
{{-- Budget Alerts --}}
@php
    $budgetAlerts = $budgets->map(function($budget) use ($spentByCategory) {
        $spent = $spentByCategory[$budget->category] ?? 0;
        $pct   = $budget->limit_amount > 0 ? ($spent / $budget->limit_amount) * 100 : 0;
        return ['budget' => $budget, 'spent' => $spent, 'pct' => $pct];
    })->filter(fn($b) => $b['pct'] >= 70)->sortByDesc('pct');
@endphp

@if($budgetAlerts->count() > 0)
    <div class="mb-4 space-y-2">
        @foreach($budgetAlerts as $alert)
            @php $over = $alert['pct'] >= 100; @endphp
            <div style="display:flex;align-items:center;gap:12px;padding:10px 16px;border-radius:12px;
                        background:{{ $over ? 'rgba(255,100,100,0.1)' : 'rgba(255,180,60,0.1)' }};
                        border:1px solid {{ $over ? 'rgba(255,100,100,0.3)' : 'rgba(255,180,60,0.3)' }}">
                <span style="font-size:18px">{{ $over ? '🚨' : '⚠️' }}</span>
                <div style="flex:1">
                    <p style="font-size:13px;font-weight:500;color:{{ $over ? '#ff8080' : '#ffb840' }}">
                        {{ $over ? 'Over budget!' : 'Nearing budget limit!' }}
                        <strong>{{ $alert['budget']->category }}</strong>
                    </p>
                    <p style="font-size:11px;color:rgba(255,255,255,0.4);margin-top:2px">
                        Spent ₱{{ number_format($alert['spent'], 2) }} of ₱{{ number_format($alert['budget']->limit_amount, 2) }} limit
                        · {{ number_format($alert['pct'], 0) }}% used
                        @if(!$over)
                            · ₱{{ number_format($alert['budget']->limit_amount - $alert['spent'], 2) }} remaining
                        @else
                            · ₱{{ number_format($alert['spent'] - $alert['budget']->limit_amount, 2) }} over limit
                        @endif
                    </p>
                </div>
                <span style="font-size:12px;font-weight:500;padding:3px 10px;border-radius:20px;
                             background:{{ $over ? 'rgba(255,100,100,0.2)' : 'rgba(255,180,60,0.2)' }};
                             color:{{ $over ? '#ff8080' : '#ffb840' }}">
                    {{ number_format($alert['pct'], 0) }}%
                </span>
            </div>
        @endforeach
    </div>
@endif
<div class="grid grid-cols-3 gap-4">

    <div class="col-span-2 glass-card overflow-hidden">
        <table class="glass-table">
            <thead>
                <tr>
                    <th>Category</th>
                    <th style="text-align:right">Limit</th>
                    <th style="text-align:right">Spent</th>
                    <th style="text-align:right">Remaining</th>
                    <th>Progress</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($budgets as $budget)
                    @php
                        $spent     = $spentByCategory[$budget->category] ?? 0;
                        $remaining = $budget->limit_amount - $spent;
                        $pct       = $budget->limit_amount > 0 ? min(($spent / $budget->limit_amount) * 100, 100) : 0;
                        $barClass  = $pct < 70 ? 'progress-safe' : ($pct < 100 ? 'progress-warn' : 'progress-over');
                    @endphp
                    <tr>
                        <td class="font-medium" style="color:rgba(255,255,255,0.82)">{{ $budget->category }}</td>
                        <td style="text-align:right;color:rgba(255,255,255,0.5)">₱{{ number_format($budget->limit_amount,2) }}</td>
                        <td style="text-align:right;color:#ff8080">₱{{ number_format($spent,2) }}</td>
                        <td style="text-align:right;color:{{ $remaining < 0 ? '#ff8080' : '#c8ff80' }};font-weight:{{ $remaining < 0 ? '500' : '400' }}">
                            ₱{{ number_format($remaining,2) }}
                        </td>
                        <td style="min-width:100px">
                            <div class="progress-track" style="margin-top:0">
                                <div class="progress-fill {{ $barClass }}" style="width:{{ $pct }}%"></div>
                            </div>
                            <p class="text-xs mt-1" style="color:rgba(255,255,255,0.25)">
                                {{ number_format($pct,0) }}%
                                @if($pct >= 100)<span style="color:#ff8080"> · Over!</span>@endif
                            </p>
                        </td>
                        <td>
                            <form method="POST" action="{{ route('budgets.destroy', $budget) }}"
                                  onsubmit="return confirm('Remove this budget?')">
                                @csrf @method('DELETE')
                                <button style="font-size:12px;color:rgba(255,100,100,0.5)">Remove</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-12 text-sm" style="color:rgba(255,255,255,0.3)">
                            No budgets set for this month.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="glass-card p-5">
        <p class="glass-label mb-4">Set budget limit</p>
        <form method="POST" action="{{ route('budgets.store') }}">
            @csrf
            <div class="mb-4">
                <label class="glass-label">Category</label>
                <select name="category" class="glass-input">
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}">{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-5">
                <label class="glass-label">Monthly limit (₱)</label>
                <input type="number" name="limit_amount" min="1" step="0.01"
                       placeholder="e.g. 3000"
                       class="glass-input">
            </div>
            <button type="submit" class="btn-cta">Save budget</button>
        </form>
        <p class="text-xs mt-3 leading-relaxed" style="color:rgba(255,255,255,0.25)">
            If a budget for that category already exists this month, it will be updated.
        </p>
    </div>

</div>
@endsection