@extends('layouts.app')
@section('title', 'Savings Goals')

@section('content')

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="page-title">Savings Goals</h1>
        <p class="text-xs mt-0.5" style="color:rgba(255,255,255,0.3)">Track your savings targets</p>
    </div>
</div>

{{-- Summary cards --}}
<div class="grid grid-cols-3 gap-3 mb-5">
    <div class="glass-card glass-green p-5">
        <p class="glass-label">Total target</p>
        <p class="num-display text-3xl" style="color:#c8ff80">₱{{ number_format($totalTarget, 2) }}</p>
        <p class="text-xs mt-2" style="color:rgba(255,255,255,0.3)">All goals combined</p>
    </div>
    <div class="glass-card glass-blue p-5">
        <p class="glass-label">Total saved</p>
        <p class="num-display text-3xl" style="color:#80b8ff">₱{{ number_format($totalSaved, 2) }}</p>
        <p class="text-xs mt-2" style="color:rgba(255,255,255,0.3)">
            {{ $totalTarget > 0 ? number_format(($totalSaved / $totalTarget) * 100, 0) : 0 }}% of total target
        </p>
    </div>
    <div class="glass-card glass-lime p-5">
        <p class="glass-label">Goals completed</p>
        <p class="num-display text-3xl" style="color:#c8ff80">{{ $completed }}</p>
        <p class="text-xs mt-2" style="color:rgba(255,255,255,0.3)">out of {{ $goals->count() }} goals</p>
    </div>
</div>

<div class="grid grid-cols-3 gap-4">

    {{-- Goals list --}}
    <div class="col-span-2 space-y-3">
        @forelse($goals as $goal)
            <div class="glass-card p-5 {{ $goal->is_completed ? 'glass-green' : '' }}">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <div style="width:40px;height:40px;border-radius:12px;background:rgba(255,255,255,0.08);display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0">
                            {{ $goal->icon }}
                        </div>
                        <div>
                            <p class="font-medium" style="font-size:14px;color:rgba(255,255,255,0.9)">{{ $goal->name }}</p>
                            <p class="text-xs mt-0.5" style="color:rgba(255,255,255,0.3)">
                                @if($goal->target_date)
                                    Target: {{ $goal->target_date->format('M d, Y') }}
                                @else
                                    No target date
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($goal->is_completed)
                            <span class="chip chip-green">✓ Completed!</span>
                        @else
                            <span class="chip chip-blue">{{ number_format($goal->progress_percent, 0) }}%</span>
                        @endif
                        <form method="POST" action="{{ route('savings.destroy', $goal) }}"
                              onsubmit="return confirm('Remove this goal?')">
                            @csrf @method('DELETE')
                            <button type="submit" style="font-size:12px;color:rgba(255,100,100,0.5);background:none;border:none;cursor:pointer">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Progress bar --}}
                <div class="progress-track mb-2">
                    <div class="progress-fill {{ $goal->is_completed ? 'progress-safe' : ($goal->progress_percent >= 70 ? 'progress-warn' : 'progress-safe') }}"
                         style="width:{{ $goal->progress_percent }}%"></div>
                </div>

                <div class="flex justify-between mb-3">
                    <span class="text-xs" style="color:rgba(255,255,255,0.5)">
                        Saved: <strong style="color:#c8ff80">₱{{ number_format($goal->saved_amount, 2) }}</strong>
                    </span>
                    <span class="text-xs" style="color:rgba(255,255,255,0.5)">
                        Target: <strong style="color:rgba(255,255,255,0.8)">₱{{ number_format($goal->target_amount, 2) }}</strong>
                    </span>
                    <span class="text-xs" style="color:rgba(255,255,255,0.5)">
                        Remaining: <strong style="color:#ffb840">₱{{ number_format($goal->remaining, 2) }}</strong>
                    </span>
                </div>

                {{-- Update saved amount --}}
                @if(!$goal->is_completed)
                    <form method="POST" action="{{ route('savings.update', $goal) }}"
                          style="display:flex;gap:8px;align-items:center">
                        @csrf @method('PATCH')
                        <input type="number" name="saved_amount" step="0.01" min="0"
                               value="{{ $goal->saved_amount }}"
                               placeholder="Update saved amount"
                               class="glass-input" style="height:34px;font-size:12px;padding:6px 12px">
                        <button type="submit" class="btn-ghost" style="padding:6px 14px;font-size:12px;white-space:nowrap;height:34px">
                            Update
                        </button>
                    </form>
                @endif
            </div>
        @empty
            <div class="glass-card p-10 text-center">
                <p style="font-size:32px;margin-bottom:10px">🎯</p>
                <p style="font-size:14px;color:rgba(255,255,255,0.5)">No savings goals yet.</p>
                <p style="font-size:12px;color:rgba(255,255,255,0.3);margin-top:4px">Add your first goal using the form →</p>
            </div>
        @endforelse
    </div>

    {{-- Add goal form --}}
    <div class="glass-card glass-lime p-5">
        <p class="glass-label mb-4">Add savings goal</p>
        <form method="POST" action="{{ route('savings.store') }}">
            @csrf

            <div class="mb-3">
                <label class="glass-label">Goal name</label>
                <input type="text" name="name"
                       value="{{ old('name') }}"
                       placeholder="e.g. Emergency fund"
                       class="glass-input">
            </div>

            <div class="mb-3">
                <label class="glass-label">Icon (emoji)</label>
                <input type="text" name="icon"
                       value="{{ old('icon', '🎯') }}"
                       placeholder="🎯"
                       class="glass-input" style="font-size:18px">
            </div>

            <div class="mb-3">
                <label class="glass-label">Target amount (₱)</label>
                <input type="number" name="target_amount" min="1" step="0.01"
                       value="{{ old('target_amount') }}"
                       placeholder="e.g. 50000"
                       class="glass-input">
            </div>

            <div class="mb-3">
                <label class="glass-label">Already saved (₱)</label>
                <input type="number" name="saved_amount" min="0" step="0.01"
                       value="{{ old('saved_amount', 0) }}"
                       placeholder="0.00"
                       class="glass-input">
            </div>

            <div class="mb-5">
                <label class="glass-label">Target date (optional)</label>
                <input type="date" name="target_date"
                       value="{{ old('target_date') }}"
                       class="glass-input">
            </div>

            <button type="submit" class="btn-cta">
                + Add goal
            </button>
        </form>
    </div>

</div>
@endsection