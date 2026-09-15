<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('saas_payment_settings')) {
            Schema::create('saas_payment_settings', function (Blueprint $table) {
                $table->id();
                
                // Midtrans Settings
                $table->boolean('midtrans_enabled')->default(true);
                $table->string('midtrans_server_key')->nullable();
                $table->string('midtrans_client_key')->nullable();
                $table->string('midtrans_merchant_id')->nullable();
                $table->boolean('midtrans_is_production')->default(false); // false = Sandbox

                // Xendit Settings
                $table->boolean('xendit_enabled')->default(true);
                $table->string('xendit_secret_key')->nullable();
                $table->string('xendit_public_key')->nullable();
                $table->string('xendit_webhook_token')->nullable();
                $table->boolean('xendit_is_production')->default(false); // false = Development/Test

                // Manual Bank Transfer Settings
                $table->boolean('manual_transfer_enabled')->default(true);
                $table->text('bank_accounts_info')->nullable();

                $table->timestamps();
            });

            // Insert initial default setting
            DB::table('saas_payment_settings')->insert([
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
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Set default Gemini API Key for existing companies if empty
        $defaultGeminiKey = 'AQ.Ab8RN6LoDJ7glHsP2wlfOaR38B9JaMfatPa3C7CCJLkBOYdksQ';
        if (Schema::hasTable('company_settings') && Schema::hasColumn('company_settings', 'gemini_api_key')) {
            DB::table('company_settings')
                ->whereNull('gemini_api_key')
                ->orWhere('gemini_api_key', '')
                ->update(['gemini_api_key' => $defaultGeminiKey]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('saas_payment_settings');
    }
};
