<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanySetting extends Model
{
    protected $guarded = [];

    protected $casts = [
        'preview_transaksi' => 'boolean',
        'cache_reports' => 'boolean',
        'cache_ar_ap' => 'boolean',
        'decimal_places' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function inventoryAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_inventory_id');
    }

    public function salesAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_sales_id');
    }

    public function salesReturnAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_sales_return_id');
    }

    public function salesDiscountAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_sales_discount_id');
    }

    public function goodsInTransitAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_goods_in_transit_id');
    }

    public function cogsAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_cogs_id');
    }

    public function purchaseReturnAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_purchase_return_id');
    }

    public function expenseAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_expense_id');
    }

    public function unbilledPurchasesAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_unbilled_purchases_id');
    }

    public function receivableAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_receivable_id');
    }

    public function payableAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_payable_id');
    }

    public function cashDrawerAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_cash_drawer_id');
    }

    public function roundingDiffAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_rounding_diff_id');
    }

    public function salesDepositAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_sales_deposit_id');
    }

    public function purchaseDownpaymentAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_purchase_downpayment_id');
    }
}
