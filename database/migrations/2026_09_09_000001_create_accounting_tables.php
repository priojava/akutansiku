<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambahan kolom pada users
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('avatar')->nullable()->after('phone');
            $table->text('address')->nullable()->after('avatar');
            $table->string('google_id')->nullable()->after('password');
            $table->unsignedBigInteger('default_company_id')->nullable()->after('google_id');
        });

        // 2. Companies (Multi-Tenant)
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('logo')->nullable();
            $table->enum('plan_type', ['free', 'premium'])->default('premium');
            $table->date('conversion_date')->nullable(); // Tanggal konversi saldo awal
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->timestamps();
        });

        // 3. Company User Pivot (Multi-User per Tenant)
        Schema::create('company_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role')->default('admin'); // admin, accountant, staff
            $table->timestamps();
        });

        // 4. Company Settings
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->boolean('preview_transaksi')->default(true);
            $table->string('number_format')->default('1,000,000.00'); // '1,000,000.00' atau '1.000.000,00'
            $table->integer('decimal_places')->default(0);
            $table->boolean('cache_reports')->default(false);
            $table->boolean('cache_ar_ap')->default(false);
            $table->timestamps();
        });

        // 5. Chart of Accounts (COA)
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('code', 50); // e.g. 1-10001
            $table->string('name');
            $table->string('category'); // e.g. Kas & Bank, Akun Piutang, Persediaan, Harta Lancar Lainnya, Harta Tetap, Depresiasi & Amortisasi, Akun Hutang, Modal, Pendapatan, HPP, Beban, Pendapatan Lainnya, Beban Lainnya
            $table->enum('type', ['Debit', 'Credit'])->default('Debit'); // Tipe saldo normal
            $table->decimal('initial_debit', 18, 2)->default(0);
            $table->decimal('initial_credit', 18, 2)->default(0);
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'code']);
        });

        // 6. Contacts (Pelanggan / Vendor / Karyawan)
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name');
            $table->enum('type', ['customer', 'vendor', 'employee', 'other'])->default('customer');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
        });

        // 7. Payment Methods (Cara Pembayaran)
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name'); // Kas Tunai, Transfer Bank BCA, dll
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->timestamps();
        });

        // 8. Tags / Label
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name');
            $table->string('color', 20)->default('#3b82f6');
            $table->timestamps();
        });

        // 9. Taxes (Pajak)
        Schema::create('taxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name'); // PPN 11%, PPh 23
            $table->decimal('rate', 5, 2)->default(0);
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->timestamps();
        });

        // 10. Transactions (Header Transaksi Catat Cepat)
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('transaction_number', 50)->unique();
            $table->date('date');
            $table->time('time')->default('00:00:00');
            $table->enum('type', ['income', 'expense', 'transfer', 'journal'])->default('income');
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->foreignId('debit_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('credit_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->decimal('amount', 18, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('tag_id')->nullable()->constrained('tags')->nullOnDelete();
            $table->foreignId('tax_id')->nullable()->constrained('taxes')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 11. Journal Entries (Buku Jurnal Umum - Double Entry Header)
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->cascadeOnDelete();
            $table->string('entry_number', 50)->unique();
            $table->date('date');
            $table->time('time')->default('00:00:00');
            $table->string('reference_number')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 12. Journal Items (Detail Debit / Kredit Per Akun)
        Schema::create('journal_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained('journal_entries')->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
            $table->decimal('debit', 18, 2)->default(0);
            $table->decimal('credit', 18, 2)->default(0);
            $table->string('memo')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_items');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('taxes');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('accounts');
        Schema::dropIfExists('company_settings');
        Schema::dropIfExists('company_user');
        Schema::dropIfExists('companies');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'avatar', 'address', 'google_id', 'default_company_id']);
        });
    }
};
