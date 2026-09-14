@extends('layouts.app')

@section('title', 'Detail Tutup Buku - ' . ($closingPeriod->period_name ?: 'Periode'))

@section('content')
<div class="space-y-6">

    <!-- Top Header -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-5 shadow-sm flex flex-wrap items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('closing.index') }}" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h1 class="text-base sm:text-lg font-bold text-slate-900">
                    Detail Tutup Buku: {{ $closingPeriod->period_name }}
                </h1>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                    Selesai Terjurnal
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1 ml-8">
                Cut-off: {{ $closingPeriod->closing_date->format('d F Y H:i:s') }} &bull; Diinput oleh: {{ $closingPeriod->creator->name ?? 'Admin' }}
            </p>
        </div>

        <div class="flex items-center space-x-2">
            <button onclick="window.print()" class="px-3.5 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-lg shadow-sm transition flex items-center space-x-1.5">
                <i class="fa-solid fa-print text-slate-500"></i>
                <span>Cetak Lembar Tutup Buku</span>
            </button>
            <a href="{{ route('closing.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition">
                Kembali
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 uppercase block">Total Pendapatan</span>
            <span class="text-sm sm:text-base font-bold text-slate-800 mt-1 block">
                Rp {{ number_format($closingPeriod->total_revenue, 0, ',', '.') }}
            </span>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 uppercase block">Total Beban</span>
            <span class="text-sm sm:text-base font-bold text-slate-800 mt-1 block">
                Rp {{ number_format($closingPeriod->total_expense, 0, ',', '.') }}
            </span>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 uppercase block">Beban Pajak</span>
            <span class="text-sm sm:text-base font-bold text-slate-800 mt-1 block">
                Rp {{ number_format($closingPeriod->tax_amount, 0, ',', '.') }}
            </span>
        </div>
        <div class="bg-blue-50/70 p-4 rounded-xl border border-blue-200 shadow-sm">
            <span class="text-[11px] font-bold text-blue-600 uppercase block">Laba/Rugi Bersih Ditransfer</span>
            <span class="text-sm sm:text-base font-bold {{ $closingPeriod->net_profit_after_tax >= 0 ? 'text-emerald-700' : 'text-rose-700' }} mt-1 block">
                @if($closingPeriod->net_profit_after_tax < 0)
                    (Rp {{ number_format(abs($closingPeriod->net_profit_after_tax), 0, ',', '.') }})
                @else
                    Rp {{ number_format($closingPeriod->net_profit_after_tax, 0, ',', '.') }}
                @endif
            </span>
        </div>
    </div>

    <!-- Double-Entry Closing Journal Voucher -->
    @if($closingPeriod->journalEntry)
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-4 bg-slate-50/80 border-b border-slate-200 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <i class="fa-solid fa-receipt text-blue-600"></i>
                    <h3 class="font-bold text-slate-900 text-xs sm:text-sm">Ayat Jurnal Penutup (Closing Journal Entry)</h3>
                </div>
                <span class="font-mono text-xs font-bold text-blue-700">{{ $closingPeriod->journalEntry->entry_number }}</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-800">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-400 font-bold uppercase text-[10px]">
                        <tr>
                            <th class="py-2.5 px-4 w-32">KODE AKUN</th>
                            <th class="py-2.5 px-4">NAMA AKUN</th>
                            <th class="py-2.5 px-4">KETERANGAN / MEMO</th>
                            <th class="py-2.5 px-4 text-right w-36">DEBIT</th>
                            <th class="py-2.5 px-4 text-right w-36">KREDIT</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-mono text-[11px]">
                        @php $totD = 0; $totC = 0; @endphp
                        @foreach($closingPeriod->journalEntry->items as $ji)
                            @php $totD += $ji->debit; $totC += $ji->credit; @endphp
                            <tr class="hover:bg-slate-50/75">
                                <td class="py-2 px-4 text-slate-500 font-semibold">{{ $ji->account->code ?? '-' }}</td>
                                <td class="py-2 px-4 font-sans font-bold text-slate-800">{{ $ji->account->name ?? '-' }}</td>
                                <td class="py-2 px-4 font-sans text-slate-600 text-xs">{{ $ji->memo }}</td>
                                <td class="py-2 px-4 text-right {{ $ji->debit > 0 ? 'text-slate-900 font-bold' : 'text-slate-300' }}">
                                    {{ $ji->debit > 0 ? 'Rp ' . number_format($ji->debit, 0, ',', '.') : '-' }}
                                </td>
                                <td class="py-2 px-4 text-right {{ $ji->credit > 0 ? 'text-slate-900 font-bold' : 'text-slate-300' }}">
                                    {{ $ji->credit > 0 ? 'Rp ' . number_format($ji->credit, 0, ',', '.') : '-' }}
                                </td>
                            </tr>
                        @endforeach
                        <tr class="bg-slate-50 font-bold text-slate-900 border-t-2 border-slate-200">
                            <td colspan="3" class="py-2.5 px-4 font-sans">Total Jurnal Penutup (Seimbang)</td>
                            <td class="py-2.5 px-4 text-right text-emerald-700">Rp {{ number_format($totD, 0, ',', '.') }}</td>
                            <td class="py-2.5 px-4 text-right text-emerald-700">Rp {{ number_format($totC, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</div>
@endsection
