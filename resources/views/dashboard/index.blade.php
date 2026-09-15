@extends('layouts.app')

@section('title', in_array($currentRole ?? 'admin', ['cashier', 'staff']) ? 'Panel Staf Operasional' : 'Executive Financial Dashboard')

@section('content')
@if(in_array($currentRole ?? 'admin', ['cashier', 'staff']))
    @include('dashboard.cashier')
@else
<div class="space-y-8 animate-in fade-in duration-300">

    <!-- 1. HERO EXECUTIVE WELCOME BANNER -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-900 via-indigo-950 to-blue-900 text-white p-6 sm:p-8 shadow-xl border border-indigo-800/40">
        <!-- Glow Orbs in Background -->
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-blue-500/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <!-- Left: Greeting & Company Info -->
            <div class="space-y-2">
                <div class="flex items-center space-x-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-500/20 text-blue-300 border border-blue-400/30 backdrop-blur-md">
                        <i class="fa-solid fa-sparkles text-amber-300 mr-1.5 text-[11px]"></i>
                        Sistem Akuntansi Terpadu
                    </span>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1.5 animate-ping"></span> Real-time Data
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
                    {{ $company->name ?? 'PT Akuntansi Cipta Prima' }}
                </h1>
                <p class="text-sm text-slate-300 max-w-2xl leading-relaxed">
                    Ikhtisar kesehatan finansial, posisi kas, perputaran piutang-hutang, dan performa laba rugi bisnis Anda.
                </p>
            </div>

            <!-- Right: Fast Action Buttons & Live Date -->
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('transactions.create') }}" class="inline-flex items-center space-x-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white text-xs sm:text-sm font-bold px-4 py-2.5 rounded-xl shadow-lg shadow-blue-500/25 transition-all duration-200 hover:scale-[1.02] active:scale-[0.98] border border-blue-400/30">
                    <i class="fa-solid fa-plus-circle text-sm"></i>
                    <span>+ Catat Transaksi</span>
                </a>
                <a href="{{ route('reports.journal') }}" class="inline-flex items-center space-x-2 bg-white/10 hover:bg-white/20 text-white text-xs sm:text-sm font-semibold px-4 py-2.5 rounded-xl backdrop-blur-md border border-white/15 transition duration-200">
                    <i class="fa-solid fa-book text-xs text-blue-300"></i>
                    <span>Jurnal Umum</span>
                </a>
                <a href="{{ route('closing.index') }}" class="inline-flex items-center space-x-2 bg-white/10 hover:bg-white/20 text-white text-xs sm:text-sm font-semibold px-4 py-2.5 rounded-xl backdrop-blur-md border border-white/15 transition duration-200">
                    <i class="fa-solid fa-calendar-check text-xs text-emerald-300"></i>
                    <span>Tutup Buku</span>
                </a>
            </div>
        </div>

        <!-- Filter Bar inside Banner -->
        <div class="mt-6 pt-5 border-t border-white/10 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center space-x-2 text-xs text-slate-300">
                <i class="fa-regular fa-calendar-days text-blue-400"></i>
                <span class="font-medium">Periode Laporan:</span>
                <span class="font-bold text-white bg-white/10 px-2.5 py-1 rounded-lg border border-white/10">
                    {{ \Carbon\Carbon::parse($startDate)->isoFormat('D MMMM Y') }} &mdash; {{ \Carbon\Carbon::parse($endDate)->isoFormat('D MMMM Y') }}
                </span>
            </div>

            <form method="GET" action="{{ route('dashboard') }}" class="flex items-center space-x-2">
                <div class="flex items-center bg-slate-800/80 border border-slate-700/80 rounded-xl px-3 py-1.5 shadow-inner text-xs text-slate-200 focus-within:border-blue-400 transition">
                    <input type="date" name="start_date" value="{{ $startDate }}" class="bg-transparent text-xs text-white focus:outline-none border-none p-0">
                    <span class="mx-2 text-slate-400">&rarr;</span>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="bg-transparent text-xs text-white focus:outline-none border-none p-0">
                </div>
                <button type="submit" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-semibold shadow-sm transition flex items-center space-x-1">
                    <i class="fa-solid fa-filter text-[10px]"></i>
                    <span>Filter</span>
                </button>
                <a href="{{ route('dashboard') }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-semibold border border-slate-700 transition" title="Reset Rentang Tanggal">
                    <i class="fa-solid fa-rotate-left text-[10px]"></i>
                </a>
            </form>
        </div>
    </div>

    <!-- 2. 6 GLOWING KPI METRIC & ANALYTIC CARDS GRID -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">

        <!-- CARD 1: KAS & BANK -->
        <div class="group bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5 flex flex-col justify-between relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500 via-indigo-500 to-blue-600"></div>
            <div>
                <div class="flex items-start justify-between mb-2">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center shadow-xs group-hover:scale-110 transition duration-200">
                            <i class="fa-solid fa-wallet text-base"></i>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Likuiditas</span>
                            <h3 class="font-bold text-slate-800 text-base">Kas & Bank</h3>
                        </div>
                    </div>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-100">
                        Liquid
                    </span>
                </div>

                <div class="mt-3">
                    <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight font-mono">
                        {{ $summary['kas_bank']['formatted'] }}
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1 flex items-center">
                        <i class="fa-solid fa-arrow-trend-up text-blue-600 mr-1.5"></i> Saldo kas & rekening operasional aktif
                    </p>
                </div>

                <!-- Line Chart -->
                <div class="mt-5 h-44 relative">
                    <canvas id="kasBankChart"></canvas>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold">
                <a href="{{ route('reports.general_ledger', ['account_id' => 1]) }}" class="text-blue-600 hover:text-blue-800 flex items-center group-hover:translate-x-1 transition duration-150">
                    <span>Lihat Buku Kas & Rekening</span>
                    <i class="fa-solid fa-arrow-right ml-1.5 text-[10px]"></i>
                </a>
            </div>
        </div>

        <!-- CARD 2: PIUTANG USAHA -->
        <div class="group bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5 flex flex-col justify-between relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-sky-400 via-cyan-500 to-blue-500"></div>
            <div>
                <div class="flex items-start justify-between mb-2">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-sky-50 border border-sky-100 text-sky-600 flex items-center justify-center shadow-xs group-hover:scale-110 transition duration-200">
                            <i class="fa-solid fa-file-invoice-dollar text-base"></i>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Aset Lancar</span>
                            <h3 class="font-bold text-slate-800 text-base">Piutang Usaha</h3>
                        </div>
                    </div>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-sky-50 text-sky-700 border border-sky-100">
                        Tagihan
                    </span>
                </div>

                <div class="mt-3">
                    <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight font-mono">
                        {{ $summary['piutang']['formatted'] }}
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1 flex items-center">
                        <i class="fa-solid fa-clock-rotate-left text-sky-600 mr-1.5"></i> Total tagihan aktif belum lunas
                    </p>
                </div>

                <!-- Line Chart -->
                <div class="mt-5 h-44 relative">
                    <canvas id="piutangChart"></canvas>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold">
                <a href="{{ route('reports.general_ledger', ['account_id' => 13]) }}" class="text-sky-600 hover:text-sky-800 flex items-center group-hover:translate-x-1 transition duration-150">
                    <span>Lihat Kartu Piutang</span>
                    <i class="fa-solid fa-arrow-right ml-1.5 text-[10px]"></i>
                </a>
            </div>
        </div>

        <!-- CARD 3: HUTANG USAHA -->
        <div class="group bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5 flex flex-col justify-between relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-rose-500 via-pink-500 to-rose-600"></div>
            <div>
                <div class="flex items-start justify-between mb-2">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-rose-50 border border-rose-100 text-rose-600 flex items-center justify-center shadow-xs group-hover:scale-110 transition duration-200">
                            <i class="fa-solid fa-receipt text-base"></i>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Kewajiban</span>
                            <h3 class="font-bold text-slate-800 text-base">Hutang Usaha</h3>
                        </div>
                    </div>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-rose-50 text-rose-700 border border-rose-100">
                        Kewajiban
                    </span>
                </div>

                <div class="mt-3">
                    <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight font-mono">
                        {{ $summary['hutang']['formatted'] }}
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1 flex items-center">
                        <i class="fa-solid fa-hand-holding-dollar text-rose-600 mr-1.5"></i> Kewajiban vendor & tagihan operasional
                    </p>
                </div>

                <!-- Line Chart -->
                <div class="mt-5 h-44 relative">
                    <canvas id="hutangChart"></canvas>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold">
                <a href="{{ route('reports.general_ledger', ['account_id' => 42]) }}" class="text-rose-600 hover:text-rose-800 flex items-center group-hover:translate-x-1 transition duration-150">
                    <span>Lihat Rincian Hutang</span>
                    <i class="fa-solid fa-arrow-right ml-1.5 text-[10px]"></i>
                </a>
            </div>
        </div>

        <!-- CARD 4: LABA RUGI BERSIH -->
        <div class="group bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5 flex flex-col justify-between relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 via-teal-500 to-green-600"></div>
            <div>
                <div class="flex items-start justify-between mb-2">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center shadow-xs group-hover:scale-110 transition duration-200">
                            <i class="fa-solid fa-chart-line text-base"></i>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Performa Finansial</span>
                            <h3 class="font-bold text-slate-800 text-base">Laba / (Rugi) Bersih</h3>
                        </div>
                    </div>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ ($summary['laba_rugi']['laba_bersih'] ?? 0) >= 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-rose-50 text-rose-700 border border-rose-100' }}">
                        {{ ($summary['laba_rugi']['laba_bersih'] ?? 0) >= 0 ? 'Surplus' : 'Defisit' }}
                    </span>
                </div>

                <div class="mt-3">
                    <div class="text-2xl sm:text-3xl font-extrabold {{ ($summary['laba_rugi']['laba_bersih'] ?? 0) >= 0 ? 'text-emerald-600' : 'text-rose-600' }} tracking-tight font-mono">
                        {{ $summary['laba_rugi']['formatted'] }}
                    </div>
                    <div class="flex items-center justify-between mt-2 text-[11px] text-slate-500">
                        <span class="flex items-center"><span class="w-2 h-2 rounded-full bg-blue-600 mr-1"></span> Pemasukan</span>
                        <span class="flex items-center"><span class="w-2 h-2 rounded-full bg-rose-500 mr-1"></span> Beban</span>
                        <span class="flex items-center"><span class="w-2 h-2 rounded-full bg-emerald-500 mr-1"></span> Laba</span>
                    </div>
                </div>

                <!-- Bar Chart -->
                <div class="mt-4 h-44 relative">
                    <canvas id="labaRugiChart"></canvas>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold">
                <a href="{{ route('reports.profit_loss') }}" class="text-emerald-600 hover:text-emerald-800 flex items-center group-hover:translate-x-1 transition duration-150">
                    <span>Laporan Laba Rugi Lengkap</span>
                    <i class="fa-solid fa-arrow-right ml-1.5 text-[10px]"></i>
                </a>
            </div>
        </div>

        <!-- CARD 5: BEBAN OPERASIONAL -->
        <div class="group bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5 flex flex-col justify-between relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-purple-500 via-indigo-500 to-purple-600"></div>
            <div>
                <div class="flex items-start justify-between mb-2">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-purple-50 border border-purple-100 text-purple-600 flex items-center justify-center shadow-xs group-hover:scale-110 transition duration-200">
                            <i class="fa-solid fa-chart-pie text-base"></i>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Distribusi Biaya</span>
                            <h3 class="font-bold text-slate-800 text-base">Beban Operasional</h3>
                        </div>
                    </div>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-purple-50 text-purple-700 border border-purple-100">
                        OPEX
                    </span>
                </div>

                <div class="mt-3">
                    <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight font-mono">
                        {{ $summary['beban_operasional']['formatted'] }}
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1 flex items-center">
                        <i class="fa-solid fa-sliders text-purple-600 mr-1.5"></i> Beban gaji, sewa, utilitas & operasional
                    </p>
                </div>

                <!-- Doughnut Chart -->
                <div class="mt-4 h-44 flex items-center justify-center relative">
                    @if($summary['beban_operasional']['total'] == 0)
                        <div class="text-center text-slate-400 text-xs py-10">
                            <i class="fa-solid fa-circle-info text-slate-300 text-xl mb-1 block"></i>
                            Belum ada beban tercatat periode ini
                        </div>
                    @else
                        <canvas id="bebanChart"></canvas>
                    @endif
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold">
                <a href="{{ route('reports.operating_expenses') }}" class="text-purple-600 hover:text-purple-800 flex items-center group-hover:translate-x-1 transition duration-150">
                    <span>Rincian Beban Operasional</span>
                    <i class="fa-solid fa-arrow-right ml-1.5 text-[10px]"></i>
                </a>
            </div>
        </div>

        <!-- CARD 6: ARUS KAS (CASH FLOW) -->
        <div class="group bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5 flex flex-col justify-between relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600"></div>
            <div>
                <div class="flex items-start justify-between mb-2">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 border border-amber-100 text-amber-600 flex items-center justify-center shadow-xs group-hover:scale-110 transition duration-200">
                            <i class="fa-solid fa-money-bill-transfer text-base"></i>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Pergerakan Kas</span>
                            <h3 class="font-bold text-slate-800 text-base">Arus Kas Bersih</h3>
                        </div>
                    </div>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-100">
                        Cash Flow
                    </span>
                </div>

                <div class="mt-3">
                    <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight font-mono">
                        {{ $summary['arus_kas']['formatted'] }}
                    </div>
                    <div class="flex items-center justify-between mt-2 text-[11px] text-slate-500">
                        <span class="flex items-center"><span class="w-2 h-2 rounded-full bg-blue-600 mr-1"></span> Kas Masuk</span>
                        <span class="flex items-center"><span class="w-2 h-2 rounded-full bg-rose-500 mr-1"></span> Kas Keluar</span>
                        <span class="flex items-center"><span class="w-2 h-2 rounded-full bg-amber-500 mr-1"></span> Kas Bersih</span>
                    </div>
                </div>

                <!-- Multi-Bar Chart -->
                <div class="mt-4 h-44 relative">
                    <canvas id="arusKasChart"></canvas>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold">
                <a href="{{ route('reports.cash_flow') }}" class="text-amber-600 hover:text-amber-800 flex items-center group-hover:translate-x-1 transition duration-150">
                    <span>Laporan Arus Kas</span>
                    <i class="fa-solid fa-arrow-right ml-1.5 text-[10px]"></i>
                </a>
            </div>
        </div>

    </div>

    <!-- 3. FINANCIAL HEALTH AUDIT & SPEED DIAL MODULES -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left: Quick Health Check Matrix (2 cols) -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm space-y-5">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-9 h-9 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center">
                        <i class="fa-solid fa-shield-halved text-sm"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-900 text-base">Integritas Pembukuan & Neraca Saldo</h4>
                        <p class="text-xs text-slate-500">Verifikasi otomatis keseimbangan debit & kredit transaksi</p>
                    </div>
                </div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <i class="fa-solid fa-circle-check text-emerald-500 mr-1.5"></i> Balanced & Verified
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 flex flex-col justify-between">
                    <span class="text-xs text-slate-500 font-medium">Buku Besar Terintegrasi</span>
                    <span class="text-base font-bold text-slate-800 mt-2 font-mono">100% Real-Time</span>
                    <a href="{{ route('reports.general_ledger') }}" class="text-[11px] font-semibold text-blue-600 hover:underline mt-2 flex items-center">
                        Buka Ledger &rarr;
                    </a>
                </div>
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 flex flex-col justify-between">
                    <span class="text-xs text-slate-500 font-medium">Kepatuhan Pajak (PPN/PPh)</span>
                    <span class="text-base font-bold text-slate-800 mt-2 font-mono">Standar DJP</span>
                    <a href="{{ route('master.taxes') }}" class="text-[11px] font-semibold text-blue-600 hover:underline mt-2 flex items-center">
                        Master Pajak &rarr;
                    </a>
                </div>
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 flex flex-col justify-between">
                    <span class="text-xs text-slate-500 font-medium">Jurnal Penutup Periode</span>
                    <span class="text-base font-bold text-slate-800 mt-2 font-mono">Otomatisasi 1-Klik</span>
                    <a href="{{ route('closing.index') }}" class="text-[11px] font-semibold text-blue-600 hover:underline mt-2 flex items-center">
                        Tutup Buku &rarr;
                    </a>
                </div>
            </div>
        </div>

        <!-- Right: Executive Quick Shortcuts -->
        <div class="bg-gradient-to-br from-slate-900 to-indigo-950 text-white rounded-2xl p-6 shadow-sm border border-slate-800 flex flex-col justify-between">
            <div>
                <div class="flex items-center space-x-2 text-amber-400 text-xs font-bold uppercase tracking-wider mb-2">
                    <i class="fa-solid fa-bolt"></i>
                    <span>Pintasan Cepat</span>
                </div>
                <h4 class="text-lg font-extrabold text-white">Akses Fitur Populer</h4>
                <p class="text-xs text-slate-400 mt-1">Navigasi instan ke fitur akuntansi utama:</p>

                <div class="mt-4 space-y-2">
                    <a href="{{ route('transactions.create') }}" class="flex items-center justify-between p-2.5 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 text-xs font-semibold transition group">
                        <span class="flex items-center"><i class="fa-solid fa-file-circle-plus text-blue-400 mr-2.5"></i> Catat Transaksi Baru</span>
                        <i class="fa-solid fa-chevron-right text-[10px] text-slate-400 group-hover:translate-x-1 transition"></i>
                    </a>
                    <a href="{{ route('reports.trial_balance') }}" class="flex items-center justify-between p-2.5 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 text-xs font-semibold transition group">
                        <span class="flex items-center"><i class="fa-solid fa-scale-balanced text-indigo-400 mr-2.5"></i> Neraca Saldo (Trial Balance)</span>
                        <i class="fa-solid fa-chevron-right text-[10px] text-slate-400 group-hover:translate-x-1 transition"></i>
                    </a>
                    <a href="{{ route('assets.index') }}" class="flex items-center justify-between p-2.5 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 text-xs font-semibold transition group">
                        <span class="flex items-center"><i class="fa-solid fa-landmark text-emerald-400 mr-2.5"></i> Master Aset & Penyusutan</span>
                        <i class="fa-solid fa-chevron-right text-[10px] text-slate-400 group-hover:translate-x-1 transition"></i>
                    </a>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-white/10 text-[11px] text-slate-400 flex items-center justify-between">
                <span>Multi-Perusahaan: Aktif</span>
                <a href="{{ route('company.switch') }}" class="text-blue-400 hover:text-blue-300 font-semibold">Ganti &rarr;</a>
            </div>
        </div>

    </div>

</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const chartLabels = {!! json_encode($summary['charts']['labels']) !!};

        // Custom Tooltip Formatter
        const formatRupiahTooltip = {
            backgroundColor: 'rgba(15, 23, 42, 0.95)',
            titleFont: { size: 12, weight: 'bold', family: "'Plus Jakarta Sans', sans-serif" },
            bodyFont: { size: 12, family: "'JetBrains Mono', monospace" },
            padding: 10,
            cornerRadius: 10,
            callbacks: {
                label: function(context) {
                    let label = context.dataset.label || '';
                    if (label) {
                        label += ': ';
                    }
                    if (context.parsed.y !== null) {
                        label += 'Rp ' + Number(context.parsed.y).toLocaleString('id-ID');
                    }
                    return label;
                }
            }
        };

        const defaultScales = {
            x: { 
                grid: { display: false }, 
                ticks: { font: { size: 10, family: "'Plus Jakarta Sans', sans-serif" }, color: '#64748b' } 
            },
            y: { 
                grid: { color: '#f1f5f9' }, 
                ticks: { 
                    font: { size: 10, family: "'JetBrains Mono', monospace" },
                    color: '#64748b',
                    callback: function(val) { 
                        if (Math.abs(val) >= 1000000) {
                            return 'Rp ' + (val / 1000000).toLocaleString('id-ID') + 'M';
                        }
                        return 'Rp ' + Number(val).toLocaleString('id-ID'); 
                    }
                } 
            }
        };

        // Chart 1: Kas & Bank Line Area (Glowing Indigo Gradient)
        const elKas = document.getElementById('kasBankChart');
        if (elKas) {
            const ctxKas = elKas.getContext('2d');
            const gradientKas = ctxKas.createLinearGradient(0, 0, 0, 180);
            gradientKas.addColorStop(0, 'rgba(59, 130, 246, 0.35)');
            gradientKas.addColorStop(1, 'rgba(59, 130, 246, 0.00)');

            new Chart(ctxKas, {
                type: 'line',
                data: {
                    labels: chartLabels,
                    datasets: [{
                        label: 'Kas & Bank',
                        data: {!! json_encode($summary['charts']['kas_bank_series']) !!},
                        borderColor: '#2563eb',
                        borderWidth: 2.5,
                        backgroundColor: gradientKas,
                        fill: true,
                        tension: 0.35,
                        pointBackgroundColor: '#2563eb',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { display: false },
                        tooltip: formatRupiahTooltip
                    },
                    scales: defaultScales
                }
            });
        }

        // Chart 2: Piutang Line Area (Cyan Gradient)
        const elPiutang = document.getElementById('piutangChart');
        if (elPiutang) {
            const ctxPiutang = elPiutang.getContext('2d');
            const gradientPiutang = ctxPiutang.createLinearGradient(0, 0, 0, 180);
            gradientPiutang.addColorStop(0, 'rgba(14, 165, 233, 0.35)');
            gradientPiutang.addColorStop(1, 'rgba(14, 165, 233, 0.00)');

            new Chart(ctxPiutang, {
                type: 'line',
                data: {
                    labels: chartLabels,
                    datasets: [{
                        label: 'Piutang Usaha',
                        data: {!! json_encode($summary['charts']['piutang_series']) !!},
                        borderColor: '#0284c7',
                        borderWidth: 2.5,
                        backgroundColor: gradientPiutang,
                        fill: true,
                        tension: 0.35,
                        pointBackgroundColor: '#0284c7',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { display: false },
                        tooltip: formatRupiahTooltip
                    },
                    scales: defaultScales
                }
            });
        }

        // Chart 3: Hutang Line Area (Rose Gradient)
        const elHutang = document.getElementById('hutangChart');
        if (elHutang) {
            const ctxHutang = elHutang.getContext('2d');
            const gradientHutang = ctxHutang.createLinearGradient(0, 0, 0, 180);
            gradientHutang.addColorStop(0, 'rgba(244, 63, 94, 0.35)');
            gradientHutang.addColorStop(1, 'rgba(244, 63, 94, 0.00)');

            new Chart(ctxHutang, {
                type: 'line',
                data: {
                    labels: chartLabels,
                    datasets: [{
                        label: 'Hutang Usaha',
                        data: {!! json_encode($summary['charts']['hutang_series']) !!},
                        borderColor: '#e11d48',
                        borderWidth: 2.5,
                        backgroundColor: gradientHutang,
                        fill: true,
                        tension: 0.35,
                        pointBackgroundColor: '#e11d48',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { display: false },
                        tooltip: formatRupiahTooltip
                    },
                    scales: defaultScales
                }
            });
        }

        // Chart 4: Laba Rugi Rounded Bar Chart
        const elPL = document.getElementById('labaRugiChart');
        if (elPL) {
            const ctxPL = elPL.getContext('2d');
            new Chart(ctxPL, {
                type: 'bar',
                data: {
                    labels: chartLabels,
                    datasets: [
                        { 
                            label: 'Pemasukan', 
                            data: {!! json_encode($summary['charts']['pemasukan_series']) !!}, 
                            backgroundColor: '#2563eb',
                            borderRadius: 6
                        },
                        { 
                            label: 'Biaya', 
                            data: {!! json_encode($summary['charts']['biaya_series']) !!}, 
                            backgroundColor: '#f43f5e',
                            borderRadius: 6
                        },
                        { 
                            label: 'Laba Bersih', 
                            data: {!! json_encode($summary['charts']['laba_bersih_series']) !!}, 
                            backgroundColor: '#10b981',
                            borderRadius: 6
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { display: false },
                        tooltip: formatRupiahTooltip
                    },
                    scales: defaultScales
                }
            });
        }

        // Chart 5: Beban Operasional Modern Doughnut Chart
        const elBeban = document.getElementById('bebanChart');
        if (elBeban) {
            const ctxBeban = elBeban.getContext('2d');
            new Chart(ctxBeban, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($summary['charts']['beban_labels']) !!},
                    datasets: [{
                        data: {!! json_encode($summary['charts']['beban_data']) !!},
                        backgroundColor: ['#6366f1', '#f43f5e', '#0ea5e9', '#10b981', '#f59e0b', '#ec4899', '#64748b'],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: { 
                            position: 'bottom', 
                            labels: { 
                                boxWidth: 8, 
                                boxHeight: 8, 
                                usePointStyle: true,
                                font: { size: 10, family: "'Plus Jakarta Sans', sans-serif" } 
                            } 
                        },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.95)',
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    return context.label + ': Rp ' + Number(context.raw).toLocaleString('id-ID');
                                }
                            }
                        }
                    }
                }
            });
        }

        // Chart 6: Arus Kas Rounded Bar Chart
        const elCF = document.getElementById('arusKasChart');
        if (elCF) {
            const ctxCF = elCF.getContext('2d');
            new Chart(ctxCF, {
                type: 'bar',
                data: {
                    labels: chartLabels,
                    datasets: [
                        { 
                            label: 'Kas Masuk', 
                            data: {!! json_encode($summary['charts']['kas_masuk_series']) !!}, 
                            backgroundColor: '#2563eb',
                            borderRadius: 6
                        },
                        { 
                            label: 'Kas Keluar', 
                            data: {!! json_encode($summary['charts']['kas_keluar_series']) !!}, 
                            backgroundColor: '#f43f5e',
                            borderRadius: 6
                        },
                        { 
                            label: 'Kas Bersih', 
                            data: {!! json_encode($summary['charts']['kas_bersih_series']) !!}, 
                            backgroundColor: '#f59e0b',
                            borderRadius: 6
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { display: false },
                        tooltip: formatRupiahTooltip
                    },
                    scales: defaultScales
                }
            });
        }
    });
</script>
@endif
@endsection
