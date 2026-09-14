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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('acquisition_date');
            $table->decimal('acquisition_cost', 15, 2)->default(0);
            $table->foreignId('asset_account_id')->constrained('accounts');
            $table->foreignId('tax_account_id')->nullable()->constrained('accounts');
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->foreignId('credited_account_id')->constrained('accounts');
            $table->string('photo_path')->nullable();

            // Depresiasi / Penyusutan
            $table->boolean('is_depreciated')->default(false);
            $table->string('depreciation_method')->default('straight_line')->nullable(); // straight_line, declining_balance
            $table->integer('useful_life_years')->nullable();
            $table->integer('useful_life_months')->nullable();
            $table->decimal('salvage_value', 15, 2)->default(0);
            $table->foreignId('expense_account_id')->nullable()->constrained('accounts');
            $table->foreignId('accumulated_depreciation_account_id')->nullable()->constrained('accounts');
            $table->decimal('accumulated_depreciation_amount', 15, 2)->default(0);
            $table->date('depreciation_end_date')->nullable();
            $table->string('depreciation_status')->default('active'); // active, stopped, completed

            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
