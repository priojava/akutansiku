@extends('layouts.app')

@section('title', 'Buat Tutup Buku Baru')

@section('content')
<div class="space-y-6" x-data="{
    closingDate: '{{ $closingDate }}',
    labaSebelumPajak: {{ (float) $worksheet['totals']['laba_rugi'] }},
    taxAmountRaw: '0',
    getTaxAmount() {
        const clean = this.taxAmountRaw.toString().replace(/\./g, '').replace(/,/g, '');
        return Number(clean) || 0;
    },
    formatRupiah(val) {
        return Number(val).toLocaleString('id-ID');
    },
    getLabaSetelahPajak() {
        return this.labaSebelumPajak - this.getTaxAmount();
    },
    formatTaxInput(e) {
        let val = e.target.value.replace(/[^0-9]/g, '');
        if (!val) {
            this.taxAmountRaw = '0';
            return;
        }
        this.taxAmountRaw = Number(val).toLocaleString('id-ID');
    },
    updateDate(newDate) {
        window.location.href = '{{ route('closing.create') }}?closing_date=' + newDate;
    }
}">

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        
        <!-- Top Period Bar (Matching Screenshot 1) -->
        <div class="p-4 sm:p-5 border-b border-slate-200 bg-white flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center space-x-2 text-xs sm:text-sm text-slate-800 font-medium">
                <span>Periode tutup buku :</span>
                <div class="flex items-center bg-slate-50 border border-slate-300 rounded-lg px-2.5 py-1 text-xs">
                    <input type="date" 
                           value="{{ $closingDate }}" 
                           @change="updateDate($event.target.value)" 
                           class="bg-transparent focus:outline-none font-semibold text-blue-700 cursor-pointer">
                    <span class="ml-1 text-slate-400 font-mono">23:59:59:000</span>
                </div>
            </div>
            <a href="{{ route('closing.index') }}" class="text-xs text-slate-500 hover:text-slate-700 flex items-center space-x-1">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Kembali ke Daftar</span>
            </a>
        </div>

        <form method="POST" action="{{ route('closing.store') }}">
            @csrf
            <input type="hidden" name="closing_date" value="{{ $closingDate }}">

            <!-- Worksheet 6-Column Matrix Table (Matching Screenshot 1) -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-800">
                    <thead>
                        <tr class="border-b border-slate-200 text-slate-400 uppercase text-[10px] font-bold bg-slate-50/50">
                            <th colspan="2" class="py-2 px-3"></th>
                            <th colspan="2" class="py-2 px-3 text-center border-l border-slate-200">NERACA SALDO</th>
                            <th colspan="2" class="py-2 px-3 text-center border-l border-slate-200">LABA RUGI</th>
                            <th colspan="2" class="py-2 px-3 text-center border-l border-slate-200">NERACA</th>
                        </tr>
                        <tr class="border-b border-slate-200 text-slate-400 uppercase text-[10px] font-bold">
                            <th class="py-2 px-3 w-28">CODE</th>
                            <th class="py-2 px-3">AKUN</th>
                            <th class="py-2 px-3 text-right w-28 border-l border-slate-100">DEBIT</th>
                            <th class="py-2 px-3 text-right w-28">CREDIT</th>
                            <th class="py-2 px-3 text-right w-28 border-l border-slate-100">DEBIT</th>
                            <th class="py-2 px-3 text-right w-28">CREDIT</th>
                            <th class="py-2 px-3 text-right w-28 border-l border-slate-100">DEBIT</th>
                            <th class="py-2 px-3 text-right w-28">CREDIT</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-mono text-[11px]">
                        @forelse($worksheet['rows'] as $row)
                            <tr class="hover:bg-slate-50/75">
                                <td class="py-1.5 px-3 font-semibold text-slate-700">{{ $row['code'] }}</td>
                                <td class="py-1.5 px-3 font-sans text-slate-800">{{ $row['name'] }}</td>
                                
                                <!-- Neraca Saldo -->
                                <td class="py-1.5 px-3 text-right border-l border-slate-100 {{ $row['ns_debit'] > 0 ? 'text-slate-900 font-semibold' : 'text-slate-300' }}">
                                    {{ $row['ns_debit'] > 0 ? 'Rp ' . number_format($row['ns_debit'], 0, ',', '.') : 'Rp 0' }}
                                </td>
                                <td class="py-1.5 px-3 text-right {{ $row['ns_credit'] > 0 ? 'text-slate-900 font-semibold' : 'text-slate-300' }}">
                                    {{ $row['ns_credit'] > 0 ? 'Rp ' . number_format($row['ns_credit'], 0, ',', '.') : 'Rp 0' }}
                                </td>

                                <!-- Laba Rugi -->
                                <td class="py-1.5 px-3 text-right border-l border-slate-100 {{ $row['lr_debit'] > 0 ? 'text-slate-900 font-semibold' : 'text-slate-300' }}">
                                    {{ $row['lr_debit'] > 0 ? 'Rp ' . number_format($row['lr_debit'], 0, ',', '.') : 'Rp 0' }}
                                </td>
                                <td class="py-1.5 px-3 text-right {{ $row['lr_credit'] > 0 ? 'text-slate-900 font-semibold' : 'text-slate-300' }}">
                                    {{ $row['lr_credit'] > 0 ? 'Rp ' . number_format($row['lr_credit'], 0, ',', '.') : 'Rp 0' }}
                                </td>

                                <!-- Neraca -->
                                <td class="py-1.5 px-3 text-right border-l border-slate-100 {{ $row['nrc_debit'] > 0 ? 'text-slate-900 font-semibold' : 'text-slate-300' }}">
                                    {{ $row['nrc_debit'] > 0 ? 'Rp ' . number_format($row['nrc_debit'], 0, ',', '.') : 'Rp 0' }}
                                </td>
                                <td class="py-1.5 px-3 text-right {{ $row['nrc_credit'] > 0 ? 'text-slate-900 font-semibold' : 'text-slate-300' }}">
                                    {{ $row['nrc_credit'] > 0 ? 'Rp ' . number_format($row['nrc_credit'], 0, ',', '.') : 'Rp 0' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-6 text-center text-slate-400 font-sans">Tidak ada mutasi saldo sebelum tanggal yang dipilih.</td>
                            </tr>
                        @endforelse

                        <!-- Row Total (Matching Screenshot 1) -->
                        <tr class="font-bold text-slate-900 bg-slate-50 border-t-2 border-slate-200">
                            <td colspan="2" class="py-2.5 px-3 font-sans">Total</td>
                            <!-- Neraca Saldo -->
                            <td class="py-2.5 px-3 text-right border-l border-slate-200">Rp {{ number_format($worksheet['totals']['ns_debit'], 0, ',', '.') }}</td>
                            <td class="py-2.5 px-3 text-right">Rp {{ number_format($worksheet['totals']['ns_credit'], 0, ',', '.') }}</td>
                            <!-- Laba Rugi -->
                            <td class="py-2.5 px-3 text-right border-l border-slate-200">Rp {{ number_format($worksheet['totals']['lr_debit'], 0, ',', '.') }}</td>
                            <td class="py-2.5 px-3 text-right">Rp {{ number_format($worksheet['totals']['lr_credit'], 0, ',', '.') }}</td>
                            <!-- Neraca -->
                            <td class="py-2.5 px-3 text-right border-l border-slate-200">Rp {{ number_format($worksheet['totals']['nrc_debit'], 0, ',', '.') }}</td>
                            <td class="py-2.5 px-3 text-right">Rp {{ number_format($worksheet['totals']['nrc_credit'], 0, ',', '.') }}</td>
                        </tr>

                        <!-- Row Laba/Rugi Highlighted (Matching Screenshot 1 pink highlight) -->
                        <tr class="bg-rose-50/75 font-bold text-slate-900 border-t border-rose-100">
                            <td colspan="2" class="py-2.5 px-3 font-sans text-rose-950">Laba/Rugi</td>
                            <!-- Neraca Saldo -->
                            <td class="py-2.5 px-3 text-right border-l border-rose-100"></td>
                            <td class="py-2.5 px-3 text-right"></td>
                            <!-- Laba Rugi -->
                            <td class="py-2.5 px-3 text-right border-l border-rose-100"></td>
                            <td class="py-2.5 px-3 text-right text-rose-950 font-bold">
                                @if($worksheet['totals']['laba_rugi'] < 0)
                                    (Rp {{ number_format(abs($worksheet['totals']['laba_rugi']), 0, ',', '.') }})
                                @else
                                    Rp {{ number_format($worksheet['totals']['laba_rugi'], 0, ',', '.') }}
                                @endif
                            </td>
                            <!-- Neraca -->
                            <td class="py-2.5 px-3 text-right border-l border-rose-100"></td>
                            <td class="py-2.5 px-3 text-right"></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Form Closing Parameters (Matching Screenshot 1 Exactly) -->
            <div class="p-6 bg-white border-t border-slate-200 space-y-4">
                
                <!-- Row 1: Beban Pajak & Jumlah Pajak -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                    <div class="md:col-span-2 text-xs font-bold text-slate-800">
                        Beban Pajak
                    </div>
                    <div class="md:col-span-5">
                        <label class="block text-[11px] font-medium text-slate-500 mb-1">Akun Beban Pajak</label>
                        <x-searchable-account-select 
                            name="tax_expense_account_id" 
                            :options="$taxExpenseAccounts" 
                            :selected="old('tax_expense_account_id', $defaultTaxExpense?->id)" 
                            placeholder="Pilih Akun Beban Pajak..." 
                            :required="false" />
                    </div>
                    <div class="md:col-span-5">
                        <label class="block text-[11px] font-medium text-slate-500 mb-1">Jumlah Pajak</label>
                        <input type="text" 
                               name="tax_amount" 
                               x-model="taxAmountRaw" 
                               @input="formatTaxInput($event)" 
                               placeholder="0" 
                               class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 font-medium">
                    </div>
                </div>

                <!-- Row 2: Akun Hutang Pajak -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                    <div class="md:col-span-2"></div>
                    <div class="md:col-span-5">
                        <label class="block text-[11px] font-medium text-slate-500 mb-1">Akun Hutang Pajak</label>
                        <x-searchable-account-select 
                            name="tax_payable_account_id" 
                            :options="$taxPayableAccounts" 
                            :selected="old('tax_payable_account_id', $defaultTaxPayable?->id)" 
                            placeholder="Pilih Akun Hutang Pajak..." 
                            :required="false" />
                    </div>
                    <div class="md:col-span-5"></div>
                </div>

                <!-- Row 3: Laba/Rugi Bersih Setelah Pajak & Akun Laba Ditahan -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                    <div class="md:col-span-2 text-xs font-bold text-slate-800">
                        Laba/Rugi Bersih Setelah Pajak
                    </div>
                    <div class="md:col-span-5">
                        <label class="block text-[11px] font-medium text-slate-500 mb-1">Akun Laba Ditahan / Ekuitas</label>
                        <x-searchable-account-select 
                            name="retained_earnings_account_id" 
                            :options="$equityAccounts" 
                            :selected="old('retained_earnings_account_id', $defaultEquity?->id)" 
                            placeholder="Pilih Akun Laba Ditahan..." 
                            :required="true" />
                    </div>
                    <div class="md:col-span-5">
                        <label class="block text-[11px] font-medium text-slate-500 mb-1">Nominal Bersih Akhir</label>
                        <div class="w-full px-3 py-2 bg-slate-100 border border-slate-200 rounded-lg text-xs font-bold text-slate-900" 
                             x-text="getLabaSetelahPajak() < 0 ? '(Rp ' + formatRupiah(Math.abs(getLabaSetelahPajak())) + ')' : 'Rp ' + formatRupiah(getLabaSetelahPajak())">
                        </div>
                    </div>
                </div>

                <!-- Row 4: Catatan Opsional -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center pt-2">
                    <div class="md:col-span-2 text-xs font-bold text-slate-700">
                        Catatan
                    </div>
                    <div class="md:col-span-10">
                        <input type="text" name="notes" placeholder="Contoh: Tutup Buku Akhir Bulan Agustus 2026" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                </div>

                <!-- Row 5: Action Button (Matching Screenshot 1 Blue Simpan Button) -->
                <div class="pt-4 flex items-center space-x-3">
                    <button type="submit" class="px-8 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition shadow-md">
                        Simpan
                    </button>
                    <a href="{{ route('closing.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition">
                        Batal
                    </a>
                </div>

            </div>

        </form>

    </div>

</div>
@endsection
