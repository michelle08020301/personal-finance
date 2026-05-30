<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavingsGoal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'target_amount', 'saved_amount', 'icon', 'target_date',
    ];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'saved_amount'  => 'decimal:2',
        'target_date'   => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getProgressPercentAttribute(): float
    {
        if ($this->target_amount <= 0) return 0;
        return min(($this->saved_amount / $this->target_amount) * 100, 100);
    }

    public function getRemainingAttribute(): float
    {
        return max($this->target_amount - $this->saved_amount, 0);
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->saved_amount >= $this->target_amount;
    }
}