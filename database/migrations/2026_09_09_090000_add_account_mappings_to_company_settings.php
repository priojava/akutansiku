<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            // Tab 1: Barang & Jasa (Penjualan & HPP)
            $table->unsignedBigInteger('account_inventory_id')->nullable()->after('cache_ar_ap'); // Persediaan Barang (1-10200)
            $table->unsignedBigInteger('account_sales_id')->nullable()->after('account_inventory_id'); // Penjualan (4-40000)
            $table->unsignedBigInteger('account_sales_return_id')->nullable()->after('account_sales_id'); // Retur Penjualan (4-40200)
            $table->unsignedBigInteger('account_sales_discount_id')->nullable()->after('account_sales_return_id'); // Diskon Penjualan (4-40100)
            $table->unsignedBigInteger('account_goods_in_transit_id')->nullable()->after('account_sales_discount_id'); // Barang Terkirim (1-10200)
            $table->unsignedBigInteger('account_cogs_id')->nullable()->after('account_goods_in_transit_id'); // Beban Pokok Penjualan / HPP (5-50000)
            $table->unsignedBigInteger('account_purchase_return_id')->nullable()->after('account_cogs_id'); // Retur Pembelian (5-50200)
            $table->unsignedBigInteger('account_expense_id')->nullable()->after('account_purchase_return_id'); // Beban Operasional Umum (6-60900)
            $table->unsignedBigInteger('account_unbilled_purchases_id')->nullable()->after('account_expense_id'); // Pembelian Belum Tertagih (2-20101)

            // Tab 2: Hutang, Piutang & Pembayaran
            $table->unsignedBigInteger('account_receivable_id')->nullable()->after('account_unbilled_purchases_id'); // Piutang Usaha (1-10100)
            $table->unsignedBigInteger('account_payable_id')->nullable()->after('account_receivable_id'); // Hutang Usaha (2-20100)
            $table->unsignedBigInteger('account_cash_drawer_id')->nullable()->after('account_payable_id'); // Kas Kasir Default (1-10001)
            $table->unsignedBigInteger('account_rounding_diff_id')->nullable()->after('account_cash_drawer_id'); // Selisih Kas / Pembulatan (8-80900)
            $table->unsignedBigInteger('account_sales_deposit_id')->nullable()->after('account_rounding_diff_id'); // Uang Muka Penjualan (2-20208)
            $table->unsignedBigInteger('account_purchase_downpayment_id')->nullable()->after('account_sales_deposit_id'); // Uang Muka Pembelian (1-10403)
        });
    }

    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn([
                'account_inventory_id',
                'account_sales_id',
                'account_sales_return_id',
                'account_sales_discount_id',
                'account_goods_in_transit_id',
                'account_cogs_id',
                'account_purchase_return_id',
                'account_expense_id',
                'account_unbilled_purchases_id',
                'account_receivable_id',
                'account_payable_id',
                'account_cash_drawer_id',
                'account_rounding_diff_id',
                'account_sales_deposit_id',
                'account_purchase_downpayment_id',
            ]);
        });
    }
};
