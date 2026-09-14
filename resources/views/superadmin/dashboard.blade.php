@extends('layouts.app')

@section('title', 'Super Admin Master SaaS Portal')

@section('content')
<div class="space-y-8 animate-in fade-in duration-300">

    <!-- HERO SUPER ADMIN BANNER -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-950 via-purple-950 to-slate-900 text-white p-6 sm:p-8 shadow-xl border border-purple-800/40">
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-purple-500/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="space-y-2">
                <div class="flex items-center space-x-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-purple-500/20 text-purple-300 border border-purple-400/30 backdrop-blur-md">
                        <i class="fa-solid fa-crown text-amber-300 mr-1.5 text-[11px]"></i>
                        Master SaaS Control Center
                    </span>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1.5 animate-ping"></span> Platform Active
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
                    Super Admin Portal & Tenant Billing
                </h1>
                <p class="text-sm text-purple-100/80 max-w-2xl leading-relaxed">
                    Pusat kendali master seluruh perusahaan/tenant terdaftar, pemantauan pembayaran langganan, masa aktif lisensi, dan pendapatan SaaS Anda.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('superadmin.tenants') }}" class="inline-flex items-center space-x-2 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white text-xs sm:text-sm font-bold px-4 py-2.5 rounded-xl shadow-lg shadow-purple-500/25 transition-all duration-200">
                    <i class="fa-solid fa-building-user text-sm"></i>
                    <span>Kelola Semua Tenant</span>
                </a>
                <a href="{{ route('superadmin.invoices') }}" class="inline-flex items-center space-x-2 bg-white/10 hover:bg-white/20 text-white text-xs sm:text-sm font-semibold px-4 py-2.5 rounded-xl backdrop-blur-md border border-white/15 transition duration-200">
                    <i class="fa-solid fa-receipt text-xs text-purple-300"></i>
                    <span>Semua Invoice Tagihan</span>
                </a>
            </div>
        </div>
    </div>

    <!-- 4 SAAS METRIC CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <!-- CARD 1: TOTAL TENANTS -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase">Total Tenant</span>
                <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-xs">
                    <i class="fa-solid fa-buildings"></i>
                </div>
            </div>
            <div class="mt-2 text-2xl font-black text-slate-900">{{ $totalCompanies }} Perusahaan</div>
            <div class="mt-1 text-[11px] text-slate-500">
                <span class="text-emerald-600 font-bold">{{ $activeCompanies }} Aktif</span> &bull; 
                <span class="text-amber-600 font-bold">{{ $trialCompanies }} Trial</span>
            </div>
        </div>

        <!-- CARD 2: PENDAPATAN BULAN INI -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase">Revenue Bulan Ini (MRR)</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xs">
                    <i class="fa-solid fa-money-bill-trend-up"></i>
                </div>
            </div>
            <div class="mt-2 text-2xl font-black text-emerald-600">
                Rp {{ number_format($thisMonthRevenue, 0, ',', '.') }}
            </div>
            <div class="mt-1 text-[11px] text-slate-500">Dari pembayaran perpanjangan langganan</div>
        </div>

        <!-- CARD 3: TOTAL AKUMULASI REVENUE -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase">Total Pendapatan SaaS</span>
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs">
                    <i class="fa-solid fa-wallet"></i>
                </div>
            </div>
            <div class="mt-2 text-2xl font-black text-blue-600">
                Rp {{ number_format($totalRevenue, 0, ',', '.') }}
            </div>
            <div class="mt-1 text-[11px] text-slate-500">Seluruh riwayat pembayaran lunas</div>
        </div>

        <!-- CARD 4: TAGIHAN PENDING -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase">Tagihan Menunggu</span>
                <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-xs">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
            </div>
            <div class="mt-2 text-2xl font-black text-slate-900">{{ $pendingInvoicesCount }} Invoice</div>
            <div class="mt-1 text-[11px] text-slate-500">Menunggu verifikasi / pembayaran</div>
        </div>

    </div>

    <!-- RECENT TENANTS & RECENT INVOICES GRID -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Left: Recent Tenants -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col justify-between">
            <div>
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm">Tenant Perusahaan Terkini</h3>
                        <p class="text-[11px] text-slate-400">Daftar klien yang terdaftar di platform</p>
                    </div>
                    <a href="{{ route('superadmin.tenants') }}" class="text-xs font-bold text-purple-600 hover:text-purple-800">
                        Lihat Semua &rarr;
                    </a>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse($recentCompanies as $comp)
                        <div class="p-4 flex items-center justify-between hover:bg-slate-50 transition">
                            <div class="flex items-center space-x-3">
                                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-purple-600 to-indigo-600 text-white flex items-center justify-center font-bold text-xs shadow-xs">
                                    {{ strtoupper(substr($comp->name, 0, 1)) }}
                                </div>
                                <div>
                                    <h4 class="font-bold text-xs text-slate-900">{{ $comp->name }}</h4>
                                    <p class="text-[10px] text-slate-400">{{ $comp->city ?: 'Indonesia' }} &bull; Paket: <span class="uppercase font-semibold text-purple-700">{{ $comp->subscription_plan ?: 'premium' }}</span></p>
                                </div>
                            </div>

                            <div class="text-right">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $comp->subscription_status === 'trial' ? 'bg-amber-50 text-amber-700 border border-amber-200' : ($comp->subscription_status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200') }}">
                                    {{ ucfirst($comp->subscription_status ?? 'Trial') }}
                                </span>
                                <span class="text-[10px] text-slate-400 block mt-0.5">{{ $comp->remaining_days }} hari tersisa</span>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-slate-400 text-xs">Belum ada tenant terdaftar.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right: Recent Invoices -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col justify-between">
            <div>
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm">Invoice Tagihan Terbaru</h3>
                        <p class="text-[11px] text-slate-400">Riwayat transaksi langganan dari semua tenant</p>
                    </div>
                    <a href="{{ route('superadmin.invoices') }}" class="text-xs font-bold text-purple-600 hover:text-purple-800">
                        Lihat Semua &rarr;
                    </a>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse($recentInvoices as $inv)
                        <div class="p-4 flex items-center justify-between hover:bg-slate-50 transition text-xs">
                            <div>
                                <span class="font-bold text-slate-900 block">{{ $inv->invoice_number }}</span>
                                <span class="text-[10px] text-slate-400">{{ $inv->company?->name }} &bull; {{ $inv->created_at->format('d/m/Y H:i') }}</span>
                            </div>

                            <div class="text-right">
                                <div class="font-bold font-mono text-slate-900">Rp {{ number_format($inv->amount, 0, ',', '.') }}</div>
                                @if($inv->status === 'paid')
                                    <span class="text-[10px] text-emerald-600 font-bold flex items-center justify-end"><i class="fa-solid fa-circle-check mr-1"></i> Lunas</span>
                                @else
                                    <span class="text-[10px] text-amber-600 font-bold flex items-center justify-end"><i class="fa-solid fa-clock mr-1"></i> Pending</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-slate-400 text-xs">Belum ada invoice tagihan.</div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
