<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'category', 'limit_amount', 'month', 'year',
    ];

    protected $casts = [
        'limit_amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeCurrentMonth($query)
    {
        return $query->where('month', now()->month)
                     ->where('year', now()->year);
    }
}