@extends('layouts.app')

@section('title', 'Kelola Paket Layanan SaaS - Super Admin')

@section('content')
<div class="max-w-6xl mx-auto space-y-6 animate-in fade-in duration-300">

    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center justify-between shadow-xs">
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    @endif

    <!-- HEADER BANNER -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-indigo-950 to-purple-950 text-white p-8 sm:p-10 shadow-xl border border-white/10">
        <div class="absolute -right-10 -bottom-10 w-80 h-80 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-indigo-500/20 text-indigo-300 border border-indigo-400/30 text-xs font-bold uppercase tracking-wider mb-3">
                    <i class="fa-solid fa-tags"></i>
                    <span>Master Pricing & Plans</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Kelola Harga & Fitur Paket SaaS</h1>
                <p class="text-xs sm:text-sm text-slate-300 mt-1 max-w-2xl leading-relaxed">
                    Atur harga bulanan, diskon periode (6 bulan & 1 tahun), dan deskripsi fitur paket langganan. Perubahan harga di sini akan langsung berlaku di halaman tagihan dan perpanjangan seluruh tenant.
                </p>
            </div>

            <div class="flex items-center space-x-2 shrink-0">
                <a href="{{ route('superadmin.dashboard') }}" class="px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white rounded-xl text-xs font-semibold backdrop-blur-md border border-white/15 transition flex items-center gap-2">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('superadmin.payment_settings') }}" class="px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-bold shadow-md shadow-purple-600/30 transition flex items-center gap-2">
                    <i class="fa-solid fa-credit-card"></i>
                    <span>Payment Gateway</span>
                </a>
            </div>
        </div>
    </div>

    <!-- FORM KELOLA PAKET -->
    <form action="{{ route('superadmin.plans.update') }}" method="POST" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- CARD 1: PAKET STANDARD -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden flex flex-col justify-between">
                <div>
                    <!-- Header Card -->
                    <div class="p-6 border-b border-slate-100 bg-gradient-to-r from-blue-50/70 to-slate-50 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center text-xl shadow-md shadow-blue-600/20">
                                <i class="fa-solid fa-store"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-900 text-base">Paket Standard</h3>
                                <p class="text-xs text-slate-500">UMKM, Toko Mandiri, 1 - 2 Cabang</p>
                            </div>
                        </div>
                        <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-bold">Standard</span>
                    </div>

                    <div class="p-6 space-y-4 text-xs">
                        <!-- Harga Bulanan -->
                        <div>
                            <label class="block font-bold text-slate-800 mb-1">Harga Bulanan (Rp / Bulan)</label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-2.5 text-slate-400 font-bold">Rp</span>
                                <input type="number" name="standard_price" value="{{ old('standard_price', $standard->price_monthly ?? 99000) }}" class="w-full pl-11 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-900 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <p class="text-[11px] text-slate-400 mt-1">Harga sebelum diskon durasi</p>
                        </div>

                        <!-- Diskon Durasi -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-slate-800 mb-1">Diskon 6 Bulan (%)</label>
                                <input type="number" name="standard_discount_6" value="{{ old('standard_discount_6', $standard->discount_6_months ?? 10) }}" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-800 mb-1">Diskon 1 Tahun (%)</label>
                                <input type="number" name="standard_discount_12" value="{{ old('standard_discount_12', $standard->discount_12_months ?? 20) }}" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>

                        <!-- Deskripsi Singkat -->
                        <!-- Deskripsi Singkat -->
                        <div>
                            <label class="block font-bold text-slate-800 mb-1">Ringkasan Fitur / Subtitle</label>
                            <input type="text" name="standard_description" value="{{ old('standard_description', $standard->description ?? 'Maks 1 Perusahaan, Manual Entry, Laporan Standar') }}" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- Info Cakupan Fitur Standar -->
                        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100 space-y-1.5 text-[11px] text-slate-600">
                            <span class="font-bold text-slate-800 block text-xs">Cakupan Fitur Bawaan:</span>
                            <div class="flex items-center gap-2"><i class="fa-solid fa-check text-blue-600"></i> Single Entity (1 Perusahaan Mandiri)</div>
                            <div class="flex items-center gap-2"><i class="fa-solid fa-check text-blue-600"></i> Maksimal 2 Pengguna (Owner & Akuntan)</div>
                            <div class="flex items-center gap-2"><i class="fa-solid fa-check text-blue-600"></i> Pencatatan Kas Masuk & Kas Keluar Manual</div>
                            <div class="flex items-center gap-2"><i class="fa-solid fa-check text-blue-600"></i> Master 120 Bagan Akun (COA) Standar</div>
                            <div class="flex items-center gap-2"><i class="fa-solid fa-check text-blue-600"></i> Laporan Laba Rugi, Neraca & Arus Kas</div>
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-slate-50 border-t border-slate-100 text-[11px] text-slate-400">
                    Target pasar: Usaha kecil yang membutuhkan pembukuan simpel tanpa otomasi AI.
                </div>
            </div>

            <!-- CARD 2: PAKET PRO ENTERPRISE -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden flex flex-col justify-between relative">
                <div class="absolute top-0 right-0 bg-gradient-to-l from-purple-600 to-indigo-600 text-white text-[10px] font-extrabold px-4 py-1 rounded-bl-2xl shadow-xs">
                    FLAGSHIP
                </div>

                <div>
                    <!-- Header Card -->
                    <div class="p-6 border-b border-slate-100 bg-gradient-to-r from-purple-50/70 to-indigo-50/50 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-purple-600 to-indigo-600 text-white flex items-center justify-center text-xl shadow-md shadow-purple-600/25">
                                <i class="fa-solid fa-crown text-amber-300"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-900 text-base">Paket Pro Enterprise</h3>
                                <p class="text-xs text-slate-500">Multi-Cabang, Korporat & AI Otomatis</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 space-y-4 text-xs">
                        <!-- Harga Bulanan -->
                        <div>
                            <label class="block font-bold text-slate-800 mb-1">Harga Bulanan (Rp / Bulan)</label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-2.5 text-slate-400 font-bold">Rp</span>
                                <input type="number" name="premium_price" value="{{ old('premium_price', $premium->price_monthly ?? 249000) }}" class="w-full pl-11 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-900 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                            </div>
                            <p class="text-[11px] text-slate-400 mt-1">Harga sebelum diskon durasi</p>
                        </div>

                        <!-- Diskon Durasi -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-slate-800 mb-1">Diskon 6 Bulan (%)</label>
                                <input type="number" name="premium_discount_6" value="{{ old('premium_discount_6', $premium->discount_6_months ?? 10) }}" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-800 mb-1">Diskon 1 Tahun (%)</label>
                                <input type="number" name="premium_discount_12" value="{{ old('premium_discount_12', $premium->discount_12_months ?? 20) }}" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                            </div>
                        </div>

                        <!-- Deskripsi Singkat -->
                        <div>
                            <label class="block font-bold text-slate-800 mb-1">Ringkasan Fitur / Subtitle</label>
                            <input type="text" name="premium_description" value="{{ old('premium_description', $premium->description ?? 'Multi-Cabang, AI Gemini, Aset Depresiasi, Tutup Buku & REST API') }}" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>

                        <!-- Info Cakupan Fitur Pro -->
                        <div class="p-3.5 bg-purple-50/60 rounded-xl border border-purple-100 space-y-1.5 text-[11px] text-slate-700">
                            <span class="font-bold text-purple-900 block text-xs">Cakupan Fitur Unggulan Pro:</span>
                            <div class="flex items-center gap-2"><i class="fa-solid fa-crown text-amber-500"></i> <strong>Unlimited Multi-Perusahaan & Cabang</strong></div>
                            <div class="flex items-center gap-2"><i class="fa-solid fa-users text-purple-600"></i> <strong>Unlimited Pengguna</strong> (Owner, Akuntan, Auditor, Staff)</div>
                            <div class="flex items-center gap-2"><i class="fa-solid fa-robot text-purple-600"></i> <strong>AI Smart Journaling</strong> (Google Gemini API Otomatis)</div>
                            <div class="flex items-center gap-2"><i class="fa-solid fa-chart-line text-purple-600"></i> <strong>Modul Aset Tetap & Depresiasi Bulanan</strong></div>
                            <div class="flex items-center gap-2"><i class="fa-solid fa-lock text-purple-600"></i> <strong>Modul Tutup Buku & Kunci Periode Akuntansi</strong></div>
                            <div class="flex items-center gap-2"><i class="fa-solid fa-code text-purple-600"></i> <strong>Full Integrasi REST API & Webhook</strong></div>
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-slate-50 border-t border-slate-100 text-[11px] text-slate-400">
                    Target pasar: Bisnis multi-outlet, distributor, restoran, dan korporat berkembang.
                </div>
            </div>

        </div>

        <!-- SAVE BUTTON -->
        <div class="flex justify-end pt-2">
            <button type="submit" class="px-8 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white text-xs sm:text-sm font-bold rounded-2xl shadow-lg shadow-purple-600/30 transition transform hover:scale-[1.01] active:scale-[0.99] flex items-center space-x-2">
                <i class="fa-solid fa-floppy-disk"></i>
                <span>Simpan Perubahan Harga & Fitur Paket</span>
            </button>
        </div>

    </form>
</div>
@endsection
