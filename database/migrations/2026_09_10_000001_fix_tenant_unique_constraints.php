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
        // 1. Transactions: Ubah unique transaction_number menjadi unique per company
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropUnique('transactions_transaction_number_unique');
            $table->unique(['company_id', 'transaction_number']);
        });

        // 2. Journal Entries: Ubah unique entry_number menjadi unique per company
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropUnique('journal_entries_entry_number_unique');
            $table->unique(['company_id', 'entry_number']);
        });

        // 3. Assets: Ubah unique code menjadi unique per company
        Schema::table('assets', function (Blueprint $table) {
            $table->dropUnique('assets_code_unique');
            $table->unique(['company_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'code']);
            $table->unique('code');
        });

        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'entry_number']);
            $table->unique('entry_number');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'transaction_number']);
            $table->unique('transaction_number');
        });
    }
};
