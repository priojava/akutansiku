<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Company extends Model
{
    protected $guarded = [];

    protected $casts = [
        'conversion_date' => 'date',
        'is_initial_balance_locked' => 'boolean',
        'subscription_expires_at' => 'datetime',
        'max_companies' => 'integer',
    ];

    public function getMaxCompaniesAttribute($value): int
    {
        if ($value !== null && (int)$value > 0) {
            return (int) $value;
        }
        return $this->subscription_plan === 'premium' ? 3 : 1;
    }

    public function subscriptionInvoices(): HasMany
    {
        return $this->hasMany(SubscriptionInvoice::class)->orderByDesc('created_at');
    }

    public function latestInvoice(): HasOne
    {
        return $this->hasOne(SubscriptionInvoice::class)->latestOfMany();
    }

    public function getRemainingDaysAttribute(): int
    {
        if (!$this->subscription_expires_at) {
            return 14; // default trial days
        }
        $diff = (int) ceil(now()->floatDiffInDays($this->subscription_expires_at, false));
        return $diff > 0 ? $diff : 0;
    }

    /**
     * Menentukan status langganan saat ini:
     * - 'active'        : Masih dalam masa aktif (trial 14 hari atau langganan aktif)
     * - 'grace_period'  : Lewat tanggal jatuh tempo, tapi masih dalam kelonggaran 7 hari (bisa transaksi + banner peringatan)
     * - 'expired'       : Lewat kelonggaran 7 hari (read-only, transaksi dikunci)
     */
    public function getSubscriptionStateAttribute(): string
    {
        if ($this->subscription_status === 'suspended') {
            return 'expired';
        }

        if (!$this->subscription_expires_at) {
            return 'active';
        }

        if (now()->lessThanOrEqualTo($this->subscription_expires_at)) {
            return 'active';
        }

        // Cek masa kelonggaran 7 hari setelah subscription_expires_at
        $graceEndDate = $this->subscription_expires_at->copy()->addDays(7);
        if (now()->lessThanOrEqualTo($graceEndDate)) {
            return 'grace_period';
        }

        return 'expired';
    }

    /**
     * Sisa hari masa kelonggaran (0-7 hari)
     */
    public function getGraceDaysRemainingAttribute(): int
    {
        if (!$this->subscription_expires_at) {
            return 0;
        }

        $graceEndDate = $this->subscription_expires_at->copy()->addDays(7);
        $diff = (int) ceil(now()->floatDiffInDays($graceEndDate, false));
        return max(0, $diff);
    }

    public function isInGracePeriod(): bool
    {
        return $this->subscription_state === 'grace_period';
    }

    public function isReadOnly(): bool
    {
        return $this->subscription_state === 'expired';
    }

    public function isSubscriptionActive(): bool
    {
        return in_array($this->subscription_state, ['active', 'grace_period']);
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class);
    }

    public function settings(): HasOne
    {
        return $this->hasOne(CompanySetting::class);
    }

    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'company_user')->withPivot('role')->withTimestamps();
    }

    public function owner(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function getOwnerUser(): ?User
    {
        if ($this->relationLoaded('owner') && $this->owner) {
            return $this->owner;
        }
        if ($this->owner_id) {
            $owner = $this->owner()->first();
            if ($owner) return $owner;
        }
        return $this->users()->wherePivot('role', 'admin')->first()
            ?? $this->users()->first()
            ?? ($this->email ? User::where('email', $this->email)->first() : null);
    }
}
