@extends('layouts.app')

@section('title', 'Akun Perkiraan Otomatis')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{ activeTab: 'goods' }">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="p-2 bg-indigo-50 text-indigo-600 rounded-xl border border-indigo-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </span>
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Akun Perkiraan (Default Account Mappings)</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Atur akun perkiraan default yang digunakan sistem saat penjurnalan otomatis transaksi kasir, penjualan, pembelian, dan persediaan.</p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('settings.main') }}" class="px-3.5 py-2 text-xs font-semibold text-slate-600 bg-white hover:bg-slate-50 border border-slate-200 rounded-lg shadow-sm transition">
                Kembali ke Pengaturan
            </a>
        </div>
    </div>

    <!-- Alert Info -->
    <div class="bg-blue-50/70 border border-blue-200/80 rounded-xl p-4 flex items-start gap-3">
        <div class="p-1.5 bg-blue-500 text-white rounded-lg shrink-0 mt-0.5 shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <div class="text-xs text-blue-900 leading-relaxed">
            <span class="font-bold">Otomasi Penjurnalan:</span> Saat kasir atau staf membuat transaksi (seperti Invoice Penjualan atau Penerimaan Kas), sistem akan otomatis mendebit dan mengkredit akun-akun yang Anda tetapkan di bawah ini tanpa perlu memilih COA secara manual.
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="flex border-b border-slate-200 bg-white rounded-t-xl px-4 pt-2 shadow-sm border-t border-x">
        <button type="button" 
                @click="activeTab = 'goods'" 
                :class="activeTab === 'goods' ? 'border-blue-600 text-blue-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 font-medium'"
                class="px-5 py-3 text-xs border-b-2 transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
            </svg>
            Barang & Jasa (Penjualan & HPP)
        </button>
        <button type="button" 
                @click="activeTab = 'finance'" 
                :class="activeTab === 'finance' ? 'border-blue-600 text-blue-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 font-medium'"
                class="px-5 py-3 text-xs border-b-2 transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            Hutang, Piutang & Kasir
        </button>
    </div>

    <!-- Main Form -->
    <form method="POST" action="{{ route('settings.account_mappings.update') }}">
        @csrf

        <div class="bg-white rounded-b-xl border-x border-b border-slate-200 shadow-sm overflow-hidden p-6 space-y-6">

            <!-- TAB 1: Barang & Jasa -->
            <div x-show="activeTab === 'goods'" class="space-y-5">
                <div class="pb-3 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-slate-800 text-sm">Pemetaan Akun Barang, Jasa & Persediaan</h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">Digunakan untuk transaksi penjualan produk, retur, diskon, dan kalkulasi HPP otomatis.</p>
                    </div>
                    <span class="text-[11px] font-semibold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-md">9 Pengaturan</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    
                    <!-- 1. Persediaan -->
                    <div class="p-3.5 bg-slate-50/60 rounded-xl border border-slate-200/70 hover:border-blue-300 transition">
                        <label class="block text-xs font-bold text-slate-800 mb-1 flex items-center justify-between">
                            <span>Persediaan (Inventory)</span>
                            <span class="text-[10px] text-blue-600 font-semibold uppercase">Aset Lancar</span>
                        </label>
                        <p class="text-[11px] text-slate-500 mb-2">Akun penampung nilai stok fisik barang dagang.</p>
                        <x-searchable-account-select 
                            name="account_inventory_id" 
                            :options="$accounts" 
                            :selected="$settings->account_inventory_id ?? $defaultMap['account_inventory_id']" 
                            placeholder="-- Pilih Akun Persediaan --" 
                            bg-color="bg-white" />
                    </div>

                    <!-- 2. Penjualan -->
                    <div class="p-3.5 bg-slate-50/60 rounded-xl border border-slate-200/70 hover:border-blue-300 transition">
                        <label class="block text-xs font-bold text-slate-800 mb-1 flex items-center justify-between">
                            <span>Penjualan (Sales Revenue)</span>
                            <span class="text-[10px] text-emerald-600 font-semibold uppercase">Pendapatan</span>
                        </label>
                        <p class="text-[11px] text-slate-500 mb-2">Akun penerimaan omset penjualan produk/jasa.</p>
                        <x-searchable-account-select 
                            name="account_sales_id" 
                            :options="$accounts" 
                            :selected="$settings->account_sales_id ?? $defaultMap['account_sales_id']" 
                            placeholder="-- Pilih Akun Penjualan --" 
                            bg-color="bg-white" />
                    </div>

                    <!-- 3. Retur Penjualan -->
                    <div class="p-3.5 bg-slate-50/60 rounded-xl border border-slate-200/70 hover:border-blue-300 transition">
                        <label class="block text-xs font-bold text-slate-800 mb-1 flex items-center justify-between">
                            <span>Retur Penjualan (Sales Return)</span>
                            <span class="text-[10px] text-amber-600 font-semibold uppercase">Kontra Pendapatan</span>
                        </label>
                        <p class="text-[11px] text-slate-500 mb-2">Akun saat barang dikembalikan oleh pelanggan.</p>
                        <x-searchable-account-select 
                            name="account_sales_return_id" 
                            :options="$accounts" 
                            :selected="$settings->account_sales_return_id ?? $defaultMap['account_sales_return_id']" 
                            placeholder="-- Pilih Akun Retur Penjualan --" 
                            bg-color="bg-white" />
                    </div>

                    <!-- 4. Diskon Penjualan -->
                    <div class="p-3.5 bg-slate-50/60 rounded-xl border border-slate-200/70 hover:border-blue-300 transition">
                        <label class="block text-xs font-bold text-slate-800 mb-1 flex items-center justify-between">
                            <span>Diskon Penjualan (Sales Discount)</span>
                            <span class="text-[10px] text-amber-600 font-semibold uppercase">Potongan Harga</span>
                        </label>
                        <p class="text-[11px] text-slate-500 mb-2">Akun potongan harga/promo yang diberikan ke pembeli.</p>
                        <x-searchable-account-select 
                            name="account_sales_discount_id" 
                            :options="$accounts" 
                            :selected="$settings->account_sales_discount_id ?? $defaultMap['account_sales_discount_id']" 
                            placeholder="-- Pilih Akun Diskon Penjualan --" 
                            bg-color="bg-white" />
                    </div>

                    <!-- 5. Barang Terkirim -->
                    <div class="p-3.5 bg-slate-50/60 rounded-xl border border-slate-200/70 hover:border-blue-300 transition">
                        <label class="block text-xs font-bold text-slate-800 mb-1 flex items-center justify-between">
                            <span>Barang Terkirim (Goods In Transit)</span>
                            <span class="text-[10px] text-blue-600 font-semibold uppercase">Persediaan</span>
                        </label>
                        <p class="text-[11px] text-slate-500 mb-2">Barang yang sedang dalam proses pengiriman ekspedisi.</p>
                        <x-searchable-account-select 
                            name="account_goods_in_transit_id" 
                            :options="$accounts" 
                            :selected="$settings->account_goods_in_transit_id ?? $defaultMap['account_goods_in_transit_id']" 
                            placeholder="-- Pilih Akun Barang Terkirim --" 
                            bg-color="bg-white" />
                    </div>

                    <!-- 6. Beban Pokok Penjualan (HPP) -->
                    <div class="p-3.5 bg-slate-50/60 rounded-xl border border-slate-200/70 hover:border-blue-300 transition">
                        <label class="block text-xs font-bold text-slate-800 mb-1 flex items-center justify-between">
                            <span>Beban Pokok Penjualan (HPP / COGS)</span>
                            <span class="text-[10px] text-rose-600 font-semibold uppercase">Harga Pokok</span>
                        </label>
                        <p class="text-[11px] text-slate-500 mb-2">Harga pokok modal barang yang terjual.</p>
                        <x-searchable-account-select 
                            name="account_cogs_id" 
                            :options="$accounts" 
                            :selected="$settings->account_cogs_id ?? $defaultMap['account_cogs_id']" 
                            placeholder="-- Pilih Akun HPP --" 
                            bg-color="bg-white" />
                    </div>

                    <!-- 7. Retur Pembelian -->
                    <div class="p-3.5 bg-slate-50/60 rounded-xl border border-slate-200/70 hover:border-blue-300 transition">
                        <label class="block text-xs font-bold text-slate-800 mb-1 flex items-center justify-between">
                            <span>Retur Pembelian (Purchase Return)</span>
                            <span class="text-[10px] text-emerald-600 font-semibold uppercase">Kontra HPP</span>
                        </label>
                        <p class="text-[11px] text-slate-500 mb-2">Pengembalian barang rusak kepada supplier.</p>
                        <x-searchable-account-select 
                            name="account_purchase_return_id" 
                            :options="$accounts" 
                            :selected="$settings->account_purchase_return_id ?? $defaultMap['account_purchase_return_id']" 
                            placeholder="-- Pilih Akun Retur Pembelian --" 
                            bg-color="bg-white" />
                    </div>

                    <!-- 8. Beban Operasional Umum -->
                    <div class="p-3.5 bg-slate-50/60 rounded-xl border border-slate-200/70 hover:border-blue-300 transition">
                        <label class="block text-xs font-bold text-slate-800 mb-1 flex items-center justify-between">
                            <span>Beban Operasional (General Expense)</span>
                            <span class="text-[10px] text-rose-600 font-semibold uppercase">Beban</span>
                        </label>
                        <p class="text-[11px] text-slate-500 mb-2">Akun default pengeluaran biaya operasional harian.</p>
                        <x-searchable-account-select 
                            name="account_expense_id" 
                            :options="$accounts" 
                            :selected="$settings->account_expense_id ?? $defaultMap['account_expense_id']" 
                            placeholder="-- Pilih Akun Beban --" 
                            bg-color="bg-white" />
                    </div>

                    <!-- 9. Pembelian Belum Tertagih -->
                    <div class="p-3.5 bg-slate-50/60 rounded-xl border border-slate-200/70 hover:border-blue-300 transition md:col-span-2">
                        <label class="block text-xs font-bold text-slate-800 mb-1 flex items-center justify-between">
                            <span>Pembelian Belum Tertagih (Unbilled Purchases)</span>
                            <span class="text-[10px] text-purple-600 font-semibold uppercase">Kewajiban Akrual</span>
                        </label>
                        <p class="text-[11px] text-slate-500 mb-2">Akun penampung saat barang supplier sudah tiba di gudang namun tagihan invoice belum diterbitkan.</p>
                        <x-searchable-account-select 
                            name="account_unbilled_purchases_id" 
                            :options="$accounts" 
                            :selected="$settings->account_unbilled_purchases_id ?? $defaultMap['account_unbilled_purchases_id']" 
                            placeholder="-- Pilih Akun Pembelian Belum Tertagih --" 
                            bg-color="bg-white" />
                    </div>

                </div>
            </div>

            <!-- TAB 2: Hutang, Piutang & Kasir -->
            <div x-show="activeTab === 'finance'" class="space-y-5" style="display: none;">
                <div class="pb-3 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-slate-800 text-sm">Pemetaan Akun Hutang, Piutang & Kasir POS</h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">Digunakan saat transaksi kas/bank, kasir mencatat penjualan langsung, kredit piutang, dan hutang supplier.</p>
                    </div>
                    <span class="text-[11px] font-semibold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-md">6 Pengaturan</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    
                    <!-- 1. Piutang Usaha -->
                    <div class="p-3.5 bg-slate-50/60 rounded-xl border border-slate-200/70 hover:border-blue-300 transition">
                        <label class="block text-xs font-bold text-slate-800 mb-1 flex items-center justify-between">
                            <span>Piutang Usaha (Accounts Receivable)</span>
                            <span class="text-[10px] text-blue-600 font-semibold uppercase">Aset Lancar</span>
                        </label>
                        <p class="text-[11px] text-slate-500 mb-2">Akun tagihan kredit kepada pelanggan/customer.</p>
                        <x-searchable-account-select 
                            name="account_receivable_id" 
                            :options="$accounts" 
                            :selected="$settings->account_receivable_id ?? $defaultMap['account_receivable_id']" 
                            placeholder="-- Pilih Akun Piutang --" 
                            bg-color="bg-white" />
                    </div>

                    <!-- 2. Hutang Usaha -->
                    <div class="p-3.5 bg-slate-50/60 rounded-xl border border-slate-200/70 hover:border-blue-300 transition">
                        <label class="block text-xs font-bold text-slate-800 mb-1 flex items-center justify-between">
                            <span>Hutang Usaha (Accounts Payable)</span>
                            <span class="text-[10px] text-purple-600 font-semibold uppercase">Kewajiban</span>
                        </label>
                        <p class="text-[11px] text-slate-500 mb-2">Akun tagihan dari supplier barang atau vendor.</p>
                        <x-searchable-account-select 
                            name="account_payable_id" 
                            :options="$accounts" 
                            :selected="$settings->account_payable_id ?? $defaultMap['account_payable_id']" 
                            placeholder="-- Pilih Akun Hutang --" 
                            bg-color="bg-white" />
                    </div>

                    <!-- 3. Kas Kasir Default -->
                    <div class="p-3.5 bg-slate-50/60 rounded-xl border border-slate-200/70 hover:border-blue-300 transition">
                        <label class="block text-xs font-bold text-slate-800 mb-1 flex items-center justify-between">
                            <span>Kas Kasir Default (Cash Drawer)</span>
                            <span class="text-[10px] text-emerald-600 font-semibold uppercase">Kas & Bank</span>
                        </label>
                        <p class="text-[11px] text-slate-500 mb-2">Akun laci kasir penampung tunai penjualan POS.</p>
                        <x-searchable-account-select 
                            name="account_cash_drawer_id" 
                            :options="$accounts" 
                            :selected="$settings->account_cash_drawer_id ?? $defaultMap['account_cash_drawer_id']" 
                            placeholder="-- Pilih Akun Kas Kasir --" 
                            bg-color="bg-white" />
                    </div>

                    <!-- 4. Selisih Kas / Pembulatan -->
                    <div class="p-3.5 bg-slate-50/60 rounded-xl border border-slate-200/70 hover:border-blue-300 transition">
                        <label class="block text-xs font-bold text-slate-800 mb-1 flex items-center justify-between">
                            <span>Selisih Kas / Pembulatan (Cash Rounding)</span>
                            <span class="text-[10px] text-amber-600 font-semibold uppercase">Pendapatan/Beban Lain</span>
                        </label>
                        <p class="text-[11px] text-slate-500 mb-2">Akun untuk mencatat pembulatan kembalian kasir.</p>
                        <x-searchable-account-select 
                            name="account_rounding_diff_id" 
                            :options="$accounts" 
                            :selected="$settings->account_rounding_diff_id ?? $defaultMap['account_rounding_diff_id']" 
                            placeholder="-- Pilih Akun Selisih Kas --" 
                            bg-color="bg-white" />
                    </div>

                    <!-- 5. Uang Muka Penjualan -->
                    <div class="p-3.5 bg-slate-50/60 rounded-xl border border-slate-200/70 hover:border-blue-300 transition">
                        <label class="block text-xs font-bold text-slate-800 mb-1 flex items-center justify-between">
                            <span>Uang Muka Penjualan (Customer Deposit)</span>
                            <span class="text-[10px] text-purple-600 font-semibold uppercase">Kewajiban Lancar</span>
                        </label>
                        <p class="text-[11px] text-slate-500 mb-2">DP / Uang muka pesanan yang diterima dari pembeli.</p>
                        <x-searchable-account-select 
                            name="account_sales_deposit_id" 
                            :options="$accounts" 
                            :selected="$settings->account_sales_deposit_id ?? $defaultMap['account_sales_deposit_id']" 
                            placeholder="-- Pilih Akun DP Penjualan --" 
                            bg-color="bg-white" />
                    </div>

                    <!-- 6. Uang Muka Pembelian -->
                    <div class="p-3.5 bg-slate-50/60 rounded-xl border border-slate-200/70 hover:border-blue-300 transition">
                        <label class="block text-xs font-bold text-slate-800 mb-1 flex items-center justify-between">
                            <span>Uang Muka Pembelian (Vendor Advance)</span>
                            <span class="text-[10px] text-blue-600 font-semibold uppercase">Aset Lancar</span>
                        </label>
                        <p class="text-[11px] text-slate-500 mb-2">DP yang kita bayarkan ke supplier saat pre-order barang.</p>
                        <x-searchable-account-select 
                            name="account_purchase_downpayment_id" 
                            :options="$accounts" 
                            :selected="$settings->account_purchase_downpayment_id ?? $defaultMap['account_purchase_downpayment_id']" 
                            placeholder="-- Pilih Akun DP Pembelian --" 
                            bg-color="bg-white" />
                    </div>

                </div>
            </div>

            <!-- Action Buttons Footer -->
            <div class="pt-6 border-t border-slate-100 flex items-center justify-between">
                <p class="text-[11px] text-slate-400">
                    * Semua perubahan pemetaan akun akan langsung diterapkan pada transaksi berikutnya.
                </p>
                <div class="flex items-center gap-3">
                    <button type="reset" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                        Batal / Reset Form
                    </button>
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg shadow-sm shadow-blue-500/30 transition transform active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Simpan Pemetaan Akun
                    </button>
                </div>
            </div>

        </div>
    </form>

</div>
@endsection
