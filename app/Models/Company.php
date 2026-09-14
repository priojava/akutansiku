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
    ];

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
        $diff = (int) now()->diffInDays($this->subscription_expires_at, false);
        return $diff > 0 ? $diff : 0;
    }

    public function isSubscriptionActive(): bool
    {
        if ($this->subscription_status === 'suspended') {
            return false;
        }
        if (!$this->subscription_expires_at) {
            return true;
        }
        return now()->lessThanOrEqualTo($this->subscription_expires_at);
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
}
