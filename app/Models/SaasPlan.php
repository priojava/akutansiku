<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaasPlan extends Model
{
    use HasFactory;

    protected $table = 'saas_plans';

    protected $fillable = [
        'code',
        'name',
        'price_monthly',
        'discount_6_months',
        'discount_12_months',
        'max_branches',
        'description',
        'features',
        'is_active',
    ];

    protected $casts = [
        'price_monthly' => 'float',
        'discount_6_months' => 'integer',
        'discount_12_months' => 'integer',
        'max_branches' => 'integer',
        'features' => 'array',
        'is_active' => 'boolean',
    ];

    public static function getPlan(string $code): ?self
    {
        return static::where('code', $code)->first();
    }
}
