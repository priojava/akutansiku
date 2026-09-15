<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaasPaymentSetting extends Model
{
    use HasFactory;

    protected $table = 'saas_payment_settings';

    protected $fillable = [
        'midtrans_enabled',
        'midtrans_server_key',
        'midtrans_client_key',
        'midtrans_merchant_id',
        'midtrans_is_production',
        'xendit_enabled',
        'xendit_secret_key',
        'xendit_public_key',
        'xendit_webhook_token',
        'xendit_is_production',
        'manual_transfer_enabled',
        'bank_accounts_info',
    ];

    protected $casts = [
        'midtrans_enabled' => 'boolean',
        'midtrans_is_production' => 'boolean',
        'xendit_enabled' => 'boolean',
        'xendit_is_production' => 'boolean',
        'manual_transfer_enabled' => 'boolean',
    ];

    /**
     * Get the singleton or first settings row.
     */
    public static function getSettings(): self
    {
        return static::firstOrCreate([], [
            'midtrans_enabled' => true,
            'midtrans_server_key' => 'SB-Mid-server-DemoExampleKey123',
            'midtrans_client_key' => 'SB-Mid-client-DemoExampleKey123',
            'midtrans_merchant_id' => 'G123456789',
            'midtrans_is_production' => false,
            'xendit_enabled' => true,
            'xendit_secret_key' => 'xnd_development_SecretDemoKey123',
            'xendit_public_key' => 'xnd_public_DemoKey123',
            'xendit_webhook_token' => 'wh_token_demo_123',
            'xendit_is_production' => false,
            'manual_transfer_enabled' => true,
            'bank_accounts_info' => "BCA: 800-123-4567 a/n PT Akuntansi Cloud Indonesia\nMandiri: 137-00-123456-7 a/n PT Akuntansi Cloud Indonesia",
        ]);
    }
}
