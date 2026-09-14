<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClosingPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'closing_date',
        'period_name',
        'notes',
        'total_revenue',
        'total_expense',
        'net_profit_before_tax',
        'tax_expense_account_id',
        'tax_amount',
        'tax_payable_account_id',
        'retained_earnings_account_id',
        'net_profit_after_tax',
        'worksheet_data',
        'journal_entry_id',
        'created_by',
    ];

    protected $casts = [
        'closing_date' => 'datetime',
        'total_revenue' => 'decimal:2',
        'total_expense' => 'decimal:2',
        'net_profit_before_tax' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'net_profit_after_tax' => 'decimal:2',
        'worksheet_data' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function taxExpenseAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'tax_expense_account_id');
    }

    public function taxPayableAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'tax_payable_account_id');
    }

    public function retainedEarningsAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'retained_earnings_account_id');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
