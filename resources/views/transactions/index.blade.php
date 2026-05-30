@extends('layouts.app')
@section('title', 'Transactions')

@section('content')

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="page-title">Transactions</h1>
        <p class="text-xs mt-0.5" style="color:rgba(255,255,255,0.3)">All income and expenses</p>
    </div>
    <a href="{{ route('transactions.create') }}" class="btn-cta" style="width:auto;padding:9px 18px">
        + Add transaction
    </a>
</div>

<form method="GET" class="glass-card p-4 mb-4 flex gap-3 flex-wrap items-end">
    <div>
        <label class="glass-label">Type</label>
        <select name="type" class="glass-input" style="width:130px">
            <option value="">All types</option>
            <option value="income"  {{ request('type')==='income'  ? 'selected':'' }}>Income</option>
            <option value="expense" {{ request('type')==='expense' ? 'selected':'' }}>Expense</option>
        </select>
    </div>
    <div>
        <label class="glass-label">Category</label>
        <select name="category" class="glass-input" style="width:150px">
            <option value="">All categories</option>
            @foreach($categories as $cat)
                <option value="{{ $cat }}" {{ request('category')===$cat ? 'selected':'' }}>{{ $cat }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="glass-label">Month</label>
        <select name="month" class="glass-input" style="width:150px">
            <option value="">All months</option>
            @foreach(range(1,12) as $m)
                <option value="{{ $m }}" {{ request('month')==$m ? 'selected':'' }}>
                    {{ date('F', mktime(0,0,0,$m,1)) }}
                </option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="btn-ghost">Filter</button>
    <a href="{{ route('transactions.index') }}" class="text-xs py-2" style="color:rgba(255,255,255,0.3)">Clear</a>
</form>

<div class="glass-card overflow-hidden">
    <table class="glass-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Description</th>
                <th>Category</th>
                <th>Type</th>
                <th style="text-align:right">Amount</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $tx)
                <tr>
                    <td style="color:rgba(255,255,255,0.4)">{{ $tx->date->format('M d, Y') }}</td>
                    <td>
                        <p class="font-medium" style="color:rgba(255,255,255,0.82)">{{ $tx->description }}</p>
                        @if($tx->notes)
                            <p class="text-xs mt-0.5" style="color:rgba(255,255,255,0.3)">{{ $tx->notes }}</p>
                        @endif
                    </td>
                    <td><span class="chip chip-blue">{{ $tx->category }}</span></td>
                    <td>
                        <span class="chip {{ $tx->type==='income' ? 'chip-green' : 'chip-red' }}">
                            {{ ucfirst($tx->type) }}
                        </span>
                    </td>
                    <td style="text-align:right">
                        <span class="font-medium" style="color:{{ $tx->type==='income' ? '#c8ff80' : '#ff8080' }}">
                            {{ $tx->type==='income' ? '+' : '-' }}₱{{ number_format($tx->amount, 2) }}
                        </span>
                    </td>
                    <td>
                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('transactions.edit', $tx) }}"
                               style="font-size:12px;color:#80b8ff">Edit</a>
                            <form method="POST" action="{{ route('transactions.destroy', $tx) }}"
                                  onsubmit="return confirm('Delete this transaction?')">
                                @csrf @method('DELETE')
                                <button type="submit" style="font-size:12px;color:rgba(255,100,100,0.5)">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-12 text-sm" style="color:rgba(255,255,255,0.3)">
                        No transactions found.
                        <a href="{{ route('transactions.create') }}" style="color:#c8ff80">Add one →</a>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    @if($transactions->hasPages())
        <div class="px-5 py-3 border-t glass-divider">
            {{ $transactions->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection