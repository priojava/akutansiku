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
        Schema::create('closing_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->dateTime('closing_date');
            $table->string('period_name')->nullable();
            $table->text('notes')->nullable();
            
            // Financial Summaries
            $table->decimal('total_revenue', 18, 2)->default(0);
            $table->decimal('total_expense', 18, 2)->default(0);
            $table->decimal('net_profit_before_tax', 18, 2)->default(0);
            
            // Tax & Retained Earnings Accounts
            $table->foreignId('tax_expense_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->decimal('tax_amount', 18, 2)->default(0);
            $table->foreignId('tax_payable_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('retained_earnings_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->decimal('net_profit_after_tax', 18, 2)->default(0);
            
            // Stored Worksheet Breakdown (JSON)
            $table->json('worksheet_data')->nullable();
            
            // Reference to created Closing Journal Entry
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('closing_periods');
    }
};
