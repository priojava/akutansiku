<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tabel Master Tipe Aset (Asset Types)
        Schema::create('asset_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('name'); // Kendaraan, Bangunan, Peralatan Kantor, Mesin Pabrik, Tanah
            $table->string('code')->nullable(); // KND, BGN, PLT, MSN, TNH
            $table->integer('useful_life_years')->default(4)->nullable(); // Default masa manfaat (tahun)
            $table->foreignId('asset_account_id')->nullable()->constrained('accounts')->onDelete('set null');
            $table->foreignId('expense_account_id')->nullable()->constrained('accounts')->onDelete('set null');
            $table->foreignId('accumulated_account_id')->nullable()->constrained('accounts')->onDelete('set null');
            $table->boolean('is_depreciated')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. Modifikasi Tabel Assets (tambah usage_date dan asset_type_id)
        Schema::table('assets', function (Blueprint $table) {
            $table->date('usage_date')->nullable()->after('acquisition_date');
            $table->foreignId('asset_type_id')->nullable()->after('company_id')->constrained('asset_types')->onDelete('set null');
        });

        // 3. Tabel Log Jurnal Penyusutan Bulanan (Asset Depreciation Logs)
        Schema::create('asset_depreciation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->onDelete('set null');
            $table->string('period', 7); // Format: YYYY-MM (misal: 2026-09)
            $table->date('depreciation_date'); // Tanggal akhir bulan eksekusi (misal: 2026-09-30)
            $table->decimal('depreciation_amount', 15, 2);
            $table->decimal('book_value_before', 15, 2)->default(0);
            $table->decimal('book_value_after', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->unique(['company_id', 'asset_id', 'period'], 'asset_depr_unique_company_asset_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_depreciation_logs');
        
        Schema::table('assets', function (Blueprint $table) {
            $table->dropForeign(['asset_type_id']);
            $table->dropColumn(['usage_date', 'asset_type_id']);
        });

        Schema::dropIfExists('asset_types');
    }
};
