@extends('layouts.app')

@section('title', 'Master Tutup Buku')

@section('content')
<div class="space-y-6">

    <!-- Header & Action Button -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-base sm:text-lg font-bold text-slate-900">Master Tutup Buku (Period Closing)</h1>
            <p class="text-xs text-slate-500">Kunci transaksi periode berjalan, hitung neraca lajur, dan otomatis hasilkan jurnal penutup ke ekuitas.</p>
        </div>
        <a href="{{ route('closing.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition shadow-sm flex items-center space-x-2">
            <i class="fa-solid fa-plus text-xs"></i>
            <span>Buat Tutup Buku Baru</span>
        </a>
    </div>

    <!-- Table Tutup Buku (Matching Screenshot 2) -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-800">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-400 font-bold uppercase text-[10px]">
                    <tr>
                        <th class="py-3 px-4 w-12 text-center">NO</th>
                        <th class="py-3 px-4 w-44">PERIODE</th>
                        <th class="py-3 px-4">CATATAN</th>
                        <th class="py-3 px-4 text-right w-44">LABA RUGI BERSIH</th>
                        <th class="py-3 px-4 text-center w-36">ACTION</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($closingPeriods as $index => $closing)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3 px-4 text-center text-slate-400 font-medium">{{ $index + 1 }}</td>
                            <td class="py-3 px-4 font-bold text-blue-700">
                                {{ $closing->period_name ?: $closing->closing_date->format('d M Y') }}
                                <span class="block text-[10px] text-slate-400 font-normal mt-0.5">
                                    Cut-off: {{ $closing->closing_date->format('d/m/Y H:i:s') }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-700">
                                <span class="font-medium">{{ $closing->notes }}</span>
                                <span class="block text-[10px] text-slate-400 mt-0.5">
                                    Diinput: {{ $closing->creator->name ?? 'Admin' }} &bull; {{ $closing->created_at->diffForHumans() }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right font-bold {{ $closing->net_profit_after_tax >= 0 ? 'text-emerald-700' : 'text-rose-600' }}">
                                @if($closing->net_profit_after_tax < 0)
                                    (Rp {{ number_format(abs($closing->net_profit_after_tax), 0, ',', '.') }})
                                @else
                                    Rp {{ number_format($closing->net_profit_after_tax, 0, ',', '.') }}
                                @endif
                                <span class="block text-[10px] font-normal text-slate-400">
                                    Pajak: Rp {{ number_format($closing->tax_amount, 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center space-x-1.5">
                                    <a href="{{ route('closing.show', $closing->id) }}" class="p-1.5 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-lg text-xs font-semibold transition flex items-center space-x-1" title="Lihat Neraca Lajur & Jurnal Penutup">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                        <span class="hidden sm:inline">Detail</span>
                                    </a>
                                    @if(session('current_role', 'admin') === 'admin')
                                        <form method="POST" action="{{ route('closing.destroy', $closing->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan/menghapus data tutup buku periode ini? Jurnal penutup yang terbentuk akan otomatis dihapus.');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 bg-rose-50 text-rose-600 hover:bg-rose-100 rounded-lg text-xs font-semibold transition" title="Batalkan Tutup Buku">
                                                <i class="fa-solid fa-trash-can text-xs"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400">
                                <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2 text-lg">
                                    <i class="fa-solid fa-file-invoice"></i>
                                </div>
                                <p class="font-medium">Belum ada riwayat tutup buku yang tercatat.</p>
                                <p class="text-xs text-slate-400 mt-1">Klik tombol <strong class="text-blue-600">"Buat Tutup Buku Baru"</strong> untuk melakukan proses penutupan buku periode akuntansi.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
