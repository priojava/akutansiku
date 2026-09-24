<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetType extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_depreciated' => 'boolean',
        'useful_life_years' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function assetAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'asset_account_id');
    }

    public function expenseAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'expense_account_id');
    }

    public function accumulatedAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'accumulated_account_id');
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }
}
