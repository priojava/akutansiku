<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    protected $guarded = [];

    protected $casts = [
        'initial_debit' => 'float',
        'initial_credit' => 'float',
        'is_active' => 'boolean',
    ];

    protected $appends = ['classification'];

    /**
     * Pengelompokan Akun Sesuai Standar Laporan Keuangan
     */
    public function getClassificationAttribute(): string
    {
        $category = $this->category;
        $code = (string) $this->code;

        if (in_array($category, ['Harta Tetap', 'Depresiasi & Amortisasi', 'Harta Lainnya']) || str_starts_with($code, '1-107') || str_starts_with($code, '1-108') || str_starts_with($code, '1-20')) {
            return 'ASET TIDAK LANCAR';
        }

        if (in_array($category, ['Kas & Bank', 'Akun Piutang', 'Persediaan', 'Harta Lancar Lainnya']) || str_starts_with($code, '1-')) {
            return 'ASET LANCAR';
        }

        if (in_array($category, ['Akun Hutang', 'Kewajiban Lancar Lainnya', 'Kewajiban Jangka Panjang']) || str_starts_with($code, '2-')) {
            return 'LIABILITAS';
        }

        if (in_array($category, ['Modal', 'Ekuitas']) || str_starts_with($code, '3-')) {
            return 'EKUITAS';
        }

        if (in_array($category, ['Pendapatan', 'Pendapatan Lainnya']) || str_starts_with($code, '4-') || str_starts_with($code, '7-')) {
            return 'PENDAPATAN';
        }

        if (in_array($category, ['Harga Pokok Penjualan', 'HPP', 'Beban', 'Beban Lainnya']) || str_starts_with($code, '5-') || str_starts_with($code, '6-') || str_starts_with($code, '8-')) {
            return 'BIAYA';
        }

        return 'LAINNYA';
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Account::class, 'parent_id');
    }

    public function journalItems(): HasMany
    {
        return $this->hasMany(JournalItem::class);
    }
}
