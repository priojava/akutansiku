@extends('layouts.app')

@section('title', 'Tambah Aset')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{
    isDepreciated: false,
    formatNumber(el) {
        let clean = el.value.replace(/[^0-9]/g, '');
        el.value = clean ? parseInt(clean, 10).toLocaleString('id-ID') : '0';
    },
    formatCode(el) {
        el.value = el.value.toLowerCase().replace(/[^a-z0-9_]/g, '');
    }
}">

    <!-- Card Form Utama matching mockup screenshot 1 -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <h2 class="text-lg font-bold text-slate-800">Tambah Aset</h2>
            <a href="{{ route('assets.index') }}" class="text-xs text-blue-600 hover:underline font-semibold flex items-center space-x-1">
                <i class="fa-solid fa-arrow-left text-xs"></i>
                <span>Kembali ke Daftar Aset</span>
            </a>
        </div>

        <form method="POST" action="{{ route('assets.store') }}" enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf

            <!-- Tanggal Akuisisi -->
            <div class="max-w-xs">
                <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Akuisisi <span class="text-rose-500">*</span></label>
                <input type="date" name="acquisition_date" value="{{ old('acquisition_date', $today) }}" required
                       class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs font-medium focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            <!-- Kode Asset & Nama Asset -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kode Asset <span class="text-rose-500">*</span></label>
                    <input type="text" name="code" value="{{ old('code') }}" @input="formatCode($el)" placeholder="contoh : toyotainnova (tanpa spasi dan huruf besar)" required
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-xs font-medium font-mono focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Asset <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="Contoh Mobil Innova Toyota" required
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-xs font-medium focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
            </div>

            <!-- Deskripsi -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Deskripsi</label>
                <textarea name="description" rows="3" placeholder="Contoh Asset untuk kendaraan kantor sehari hari"
                          class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-xs font-medium focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">{{ old('description') }}</textarea>
            </div>

            <!-- Akun Asset Tetap & Nilai Perolehan -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Akun Asset Tetap <span class="text-rose-500">*</span></label>
                    <select name="asset_account_id" required
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-xs font-medium focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="">Pilih Akun Asset Tetap...</option>
                        @foreach($assetAccounts as $acc)
                            <option value="{{ $acc->id }}" {{ old('asset_account_id') == $acc->id ? 'selected' : '' }}>
                                {{ $acc->name }} ({{ $acc->code }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nilai Perolehan (IDR) <span class="text-rose-500">*</span></label>
                    <input type="text" name="acquisition_cost" value="{{ old('acquisition_cost', '0') }}" @input="formatNumber($el)" required
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-xs font-bold text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
            </div>

            <!-- Akun Pajak & Nilai Pajak (Opsional) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Akun Pajak (Opsional)</label>
                    <select name="tax_account_id"
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-xs font-medium focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="">Pilih Akun Pajak</option>
                        @foreach($taxAccounts as $taxAcc)
                            <option value="{{ $taxAcc->id }}" {{ old('tax_account_id') == $taxAcc->id ? 'selected' : '' }}>
                                {{ $taxAcc->name }} ({{ $taxAcc->code }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nilai Pajak (IDR) (Opsional)</label>
                    <input type="text" name="tax_amount" value="{{ old('tax_amount', '0') }}" @input="formatNumber($el)"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-xs font-bold text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
            </div>

            <!-- Akun Dikreditkan & Foto Asset -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Akun Dikreditkan <span class="text-rose-500">*</span></label>
                    <select name="credited_account_id" required
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-xs font-medium focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        @foreach($creditedAccounts as $credAcc)
                            <option value="{{ $credAcc->id }}" {{ old('credited_account_id') == $credAcc->id ? 'selected' : ($credAcc->code == '1-10001' ? 'selected' : '') }}>
                                {{ $credAcc->name }} ({{ $credAcc->code }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Foto Asset</label>
                    <input type="file" name="photo" accept="image/*"
                           class="w-full px-3 py-1.5 bg-slate-50 border border-slate-300 rounded-lg text-xs text-slate-600 file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>
            </div>

            <!-- Bagian Penyusutan -->
            <div class="pt-6 border-t border-slate-200">
                <h3 class="text-base font-bold text-slate-800 mb-3">Penyusutan</h3>
                
                <div class="flex items-center space-x-2">
                    <input type="checkbox" id="is_depreciated" name="is_depreciated" value="1" x-model="isDepreciated"
                           class="w-4 h-4 text-blue-600 bg-slate-100 border-slate-300 rounded focus:ring-blue-500">
                    <label for="is_depreciated" class="text-xs font-semibold text-slate-700 cursor-pointer">
                        Asset Depresiasi
                    </label>
                </div>

                <!-- Sub-form Penyusutan (Muncul saat Checkbox Dicentang) -->
                <div x-show="isDepreciated" x-cloak class="mt-5 p-5 bg-blue-50/50 rounded-xl border border-blue-100 space-y-4">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Metode Depresiasi</label>
                            <select name="depreciation_method" class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-lg text-xs font-medium focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                <option value="straight_line">Garis Lurus (Straight Line)</option>
                                <option value="declining_balance">Saldo Menurun (Declining Balance)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Masa Manfaat (Tahun)</label>
                            <input type="number" name="useful_life_years" min="1" max="50" value="{{ old('useful_life_years', 4) }}"
                                   class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-lg text-xs font-bold text-slate-800 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Akun Beban Penyusutan</label>
                            <select name="expense_account_id" class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-lg text-xs font-medium focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                <option value="">Pilih Akun Beban...</option>
                                @foreach($expenseAccounts as $expAcc)
                                    <option value="{{ $expAcc->id }}">{{ $expAcc->name }} ({{ $expAcc->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Akun Akumulasi Penyusutan</label>
                            <select name="accumulated_depreciation_account_id" class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-lg text-xs font-medium focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                <option value="">Pilih Akun Akumulasi...</option>
                                @foreach($accumulatedAccounts as $accAcc)
                                    <option value="{{ $accAcc->id }}">{{ $accAcc->name }} ({{ $accAcc->code }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Nilai Sisa / Residu (IDR)</label>
                            <input type="text" name="salvage_value" value="0" @input="formatNumber($el)"
                                   class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-lg text-xs font-bold text-slate-800 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Bulan Akhir Akumulasi (Opsional)</label>
                            <input type="date" name="depreciation_end_date"
                                   class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-lg text-xs font-medium focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>
                    </div>

                </div>
            </div>

            <!-- Submit Button matching mockup -->
            <div class="pt-4 flex items-center justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs px-8 py-2.5 rounded-lg shadow-sm transition">
                    Simpan
                </button>
            </div>

        </form>
    </div>

</div>
@endsection
