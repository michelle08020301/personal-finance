@extends('layouts.app')
@section('title', 'Add Transaction')

@section('content')

<div class="max-w-lg mx-auto">
    <div class="flex items-center gap-3 mb-5">
        <a href="{{ route('transactions.index') }}"
           class="text-xs" style="color:rgba(255,255,255,0.3)">← Back</a>
        <h1 class="page-title text-xl">Add Transaction</h1>
    </div>

    <div class="glass-card p-6">
        <form method="POST" action="{{ route('transactions.store') }}">
            @csrf

            <div class="mb-5">
                <label class="glass-label">Type</label>
                <div class="flex gap-2">
                    <label class="flex-1 cursor-pointer">
                        <input type="radio" name="type" value="expense"
                               class="peer sr-only"
                               {{ old('type','expense')==='expense' ? 'checked':'' }}>
                        <div class="border rounded-xl p-3 text-center text-sm transition-all"
                             style="border-color:rgba(255,255,255,0.1);color:rgba(255,255,255,0.4)"
                             id="expense-label">
                            ↓ Expense
                        </div>
                    </label>
                    <label class="flex-1 cursor-pointer">
                        <input type="radio" name="type" value="income"
                               class="peer sr-only"
                               {{ old('type')==='income' ? 'checked':'' }}>
                        <div class="border rounded-xl p-3 text-center text-sm transition-all"
                             style="border-color:rgba(255,255,255,0.1);color:rgba(255,255,255,0.4)"
                             id="income-label">
                            ↑ Income
                        </div>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 mb-4">
                <div>
                    <label class="glass-label">Amount (₱)</label>
                    <input type="number" name="amount" step="0.01" min="0.01"
                           value="{{ old('amount') }}" placeholder="0.00"
                           class="glass-input">
                </div>
                <div>
                    <label class="glass-label">Date</label>
                    <input type="date" name="date"
                           value="{{ old('date', date('Y-m-d')) }}"
                           class="glass-input">
                </div>
            </div>

            <div class="mb-4">
                <label class="glass-label">Description</label>
                <input type="text" name="description"
                       value="{{ old('description') }}"
                       placeholder="e.g. Lunch, Salary, Jeepney fare"
                       class="glass-input">
            </div>

            <div class="mb-4">
                <label class="glass-label">Category</label>
                <select name="category" class="glass-input">
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}"
                            {{ old('category')===$cat ? 'selected':'' }}>
                            {{ $cat }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-6">
                <label class="glass-label">Notes (optional)</label>
                <input type="text" name="notes"
                       value="{{ old('notes') }}"
                       placeholder="Any additional notes..."
                       class="glass-input">
            </div>

            <button type="submit" class="btn-cta">
                Save transaction
            </button>
        </form>
    </div>
</div>
@endsection