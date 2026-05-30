@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="text-xs mt-0.5" style="color:rgba(255,255,255,0.3)">{{ now()->format('F Y') }} · Welcome back, {{ Auth::user()->name }}</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('export.excel') }}" class="btn-ghost" style="padding:9px 18px">
            ↓ Export Excel
        </a>
        <a href="{{ route('transactions.create') }}" class="btn-cta" style="width:auto;padding:9px 18px">
            + New transaction
        </a>
    </div>
</div>
{{-- Budget Alerts --}}
@php
    $alerts = $budgets->map(function($budget) use ($spentByCategory) {
        $spent = $spentByCategory[$budget->category] ?? 0;
        $pct   = $budget->limit_amount > 0 ? ($spent / $budget->limit_amount) * 100 : 0;
        return ['budget' => $budget, 'spent' => $spent, 'pct' => $pct];
    })->filter(fn($b) => $b['pct'] >= 70)->sortByDesc('pct');
@endphp

@if($alerts->count() > 0)
    <div class="mb-4 space-y-2">
        @foreach($alerts as $alert)
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
                        ({{ number_format($alert['pct'], 0) }}%)
                    </p>
                </div>
                <a href="{{ route('budgets.index') }}"
                   style="font-size:11px;color:{{ $over ? '#ff8080' : '#ffb840' }};text-decoration:none;white-space:nowrap">
                    Manage →
                </a>
            </div>
        @endforeach
    </div>
@endif
<div class="grid grid-cols-3 gap-3">

    {{-- Income --}}
    <div class="glass-card glass-green p-5">
        <p class="glass-label">Total income</p>
        <p class="num-display text-3xl" style="color:#c8ff80">₱{{ number_format($totalIncome, 2) }}</p>
        <p class="text-xs mt-2" style="color:rgba(255,255,255,0.3)">This month</p>
        <span class="chip chip-green mt-2">Active income</span>
    </div>

    {{-- Expenses --}}
    <div class="glass-card glass-red p-5">
        <p class="glass-label">Total expenses</p>
        <p class="num-display text-3xl" style="color:#ff8080">₱{{ number_format($totalExpenses, 2) }}</p>
        <p class="text-xs mt-2" style="color:rgba(255,255,255,0.3)">
            {{ $totalIncome > 0 ? number_format(($totalExpenses / $totalIncome) * 100, 0) : 0 }}% of income
        </p>
        <span class="chip chip-red mt-2">This month</span>
    </div>

    {{-- Balance --}}
    <div class="glass-card glass-blue p-5">
        <p class="glass-label">Net balance</p>
        <p class="num-display text-3xl" style="color:{{ $balance >= 0 ? '#80b8ff' : '#ff8080' }}">
            ₱{{ number_format(abs($balance), 2) }}
        </p>
        <p class="text-xs mt-2" style="color:rgba(255,255,255,0.3)">
            Savings: {{ $totalIncome > 0 ? number_format(($balance / $totalIncome) * 100, 0) : 0 }}%
        </p>
        <span class="chip {{ $balance >= 0 ? 'chip-blue' : 'chip-red' }} mt-2">
            {{ $balance >= 0 ? 'On track' : 'Overspent' }}
        </span>
    </div>

    {{-- Monthly Chart (span 2) --}}
    <div class="glass-card col-span-2 p-5">
        <div class="sec-header">
            <span class="sec-title">Monthly summary — last 6 months</span>
            <div style="display:flex;gap:12px;align-items:center">
                <span style="display:flex;align-items:center;gap:5px;font-size:11px;color:rgba(255,255,255,0.5)">
                    <span style="width:10px;height:10px;border-radius:2px;background:#c8ff80;display:inline-block"></span> Income
                </span>
                <span style="display:flex;align-items:center;gap:5px;font-size:11px;color:rgba(255,255,255,0.5)">
                    <span style="width:10px;height:10px;border-radius:2px;background:#ff8080;display:inline-block"></span> Expenses
                </span>
            </div>
        </div>
        <div style="position:relative;height:180px">
            <canvas id="monthlyChart"></canvas>
        </div>
    </div>

    {{-- Debts --}}
    <div class="glass-card glass-dark p-5">
        <div class="sec-header">
            <span class="sec-title">Active debts</span>
            <a href="{{ route('debts.index') }}" class="sec-link">Manage →</a>
        </div>
        <div class="glass-card glass-amber p-3 text-center mb-4">
            <p class="text-xs mb-1" style="color:rgba(255,180,60,0.6)">Total unpaid</p>
            <p class="num-display text-xl" style="color:#ffb840">₱{{ number_format($totalUnpaidDebt, 2) }}</p>
        </div>
        @forelse($activeDebts as $debt)
            <div class="flex justify-between items-center py-2 border-b glass-divider last:border-0 last:pb-0">
                <div>
                    <p class="text-xs font-medium" style="color:rgba(255,255,255,0.8)">{{ $debt->borrowed_from }}</p>
                    @if($debt->due_date)
                        <p class="text-xs" style="color:rgba(255,255,255,0.3)">Due {{ $debt->due_date->format('M d') }}</p>
                    @endif
                </div>
                <div class="text-right">
                    <p class="text-xs font-medium" style="color:#ffb840">₱{{ number_format($debt->amount, 2) }}</p>
                    <span class="chip chip-amber mt-1" style="font-size:9px;padding:1px 7px">Unpaid</span>
                </div>
            </div>
        @empty
            <p class="text-xs text-center py-3" style="color:rgba(255,255,255,0.3)">No active debts! 🎉</p>
        @endforelse
    </div>

    {{-- Budget status --}}
    <div class="glass-card col-span-2 p-5">
        <div class="sec-header">
            <span class="sec-title">Budget status</span>
            <a href="{{ route('budgets.index') }}" class="sec-link">Manage →</a>
        </div>
        @forelse($budgets as $budget)
            @php
                $spent    = $spentByCategory[$budget->category] ?? 0;
                $pct      = $budget->limit_amount > 0 ? min(($spent / $budget->limit_amount) * 100, 100) : 0;
                $barClass = $pct < 70 ? 'progress-safe' : ($pct < 100 ? 'progress-warn' : 'progress-over');
            @endphp
            <div class="mb-3 last:mb-0">
                <div class="flex justify-between mb-1.5">
                    <span class="text-xs font-medium" style="color:rgba(255,255,255,0.7)">
                        {{ $budget->category }}
                        @if($pct >= 100)
                            <span class="chip chip-red ml-1" style="padding:1px 6px;font-size:9px">Over!</span>
                        @endif
                    </span>
                    <span class="text-xs" style="color:rgba(255,255,255,0.3)">
                        ₱{{ number_format($spent,2) }} / ₱{{ number_format($budget->limit_amount,2) }}
                    </span>
                </div>
                <div class="progress-track">
                    <div class="progress-fill {{ $barClass }}" style="width:{{ $pct }}%"></div>
                </div>
            </div>
        @empty
            <p class="text-xs text-center py-4" style="color:rgba(255,255,255,0.3)">
                No budgets set yet.
                <a href="{{ route('budgets.index') }}" style="color:#c8ff80">Set one →</a>
            </p>
        @endforelse
    </div>

    {{-- Recent transactions --}}
    <div class="glass-card p-5">
        <div class="sec-header">
            <span class="sec-title">Recent transactions</span>
            <a href="{{ route('transactions.index') }}" class="sec-link">View all →</a>
        </div>
        @forelse($recentTransactions as $tx)
            <div class="flex items-center gap-3 py-2 border-b glass-divider last:border-0 last:pb-0">
                <div class="tx-icon {{ $tx->type === 'income' ? 'tx-icon-income' : 'tx-icon-expense' }}">
                    <span style="color:{{ $tx->type === 'income' ? '#c8ff80' : '#ff8080' }}">
                        {{ $tx->type === 'income' ? '↑' : '↓' }}
                    </span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-medium truncate" style="color:rgba(255,255,255,0.82)">{{ $tx->description }}</p>
                    <p class="text-xs" style="color:rgba(255,255,255,0.3)">{{ $tx->category }} · {{ $tx->date->format('M d, Y') }}</p>
                </div>
                <span class="text-xs font-medium whitespace-nowrap" style="color:{{ $tx->type === 'income' ? '#c8ff80' : '#ff8080' }}">
                    {{ $tx->type === 'income' ? '+' : '-' }}₱{{ number_format($tx->amount, 2) }}
                </span>
            </div>
        @empty
            <p class="text-xs text-center py-6" style="color:rgba(255,255,255,0.3)">No transactions yet.</p>
        @endforelse
    </div>
{{-- Spending breakdown --}}
<div class="glass-card p-5">
    <div class="sec-header">
        <span class="sec-title">Spending breakdown</span>
        <span class="text-xs" style="color:rgba(255,255,255,0.3)">{{ now()->format('F Y') }}</span>
    </div>
    @if($spentByCategory->count() > 0)
        <div style="position:relative;height:160px">
            <canvas id="spendingChart"></canvas>
        </div>
    @else
        <div style="position:relative;height:160px;display:flex;align-items:center;justify-content:center">
            <canvas id="spendingChart" style="display:none"></canvas>
            <p class="text-xs text-center" style="color:rgba(255,255,255,0.3)">No expenses yet this month.</p>
        </div>
    @endif
</div>

{{-- AI Insight --}}
<div class="glass-card glass-lime p-5 flex flex-col gap-4">
    {{-- AI Insight --}}
    <div class="glass-card glass-lime p-5 flex flex-col gap-4">
        <div>
            <p class="glass-label">AI insight</p>
            <div class="insight-box">
                <div class="insight-icon">✦</div>
                <p class="insight-text">
                    @php $overBudget = $budgets->filter(fn($b) => ($spentByCategory[$b->category] ?? 0) > $b->limit_amount); @endphp
                    @if($overBudget->count() > 0)
                        <strong>{{ $overBudget->first()->category }}</strong> is over budget by
                        ₱{{ number_format(($spentByCategory[$overBudget->first()->category] ?? 0) - $overBudget->first()->limit_amount, 2) }}.
                        Consider adjusting your limit next month.
                    @elseif($balance >= 0)
                        You're saving <strong>{{ $totalIncome > 0 ? number_format(($balance / $totalIncome) * 100, 0) : 0 }}%</strong> of your income this month. Keep it up!
                    @else
                        You've spent more than you earned this month. Review your expenses.
                    @endif
                </p>
            </div>
        </div>
        <a href="{{ route('transactions.create') }}" class="btn-cta mt-auto">
            + New transaction
        </a>
    </div>

</div>

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Monthly bar chart
const ctx1 = document.getElementById('monthlyChart').getContext('2d');
const labels  = @json($chartLabels);
const income  = @json($chartIncome);
const expense = @json($chartExpense);

new Chart(ctx1, {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [
            {
                label: 'Income',
                data: income,
                backgroundColor: 'rgba(200, 255, 128, 0.7)',
                borderColor: '#c8ff80',
                borderWidth: 1,
                borderRadius: 4,
            },
            {
                label: 'Expenses',
                data: expense,
                backgroundColor: 'rgba(255, 128, 128, 0.7)',
                borderColor: '#ff8080',
                borderWidth: 1,
                borderRadius: 4,
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => '₱' + ctx.parsed.y.toLocaleString('en-PH', {minimumFractionDigits:2})
                }
            }
        },
        scales: {
            x: {
                grid: { color: 'rgba(255,255,255,0.05)' },
                ticks: { color: 'rgba(255,255,255,0.4)', font: { size: 11 } }
            },
            y: {
                grid: { color: 'rgba(255,255,255,0.05)' },
                ticks: {
                    color: 'rgba(255,255,255,0.4)',
                    font: { size: 11 },
                    callback: val => '₱' + val.toLocaleString()
                }
            }
        }
    }
});

// Spending breakdown pie chart
const ctx2 = document.getElementById('spendingChart').getContext('2d');
const spendingLabels = @json($spentByCategory->keys());
const spendingData   = @json($spentByCategory->values());
const pieColors = [
    'rgba(200,255,128,0.8)',
    'rgba(255,128,128,0.8)',
    'rgba(128,184,255,0.8)',
    'rgba(255,184,64,0.8)',
    'rgba(128,255,234,0.8)',
    'rgba(200,128,255,0.8)',
    'rgba(255,200,128,0.8)',
];

new Chart(ctx2, {
    type: 'doughnut',
    data: {
        labels: spendingLabels,
        datasets: [{
            data: spendingData,
            backgroundColor: pieColors,
            borderColor: 'rgba(255,255,255,0.1)',
            borderWidth: 2,
            hoverOffset: 6,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'right',
                labels: {
                    color: 'rgba(255,255,255,0.6)',
                    font: { size: 11 },
                    padding: 12,
                    boxWidth: 12,
                    boxHeight: 12,
                }
            },
            tooltip: {
                callbacks: {
                    label: ctx => ' ₱' + ctx.parsed.toLocaleString('en-PH', {minimumFractionDigits:2})
                }
            }
        },
        cutout: '65%',
    }
});
</script>

@endsection