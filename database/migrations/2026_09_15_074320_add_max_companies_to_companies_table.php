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
        Schema::table('companies', function (Blueprint $table) {
            $table->integer('max_companies')->default(1)->after('subscription_expires_at');
        });

        // Set default kuota: 3 untuk paket premium / pro, 1 untuk standard
        \Illuminate\Support\Facades\DB::table('companies')
            ->where('subscription_plan', 'premium')
            ->orWhere('plan_type', 'premium')
            ->update(['max_companies' => 3]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('max_companies');
        });
    }
};

