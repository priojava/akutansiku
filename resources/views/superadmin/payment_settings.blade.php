@extends('layouts.app')

@section('title', 'Pengaturan Payment Gateway - Super Admin')

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
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-purple-950 to-indigo-950 text-white p-8 sm:p-10 shadow-xl border border-white/10">
        <div class="absolute -right-10 -bottom-10 w-80 h-80 bg-purple-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-purple-500/20 text-purple-300 border border-purple-400/30 text-xs font-bold uppercase tracking-wider mb-3">
                    <i class="fa-solid fa-shield-halved"></i>
                    <span>Super Admin Master Control</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Pengaturan Payment Gateway</h1>
                <p class="text-xs sm:text-sm text-slate-300 mt-1 max-w-2xl leading-relaxed">
                    Konfigurasi integrasi gateway pembayaran otomatis <strong>Midtrans</strong> dan <strong>Xendit</strong>. Opsi yang aktif akan otomatis muncul pada saat perusahaan (tenant) melakukan perpanjangan langganan paket SaaS.
                </p>
            </div>

            <div class="flex items-center space-x-2 shrink-0">
                <a href="{{ route('superadmin.dashboard') }}" class="px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white rounded-xl text-xs font-semibold backdrop-blur-md border border-white/15 transition flex items-center gap-2">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('superadmin.invoices') }}" class="px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-bold shadow-md shadow-purple-600/30 transition flex items-center gap-2">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                    <span>Lihat Invoices</span>
                </a>
            </div>
        </div>
    </div>

    <!-- MAIN FORM -->
    <form action="{{ route('superadmin.payment_settings.update') }}" method="POST" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- CARD 1: MIDTRANS INTEGRATION -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col justify-between" x-data="{ enabled: {{ $settings->midtrans_enabled ? 'true' : 'false' }}, isProd: '{{ $settings->midtrans_is_production ? 'production' : 'sandbox' }}' }">
                <div>
                    <!-- Card Header -->
                    <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-black text-sm shadow-md shadow-blue-600/20">
                                M
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 text-sm">Midtrans Payment Gateway</h3>
                                <p class="text-[11px] text-slate-500">Virtual Account (BCA, Mandiri, BNI, BRI), QRIS, CC</p>
                            </div>
                        </div>

                        <!-- Toggle Enable -->
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="midtrans_enabled" value="1" x-model="enabled" class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>

                    <!-- Card Body -->
                    <div class="p-6 space-y-4 text-xs" :class="{ 'opacity-50 pointer-events-none': !enabled }">
                        <!-- Mode Environment -->
                        <div>
                            <label class="block font-bold text-slate-700 mb-1.5">Environment Mode</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="p-3 rounded-xl border text-center cursor-pointer transition flex items-center justify-center space-x-2" :class="isProd === 'sandbox' ? 'border-blue-600 bg-blue-50/60 text-blue-700 font-bold' : 'border-slate-200 hover:bg-slate-50 text-slate-600'">
                                    <input type="radio" name="midtrans_environment" value="sandbox" x-model="isProd" class="sr-only">
                                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                    <span>Sandbox (Testing)</span>
                                </label>
                                <label class="p-3 rounded-xl border text-center cursor-pointer transition flex items-center justify-center space-x-2" :class="isProd === 'production' ? 'border-emerald-600 bg-emerald-50/60 text-emerald-700 font-bold' : 'border-slate-200 hover:bg-slate-50 text-slate-600'">
                                    <input type="radio" name="midtrans_environment" value="production" x-model="isProd" class="sr-only">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    <span>Production (Live)</span>
                                </label>
                            </div>
                        </div>

                        <!-- Merchant ID -->
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Merchant ID</label>
                            <input type="text" name="midtrans_merchant_id" value="{{ old('midtrans_merchant_id', $settings->midtrans_merchant_id) }}" placeholder="Contoh: G123456789" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl font-mono text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- Client Key -->
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Client Key</label>
                            <input type="text" name="midtrans_client_key" value="{{ old('midtrans_client_key', $settings->midtrans_client_key) }}" placeholder="Contoh: SB-Mid-client-..." class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl font-mono text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- Server Key -->
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Server Key</label>
                            <input type="password" name="midtrans_server_key" value="{{ old('midtrans_server_key', $settings->midtrans_server_key) }}" placeholder="Contoh: SB-Mid-server-..." class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl font-mono text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                <div class="px-6 py-3 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                    <span>Status: <strong :class="enabled ? 'text-blue-600' : 'text-slate-400'" x-text="enabled ? 'Aktif di Checkout Tenant' : 'Non-Aktif'"></strong></span>
                    <a href="https://dashboard.midtrans.com" target="_blank" class="text-blue-600 hover:underline">Dashboard Midtrans &rarr;</a>
                </div>
            </div>

            <!-- CARD 2: XENDIT INTEGRATION -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col justify-between" x-data="{ enabled: {{ $settings->xendit_enabled ? 'true' : 'false' }}, isProd: '{{ $settings->xendit_is_production ? 'production' : 'sandbox' }}' }">
                <div>
                    <!-- Card Header -->
                    <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-slate-900 text-white flex items-center justify-center font-black text-sm shadow-md shadow-slate-900/20">
                                X
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 text-sm">Xendit Payment Gateway</h3>
                                <p class="text-[11px] text-slate-500">Xendit Invoice (VA Multi-Bank, QRIS, e-Wallet OVO/Dana)</p>
                            </div>
                        </div>

                        <!-- Toggle Enable -->
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="xendit_enabled" value="1" x-model="enabled" class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-slate-900"></div>
                        </label>
                    </div>

                    <!-- Card Body -->
                    <div class="p-6 space-y-4 text-xs" :class="{ 'opacity-50 pointer-events-none': !enabled }">
                        <!-- Mode Environment -->
                        <div>
                            <label class="block font-bold text-slate-700 mb-1.5">Environment Mode</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="p-3 rounded-xl border text-center cursor-pointer transition flex items-center justify-center space-x-2" :class="isProd === 'sandbox' ? 'border-slate-900 bg-slate-100 text-slate-900 font-bold' : 'border-slate-200 hover:bg-slate-50 text-slate-600'">
                                    <input type="radio" name="xendit_environment" value="sandbox" x-model="isProd" class="sr-only">
                                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                    <span>Development / Test</span>
                                </label>
                                <label class="p-3 rounded-xl border text-center cursor-pointer transition flex items-center justify-center space-x-2" :class="isProd === 'production' ? 'border-emerald-600 bg-emerald-50/60 text-emerald-700 font-bold' : 'border-slate-200 hover:bg-slate-50 text-slate-600'">
                                    <input type="radio" name="xendit_environment" value="production" x-model="isProd" class="sr-only">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    <span>Production (Live)</span>
                                </label>
                            </div>
                        </div>

                        <!-- Secret Key -->
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Secret API Key</label>
                            <input type="password" name="xendit_secret_key" value="{{ old('xendit_secret_key', $settings->xendit_secret_key) }}" placeholder="Contoh: xnd_development_..." class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl font-mono text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-slate-900">
                        </div>

                        <!-- Public Key -->
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Public Key (Opsional)</label>
                            <input type="text" name="xendit_public_key" value="{{ old('xendit_public_key', $settings->xendit_public_key) }}" placeholder="Contoh: xnd_public_..." class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl font-mono text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-slate-900">
                        </div>

                        <!-- Webhook Verification Token -->
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Webhook Verification Token</label>
                            <input type="text" name="xendit_webhook_token" value="{{ old('xendit_webhook_token', $settings->xendit_webhook_token) }}" placeholder="Contoh: wh_token_..." class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl font-mono text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-slate-900">
                        </div>
                    </div>
                </div>

                <div class="px-6 py-3 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                    <span>Status: <strong :class="enabled ? 'text-slate-900' : 'text-slate-400'" x-text="enabled ? 'Aktif di Checkout Tenant' : 'Non-Aktif'"></strong></span>
                    <a href="https://dashboard.xendit.co" target="_blank" class="text-slate-700 hover:underline">Dashboard Xendit &rarr;</a>
                </div>
            </div>

        </div>

        <!-- CARD 3: MANUAL BANK TRANSFER -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4" x-data="{ enabled: {{ $settings->manual_transfer_enabled ? 'true' : 'false' }} }">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold text-sm shadow-md shadow-amber-500/20">
                        <i class="fa-solid fa-building-columns"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 text-sm">Metode Transfer Bank Manual</h3>
                        <p class="text-[11px] text-slate-500">Transfer langsung ke rekening Admin SaaS dengan konfirmasi bukti bayar</p>
                    </div>
                </div>

                <!-- Toggle Enable -->
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="manual_transfer_enabled" value="1" x-model="enabled" class="sr-only peer">
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
                </label>
            </div>

            <div :class="{ 'opacity-50 pointer-events-none': !enabled }">
                <label class="block text-xs font-bold text-slate-700 mb-1">Daftar Rekening Bank & Instruksi Pembayaran</label>
                <textarea name="bank_accounts_info" rows="3" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500 leading-relaxed" placeholder="Tuliskan nomor rekening dan atas nama...">{{ old('bank_accounts_info', $settings->bank_accounts_info) }}</textarea>
            </div>
        </div>

        <!-- SAVE BUTTON -->
        <div class="flex justify-end pt-2">
            <button type="submit" class="px-8 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white text-xs sm:text-sm font-bold rounded-2xl shadow-lg shadow-purple-600/30 transition transform hover:scale-[1.01] active:scale-[0.99] flex items-center space-x-2">
                <i class="fa-solid fa-floppy-disk"></i>
                <span>Simpan Pengaturan Payment Gateway</span>
            </button>
        </div>

    </form>
</div>
@endsection
