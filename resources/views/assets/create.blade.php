@extends('layouts.app')

@section('title', 'Tambah Aset Tetap')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{
    assetTypes: {{ json_encode($assetTypes) }},
    selectedType: '{{ old('asset_type_id', '') }}',
    isDepreciated: true,
    acquisitionCostRaw: 0,
    salvageValueRaw: 0,
    usefulYears: 4,
    usefulMonths: 0,
    selectedAssetAccount: '{{ old('asset_account_id', '') }}',
    selectedExpenseAccount: '{{ old('expense_account_id', '') }}',
    selectedAccumulatedAccount: '{{ old('accumulated_depreciation_account_id', '') }}',

    init() {
        if (this.selectedType) {
            this.onTypeChange();
        }
    },

    onTypeChange() {
        const t = this.assetTypes.find(x => x.id == this.selectedType);
        if (t) {
            this.isDepreciated = Boolean(t.is_depreciated);
            if (t.useful_life_years !== null) {
                this.usefulYears = t.useful_life_years;
            }
            if (t.asset_account_id) {
                this.selectedAssetAccount = t.asset_account_id;
            }
            if (t.expense_account_id) {
                this.selectedExpenseAccount = t.expense_account_id;
            }
            if (t.accumulated_account_id) {
                this.selectedAccumulatedAccount = t.accumulated_account_id;
            }
        }
    },

    formatNumber(el, field) {
        let clean = el.value.replace(/[^0-9]/g, '');
        let num = clean ? parseInt(clean, 10) : 0;
        el.value = num > 0 ? num.toLocaleString('id-ID') : '0';
        if (field === 'cost') this.acquisitionCostRaw = num;
        if (field === 'salvage') this.salvageValueRaw = num;
    },

    formatCode(el) {
        el.value = el.value.toLowerCase().replace(/[^a-z0-9_]/g, '');
    },

    get estimatedMonthly() {
        if (!this.isDepreciated) return 0;
        const totalMonths = (parseInt(this.usefulYears) || 0) * 12 + (parseInt(this.usefulMonths) || 0);
        if (totalMonths <= 0) return 0;
        const netCost = Math.max(0, this.acquisitionCostRaw - this.salvageValueRaw);
        return Math.round(netCost / totalMonths);
    },

    get totalMonthsCount() {
        return (parseInt(this.usefulYears) || 0) * 12 + (parseInt(this.usefulMonths) || 0);
    },

    formatRupiah(num) {
        return 'Rp ' + Number(num).toLocaleString('id-ID');
    }
}">

    <!-- Card Form Utama -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 bg-slate-50/70 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-base sm:text-lg font-bold text-slate-800">Tambah Aset Tetap Baru</h2>
                <p class="text-xs text-slate-500 mt-0.5">Catat perolehan aktiva tetap dan atur skema depresiasi berkala</p>
            </div>
            <a href="{{ route('assets.index') }}" class="open-in-tab text-xs text-blue-600 hover:text-blue-800 font-bold flex items-center space-x-1.5 px-3 py-1.5 rounded-xl bg-white border border-slate-200 shadow-2xs hover:bg-slate-50 transition" data-tab-title="Daftar Aset" data-tab-icon="fa-solid fa-boxes-stacked">
                <i class="fa-solid fa-arrow-left text-xs"></i>
                <span>Kembali ke Daftar Aset</span>
            </a>
        </div>

        <form method="POST" action="{{ route('assets.store') }}" enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf

            <!-- Section 1: Tipe Aset & Tanggal (Perolehan & Pakai) -->
            <div class="p-4 bg-gradient-to-r from-blue-50/50 via-indigo-50/30 to-slate-50 rounded-2xl border border-blue-100 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Tipe Aset Selector -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-semibold text-slate-700">Tipe / Kategori Aset</label>
                            <a href="{{ route('master.asset_types') }}" class="open-in-tab text-[10px] text-blue-600 hover:text-blue-800 hover:underline font-bold flex items-center cursor-pointer" data-tab-title="Master Tipe Aset" data-tab-icon="fa-solid fa-tags">
                                <i class="fa-solid fa-plus text-[8px] mr-1"></i> Kelola Tipe
                            </a>
                        </div>
                        <select name="asset_type_id" x-model="selectedType" @change="onTypeChange()"
                                class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-blue-500 focus:outline-none shadow-2xs">
                            <option value="">-- Pilih Tipe Aset --</option>
                            @foreach($assetTypes as $type)
                                <option value="{{ $type->id }}">
                                    🏷️ {{ $type->name }} {{ $type->code ? '('.$type->code.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <span class="text-[10px] text-slate-400 mt-1 block">Memilih tipe aset otomatis mengisi akun depresiasi</span>
                    </div>

                    <!-- Tanggal Akuisisi / Beli -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Beli (Akuisisi) <span class="text-rose-500">*</span></label>
                        <input type="date" name="acquisition_date" value="{{ old('acquisition_date', $today) }}" required
                               class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-blue-500 focus:outline-none shadow-2xs">
                        <span class="text-[10px] text-slate-400 mt-1 block">Tanggal faktur/pembelian aset</span>
                    </div>

                    <!-- Tanggal Mulai Pakai / Disusutkan -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Mulai Pakai</label>
                        <input type="date" name="usage_date" value="{{ old('usage_date', $today) }}"
                               class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-blue-500 focus:outline-none shadow-2xs">
                        <span class="text-[10px] text-slate-400 mt-1 block">Depresiasi dihitung mulai tanggal ini</span>
                    </div>
                </div>
            </div>

            <!-- Section 2: Identitas & Nilai Perolehan -->
            <div class="space-y-4">
                <!-- Kode Asset & Nama Asset -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Kode Aset <span class="text-rose-500">*</span></label>
                        <input type="text" name="code" value="{{ old('code') }}" @input="formatCode($el)" placeholder="contoh: motorbeat2026 (tanpa spasi)" required
                               class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold font-mono focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Aset Tetap <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="Contoh: Sepeda Motor Honda Beat Street" required
                               class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-semibold text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                </div>

                <!-- Deskripsi -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Keterangan / Spesifikasi</label>
                    <textarea name="description" rows="2" placeholder="Contoh: Plat nomor B 1234 XYZ, warna hitam doff, untuk operasional kurir toko..."
                              class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">{{ old('description') }}</textarea>
                </div>

                <!-- Akun Asset Tetap & Nilai Perolehan -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-semibold text-slate-700">Akun Harta Tetap (Neraca) <span class="text-rose-500">*</span></label>
                            <a href="{{ route('master.accounts') }}" class="open-in-tab text-[10px] text-blue-600 hover:text-blue-800 hover:underline font-bold flex items-center cursor-pointer" data-tab-title="Master Akun (COA)" data-tab-icon="fa-solid fa-database">
                                <i class="fa-solid fa-plus text-[8px] mr-1"></i> COA
                            </a>
                        </div>
                        <select name="asset_account_id" x-model="selectedAssetAccount" required
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">-- Pilih Akun Harta Tetap --</option>
                            @foreach($assetAccounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Harga Beli / Nilai Perolehan (Rp) <span class="text-rose-500">*</span></label>
                        <input type="text" name="acquisition_cost" value="{{ old('acquisition_cost', '0') }}" @input="formatNumber($el, 'cost')" required
                               class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-black text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                </div>

                <!-- Akun Pajak & Nilai Pajak (Opsional) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Akun Pajak Masukan (Opsional)</label>
                        <select name="tax_account_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">-- Tanpa Pajak --</option>
                            @foreach($taxAccounts as $acc)
                                <option value="{{ $acc->id }}" {{ old('tax_account_id') == $acc->id ? 'selected' : '' }}>{{ $acc->code }} - {{ $acc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nilai Pajak (IDR Opsional)</label>
                        <input type="text" name="tax_amount" value="{{ old('tax_amount', '0') }}" @input="formatNumber($el, 'tax')"
                               class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                </div>

                <!-- Akun Sumber Dana (Kas/Bank/Hutang) & Foto -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Sumber Dana (Akun Dikreditkan) <span class="text-rose-500">*</span></label>
                        <select name="credited_account_id" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">-- Pilih Akun Kas/Bank/Hutang --</option>
                            @foreach($creditedAccounts as $acc)
                                <option value="{{ $acc->id }}" {{ old('credited_account_id', $creditedAccounts->firstWhere('code', '1-10001')?->id) == $acc->id ? 'selected' : '' }}>
                                    {{ $acc->code }} - {{ $acc->name }} ({{ $acc->category }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Foto Fisik Aset (Opsional)</label>
                        <input type="file" name="photo" accept="image/*"
                               class="w-full px-3 py-1 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-600 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>
                </div>
            </div>

            <!-- Section 3: Skema Penyusutan Bulanan (Depresiasi) -->
            <div class="pt-5 border-t border-slate-200">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold text-slate-800 flex items-center">
                        <i class="fa-solid fa-calculator text-blue-600 mr-2"></i>
                        <span>Skema Penyusutan / Depresiasi</span>
                    </h3>
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" id="is_depreciated" name="is_depreciated" value="1" x-model="isDepreciated"
                               class="w-4 h-4 text-blue-600 bg-slate-100 border-slate-300 rounded focus:ring-blue-500">
                        <span class="text-xs font-bold text-slate-700">Aktifkan Penyusutan</span>
                    </label>
                </div>

                <!-- Sub-form Penyusutan (Muncul saat Checkbox Dicentang) -->
                <div x-show="isDepreciated" x-cloak class="p-5 bg-gradient-to-br from-blue-50/60 via-indigo-50/40 to-slate-50 rounded-2xl border border-blue-200/80 space-y-4 shadow-2xs">
                    
                    <!-- Live Preview Badge -->
                    <div class="p-3 bg-white rounded-xl border border-blue-200 flex flex-wrap items-center justify-between gap-3 shadow-2xs">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-xs shadow-sm">
                                <i class="fa-solid fa-chart-line-down"></i>
                            </div>
                            <div>
                                <div class="text-[11px] text-slate-500 font-semibold">Estimasi Beban Depresiasi Akhir Bulan</div>
                                <div class="text-sm font-extrabold text-blue-900" x-text="formatRupiah(estimatedMonthly) + ' / bulan'"></div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-[11px] text-slate-500 font-semibold">Total Masa Manfaat</div>
                            <div class="text-xs font-bold text-indigo-700" x-text="totalMonthsCount + ' Bulan (' + (usefulYears || 0) + ' Tahun ' + (usefulMonths || 0) + ' Bulan)'"></div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Metode Depresiasi</label>
                            <select name="depreciation_method" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                <option value="straight_line">Garis Lurus (Straight Line) - Nilai Tetap Tiap Bulan</option>
                                <option value="declining_balance">Saldo Menurun (Declining Balance)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Masa Manfaat</label>
                            <div class="grid grid-cols-2 gap-2">
                                <div class="relative">
                                    <input type="number" name="useful_life_years" min="0" max="50" x-model="usefulYears"
                                           placeholder="0"
                                           class="w-full px-3 py-2 pr-12 bg-white border border-slate-300 rounded-xl text-xs font-bold text-slate-800 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                    <span class="absolute right-3 top-2 text-[11px] text-slate-400 font-semibold pointer-events-none">Tahun</span>
                                </div>
                                <div class="relative">
                                    <input type="number" name="useful_life_months" min="0" max="11" x-model="usefulMonths"
                                           placeholder="0"
                                           class="w-full px-3 py-2 pr-12 bg-white border border-slate-300 rounded-xl text-xs font-bold text-slate-800 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                    <span class="absolute right-3 top-2 text-[11px] text-slate-400 font-semibold pointer-events-none">Bulan</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pemilihan Akun Beban & Akumulasi Penyusutan -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Akun Beban Penyusutan (Laba Rugi)</label>
                            <select name="expense_account_id" x-model="selectedExpenseAccount"
                                    class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-semibold text-rose-800 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                <option value="">-- Pilih Akun Beban Penyusutan --</option>
                                @foreach($expenseAccounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                                @endforeach
                            </select>
                            <span class="text-[10px] text-slate-400 mt-1 block">Contoh: <strong>6-60502 Depresiasi - Kendaraan</strong></span>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Akun Akumulasi Penyusutan (Neraca)</label>
                            <select name="accumulated_depreciation_account_id" x-model="selectedAccumulatedAccount"
                                    class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-semibold text-indigo-800 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                <option value="">-- Pilih Akun Akumulasi Penyusutan --</option>
                                @foreach($accumulatedAccounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                                @endforeach
                            </select>
                            <span class="text-[10px] text-slate-400 mt-1 block">Contoh: <strong>1-10753 Akumulasi Penyusutan - Kendaraan</strong></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Nilai Sisa / Residu (Rp)</label>
                            <input type="text" name="salvage_value" value="0" @input="formatNumber($el, 'salvage')"
                                   class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-xl text-xs font-bold text-slate-800 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <span class="text-[10px] text-slate-400 mt-1 block">Perkiraan nilai jual sisa di akhir masa manfaat (opsional, default 0)</span>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Berakhir Penyusutan (Opsional)</label>
                            <input type="date" name="depreciation_end_date"
                                   class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <span class="text-[10px] text-slate-400 mt-1 block">Otomatis dihitung dari tanggal pakai + masa manfaat</span>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-4 flex items-center justify-end space-x-3 border-t border-slate-100">
                <a href="{{ route('assets.index') }}" class="open-in-tab px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition" data-tab-title="Daftar Aset" data-tab-icon="fa-solid fa-boxes-stacked">
                    Batal
                </a>
                <button type="submit" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold text-xs px-8 py-2.5 rounded-xl shadow-md shadow-blue-500/20 transition cursor-pointer">
                    Simpan & Catat Jurnal Perolehan
                </button>
            </div>

        </form>
    </div>

</div>
@endsection
