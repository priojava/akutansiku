<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambahan kolom superadmin pada users
        if (!Schema::hasColumn('users', 'is_superadmin')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_superadmin')->default(false)->after('default_company_id');
            });
        }

        // 2. Tambahan kolom status langganan pada companies
        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies', 'subscription_status')) {
                $table->string('subscription_status')->default('trial')->after('plan_type'); // trial, active, expired, suspended
            }
            if (!Schema::hasColumn('companies', 'subscription_expires_at')) {
                $table->dateTime('subscription_expires_at')->nullable()->after('subscription_status');
            }
            if (!Schema::hasColumn('companies', 'subscription_plan')) {
                $table->string('subscription_plan')->default('premium')->after('subscription_expires_at'); // standard, premium/pro
            }
        });

        // 3. Tabel Riwayat Invoice Tagihan & Pembayaran Langganan (SaaS Invoices)
        if (!Schema::hasTable('subscription_invoices')) {
            Schema::create('subscription_invoices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
                $table->string('invoice_number')->unique();
                $table->string('plan_name')->default('premium'); // standard, premium/pro
                $table->integer('duration_months')->default(1);
                $table->decimal('amount', 18, 2)->default(0);
                $table->enum('status', ['paid', 'pending', 'rejected', 'expired'])->default('pending');
                $table->string('description')->nullable();
                $table->string('payment_method')->nullable();
                $table->string('proof_file')->nullable();
                $table->dateTime('start_date')->nullable();
                $table->dateTime('end_date')->nullable();
                $table->dateTime('paid_at')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_invoices');
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['subscription_status', 'subscription_expires_at', 'subscription_plan']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_superadmin');
        });
    }
};
