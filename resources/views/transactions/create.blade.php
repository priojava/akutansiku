@extends('layouts.app')

@section('title', 'Tambah Transaksi')

@section('content')
    <script>
        window.accountsData = {!! $accountsJson ?? '[]' !!};
    </script>

    <div :class="entryMode === 'multi' ? 'max-w-7xl' : 'max-w-6xl'" class="mx-auto transition-all duration-300" x-data="{ 
            entryMode: 'simple', // 'simple' atau 'multi'
            aiModalOpen: false, 
            aiPrompt: '', 
            aiLoading: false, 
            aiResultText: '',
            type: '{{ old('type', 'expense') }}',
            debitAccount: '{{ old('debit_account_id', '') }}',
            creditAccount: '{{ old('credit_account_id', '') }}',
            amount: '{{ old('amount', '') }}',
            notes: '{{ old('notes', '') }}',
            showOptional: false,
            accounts: window.accountsData || [],

            // Multi-line journal items (untuk kasus Gambar 2)
            items: [
                { account_id: '', debit: '', credit: '', memo: '' },
                { account_id: '', debit: '', credit: '', memo: '' }
            ],

            addItem() {
                this.items.push({ account_id: '', debit: '', credit: '', memo: '' });
            },

            removeItem(index) {
                if (this.items.length > 2) {
                    this.items.splice(index, 1);
                } else {
                    alert('Transaksi minimal membutuhkan 2 baris akun.');
                }
            },

            formatNumber(val) {
                let clean = String(val).replace(/[^0-9]/g, '');
                return clean ? parseInt(clean, 10).toLocaleString('id-ID') : '';
            },

            parseNumber(val) {
                if (!val) return 0;
                let clean = String(val).replace(/[^0-9]/g, '');
                return clean ? parseInt(clean, 10) : 0;
            },

            formatAmount(el) {
                this.amount = this.formatNumber(el.value);
            },

            getTotalDebit() {
                return this.items.reduce((sum, item) => sum + this.parseNumber(item.debit), 0);
            },

            getTotalCredit() {
                return this.items.reduce((sum, item) => sum + this.parseNumber(item.credit), 0);
            },

            isMultiBalanced() {
                let d = this.getTotalDebit();
                let c = this.getTotalCredit();
                return d > 0 && c > 0 && d === c;
            },

            getMultiDiff() {
                return Math.abs(this.getTotalDebit() - this.getTotalCredit());
            },

            // Template Demo Kasus Gambar 2 (Jasa + Penjualan Barang + HPP + Persediaan)
            loadWorkshopTemplate() {
                this.entryMode = 'multi';
                this.notes = 'Servis Tuneup & Ganti Oli Mesin';

                let findAcc = (cat, nameKeyword, defaultIndex = 0) => {
                    let match = this.accounts.find(a => a.category.toLowerCase().includes(cat.toLowerCase()) && a.name.toLowerCase().includes(nameKeyword.toLowerCase()));
                    if (!match) match = this.accounts.find(a => a.category.toLowerCase().includes(cat.toLowerCase()));
                    return match ? match.id : (this.accounts[defaultIndex] ? this.accounts[defaultIndex].id : '');
                };

                let kasId = findAcc('Kas', 'kas');
                let jasaId = findAcc('Pendapatan', 'jasa') || findAcc('Pendapatan', 'pendapatan');
                let penjualanId = findAcc('Pendapatan', 'penjualan') || findAcc('Pendapatan', 'pendapatan');
                let hppId = findAcc('Harga Pokok Penjualan', 'beban') || findAcc('Harga Pokok Penjualan', 'hpp');
                let persediaanId = findAcc('Persediaan', 'persediaan');

                this.items = [
                    { account_id: kasId, debit: '100.000', credit: '', memo: 'Penerimaan Kas dari Pelanggan' },
                    { account_id: jasaId, debit: '', credit: '60.000', memo: 'Pendapatan Jasa (Tuneup)' },
                    { account_id: penjualanId, debit: '', credit: '40.000', memo: 'Penjualan Barang (Oli)' },
                    { account_id: hppId, debit: '25.000', credit: '', memo: 'Beban Pokok Penjualan (HPP Oli)' },
                    { account_id: persediaanId, debit: '', credit: '25.000', memo: 'Pengurangan Stok Persediaan (Oli)' },
                ];
            },

            askAi() {
                if (!this.aiPrompt) return;
                this.aiLoading = true;
                fetch('{{ route('transactions.ai_parse') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ prompt: this.aiPrompt })
                })
                .then(res => res.json())
                .then(data => {
                    this.aiLoading = false;
                    if (data.success && data.parsed_data) {
                        if (data.parsed_data.type) this.type = data.parsed_data.type;
                        if (data.parsed_data.debit_account_id) this.debitAccount = data.parsed_data.debit_account_id;
                        if (data.parsed_data.credit_account_id) this.creditAccount = data.parsed_data.credit_account_id;
                        if (data.parsed_data.amount) this.amount = this.formatNumber(data.parsed_data.amount);
                        if (data.parsed_data.notes) this.notes = data.parsed_data.notes;
                        this.aiResultText = data.explanation;
                        setTimeout(() => { this.aiModalOpen = false; }, 1200);
                    }
                })
                .catch(err => {
                    this.aiLoading = false;
                    alert('Gagal menghubungi AI helper.');
                });
            }
        }">

        <div
            :class="entryMode === 'multi' ? 'grid grid-cols-1 xl:grid-cols-12 gap-6' : 'grid grid-cols-1 lg:grid-cols-3 gap-8'">

            <!-- FORM UTAMA -->
            <div :class="entryMode === 'multi' ? 'xl:col-span-8' : 'lg:col-span-2'">
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">

                    <!-- Tab Pilihan Mode Transaksi (Catat Cepat vs Majemuk Multi-Akun) -->
                    <div
                        class="p-4 border-b border-slate-200 bg-slate-50/70 flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h2 class="text-base sm:text-lg font-bold text-slate-800">Catat Transaksi</h2>
                            <p class="text-xs text-slate-500">Pilih mode pencatatan sesuai kebutuhan transaksi Anda</p>
                        </div>

                        <!-- Mode Toggle Buttons -->
                        <div class="inline-flex rounded-xl border border-slate-300 bg-white p-1 shadow-2xs">
                            <button type="button" @click="entryMode = 'simple'"
                                :class="entryMode === 'simple' ? 'bg-blue-600 text-white font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                                class="px-3.5 py-1.5 text-xs rounded-lg transition flex items-center space-x-1.5">
                                <i class="fa-solid fa-bolt text-[11px]"></i>
                                <span>Catat Cepat (1-ke-1)</span>
                            </button>
                            <button type="button" @click="entryMode = 'multi'"
                                :class="entryMode === 'multi' ? 'bg-purple-600 text-white font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                                class="px-3.5 py-1.5 text-xs rounded-lg transition flex items-center space-x-1.5"
                                title="Untuk transaksi majemuk seperti barang + jasa + HPP">
                                <i class="fa-solid fa-layer-group text-[11px]"></i>
                                <span>Jurnal Majemuk (Multi-Akun)</span>
                            </button>
                        </div>
                    </div>

                    <!-- Notifikasi Error & Sukses -->
                    @if($errors->any())
                        <div class="mx-6 mt-5 p-4 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-800 shadow-xs">
                            <div class="font-bold flex items-center space-x-2 mb-1.5 text-rose-700">
                                <i class="fa-solid fa-circle-exclamation text-sm"></i>
                                <span>Gagal Menyimpan Transaksi:</span>
                            </div>
                            <ul class="list-disc pl-5 space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(session('error'))
                        <div
                            class="mx-6 mt-5 p-4 bg-rose-50 border border-rose-200 rounded-xl text-xs font-semibold text-rose-800 flex items-start space-x-2.5 shadow-xs">
                            <i class="fa-solid fa-circle-exclamation text-rose-600 text-sm mt-0.5"></i>
                            <span>{{ session('error') }}</span>
                        </div>
                    @endif

                    @if(session('success'))
                        <div
                            class="mx-6 mt-5 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-xs font-semibold text-emerald-800 flex items-start space-x-2.5 shadow-xs">
                            <i class="fa-solid fa-circle-check text-emerald-600 text-sm mt-0.5"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('transactions.store') }}" class="p-6 space-y-5" @submit="
                                if (entryMode === 'simple') {
                                    if (!debitAccount) {
                                        alert('Silakan pilih Akun Simpan ke (Debit) terlebih dahulu.');
                                        $event.preventDefault();
                                        return false;
                                    }
                                    if (!creditAccount) {
                                        alert('Silakan pilih Akun Diterima dari (Kredit) terlebih dahulu.');
                                        $event.preventDefault();
                                        return false;
                                    }
                                    if (debitAccount == creditAccount) {
                                        alert('Akun Debit dan Akun Kredit tidak boleh sama.');
                                        $event.preventDefault();
                                        return false;
                                    }
                                    let val = parseNumber(amount);
                                    if (!val || val <= 0) {
                                        alert('Nominal transaksi harus lebih dari 0.');
                                        $event.preventDefault();
                                        return false;
                                    }
                                } else if (entryMode === 'multi') {
                                    if (!isMultiBalanced()) {
                                        alert('Jurnal Majemuk belum seimbang! Pastikan Total Debit dan Total Kredit sama dan lebih dari 0.');
                                        $event.preventDefault();
                                        return false;
                                    }
                                }
                              ">
                        @csrf
                        <input type="hidden" name="entry_mode" :value="entryMode">

                        <!-- Tanggal & Jam -->
                        <div class="grid grid-cols-3 gap-4">
                            <div class="col-span-2">
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal <span
                                        class="text-rose-500">*</span></label>
                                <input type="date" name="date" value="{{ old('date', $today->format('Y-m-d')) }}" required
                                    class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs font-medium focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Jam <span
                                        class="text-rose-500">*</span></label>
                                <input type="time" name="time" value="{{ old('time', $today->format('H:i')) }}" required
                                    class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs font-medium focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            </div>
                        </div>

                        <!-- ======================================================== -->
                        <!-- MODE 1: CATAT CEPAT (1 DEBIT - 1 KREDIT)                -->
                        <!-- ======================================================== -->
                        <div x-show="entryMode === 'simple'" class="space-y-4">

                            <!-- Jenis Transaksi (Tab Selector Cepat) + AI Button -->
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="block text-xs font-semibold text-slate-700">Jenis Transaksi</label>
                                    <button type="button" @click="aiModalOpen = true"
                                        class="text-xs text-blue-600 hover:text-blue-700 hover:underline font-medium flex items-center">
                                        <i class="fa-solid fa-wand-magic-sparkles text-amber-500 mr-1 text-xs"></i>
                                        <span>minta bantuan AI untuk penjurnalan</span>
                                    </button>
                                </div>

                                <!-- Tab Pilihan Jenis Transaksi -->
                                <div class="grid grid-cols-3 gap-2">
                                    <label class="cursor-pointer">
                                        <input type="radio" name="type" value="income" x-model="type"
                                            :disabled="entryMode !== 'simple'" class="peer sr-only">
                                        <div
                                            class="flex items-center justify-center space-x-2 py-2 px-3 rounded-lg border text-xs font-semibold peer-checked:bg-emerald-50 peer-checked:border-emerald-500 peer-checked:text-emerald-700 border-slate-300 bg-slate-50 hover:bg-slate-100 transition">
                                            <i class="fa-solid fa-arrow-down-left text-emerald-600"></i>
                                            <span>Pemasukan</span>
                                        </div>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="type" value="expense" x-model="type"
                                            :disabled="entryMode !== 'simple'" class="peer sr-only">
                                        <div
                                            class="flex items-center justify-center space-x-2 py-2 px-3 rounded-lg border text-xs font-semibold peer-checked:bg-rose-50 peer-checked:border-rose-500 peer-checked:text-rose-700 border-slate-300 bg-slate-50 hover:bg-slate-100 transition">
                                            <i class="fa-solid fa-arrow-up-right text-rose-600"></i>
                                            <span>Pengeluaran</span>
                                        </div>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="type" value="transfer" x-model="type"
                                            :disabled="entryMode !== 'simple'" class="peer sr-only">
                                        <div
                                            class="flex items-center justify-center space-x-2 py-2 px-3 rounded-lg border text-xs font-semibold peer-checked:bg-blue-50 peer-checked:border-blue-500 peer-checked:text-blue-700 border-slate-300 bg-slate-50 hover:bg-slate-100 transition">
                                            <i class="fa-solid fa-arrow-right-arrow-left text-blue-600"></i>
                                            <span>Transfer Kas</span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Searchable Combobox: Simpan ke (Debit) -->
                            <div x-data="{
                                    open: false,
                                    search: '',
                                    get filteredAccounts() {
                                        if (!this.search) return this.accounts;
                                        let s = this.search.toLowerCase();
                                        return this.accounts.filter(a => a.name.toLowerCase().includes(s) || a.code.toLowerCase().includes(s) || a.category.toLowerCase().includes(s));
                                    },
                                    getSelectedAccount() {
                                        return this.accounts.find(a => a.id == this.debitAccount);
                                    }
                                }" class="relative">
                                <label class="block text-xs font-semibold text-slate-700 mb-1">
                                    Simpan ke (Debit) <span class="text-rose-500">*</span>
                                    <span class="text-[10px] font-normal text-slate-400 ml-1">
                                        <span x-show="type === 'income'">(Akun Kas/Bank penerima uang)</span>
                                        <span x-show="type === 'expense'">(Akun Beban / Biaya yang dibayar)</span>
                                        <span x-show="type === 'transfer'">(Akun Kas/Bank tujuan transfer)</span>
                                    </span>
                                </label>
                                <input type="hidden" name="debit_account_id" :value="debitAccount"
                                    :disabled="entryMode !== 'simple'">

                                <!-- Selected Display / Trigger -->
                                <div @click="open = !open"
                                    class="w-full min-h-[42px] px-3.5 py-2 bg-slate-50 border rounded-lg text-xs cursor-pointer flex items-center justify-between hover:bg-slate-100 transition focus:ring-2 focus:ring-blue-500"
                                    :class="!debitAccount ? 'border-slate-300' : 'border-blue-400 bg-blue-50/20'">
                                    <div class="flex items-center space-x-2 truncate">
                                        <template x-if="getSelectedAccount()">
                                            <div class="flex items-center space-x-2 truncate">
                                                <span class="font-bold text-slate-800"
                                                    x-text="getSelectedAccount().name"></span>
                                                <span
                                                    class="font-mono text-[10px] bg-slate-200 text-slate-700 px-1.5 py-0.5 rounded"
                                                    x-text="getSelectedAccount().code"></span>
                                                <span class="text-[10px] text-slate-500"
                                                    x-text="'[' + getSelectedAccount().category + ']'"></span>
                                            </div>
                                        </template>
                                        <template x-if="!getSelectedAccount()">
                                            <span class="text-slate-400">Pilih atau cari akun debit...</span>
                                        </template>
                                    </div>
                                    <div class="flex items-center space-x-1.5 text-slate-400">
                                        <i class="fa-solid fa-magnifying-glass text-[11px]"></i>
                                        <i class="fa-solid fa-chevron-down text-[10px] transition-transform"
                                            :class="open ? 'rotate-180' : ''"></i>
                                    </div>
                                </div>

                                <!-- Dropdown Box with Live Search -->
                                <div x-show="open" @click.away="open = false" x-cloak
                                    class="absolute z-30 left-0 right-0 mt-1 bg-white border border-slate-300 rounded-xl shadow-xl overflow-hidden">
                                    <div class="p-2 border-b border-slate-100 bg-slate-50">
                                        <div class="relative">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                                            <input type="text" x-model="search"
                                                placeholder="Ketik nama, kode, atau kategori akun..."
                                                @keydown.escape="open = false"
                                                class="w-full pl-8 pr-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500">
                                        </div>
                                    </div>
                                    <div class="max-h-56 overflow-y-auto divide-y divide-slate-50">
                                        <template x-for="acc in filteredAccounts" :key="acc.id">
                                            <div @click="debitAccount = acc.id; open = false; search = ''"
                                                class="p-2.5 text-xs hover:bg-blue-50 cursor-pointer flex items-center justify-between transition"
                                                :class="debitAccount == acc.id ? 'bg-blue-50/70 font-semibold' : ''">
                                                <div>
                                                    <div class="text-slate-800" x-text="acc.name"></div>
                                                    <div class="text-[10px] text-slate-400 mt-0.5">
                                                        <span class="font-mono bg-slate-100 px-1 py-0.2 rounded mr-1"
                                                            x-text="acc.code"></span>
                                                        <span x-text="acc.category"></span>
                                                    </div>
                                                </div>
                                                <i x-show="debitAccount == acc.id"
                                                    class="fa-solid fa-check text-blue-600 text-xs"></i>
                                            </div>
                                        </template>
                                        <div x-show="filteredAccounts.length === 0"
                                            class="p-4 text-center text-xs text-slate-400">
                                            Tidak ada akun yang sesuai pencarian.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Searchable Combobox: Diterima dari (Kredit) -->
                            <div x-data="{
                                    open: false,
                                    search: '',
                                    get filteredAccounts() {
                                        if (!this.search) return this.accounts;
                                        let s = this.search.toLowerCase();
                                        return this.accounts.filter(a => a.name.toLowerCase().includes(s) || a.code.toLowerCase().includes(s) || a.category.toLowerCase().includes(s));
                                    },
                                    getSelectedAccount() {
                                        return this.accounts.find(a => a.id == this.creditAccount);
                                    }
                                }" class="relative">
                                <label class="block text-xs font-semibold text-slate-700 mb-1">
                                    Diterima dari (Kredit) <span class="text-rose-500">*</span>
                                    <span class="text-[10px] font-normal text-slate-400 ml-1">
                                        <span x-show="type === 'income'">(Akun Pendapatan / Sumber dana)</span>
                                        <span x-show="type === 'expense'">(Akun Kas/Bank yang mengeluarkan uang)</span>
                                        <span x-show="type === 'transfer'">(Akun Kas/Bank asal pengiriman)</span>
                                    </span>
                                </label>
                                <input type="hidden" name="credit_account_id" :value="creditAccount"
                                    :disabled="entryMode !== 'simple'">

                                <!-- Selected Display / Trigger -->
                                <div @click="open = !open"
                                    class="w-full min-h-[42px] px-3.5 py-2 bg-slate-50 border rounded-lg text-xs cursor-pointer flex items-center justify-between hover:bg-slate-100 transition focus:ring-2 focus:ring-blue-500"
                                    :class="!creditAccount ? 'border-slate-300' : 'border-blue-400 bg-blue-50/20'">
                                    <div class="flex items-center space-x-2 truncate">
                                        <template x-if="getSelectedAccount()">
                                            <div class="flex items-center space-x-2 truncate">
                                                <span class="font-bold text-slate-800"
                                                    x-text="getSelectedAccount().name"></span>
                                                <span
                                                    class="font-mono text-[10px] bg-slate-200 text-slate-700 px-1.5 py-0.5 rounded"
                                                    x-text="getSelectedAccount().code"></span>
                                                <span class="text-[10px] text-slate-500"
                                                    x-text="'[' + getSelectedAccount().category + ']'"></span>
                                            </div>
                                        </template>
                                        <template x-if="!getSelectedAccount()">
                                            <span class="text-slate-400">Pilih atau cari akun kredit...</span>
                                        </template>
                                    </div>
                                    <div class="flex items-center space-x-1.5 text-slate-400">
                                        <i class="fa-solid fa-magnifying-glass text-[11px]"></i>
                                        <i class="fa-solid fa-chevron-down text-[10px] transition-transform"
                                            :class="open ? 'rotate-180' : ''"></i>
                                    </div>
                                </div>

                                <!-- Dropdown Box with Live Search -->
                                <div x-show="open" @click.away="open = false" x-cloak
                                    class="absolute z-30 left-0 right-0 mt-1 bg-white border border-slate-300 rounded-xl shadow-xl overflow-hidden">
                                    <div class="p-2 border-b border-slate-100 bg-slate-50">
                                        <div class="relative">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                                            <input type="text" x-model="search"
                                                placeholder="Ketik nama, kode, atau kategori akun..."
                                                @keydown.escape="open = false"
                                                class="w-full pl-8 pr-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500">
                                        </div>
                                    </div>
                                    <div class="max-h-56 overflow-y-auto divide-y divide-slate-50">
                                        <template x-for="acc in filteredAccounts" :key="acc.id">
                                            <div @click="creditAccount = acc.id; open = false; search = ''"
                                                class="p-2.5 text-xs hover:bg-blue-50 cursor-pointer flex items-center justify-between transition"
                                                :class="creditAccount == acc.id ? 'bg-blue-50/70 font-semibold' : ''">
                                                <div>
                                                    <div class="text-slate-800" x-text="acc.name"></div>
                                                    <div class="text-[10px] text-slate-400 mt-0.5">
                                                        <span class="font-mono bg-slate-100 px-1 py-0.2 rounded mr-1"
                                                            x-text="acc.code"></span>
                                                        <span x-text="acc.category"></span>
                                                    </div>
                                                </div>
                                                <i x-show="creditAccount == acc.id"
                                                    class="fa-solid fa-check text-blue-600 text-xs"></i>
                                            </div>
                                        </template>
                                        <div x-show="filteredAccounts.length === 0"
                                            class="p-4 text-center text-xs text-slate-400">
                                            Tidak ada akun yang sesuai pencarian.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Nominal -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Nominal Transaksi <span
                                        class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <span
                                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 text-xs font-bold">Rp</span>
                                    <input type="text" name="amount" x-model="amount" @input="formatAmount($el)"
                                        placeholder="0" :disabled="entryMode !== 'simple'"
                                        class="w-full pl-11 pr-4 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-sm font-bold text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none font-mono">
                                </div>
                            </div>
                        </div>

                        <!-- ======================================================== -->
                        <!-- MODE 2: JURNAL MAJEMUK (KASUS GAMBAR 2: BARANG+JASA+HPP) -->
                        <!-- ======================================================== -->
                        <div x-show="entryMode === 'multi'" class="space-y-4" style="display: none;">
                            <div
                                class="bg-purple-50/60 border border-purple-200 rounded-xl p-3 text-xs text-purple-900 flex flex-wrap items-center justify-between gap-2">
                                <div class="flex items-center space-x-2">
                                    <i class="fa-solid fa-circle-info text-purple-600"></i>
                                    <span>Mode ini cocok untuk transaksi majemuk: Penjualan Barang + Jasa + HPP + Persediaan
                                        dalam 1 transaksi.</span>
                                </div>
                                <button type="button" @click="loadWorkshopTemplate()"
                                    class="px-2.5 py-1 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg text-[11px] transition shadow-2xs">
                                    <i class="fa-solid fa-wand-magic-sparkles mr-1"></i>
                                    <span>Muat Contoh</span>
                                </button>
                            </div>

                            <!-- Dynamic Journal Lines Table -->
                            <div class="border border-slate-200 rounded-xl bg-white shadow-2xs overflow-hidden">
                                <div class="overflow-x-auto min-h-[260px]">
                                    <table class="w-full text-left text-xs min-w-[760px]">
                                        <thead
                                            class="bg-slate-100 border-b border-slate-200 text-slate-600 uppercase text-[10px] tracking-wider font-bold">
                                            <tr>
                                                <th class="py-2.5 px-3 w-64 sm:w-72 min-w-[220px]">Pilih Akun (Searchable)
                                                </th>
                                                <th class="py-2.5 px-3 w-36 min-w-[120px] text-right">Debit (Rp)</th>
                                                <th class="py-2.5 px-3 w-36 min-w-[120px] text-right">Kredit (Rp)</th>
                                                <th class="py-2.5 px-3 min-w-[220px]">Keterangan / Memo</th>
                                                <th class="py-2.5 px-2 w-10 text-center">#</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            <template x-for="(item, index) in items" :key="index">
                                                <tr class="hover:bg-slate-50/80 transition">
                                                    <!-- Dropdown Akun Searchable -->
                                                    <td class="p-2 relative" x-data="{
                                                            open: false,
                                                            search: '',
                                                            get filteredAccounts() {
                                                                if (!this.search) return accounts;
                                                                let s = this.search.toLowerCase();
                                                                return accounts.filter(a => a.name.toLowerCase().includes(s) || a.code.toLowerCase().includes(s) || a.category.toLowerCase().includes(s));
                                                            },
                                                            getSelectedAccount() {
                                                                return accounts.find(a => a.id == item.account_id);
                                                            }
                                                        }">
                                                        <input type="hidden" :name="'items[' + index + '][account_id]'"
                                                            :value="item.account_id" :disabled="entryMode !== 'multi'">

                                                        <!-- Trigger Button -->
                                                        <div @click="open = !open; if(open) setTimeout(() => $el.nextElementSibling.querySelector('input').focus(), 50)"
                                                            class="w-full min-h-[34px] px-2.5 py-1.5 bg-slate-50 border border-slate-300 rounded-lg text-xs cursor-pointer flex items-center justify-between hover:bg-slate-100 transition focus:ring-1 focus:ring-purple-500">
                                                            <div class="truncate mr-1">
                                                                <template x-if="getSelectedAccount()">
                                                                    <div class="truncate">
                                                                        <span class="font-bold text-slate-800"
                                                                            x-text="getSelectedAccount().name"></span>
                                                                        <span
                                                                            class="font-mono text-[10px] text-slate-400 ml-1"
                                                                            x-text="'(' + getSelectedAccount().code + ')'"></span>
                                                                    </div>
                                                                </template>
                                                                <template x-if="!getSelectedAccount()">
                                                                    <span class="text-slate-400">Pilih / Cari Akun...</span>
                                                                </template>
                                                            </div>
                                                            <div
                                                                class="flex items-center space-x-1 text-slate-400 flex-shrink-0">
                                                                <i class="fa-solid fa-magnifying-glass text-[10px]"></i>
                                                                <i class="fa-solid fa-chevron-down text-[8px] transition-transform"
                                                                    :class="open ? 'rotate-180' : ''"></i>
                                                            </div>
                                                        </div>

                                                        <!-- Dropdown Box with Live Search -->
                                                        <div x-show="open" @click.away="open = false" x-cloak
                                                            class="absolute left-2 z-50 mt-1 w-72 sm:w-80 bg-white border border-slate-300 rounded-xl shadow-2xl overflow-hidden">
                                                            <div class="p-2 border-b border-slate-100 bg-slate-50">
                                                                <div class="relative">
                                                                    <i
                                                                        class="fa-solid fa-magnifying-glass absolute left-2.5 top-2 text-slate-400 text-[10px]"></i>
                                                                    <input type="text" x-model="search"
                                                                        placeholder="Cari nama, kode, kategori..."
                                                                        @keydown.escape="open = false"
                                                                        class="w-full pl-7 pr-2 py-1 text-xs bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-purple-500">
                                                                </div>
                                                            </div>
                                                            <div class="max-h-48 overflow-y-auto divide-y divide-slate-50">
                                                                <template x-for="acc in filteredAccounts" :key="acc.id">
                                                                    <div @click="item.account_id = acc.id; open = false; search = ''"
                                                                        class="p-2 text-xs hover:bg-purple-50 cursor-pointer flex items-center justify-between transition"
                                                                        :class="item.account_id == acc.id ? 'bg-purple-50 font-semibold' : ''">
                                                                        <div class="truncate mr-2">
                                                                            <div class="text-slate-800 truncate"
                                                                                x-text="acc.name"></div>
                                                                            <div class="text-[10px] text-slate-400">
                                                                                <span
                                                                                    class="font-mono bg-slate-100 px-1 py-0.2 rounded mr-1"
                                                                                    x-text="acc.code"></span>
                                                                                <span x-text="acc.category"></span>
                                                                            </div>
                                                                        </div>
                                                                        <i x-show="item.account_id == acc.id"
                                                                            class="fa-solid fa-check text-purple-600 text-xs flex-shrink-0"></i>
                                                                    </div>
                                                                </template>
                                                                <div x-show="filteredAccounts.length === 0"
                                                                    class="p-3 text-center text-xs text-slate-400">
                                                                    Tidak ada akun yang sesuai.
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>

                                                    <!-- Input Debit -->
                                                    <td class="p-2">
                                                        <input type="text" :name="'items[' + index + '][debit]'"
                                                            x-model="item.debit"
                                                            @input="item.debit = formatNumber($el.value); if (item.debit) item.credit = ''"
                                                            placeholder="0" :disabled="entryMode !== 'multi'"
                                                            class="w-full px-2 py-1.5 bg-slate-50 border border-slate-300 rounded-lg text-xs font-mono text-right focus:bg-white focus:ring-1 focus:ring-purple-500 focus:outline-none">
                                                    </td>

                                                    <!-- Input Kredit -->
                                                    <td class="p-2">
                                                        <input type="text" :name="'items[' + index + '][credit]'"
                                                            x-model="item.credit"
                                                            @input="item.credit = formatNumber($el.value); if (item.credit) item.debit = ''"
                                                            placeholder="0" :disabled="entryMode !== 'multi'"
                                                            class="w-full px-2 py-1.5 bg-slate-50 border border-slate-300 rounded-lg text-xs font-mono text-right focus:bg-white focus:ring-1 focus:ring-purple-500 focus:outline-none">
                                                    </td>

                                                    <!-- Memo -->
                                                    <td class="p-2">
                                                        <input type="text" :name="'items[' + index + '][memo]'"
                                                            x-model="item.memo" placeholder="Keterangan / memo..."
                                                            :disabled="entryMode !== 'multi'"
                                                            class="w-full px-3 py-1.5 bg-slate-50 border border-slate-300 rounded-lg text-xs focus:bg-white focus:ring-1 focus:ring-purple-500 focus:outline-none placeholder:text-slate-400">
                                                    </td>

                                                    <!-- Tombol Hapus -->
                                                    <td class="p-2 text-center">
                                                        <button type="button" @click="removeItem(index)"
                                                            class="text-rose-400 hover:text-rose-600 p-1">
                                                            <i class="fa-solid fa-trash text-xs"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                        <!-- Total Summary Footer -->
                                        <tfoot class="bg-slate-50 border-t-2 border-slate-300 font-bold text-xs">
                                            <tr>
                                                <td class="py-2.5 px-3 text-slate-800">Total Saldo</td>
                                                <td class="py-2.5 px-3 text-right font-mono text-blue-700"
                                                    x-text="'Rp ' + getTotalDebit().toLocaleString('id-ID')"></td>
                                                <td class="py-2.5 px-3 text-right font-mono text-purple-700"
                                                    x-text="'Rp ' + getTotalCredit().toLocaleString('id-ID')"></td>
                                                <td colspan="2" class="py-2.5 px-3 text-right">
                                                    <span x-show="isMultiBalanced()"
                                                        class="inline-flex items-center text-emerald-700 bg-emerald-100/80 px-2 py-0.5 rounded-full text-[11px]">
                                                        <i class="fa-solid fa-circle-check mr-1"></i> Seimbang
                                                    </span>
                                                    <span
                                                        x-show="!isMultiBalanced() && (getTotalDebit() > 0 || getTotalCredit() > 0)"
                                                        class="inline-flex items-center text-rose-700 bg-rose-100 px-2 py-0.5 rounded-full text-[11px]">
                                                        <i class="fa-solid fa-circle-exclamation mr-1"></i> Selisih: Rp
                                                        <span x-text="getMultiDiff().toLocaleString('id-ID')"></span>
                                                    </span>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                <div class="p-2.5 bg-slate-50/60 border-t border-slate-200">
                                    <button type="button" @click="addItem()"
                                        class="px-3 py-1.5 bg-white border border-slate-300 hover:bg-slate-100 text-slate-700 text-xs font-semibold rounded-lg shadow-2xs transition flex items-center space-x-1">
                                        <i class="fa-solid fa-plus text-[10px] text-blue-600"></i>
                                        <span>+ Tambah Baris Akun</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Catatan Utama -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan / Deskripsi Transaksi
                                <span class="text-rose-500">*</span></label>
                            <textarea name="notes" x-model="notes" rows="2" placeholder="Catatan transaksi..." required
                                class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none"></textarea>
                        </div>

                        <!-- Kontak -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Pelanggan / Vendor / Kontak
                                (Opsional)</label>
                            <select name="contact_id"
                                class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                <option value="">Tanpa Kontak...</option>
                                @foreach($contacts as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }} ({{ ucfirst($c->type) }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Link Opsional (Tag & Pajak) -->
                        <div>
                            <button type="button" @click="showOptional = !showOptional"
                                class="text-xs text-blue-600 hover:underline font-medium">
                                <span
                                    x-text="showOptional ? '- Sembunyikan Opsi Tambahan' : '+ Opsional (Tag Proyek & Pajak)'"></span>
                            </button>

                            <div x-show="showOptional"
                                class="mt-3 grid grid-cols-2 gap-4 p-3 bg-slate-50 rounded-lg border border-slate-200">
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Tag Proyek /
                                        Cabang</label>
                                    <select name="tag_id"
                                        class="w-full px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-xs">
                                        <option value="">Tanpa Tag</option>
                                        @foreach($tags as $tag)
                                            <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div x-show="entryMode === 'simple'">
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Pajak</label>
                                    <select name="tax_id" :disabled="entryMode !== 'simple'"
                                        class="w-full px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-xs">
                                        <option value="">Tanpa Pajak</option>
                                        @foreach($taxes as $tax)
                                            <option value="{{ $tax->id }}">{{ $tax->name }} ({{ $tax->rate }}%)</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Tombol Simpan -->
                        <div class="pt-2">
                            <button type="submit" :disabled="entryMode === 'multi' && !isMultiBalanced()"
                                :class="entryMode === 'multi' && !isMultiBalanced() ? 'opacity-50 cursor-not-allowed bg-slate-400' : 'bg-blue-600 hover:bg-blue-700'"
                                class="w-full text-white font-semibold py-3 px-4 rounded-xl shadow-sm transition flex items-center justify-center space-x-2 text-sm">
                                <i class="fa-solid fa-check"></i>
                                <span
                                    x-text="entryMode === 'multi' ? (isMultiBalanced() ? 'Simpan Transaksi Majemuk' : 'Jurnal Belum Seimbang') : 'Simpan Transaksi'"></span>
                            </button>
                        </div>

                    </form>
                </div>
            </div>

            <!-- SIDEBAR KANAN (Info & History) -->
            <div :class="entryMode === 'multi' ? 'xl:col-span-4' : 'lg:col-span-1'" class="space-y-6">

                <!-- Panduan Kasus (Bengkel / Jasa + Barang) -->
                <div
                    class="bg-gradient-to-br from-purple-50 to-indigo-50 rounded-xl border border-purple-200 p-5 shadow-sm">
                    <div class="flex items-center space-x-2 text-purple-800 font-bold text-sm mb-2">
                        <i class="fa-solid fa-lightbulb text-amber-500"></i>
                        <span>Solusi Kasus Barang + Jasa + HPP</span>
                    </div>
                    <p class="text-xs text-purple-900 leading-relaxed mb-3">
                        Untuk transaksi bengkel / toko servis , gunakan <strong>Jurnal Majemuk</strong>:
                    </p>
                    <div
                        class="bg-white/90 border border-purple-200 rounded-lg p-3 font-mono text-[11px] space-y-1 text-slate-800">
                        <div class="flex justify-between"><span>Kas (D)</span><span>100.000</span></div>
                        <div class="flex justify-between text-slate-600"><span>Pendptan Jasa (K)</span><span>60.000</span>
                        </div>
                        <div class="flex justify-between text-slate-600"><span>Penjualan Oli (K)</span><span>40.000</span>
                        </div>
                        <div class="flex justify-between"><span>HPP Beban Pokok (D)</span><span>25.000</span></div>
                        <div class="flex justify-between text-slate-600"><span>Persediaan Oli (K)</span><span>25.000</span>
                        </div>
                        <div class="border-t border-slate-300 pt-1 flex justify-between font-bold text-emerald-700">
                            <span>Total (D = K)</span><span>125.000</span>
                        </div>
                    </div>
                    <button type="button" @click="loadWorkshopTemplate()"
                        class="mt-3.5 w-full py-2 bg-purple-600 hover:bg-purple-700 text-white text-xs font-semibold rounded-lg transition shadow-2xs flex items-center justify-center space-x-1.5">
                        <i class="fa-solid fa-wand-magic-sparkles"></i>
                        <span>Terapkan Contoh Ini Langsung</span>
                    </button>
                </div>

                <!-- Card 2: History Transaksi -->
                <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
                    <h3 class="font-bold text-slate-800 text-base mb-2">Riwayat Transaksi</h3>
                    <p class="text-xs text-slate-500 leading-relaxed mb-5">
                        Lihat semua riwayat transaksi pemasukan, pengeluaran, transfer, dan jurnal majemuk yang sudah
                        tersimpan.
                    </p>
                    <a href="{{ route('transactions.history') }}"
                        class="block text-center w-full py-2.5 px-4 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-lg shadow-sm transition">
                        Buka Riwayat Transaksi
                    </a>
                </div>

            </div>

        </div>

        <!-- MODAL BANTUAN AI PENJURNALAN -->
        <div x-show="aiModalOpen" x-cloak
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 relative"
                @click.away="aiModalOpen = false">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center space-x-2 text-slate-800 font-bold text-base">
                        <i class="fa-solid fa-wand-magic-sparkles text-amber-500"></i>
                        <span>AI Assistant Penjurnalan</span>
                    </div>
                    <button @click="aiModalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <div class="mt-4 space-y-4">
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Ketik transaksi bisnis Anda dalam bahasa bebas, AI akan otomatis memilih akun Debit, Kredit, jenis
                        transaksi, dan nominalnya.
                    </p>

                    <div class="space-y-2">
                        <label class="block text-xs font-semibold text-slate-700">Contoh Kalimat Transaksi:</label>
                        <div class="flex flex-wrap gap-1.5">
                            <button type="button" @click="aiPrompt = 'Beli bensin dan parkir 50rb pakai kas'"
                                class="text-[11px] bg-slate-100 hover:bg-blue-50 text-slate-700 px-2.5 py-1 rounded-full border border-slate-200">"Beli
                                bensin 50rb pakai kas"</button>
                            <button type="button" @click="aiPrompt = 'Terima pendapatan catering 1.5jt via BCA'"
                                class="text-[11px] bg-slate-100 hover:bg-blue-50 text-slate-700 px-2.5 py-1 rounded-full border border-slate-200">"Terima
                                pendapatan 1.5jt via BCA"</button>
                            <button type="button" @click="aiPrompt = 'Bayar gaji karyawan 2.5jt transfer Mandiri'"
                                class="text-[11px] bg-slate-100 hover:bg-blue-50 text-slate-700 px-2.5 py-1 rounded-full border border-slate-200">"Bayar
                                gaji 2.5jt"</button>
                        </div>
                    </div>

                    <div class="relative">
                        <textarea x-model="aiPrompt" rows="3"
                            placeholder="Misal: Bayar tagihan listrik kantor 500rb tunai..."
                            class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"></textarea>
                    </div>

                    <div x-show="aiResultText"
                        class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-xs text-emerald-800 flex items-center space-x-2">
                        <i class="fa-solid fa-circle-check text-emerald-600"></i>
                        <span x-text="aiResultText"></span>
                    </div>

                    <div class="flex justify-end space-x-3 pt-2">
                        <button type="button" @click="aiModalOpen = false"
                            class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg">
                            Batal
                        </button>
                        <button type="button" @click="askAi()" :disabled="aiLoading"
                            class="px-5 py-2 text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow-sm flex items-center space-x-2">
                            <i x-show="aiLoading" class="fa-solid fa-spinner fa-spin"></i>
                            <span x-text="aiLoading ? 'Menganalisis...' : 'Terapkan ke Form'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection