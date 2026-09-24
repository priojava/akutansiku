<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $guarded = [];

    protected $casts = [
        'contract_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    // Helper hitung total pendapatan proyek
    public function getTotalIncomeAttribute(): float
    {
        return (float) $this->transactions()->where('type', 'income')->sum('amount');
    }

    // Helper hitung total pengeluaran proyek
    public function getTotalExpenseAttribute(): float
    {
        return (float) $this->transactions()->where('type', 'expense')->sum('amount');
    }

    // Laba/Rugi bersih proyek
    public function getNetProfitAttribute(): float
    {
        return $this->total_income - $this->total_expense;
    }
}
