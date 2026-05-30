@extends('layouts.app')
@section('title', 'Debts')

@section('content')

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="page-title">Debt Tracker</h1>
        <p class="text-xs mt-0.5" style="color:rgba(255,255,255,0.3)">Monitor and manage your borrowed money</p>
    </div>
    <div class="glass-card glass-amber px-4 py-2.5 text-sm">
        Total unpaid:
        <span class="font-bold ml-1" style="color:#ffb840">₱{{ number_format($totalUnpaid, 2) }}</span>
    </div>
</div>

<div class="grid grid-cols-3 gap-4">

    <div class="col-span-2 glass-card overflow-hidden">
        <table class="glass-table">
            <thead>
                <tr>
                    <th>Borrowed from</th>
                    <th>Reason</th>
                    <th style="text-align:right">Amount</th>
                    <th>Due date</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($debts as $debt)
                    <tr style="{{ $debt->status === 'paid' ? 'opacity:0.5' : '' }}">
                        <td class="font-medium" style="color:rgba(255,255,255,0.82)">{{ $debt->borrowed_from }}</td>
                        <td style="color:rgba(255,255,255,0.4)">{{ $debt->reason ?? '—' }}</td>
                        <td style="text-align:right">
                            <span class="font-medium"
                                  style="color:{{ $debt->status==='paid' ? 'rgba(255,255,255,0.3)' : '#ffb840' }};
                                         {{ $debt->status==='paid' ? 'text-decoration:line-through' : '' }}">
                                ₱{{ number_format($debt->amount, 2) }}
                            </span>
                        </td>
                        <td>
                            @if($debt->due_date)
                                <span style="color:{{ $debt->due_date->isPast() && $debt->status==='unpaid' ? '#ff8080' : 'rgba(255,255,255,0.4)' }};
                                             font-weight:{{ $debt->due_date->isPast() && $debt->status==='unpaid' ? '500' : '400' }}">
                                    {{ $debt->due_date->format('M d, Y') }}
                                </span>
                            @else
                                <span style="color:rgba(255,255,255,0.25)">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="chip {{ $debt->status==='paid' ? 'chip-green' : 'chip-amber' }}">
                                {{ ucfirst($debt->status) }}
                            </span>
                        </td>
                        <td>
                            <div class="flex items-center gap-3">
                                <form method="POST" action="{{ route('debts.markPaid', $debt) }}">
                                    @csrf @method('PATCH')
                                    <button style="font-size:12px;color:#80b8ff">
                                        {{ $debt->status==='paid' ? 'Undo' : 'Mark paid' }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('debts.destroy', $debt) }}"
                                      onsubmit="return confirm('Remove this debt?')">
                                    @csrf @method('DELETE')
                                    <button style="font-size:12px;color:rgba(255,100,100,0.5)">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-12 text-sm" style="color:rgba(255,255,255,0.3)">
                            No debts recorded. 🎉
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="glass-card glass-amber p-5">
        <p class="glass-label mb-4">Add new debt</p>
        <form method="POST" action="{{ route('debts.store') }}">
            @csrf
            <div class="mb-3">
                <label class="glass-label">Borrowed from</label>
                <input type="text" name="borrowed_from"
                       value="{{ old('borrowed_from') }}"
                       placeholder="Name or source"
                       class="glass-input">
            </div>
            <div class="mb-3">
                <label class="glass-label">Amount (₱)</label>
                <input type="number" name="amount" min="0.01" step="0.01"
                       value="{{ old('amount') }}" placeholder="0.00"
                       class="glass-input">
            </div>
            <div class="mb-3">
                <label class="glass-label">Reason (optional)</label>
                <input type="text" name="reason"
                       value="{{ old('reason') }}"
                       placeholder="What for?"
                       class="glass-input">
            </div>
            <div class="mb-5">
                <label class="glass-label">Due date (optional)</label>
                <input type="date" name="due_date"
                       value="{{ old('due_date') }}"
                       class="glass-input">
            </div>
            <button type="submit" class="btn-cta"
                    style="background:linear-gradient(135deg,#ffb840,#ff8c40);color:#1a0a00">
                Add debt
            </button>
        </form>
    </div>

</div>
@endsection