<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('subscription_invoices', 'snap_token')) {
                $table->string('snap_token')->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('subscription_invoices', 'snap_redirect_url')) {
                $table->text('snap_redirect_url')->nullable()->after('snap_token');
            }
        });
    }

    public function down(): void
    {
        Schema::table('subscription_invoices', function (Blueprint $table) {
            $table->dropColumn(['snap_token', 'snap_redirect_url']);
        });
    }
};
