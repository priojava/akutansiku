<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asset extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'acquisition_date' => 'date',
        'acquisition_cost' => 'float',
        'tax_amount' => 'float',
        'is_depreciated' => 'boolean',
        'salvage_value' => 'float',
        'accumulated_depreciation_amount' => 'float',
        'depreciation_end_date' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function assetAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'asset_account_id');
    }

    public function taxAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'tax_account_id');
    }

    public function creditedAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'credited_account_id');
    }

    public function expenseAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'expense_account_id');
    }

    public function accumulatedAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'accumulated_depreciation_account_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getBookValueAttribute(): float
    {
        return max(0, $this->acquisition_cost - $this->accumulated_depreciation_amount);
    }
}
