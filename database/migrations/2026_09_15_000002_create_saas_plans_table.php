<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('saas_plans')) {
            Schema::create('saas_plans', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique(); // 'standard', 'premium'
                $table->string('name');            // 'Standard', 'Pro Enterprise'
                $table->decimal('price_monthly', 18, 2)->default(99000);
                $table->integer('discount_6_months')->default(10);  // 10%
                $table->integer('discount_12_months')->default(20); // 20%
                $table->integer('max_branches')->nullable()->default(2);
                $table->text('description')->nullable();
                $table->json('features')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            // Seed default plans
            DB::table('saas_plans')->insert([
                [
                    'code' => 'standard',
                    'name' => 'Standard',
                    'price_monthly' => 99000,
                    'discount_6_months' => 10,
                    'discount_12_months' => 20,
                    'max_branches' => 2,
                    'description' => 'Maks 2 Cabang, Kasir POS, Laporan Keuangan Dasar',
                    'features' => json_encode([
                        'Maksimal 2 Cabang',
                        'Kasir POS & Catat Transaksi Harian',
                        'Master 120 Bagan Akun (COA)',
                        'Laporan Laba Rugi & Arus Kas Dasar',
                    ]),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'code' => 'premium',
                    'name' => 'Pro Enterprise',
                    'price_monthly' => 249000,
                    'discount_6_months' => 10,
                    'discount_12_months' => 20,
                    'max_branches' => null, // unlimited
                    'description' => 'Unlimited Cabang, AI Jurnal Google Gemini & Pajak DJP',
                    'features' => json_encode([
                        'Unlimited Cabang (Banyak Outlet)',
                        'AI Natural Language Journaling (Google Gemini)',
                        'Modul Pajak DJP Lengkap (PPN, PPh 21, PPh 23, PPh Final)',
                        'Laporan Akuntansi Lengkap: Neraca Saldo, Buku Besar, Tutup Buku',
                        'Multi-User Penuh (Owner, Akuntan, Kasir, Auditor)',
                    ]),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ]);
        }

        // Ensure Super Admin account is verified & password is superadmin123
        $superadmin = DB::table('users')->where('email', 'superadmin@dapurgemoy.com')->first();
        if ($superadmin) {
            DB::table('users')->where('id', $superadmin->id)->update([
                'name' => 'Super Administrator (SaaS Master)',
                'password' => Hash::make('superadmin123'),
                'is_superadmin' => true,
            ]);
        } else {
            DB::table('users')->insert([
                'name' => 'Super Administrator (SaaS Master)',
                'email' => 'superadmin@dapurgemoy.com',
                'password' => Hash::make('superadmin123'),
                'is_superadmin' => true,
                'default_company_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('saas_plans');
    }
};
