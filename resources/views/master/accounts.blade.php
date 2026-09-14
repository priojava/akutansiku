@extends('layouts.app')

@section('title', 'Master Akun (COA) & Saldo Awal')

@section('content')
<div class="space-y-6" x-data="{
    search: '',
    activeTab: 'ALL',
    modalTambahOpen: false,
    selectedGroup: 'ASET LANCAR',
    selectedCategory: 'Kas & Bank',
    codePrefix: '1-10',
    accountType: 'Debit',
    totalDebit: {{ $totalInitialDebit }},
    totalCredit: {{ $totalInitialCredit }},
    isLocked: {{ $company->is_initial_balance_locked ? 'true' : 'false' }},

    categoriesByGroup: {
        'ASET LANCAR': [
            { name: 'Kas & Bank', prefix: '1-10', type: 'Debit' },
            { name: 'Akun Piutang', prefix: '1-10', type: 'Debit' },
            { name: 'Persediaan', prefix: '1-10', type: 'Debit' },
            { name: 'Harta Lancar Lainnya', prefix: '1-10', type: 'Debit' }
        ],
        'ASET TIDAK LANCAR': [
            { name: 'Harta Tetap', prefix: '1-10', type: 'Debit' },
            { name: 'Depresiasi & Amortisasi', prefix: '1-10', type: 'Credit' },
            { name: 'Harta Lainnya', prefix: '1-10', type: 'Debit' }
        ],
        'LIABILITAS': [
            { name: 'Akun Hutang', prefix: '2-20', type: 'Credit' },
            { name: 'Kewajiban Lancar Lainnya', prefix: '2-20', type: 'Credit' },
            { name: 'Kewajiban Jangka Panjang', prefix: '2-20', type: 'Credit' }
        ],
        'EKUITAS': [
            { name: 'Modal', prefix: '3-30', type: 'Credit' },
            { name: 'Laba Ditahan', prefix: '3-30', type: 'Credit' }
        ],
        'PENDAPATAN': [
            { name: 'Pendapatan', prefix: '4-40', type: 'Credit' },
            { name: 'Pendapatan Lainnya', prefix: '7-70', type: 'Credit' }
        ],
        'BIAYA': [
            { name: 'Harga Pokok Penjualan', prefix: '5-50', type: 'Debit' },
            { name: 'Beban', prefix: '6-60', type: 'Debit' },
            { name: 'Beban Lainnya', prefix: '8-80', type: 'Debit' }
        ]
    },

    onGroupChange() {
        let cats = this.categoriesByGroup[this.selectedGroup] || [];
        if (cats.length > 0) {
            this.selectedCategory = cats[0].name;
            this.codePrefix = cats[0].prefix;
            this.accountType = cats[0].type;
        }
    },

    onCategoryChange() {
        let cats = this.categoriesByGroup[this.selectedGroup] || [];
        let found = cats.find(c => c.name === this.selectedCategory);
        if (found) {
            this.codePrefix = found.prefix;
            this.accountType = found.type;
        }
    },

    formatNumber(el) {
        if (this.isLocked) return;
        let clean = el.value.replace(/[^0-9]/g, '');
        if (!clean) {
            el.value = '0';
        } else {
            el.value = parseInt(clean, 10).toLocaleString('id-ID');
        }
        this.calculateTotals();
    },

    calculateTotals() {
        if (this.isLocked) return;
        let debit = 0;
        let credit = 0;
        document.querySelectorAll('.input-debit').forEach(el => {
            debit += parseFloat(el.value.replace(/[^0-9]/g, '')) || 0;
        });
        document.querySelectorAll('.input-credit').forEach(el => {
            credit += parseFloat(el.value.replace(/[^0-9]/g, '')) || 0;
        });
        this.totalDebit = debit;
        this.totalCredit = credit;
        let dEl = document.getElementById('total-debit-display');
        let cEl = document.getElementById('total-credit-display');
        if (dEl) dEl.innerText = 'TOTAL: RP ' + debit.toLocaleString('id-ID');
        if (cEl) cEl.innerText = 'TOTAL: RP ' + credit.toLocaleString('id-ID');
    },

    autoBalance() {
        if (this.isLocked) return;
        let diff = this.totalDebit - this.totalCredit;
        if (diff === 0) return;
        
        let elCredit30999 = document.querySelector('.input-credit-30999');
        let elDebit30999 = document.querySelector('.input-debit-30999');
        
        if (diff > 0 && elCredit30999) {
            let current = parseFloat(elCredit30999.value.replace(/[^0-9]/g, '')) || 0;
            elCredit30999.value = (current + diff).toLocaleString('id-ID');
        } else if (diff < 0 && elDebit30999) {
            let current = parseFloat(elDebit30999.value.replace(/[^0-9]/g, '')) || 0;
            elDebit30999.value = (current + Math.abs(diff)).toLocaleString('id-ID');
        }
        this.calculateTotals();
    }
}">

    @php
        $currentUser = auth()->user() ?? \App\Models\User::first();
        $isAdmin = $currentUser ? $currentUser->isAdmin($company->id) : false;
    @endphp

    <!-- Flash Notifications -->
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-xs font-semibold text-emerald-800 flex items-center space-x-2 shadow-2xs">
            <i class="fa-solid fa-circle-check text-emerald-600"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-xs font-semibold text-rose-800 flex items-center space-x-2 shadow-2xs">
            <i class="fa-solid fa-circle-exclamation text-rose-600"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if($company->is_initial_balance_locked)
        <!-- LOCKED ALERT BANNER -->
        <div class="p-4 rounded-xl bg-slate-900 text-white shadow-md flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-lg bg-blue-600 flex items-center justify-center text-lg font-bold">
                    <i class="fa-solid fa-lock"></i>
                </div>
                <div>
                    <h4 class="font-bold text-sm text-blue-200">Saldo Awal Terkunci (Read-Only Mode)</h4>
                    <p class="text-xs text-slate-300">
                        Saldo awal telah dikunci oleh Administrator untuk mencegah manipulasi. Seluruh mutasi keuangan selanjutnya wajib dicatat melalui menu <a href="{{ route('transactions.create') }}" class="text-blue-400 font-bold underline hover:text-blue-300">Transaksi</a>.
                    </p>
                </div>
            </div>

            @if($isAdmin)
                <form method="POST" action="{{ route('master.accounts.toggle_lock') }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 hover:border-slate-600 rounded-lg text-xs font-semibold transition flex items-center space-x-1.5">
                        <i class="fa-solid fa-lock-open text-xs text-amber-400"></i>
                        <span>Buka Kunci Saldo (Admin)</span>
                    </button>
                </form>
            @else
                <span class="px-3 py-1.5 rounded-lg bg-slate-800 border border-slate-700 text-[11px] font-semibold text-slate-400 flex items-center space-x-1.5">
                    <i class="fa-solid fa-shield-halved text-amber-400"></i>
                    <span>Terkunci oleh Owner</span>
                </span>
            @endif
        </div>
    @endif

    <form method="POST" action="{{ route('master.accounts.initial_balances') }}">
        @csrf

        <!-- Top Header Card -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center space-x-3">
                    <span class="text-xs font-semibold text-slate-700">Tanggal Konversi Saldo Awal:</span>
                    <input type="date" name="conversion_date" value="{{ $company->conversion_date ? \Carbon\Carbon::parse($company->conversion_date)->format('Y-m-d') : '2026-09-01' }}"
                           {{ $company->is_initial_balance_locked ? 'disabled' : '' }}
                           class="px-3 py-1.5 bg-slate-50 border border-slate-300 rounded-lg text-xs font-medium focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none disabled:bg-slate-100 disabled:text-slate-500">
                </div>

                <div class="flex items-center space-x-3">
                    <div class="relative">
                        <input type="text" x-model="search" placeholder="Cari nama, kode, kategori akun..."
                               class="w-48 sm:w-64 pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-300 rounded-lg text-xs focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2 text-blue-500 text-xs"></i>
                    </div>

                    <!-- Tombol Tambah Akun Baru -->
                    <button type="button" @click="modalTambahOpen = true; onGroupChange()" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold px-4 py-2 rounded-lg shadow-sm transition flex items-center space-x-1.5">
                        <i class="fa-solid fa-plus text-xs"></i>
                        <span>Tambah Akun COA</span>
                    </button>

                    @if(!$company->is_initial_balance_locked)
                        <!-- Tombol Simpan Saldo Awal -->
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-5 py-2 rounded-lg shadow-sm transition flex items-center space-x-1.5">
                            <i class="fa-solid fa-floppy-disk text-xs"></i>
                            <span>Simpan Saldo</span>
                        </button>

                        @if($isAdmin)
                            <!-- Tombol Kunci Saldo Awal (Khusus Admin) -->
                            <button type="button" onclick="document.getElementById('form-lock-balances').submit()" class="bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold px-4 py-2 rounded-lg shadow-sm transition flex items-center space-x-1.5" title="Kunci Saldo Awal agar tidak bisa diedit sembarangan">
                                <i class="fa-solid fa-lock text-amber-400 text-xs"></i>
                                <span>Kunci Saldo</span>
                            </button>
                        @endif
                    @endif
                </div>
            </div>

            <!-- Balance Status Banner -->
            <div class="pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3 text-xs">
                <div class="flex items-center space-x-2">
                    <span class="font-bold text-slate-700">Status Saldo Awal:</span>
                    <template x-if="totalDebit === totalCredit">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 font-bold border border-emerald-200">
                            <i class="fa-solid fa-circle-check mr-1.5 text-emerald-500"></i> SEIMBANG (BALANCE)
                        </span>
                    </template>
                    <template x-if="totalDebit !== totalCredit">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-amber-50 text-amber-800 font-bold border border-amber-200">
                            <i class="fa-solid fa-triangle-exclamation mr-1.5 text-amber-500"></i> 
                            BELUM SEIMBANG (Selisih: Rp <span x-text="Math.abs(totalDebit - totalCredit).toLocaleString('id-ID')"></span>)
                        </span>
                    </template>
                </div>

                @if(!$company->is_initial_balance_locked)
                    <template x-if="totalDebit !== totalCredit">
                        <button type="button" @click="autoBalance()" class="inline-flex items-center space-x-1.5 text-xs text-blue-700 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg font-bold border border-blue-200 transition">
                            <i class="fa-solid fa-wand-magic-sparkles text-amber-500"></i>
                            <span>Seimbangkan Otomatis ke Akun Saldo Awal (3-30999)</span>
                        </button>
                    </template>
                @endif
            </div>

            <!-- Tab Pengelompokan Akun (Pills Filter) -->
            <div class="pt-3 border-t border-slate-100 flex flex-wrap items-center gap-1.5">
                <button type="button" @click="activeTab = 'ALL'"
                        :class="activeTab === 'ALL' ? 'bg-slate-900 text-white shadow-xs font-bold' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'"
                        class="px-3.5 py-1.5 rounded-lg text-xs transition flex items-center space-x-1.5">
                    <span>Semua Akun</span>
                    <span class="text-[10px] px-1.5 py-0.2 rounded-full" :class="activeTab === 'ALL' ? 'bg-slate-700 text-white' : 'bg-slate-200 text-slate-700'">
                        {{ count($accounts) }}
                    </span>
                </button>

                <button type="button" @click="activeTab = 'ASET LANCAR'"
                        :class="activeTab === 'ASET LANCAR' ? 'bg-blue-600 text-white shadow-xs font-bold' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'"
                        class="px-3.5 py-1.5 rounded-lg text-xs transition flex items-center space-x-1.5">
                    <span>Aset Lancar</span>
                    <span class="text-[10px] px-1.5 py-0.2 rounded-full" :class="activeTab === 'ASET LANCAR' ? 'bg-blue-700 text-white' : 'bg-slate-200 text-slate-700'">
                        {{ $groupSummaries['ASET LANCAR']['count'] ?? 0 }}
                    </span>
                </button>

                <button type="button" @click="activeTab = 'ASET TIDAK LANCAR'"
                        :class="activeTab === 'ASET TIDAK LANCAR' ? 'bg-indigo-600 text-white shadow-xs font-bold' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'"
                        class="px-3.5 py-1.5 rounded-lg text-xs transition flex items-center space-x-1.5">
                    <span>Aset Tidak Lancar</span>
                    <span class="text-[10px] px-1.5 py-0.2 rounded-full" :class="activeTab === 'ASET TIDAK LANCAR' ? 'bg-indigo-700 text-white' : 'bg-slate-200 text-slate-700'">
                        {{ $groupSummaries['ASET TIDAK LANCAR']['count'] ?? 0 }}
                    </span>
                </button>

                <button type="button" @click="activeTab = 'LIABILITAS'"
                        :class="activeTab === 'LIABILITAS' ? 'bg-rose-600 text-white shadow-xs font-bold' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'"
                        class="px-3.5 py-1.5 rounded-lg text-xs transition flex items-center space-x-1.5">
                    <span>Liabilitas</span>
                    <span class="text-[10px] px-1.5 py-0.2 rounded-full" :class="activeTab === 'LIABILITAS' ? 'bg-rose-700 text-white' : 'bg-slate-200 text-slate-700'">
                        {{ $groupSummaries['LIABILITAS']['count'] ?? 0 }}
                    </span>
                </button>

                <button type="button" @click="activeTab = 'EKUITAS'"
                        :class="activeTab === 'EKUITAS' ? 'bg-amber-600 text-white shadow-xs font-bold' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'"
                        class="px-3.5 py-1.5 rounded-lg text-xs transition flex items-center space-x-1.5">
                    <span>Ekuitas</span>
                    <span class="text-[10px] px-1.5 py-0.2 rounded-full" :class="activeTab === 'EKUITAS' ? 'bg-amber-700 text-white' : 'bg-slate-200 text-slate-700'">
                        {{ $groupSummaries['EKUITAS']['count'] ?? 0 }}
                    </span>
                </button>

                <button type="button" @click="activeTab = 'PENDAPATAN'"
                        :class="activeTab === 'PENDAPATAN' ? 'bg-emerald-600 text-white shadow-xs font-bold' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'"
                        class="px-3.5 py-1.5 rounded-lg text-xs transition flex items-center space-x-1.5">
                    <span>Pendapatan</span>
                    <span class="text-[10px] px-1.5 py-0.2 rounded-full" :class="activeTab === 'PENDAPATAN' ? 'bg-emerald-700 text-white' : 'bg-slate-200 text-slate-700'">
                        {{ $groupSummaries['PENDAPATAN']['count'] ?? 0 }}
                    </span>
                </button>

                <button type="button" @click="activeTab = 'BIAYA'"
                        :class="activeTab === 'BIAYA' ? 'bg-orange-600 text-white shadow-xs font-bold' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'"
                        class="px-3.5 py-1.5 rounded-lg text-xs transition flex items-center space-x-1.5">
                    <span>Biaya & Beban</span>
                    <span class="text-[10px] px-1.5 py-0.2 rounded-full" :class="activeTab === 'BIAYA' ? 'bg-orange-700 text-white' : 'bg-slate-200 text-slate-700'">
                        {{ $groupSummaries['BIAYA']['count'] ?? 0 }}
                    </span>
                </button>
            </div>
        </div>

        <!-- Table Card -->
        <div class="mt-6 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto max-h-[70vh] overflow-y-auto sidebar-scroll">
                <table class="w-full text-left text-xs text-slate-700">
                    <thead class="bg-blue-50/70 border-b border-blue-100 text-blue-900 uppercase text-[10px] tracking-wider font-bold sticky top-0 z-10">
                        <tr>
                            <th class="px-4 py-3.5 w-28">KODE</th>
                            <th class="px-4 py-3.5">NAMA AKUN</th>
                            <th class="px-4 py-3.5">KATEGORI</th>
                            <th class="px-4 py-3.5 w-24">TIPE AKUN</th>
                            <th class="px-4 py-3.5 w-48 text-right">
                                <div>SALDO DEBIT</div>
                                <div id="total-debit-display" class="text-[10px] font-bold text-blue-600">TOTAL: RP {{ number_format($totalInitialDebit, 0, ',', '.') }}</div>
                            </th>
                            <th class="px-4 py-3.5 w-48 text-right">
                                <div>SALDO KREDIT</div>
                                <div id="total-credit-display" class="text-[10px] font-bold text-rose-600">TOTAL: RP {{ number_format($totalInitialCredit, 0, ',', '.') }}</div>
                            </th>
                            @if($isAdmin)
                                <th class="px-3 py-3.5 w-16 text-center">AKSI</th>
                            @endif
                        </tr>
                    </thead>

                    @foreach($groupedAccounts as $classification => $groupAccs)
                        <tbody class="divide-y divide-slate-100" 
                               x-show="activeTab === 'ALL' || activeTab === '{{ $classification }}'">
                            
                            <!-- GROUP HEADER ROW (Sesuai Gambar User: ASET LANCAR dll) -->
                            <tr class="bg-slate-50/95 border-t-2 border-b border-slate-200/90">
                                <td colspan="{{ $isAdmin ? 7 : 6 }}" class="px-4 py-3">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-2.5">
                                            <span class="w-2.5 h-4 rounded-xs {{ match($classification) {
                                                'ASET LANCAR' => 'bg-blue-600',
                                                'ASET TIDAK LANCAR' => 'bg-indigo-600',
                                                'LIABILITAS' => 'bg-rose-500',
                                                'EKUITAS' => 'bg-amber-500',
                                                'PENDAPATAN' => 'bg-emerald-600',
                                                'BIAYA' => 'bg-orange-600',
                                                default => 'bg-slate-600'
                                            } }}"></span>
                                            <h3 class="font-black text-xs sm:text-sm tracking-wide text-blue-950 uppercase">
                                                {{ $classification }}
                                            </h3>
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-200 text-slate-700">
                                                {{ count($groupAccs) }} Akun
                                            </span>
                                        </div>
                                        @if(isset($groupSummaries[$classification]))
                                            <div class="text-[11px] font-medium text-slate-500 hidden sm:flex items-center space-x-4">
                                                <span>Total Debit: <strong class="font-mono text-blue-700">Rp {{ number_format($groupSummaries[$classification]['debit'], 0, ',', '.') }}</strong></span>
                                                <span>Total Kredit: <strong class="font-mono text-rose-700">Rp {{ number_format($groupSummaries[$classification]['credit'], 0, ',', '.') }}</strong></span>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            @foreach($groupAccs as $acc)
                                <tr class="hover:bg-blue-50/30 transition {{ $acc->code == '3-30999' ? 'bg-amber-50/30' : '' }}" 
                                    x-show="!search || '{{ strtolower($acc->code . ' ' . $acc->name . ' ' . $acc->category . ' ' . $classification) }}'.includes(search.toLowerCase())">
                                    <td class="px-4 py-3 font-bold text-blue-950 font-mono">
                                        {{ $acc->code }}
                                    </td>
                                    <td class="px-4 py-3 font-semibold text-slate-800 {{ !$acc->is_active ? 'opacity-60' : '' }}">
                                        {{ $acc->name }}
                                        @if(!$acc->is_active)
                                            <span class="ml-1 text-[10px] px-1.5 py-0.5 rounded bg-slate-200 text-slate-600 font-bold">Nonaktif</span>
                                        @endif
                                        @if($acc->code == '3-30999')
                                            <span class="ml-1 text-[10px] px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 font-bold">Penyeimbang</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-slate-500">
                                        <span class="px-2 py-0.5 rounded-md text-[11px] bg-slate-100 text-slate-700 font-medium">
                                            {{ $acc->category }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $acc->type == 'Debit' ? 'bg-blue-100 text-blue-700' : 'bg-rose-100 text-rose-700' }}">
                                            {{ $acc->type }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 text-right">
                                        <input type="text" 
                                               name="debit[{{ $acc->id }}]" 
                                               value="{{ $acc->initial_debit > 0 ? number_format($acc->initial_debit, 0, ',', '.') : 0 }}" 
                                               @input="formatNumber($el)"
                                               {{ $company->is_initial_balance_locked ? 'readonly' : '' }}
                                               class="input-debit {{ $acc->code == '3-30999' ? 'input-debit-30999' : '' }} w-full text-right px-3 py-1.5 bg-slate-50 border border-slate-300 rounded-lg text-xs font-semibold focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none disabled:bg-slate-100 disabled:text-slate-500 {{ $company->is_initial_balance_locked ? 'cursor-not-allowed bg-slate-100 text-slate-600' : '' }}">
                                    </td>
                                    <td class="px-4 py-2 text-right">
                                        <input type="text" 
                                               name="credit[{{ $acc->id }}]" 
                                               value="{{ $acc->initial_credit > 0 ? number_format($acc->initial_credit, 0, ',', '.') : 0 }}" 
                                               @input="formatNumber($el)"
                                               {{ $company->is_initial_balance_locked ? 'readonly' : '' }}
                                               class="input-credit {{ $acc->code == '3-30999' ? 'input-credit-30999' : '' }} w-full text-right px-3 py-1.5 bg-slate-50 border border-slate-300 rounded-lg text-xs font-semibold focus:bg-white focus:ring-2 focus:ring-rose-500 focus:outline-none disabled:bg-slate-100 disabled:text-slate-500 {{ $company->is_initial_balance_locked ? 'cursor-not-allowed bg-slate-100 text-slate-600' : '' }}">
                                    </td>
                                    @if($isAdmin)
                                        <td class="px-3 py-2 text-center whitespace-nowrap">
                                            @if($acc->code !== '3-30999')
                                                <div class="inline-flex items-center space-x-1">
                                                    <!-- 1. Tombol Nonaktifkan / Aktifkan (Sangat Direkomendasikan) -->
                                                    <button type="button" 
                                                            onclick="if(confirm('{{ $acc->is_active ? 'Nonaktifkan akun [' . $acc->code . '] ' . $acc->name . '? Akun tidak akan muncul lagi di pilihan transaksi baru, namun seluruh riwayat pembukuan masa lalu tetap aman.' : 'Aktifkan kembali akun [' . $acc->code . '] ' . $acc->name . '?' }}')) document.getElementById('form-toggle-active-{{ $acc->id }}').submit();"
                                                            title="{{ $acc->is_active ? 'Nonaktifkan Akun (Aman, Riwayat Tetap Utuh)' : 'Aktifkan Kembali Akun' }}"
                                                            class="p-1.5 rounded-lg transition {{ $acc->is_active ? 'text-slate-500 hover:text-amber-600 hover:bg-amber-50' : 'text-emerald-600 bg-emerald-50 hover:bg-emerald-100 font-bold' }}">
                                                        <i class="fa-solid {{ $acc->is_active ? 'fa-ban' : 'fa-check' }} text-xs"></i>
                                                    </button>

                                                    <!-- 2. Tombol Hapus Permanen (Hanya Jika Benar-Benar Belum Ada Transaksi) -->
                                                    @if(($acc->journal_items_count ?? 0) > 0 || ($acc->initial_debit ?? 0) > 0 || ($acc->initial_credit ?? 0) > 0)
                                                        <span title="Tidak dapat dihapus permanen karena sudah ada mutasi jurnal. Gunakan tombol Nonaktifkan di sebelah kiri."
                                                              class="p-1.5 text-slate-300 cursor-not-allowed inline-flex items-center">
                                                            <i class="fa-solid fa-lock text-[11px]"></i>
                                                        </span>
                                                    @else
                                                        <button type="button" 
                                                                onclick="if(confirm('Hapus akun [{{ $acc->code }}] {{ $acc->name }} secara permanen?\n\nAkun ini belum memiliki transaksi sehingga aman dihapus.')) document.getElementById('form-delete-{{ $acc->id }}').submit();"
                                                                title="Hapus Akun Permanen (Akun Baru / Kosong)"
                                                                class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition">
                                                            <i class="fa-solid fa-trash text-xs"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-[10px] text-slate-400 font-mono" title="Akun Penyeimbang Sistem">-</span>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    @endforeach
                </table>
            </div>
        </div>

    </form>

    <!-- FORM HIDDEN UNTUK TOGGLE LOCK, HAPUS AKUN & TOGGLE STATUS (KHUSUS OWNER/ADMIN) -->
    <form id="form-lock-balances" method="POST" action="{{ route('master.accounts.toggle_lock') }}" class="hidden">
        @csrf
    </form>

    @if($isAdmin)
        @foreach($accounts as $acc)
            @if($acc->code !== '3-30999')
                <form id="form-delete-{{ $acc->id }}" method="POST" action="{{ route('master.accounts.destroy', $acc->id) }}" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
                <form id="form-toggle-active-{{ $acc->id }}" method="POST" action="{{ route('master.accounts.toggle_active', $acc->id) }}" class="hidden">
                    @csrf
                </form>
            @endif
        @endforeach
    @endif

    <!-- MODAL TAMBAH DATA AKUN (COA) -->
    <div x-show="modalTambahOpen" x-cloak class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl border border-slate-100 overflow-hidden" @click.away="modalTambahOpen = false">
            <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm">
                        <i class="fa-solid fa-plus"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 text-base">Tambah Akun COA Baru</h3>
                        <p class="text-[11px] text-slate-500">Buat akun baru sesuai kelompok dan posisinya dalam laporan keuangan</p>
                    </div>
                </div>
                <button @click="modalTambahOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('master.accounts.store') }}" class="p-6 space-y-4">
                @csrf
                
                <!-- 1. Kelompok Akun (Klasifikasi Utama) -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Kelompok Akun (Klasifikasi) <span class="text-rose-500">*</span>
                    </label>
                    <select name="classification" x-model="selectedGroup" @change="onGroupChange()" required
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-xs font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="ASET LANCAR">Aset Lancar (Kas, Bank, Piutang, Persediaan)</option>
                        <option value="ASET TIDAK LANCAR">Aset Tidak Lancar (Harta Tetap, Akumulasi Penyusutan)</option>
                        <option value="LIABILITAS">Liabilitas / Kewajiban (Hutang Usaha, Kewajiban Lancar/Panjang)</option>
                        <option value="EKUITAS">Ekuitas / Modal (Modal Usaha, Laba Ditahan)</option>
                        <option value="PENDAPATAN">Pendapatan (Pendapatan Usaha, Pendapatan Lainnya)</option>
                        <option value="BIAYA">Biaya & Beban (HPP, Beban Operasional, Beban Lainnya)</option>
                    </select>
                </div>

                <!-- 2. Kategori Akun (Sub-Kategori Dinamis) -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Kategori Akun <span class="text-rose-500">*</span>
                    </label>
                    <select name="category" x-model="selectedCategory" @change="onCategoryChange()" required
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-xs font-medium focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <template x-for="cat in (categoriesByGroup[selectedGroup] || [])" :key="cat.name">
                            <option :value="cat.name" x-text="cat.name"></option>
                        </template>
                    </select>
                </div>

                <!-- 3. Nama Akun -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Nama Akun <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name" placeholder="Misal: Kas Kecil Kantor, Piutang Pelanggan, dll." required
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-xs font-medium focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>

                <!-- 4. Kode Akun & Tipe Normal -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Kode Akun <span class="text-rose-500">*</span>
                        </label>
                        <div class="flex items-center rounded-lg border border-slate-300 overflow-hidden bg-slate-50 focus-within:ring-2 focus-within:ring-blue-500 focus-within:bg-white">
                            <div class="px-3 py-2 bg-slate-100 border-r border-slate-300 text-xs font-bold font-mono text-slate-600" x-text="codePrefix">
                                1-10
                            </div>
                            <input type="hidden" name="code_prefix" :value="codePrefix">
                            <input type="text" name="code_number" placeholder="102" required
                                   class="flex-1 px-3 py-2 bg-transparent border-none text-xs font-bold font-mono text-slate-800 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Posisi Saldo Normal
                        </label>
                        <input type="hidden" name="type" :value="accountType">
                        <div class="flex items-center space-x-2 pt-0.5">
                            <button type="button" @click="accountType = 'Debit'"
                                    :class="accountType === 'Debit' ? 'bg-blue-600 text-white shadow-xs font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                    class="flex-1 py-2 px-3 rounded-lg text-xs transition text-center">
                                Debit (+)
                            </button>
                            <button type="button" @click="accountType = 'Credit'"
                                    :class="accountType === 'Credit' ? 'bg-rose-600 text-white shadow-xs font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                    class="flex-1 py-2 px-3 rounded-lg text-xs transition text-center">
                                Kredit (+)
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 5. Deskripsi (Opsional) -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Deskripsi / Keterangan (Opsional)</label>
                    <textarea name="description" rows="2" placeholder="Catatan tambahan fungsi akun ini..."
                              class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs font-medium focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none"></textarea>
                </div>

                <div class="flex justify-end space-x-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="modalTambahOpen = false" class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg shadow-sm transition flex items-center space-x-1.5">
                        <i class="fa-solid fa-check"></i>
                        <span>Simpan Akun COA</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
