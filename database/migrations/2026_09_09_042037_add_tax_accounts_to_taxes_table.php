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
        Schema::table('taxes', function (Blueprint $table) {
            $table->boolean('is_withholding')->default(false)->after('rate');
            $table->foreignId('sales_account_id')->nullable()->after('is_withholding')->constrained('accounts')->nullOnDelete();
            $table->foreignId('purchase_account_id')->nullable()->after('sales_account_id')->constrained('accounts')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('taxes', function (Blueprint $table) {
            $table->dropForeign(['sales_account_id']);
            $table->dropForeign(['purchase_account_id']);
            $table->dropColumn(['is_withholding', 'sales_account_id', 'purchase_account_id']);
        });
    }
};
