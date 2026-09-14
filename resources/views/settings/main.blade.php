@extends('layouts.app')

@section('title', 'Pengaturan Utama')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Pengaturan Utama</h1>
            <p class="text-xs text-slate-500 mt-1">Konfigurasi preferensi sistem transaksi, format laporan, dan pemetaan akun</p>
        </div>
        <a href="{{ route('settings.account_mappings') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold rounded-xl border border-indigo-200 shadow-2xs transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            <span>Buka Akun Perkiraan (Mappings)</span>
        </a>
    </div>

    <form method="POST" action="{{ route('settings.main.update') }}">
        @csrf

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 bg-slate-50/50">
                <h3 class="font-bold text-slate-800 text-sm">Menu Transaksi</h3>
            </div>

            <div class="p-6 space-y-6">
                
                <!-- 1. Preview Transaksi Switch -->
                <div class="flex items-center justify-between pb-5 border-b border-slate-100">
                    <div>
                        <h4 class="text-xs font-bold text-slate-800">Preview Transaksi</h4>
                        <p class="text-[11px] text-slate-500 mt-0.5">Sistem akan menampilkan preview data transaksi sebelum dilakukan simpan</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="preview_transaksi" value="1" {{ $settings->preview_transaksi ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                </div>

                <!-- 2. Format Pemisah Angka -->
                <div>
                    <label class="block text-xs font-bold text-slate-800 mb-1">Format Pemisah Angka</label>
                    <p class="text-[11px] text-slate-500 mb-2">Format pemisah akan dipergunakan sebagai standar di semua menu transaksi, laporan dll</p>
                    <select name="number_format" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-xs font-medium focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="1,000,000.00" {{ $settings->number_format == '1,000,000.00' ? 'selected' : '' }}>1,000,000.00 (Standar Internasional)</option>
                        <option value="1.000.000,00" {{ $settings->number_format == '1.000.000,00' ? 'selected' : '' }}>1.000.000,00 (Standar Indonesia)</option>
                    </select>
                </div>

                <!-- 3. Desimal Angka -->
                <div>
                    <label class="block text-xs font-bold text-slate-800 mb-1">Desimal Angka</label>
                    <p class="text-[11px] text-slate-500 mb-2">Angka dibelakang koma untuk menampilkan pada semua laporan</p>
                    <select name="decimal_places" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-xs font-medium focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="0" {{ $settings->decimal_places == 0 ? 'selected' : '' }}>0</option>
                        <option value="2" {{ $settings->decimal_places == 2 ? 'selected' : '' }}>2</option>
                    </select>
                </div>

                <!-- 4. Cache Laporan Switch -->
                <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                    <div>
                        <h4 class="text-xs font-bold text-slate-800">Cache Laporan Buku Besar dan Dashboard</h4>
                        <p class="text-[11px] text-slate-500 mt-0.5">Fitur ini memungkinkan laporan buku besar dan dashboard lebih cepat diproses jika diaktifkan</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="cache_reports" value="1" {{ $settings->cache_reports ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                </div>

                <!-- 5. Cache Hutang Piutang Switch -->
                <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                    <div>
                        <h4 class="text-xs font-bold text-slate-800">Cache Hutang Piutang</h4>
                        <p class="text-[11px] text-slate-500 mt-0.5">Fitur ini memungkinkan hutang piutang lebih cepat diproses jika diaktifkan</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="cache_ar_ap" value="1" {{ $settings->cache_ar_ap ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                </div>

            </div>

            <!-- INTEGRASI GOOGLE AI STUDIO (GEMINI 1.5 FLASH) -->
            <div class="p-5 border-t border-b border-slate-100 bg-gradient-to-r from-blue-50/50 via-indigo-50/30 to-purple-50/50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center shadow-md shadow-blue-500/20">
                            <i class="fa-solid fa-wand-magic-sparkles text-xs"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                                <span>Integrasi Google AI Studio (Gemini)</span>
                                @if(!empty($settings->gemini_api_key) || !empty(config('services.gemini.api_key')))
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1 animate-pulse"></span>
                                        Gemini Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                        Mode Lokal (Regex Heuristik)
                                    </span>
                                @endif
                            </h3>
                            <p class="text-[11px] text-slate-500">Membantu penjurnalan otomatis transaksi dari kalimat bahasa bebas</p>
                        </div>
                    </div>
                    <a href="https://aistudio.google.com/app/apikey" target="_blank" rel="noopener noreferrer" 
                       class="text-[11px] font-bold text-blue-600 hover:text-blue-700 bg-white hover:bg-blue-50 px-3 py-1.5 rounded-lg border border-blue-200 shadow-2xs transition flex items-center gap-1.5">
                        <i class="fa-brands fa-google text-blue-600"></i>
                        <span>Dapatkan API Key Gratis &rarr;</span>
                    </a>
                </div>
            </div>

            <div class="p-6 space-y-4" x-data="{
                apiKey: '{{ $settings->gemini_api_key ?? '' }}',
                showKey: false,
                testLoading: false,
                testStatus: null,
                testMessage: '',
                testModel: '',
                runTest() {
                    this.testLoading = true;
                    this.testStatus = null;
                    this.testMessage = '';
                    fetch('{{ route('settings.main.test_gemini') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ gemini_api_key: this.apiKey })
                    })
                    .then(res => res.json().then(data => ({ status: res.status, ok: res.ok, data })))
                    .then(res => {
                        this.testLoading = false;
                        if (res.ok && res.data.success) {
                            this.testStatus = 'success';
                            this.testMessage = res.data.message;
                            this.testModel = res.data.model || 'Gemini 1.5 Flash';
                        } else {
                            this.testStatus = 'error';
                            this.testMessage = res.data.message || 'Gagal terhubung ke API Gemini.';
                        }
                    })
                    .catch(err => {
                        this.testLoading = false;
                        this.testStatus = 'error';
                        this.testMessage = 'Gagal menghubungi server aplikasi.';
                    });
                }
            }">
                <div>
                    <label class="block text-xs font-bold text-slate-800 mb-1">Google AI Studio API Key</label>
                    <p class="text-[11px] text-slate-500 mb-2">
                        Masukkan API Key dari Google AI Studio. Jika dikosongkan, sistem akan otomatis tetap berjalan dengan <strong>Mesin Pintar Lokal</strong> bawaan tanpa error.
                    </p>
                    <div class="relative flex items-center">
                        <input :type="showKey ? 'text' : 'password'" 
                               name="gemini_api_key" 
                               x-model="apiKey"
                               placeholder="Contoh: AIzaSy..." 
                               class="w-full pl-3.5 pr-24 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-xs font-mono focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        
                        <div class="absolute right-2 flex items-center space-x-1">
                            <button type="button" @click="showKey = !showKey" 
                                    class="p-1.5 text-slate-400 hover:text-slate-600 rounded text-xs" 
                                    :title="showKey ? 'Sembunyikan' : 'Tampilkan'">
                                <i :class="showKey ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'"></i>
                            </button>
                            <button type="button" @click="runTest()" :disabled="testLoading || !apiKey" 
                                    class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 disabled:opacity-40 text-indigo-700 text-[11px] font-bold rounded border border-indigo-200 transition flex items-center gap-1">
                                <i x-show="testLoading" class="fa-solid fa-spinner fa-spin text-[10px]"></i>
                                <span x-text="testLoading ? 'Menguji...' : 'Uji AI'"></span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Notifikasi Hasil Test Uji AI -->
                <div x-show="testStatus === 'success'" x-cloak class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-xs text-emerald-800 flex items-start space-x-2">
                    <i class="fa-solid fa-circle-check text-emerald-600 mt-0.5"></i>
                    <div>
                        <span class="font-bold block" x-text="testMessage"></span>
                        <span class="text-[11px] text-emerald-700">Model: <code class="font-mono bg-emerald-100/70 px-1 py-0.5 rounded" x-text="testModel"></code> siap digunakan untuk menjurnal otomatis.</span>
                    </div>
                </div>

                <div x-show="testStatus === 'error'" x-cloak class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-800 flex items-start space-x-2">
                    <i class="fa-solid fa-circle-exclamation text-rose-600 mt-0.5"></i>
                    <div>
                        <span class="font-bold block">Uji Koneksi Gagal</span>
                        <span class="text-[11px] text-rose-700" x-text="testMessage"></span>
                    </div>
                </div>

                <!-- Panduan 3 Langkah -->
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-3.5 text-[11px] text-slate-600 space-y-1.5">
                    <div class="font-bold text-slate-800 flex items-center gap-1.5">
                        <i class="fa-solid fa-circle-info text-blue-600"></i>
                        <span>Cara Mendapatkan API Key Google AI Studio Gratis (1 Menit):</span>
                    </div>
                    <ol class="list-decimal list-inside space-y-1 pl-1 text-slate-600">
                        <li>Buka link <a href="https://aistudio.google.com/app/apikey" target="_blank" class="text-blue-600 font-semibold underline">aistudio.google.com</a> dan login dengan akun Google/Gmail Anda.</li>
                        <li>Klik tombol <strong>"Create API key"</strong> &rarr; pilih project baru.</li>
                        <li>Salin kunci <em>(starts with AIzaSy...)</em> dan tempel pada kolom di atas, lalu klik <strong>Simpan</strong>.</li>
                    </ol>
                </div>
            </div>

            <div class="p-6 bg-slate-50 border-t border-slate-200">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-6 py-2.5 rounded-lg shadow-sm transition">
                    Simpan Pengaturan
                </button>
            </div>
        </div>

    </form>

    @php
        $currentUser = auth()->user() ?? \App\Models\User::first();
        $isAdmin = $currentUser ? $currentUser->isAdmin($company->id) : false;
    @endphp

    @if($isAdmin)
        <!-- DANGER ZONE: RESET DATA TESTING -->
        <div class="bg-white rounded-xl border border-rose-200 shadow-sm overflow-hidden" x-data="{
            modalResetOpen: false,
            resetType: 'transactions_only',
            confirmText: '',
            get resetTitle() {
                if (this.resetType === 'transactions_only') return 'Reset Semua Transaksi & Jurnal Testing';
                if (this.resetType === 'initial_balances') return 'Reset Saldo Awal COA ke Nol';
                return 'Reset Total Pembukuan (Factory Reset)';
            },
            get resetDesc() {
                if (this.resetType === 'transactions_only') return 'Tindakan ini akan menghapus permanen SEMUA transaksi pemasukan, pengeluaran, transfer, dan jurnal umum. Daftar Master Akun COA dan Saldo Awal Anda akan tetap tersimpan.';
                if (this.resetType === 'initial_balances') return 'Tindakan ini akan mengosongkan seluruh Saldo Awal (Debit & Kredit) akun COA menjadi Rp 0 dan membuka kunci status COA.';
                return 'Tindakan ini akan menghapus SEMUA transaksi & jurnal sekaligus mereset seluruh Saldo Awal COA ke Rp 0. Database pembukuan akan kembali bersih seperti baru.';
            }
        }">
            <div class="p-5 border-b border-rose-100 bg-rose-50/50 flex items-center justify-between">
                <div class="flex items-center space-x-2.5">
                    <div class="w-8 h-8 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center font-bold text-sm">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-rose-900 text-sm">Zona Bahaya: Reset Data Pembukuan (Khusus Owner)</h3>
                        <p class="text-[11px] text-rose-700">Gunakan fitur ini setelah selesai masa uji coba (testing) untuk memulai pembukuan riil yang bersih</p>
                    </div>
                </div>
                <span class="px-2.5 py-1 rounded-full bg-rose-100 text-rose-800 text-[10px] font-extrabold tracking-wide uppercase">
                    Owner Only
                </span>
            </div>

            <div class="p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    
                    <!-- Opsi 1: Reset Transaksi Saja -->
                    <div class="p-4 rounded-xl border border-slate-200 hover:border-blue-400 bg-slate-50/60 flex flex-col justify-between transition">
                        <div>
                            <div class="flex items-center space-x-2 text-blue-600 font-bold text-xs mb-1.5">
                                <i class="fa-solid fa-receipt"></i>
                                <span>Reset Transaksi Saja</span>
                            </div>
                            <p class="text-[11px] text-slate-600 leading-relaxed">
                                Hapus seluruh riwayat transaksi & jurnal testing. Master Akun COA & Saldo Awal tetap aman.
                            </p>
                        </div>
                        <button type="button" @click="resetType = 'transactions_only'; confirmText = ''; modalResetOpen = true" class="mt-4 w-full py-2 px-3 bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold text-xs rounded-lg border border-blue-200 transition">
                            Pilih & Reset Transaksi
                        </button>
                    </div>

                    <!-- Opsi 2: Reset Saldo Awal -->
                    <div class="p-4 rounded-xl border border-slate-200 hover:border-amber-400 bg-slate-50/60 flex flex-col justify-between transition">
                        <div>
                            <div class="flex items-center space-x-2 text-amber-600 font-bold text-xs mb-1.5">
                                <i class="fa-solid fa-scale-balanced"></i>
                                <span>Reset Saldo Awal COA</span>
                            </div>
                            <p class="text-[11px] text-slate-600 leading-relaxed">
                                Kosongkan seluruh saldo awal COA ke Rp 0 dan buka kembali status kunci COA.
                            </p>
                        </div>
                        <button type="button" @click="resetType = 'initial_balances'; confirmText = ''; modalResetOpen = true" class="mt-4 w-full py-2 px-3 bg-amber-50 hover:bg-amber-100 text-amber-800 font-bold text-xs rounded-lg border border-amber-200 transition">
                            Pilih & Reset Saldo
                        </button>
                    </div>

                    <!-- Opsi 3: Factory Reset Total -->
                    <div class="p-4 rounded-xl border border-rose-200 hover:border-rose-400 bg-rose-50/40 flex flex-col justify-between transition">
                        <div>
                            <div class="flex items-center space-x-2 text-rose-600 font-bold text-xs mb-1.5">
                                <i class="fa-solid fa-trash-can"></i>
                                <span>Reset Total (Bersih)</span>
                            </div>
                            <p class="text-[11px] text-slate-600 leading-relaxed">
                                Hapus semua transaksi + kosongkan saldo awal COA. Siap digunakan untuk pembukuan baru dari nol.
                            </p>
                        </div>
                        <button type="button" @click="resetType = 'factory_reset'; confirmText = ''; modalResetOpen = true" class="mt-4 w-full py-2 px-3 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-lg shadow-sm transition">
                            Factory Reset Total
                        </button>
                    </div>

                </div>
            </div>

            <!-- MODAL KONFIRMASI RESET -->
            <div x-show="modalResetOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-xs">
                <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden border border-slate-200" @click.away="modalResetOpen = false">
                    
                    <div class="p-5 bg-rose-50 border-b border-rose-100 flex items-center justify-between">
                        <div class="flex items-center space-x-2 text-rose-700 font-bold text-sm">
                            <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
                            <span x-text="resetTitle"></span>
                        </div>
                        <button type="button" @click="modalResetOpen = false" class="text-slate-400 hover:text-slate-600 text-sm">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('settings.reset_data') }}" class="p-6 space-y-4">
                        @csrf
                        <input type="hidden" name="reset_type" :value="resetType">

                        <div class="p-3.5 bg-rose-50/80 rounded-xl border border-rose-200 text-xs text-rose-800 leading-relaxed">
                            <span class="font-bold">PERINGATAN:</span> <span x-text="resetDesc"></span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Ketik kata <span class="font-extrabold text-rose-600 bg-rose-50 px-1.5 py-0.5 rounded border border-rose-200">RESET</span> di bawah ini untuk konfirmasi:
                            </label>
                            <input type="text" name="confirmation_text" x-model="confirmText" placeholder="Ketik RESET" required
                                   class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-sm font-bold text-slate-900 focus:bg-white focus:ring-2 focus:ring-rose-500 focus:outline-none">
                        </div>

                        <div class="pt-2 flex items-center justify-end space-x-3">
                            <button type="button" @click="modalResetOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition">
                                Batal
                            </button>
                            <button type="submit" :disabled="confirmText.toUpperCase() !== 'RESET'" 
                                    class="px-5 py-2 bg-rose-600 hover:bg-rose-700 disabled:opacity-40 disabled:cursor-not-allowed text-white rounded-lg text-xs font-bold transition shadow-sm flex items-center space-x-1.5">
                                <i class="fa-solid fa-trash-can text-xs"></i>
                                <span>Konfirmasi & Eksekusi Reset</span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endif

</div>
@endsection
