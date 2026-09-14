@extends('layouts.app')

@section('title', 'Invoice ' . $invoice->invoice_number)

@section('content')
<div class="max-w-3xl mx-auto space-y-6 animate-in fade-in duration-300">

    <div class="flex items-center justify-between no-print">
        <a href="{{ route('subscription.index') }}" class="inline-flex items-center space-x-1.5 text-xs font-semibold text-slate-600 hover:text-blue-600 transition">
            <i class="fa-solid fa-arrow-left text-[11px]"></i>
            <span>Kembali ke Status Langganan</span>
        </a>

        <div class="flex items-center space-x-2">
            <button onclick="window.print()" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold shadow-sm transition flex items-center space-x-1.5 cursor-pointer">
                <i class="fa-solid fa-print text-xs"></i>
                <span>Cetak / PDF</span>
            </button>
        </div>
    </div>

    <!-- INVOICE CARD CONTAINER -->
    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-xl p-8 sm:p-10 relative overflow-hidden" id="printableInvoice">
        
        <!-- PAID STAMP WATERMARK -->
        @if($invoice->status === 'paid')
            <div class="absolute -right-8 -top-8 w-44 h-44 border-8 border-emerald-500/20 rounded-full flex items-center justify-center pointer-events-none rotate-12">
                <span class="text-2xl font-black text-emerald-500/30 uppercase tracking-widest">LUNAS</span>
            </div>
        @endif

        <!-- TOP BAR: SAAS PROVIDER & INVOICE META -->
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-6 pb-8 border-b border-slate-100">
            <div>
                <div class="flex items-center space-x-2">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-blue-700 via-indigo-600 to-blue-500 text-white flex items-center justify-center font-bold text-sm shadow-md">
                        <i class="fa-solid fa-shapes text-white text-base"></i>
                    </div>
                    <div>
                        <span class="text-base font-extrabold text-slate-900 tracking-tight block">Akuntansi Cloud SaaS</span>
                        <span class="text-[10px] text-slate-400">Enterprise Accounting Platform</span>
                    </div>
                </div>
                <div class="text-[11px] text-slate-500 mt-3 space-y-0.5">
                    <p>PT Teknologi Akuntansi Cipta</p>
                    <p>billing@akuntansicloud.id &bull; +62 811-2233-4455</p>
                    <p>Jakarta, Indonesia</p>
                </div>
            </div>

            <div class="text-left sm:text-right space-y-1">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Invoice Resmi</span>
                <h1 class="text-xl sm:text-2xl font-black font-mono text-slate-900">{{ $invoice->invoice_number }}</h1>
                <div class="text-xs text-slate-500">
                    <span>Tanggal:</span>
                    <strong class="text-slate-800">{{ $invoice->created_at->format('d F Y H:i') }}</strong>
                </div>
                <div>
                    @if($invoice->status === 'paid')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300">
                            <i class="fa-solid fa-check-circle mr-1"></i> LUNAS (PAID)
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold bg-amber-100 text-amber-800 border border-amber-300">
                            MENUNGGU PEMBAYARAN
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- BILLED TO -->
        <div class="py-6 border-b border-slate-100">
            <span class="text-[10px] font-bold uppercase text-slate-400 tracking-wider">Ditagihkan Kepada (Tenant):</span>
            <div class="mt-1 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">{{ $company->name }}</h3>
                    <p class="text-xs text-slate-500">{{ $company->city ?: 'Indonesia' }} &bull; {{ $company->email ?: 'admin@tenant.com' }}</p>
                </div>
                <div class="text-xs text-slate-600">
                    <span class="text-slate-400">Metode Bayar:</span>
                    <strong class="text-slate-800">{{ $invoice->payment_method ?: 'Transfer Bank' }}</strong>
                </div>
            </div>
        </div>

        <!-- LINE ITEMS TABLE -->
        <div class="py-6 border-b border-slate-100">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-slate-400 uppercase text-[10px] font-bold border-b border-slate-100">
                        <th class="pb-3">Deskripsi Item</th>
                        <th class="pb-3 text-center">Durasi</th>
                        <th class="pb-3 text-right">Harga</th>
                        <th class="pb-3 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr>
                        <td class="py-4">
                            <span class="font-bold text-slate-900 block text-sm">Paket Langganan {{ ucfirst($invoice->plan_name) }}</span>
                            <span class="text-[11px] text-slate-500">{{ $invoice->description }}</span>
                        </td>
                        <td class="py-4 text-center font-medium text-slate-700">{{ $invoice->duration_months ?: 1 }} Bulan</td>
                        <td class="py-4 text-right font-mono text-slate-700">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</td>
                        <td class="py-4 text-right font-bold font-mono text-slate-900">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- TOTALS -->
        <div class="py-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="text-xs text-slate-400 space-y-1">
                <p>Terima kasih telah mempercayai platform akuntansi kami.</p>
                <p>Invoice ini dibuat secara komputerisasi dan sah tanpa tanda tangan basah.</p>
            </div>

            <div class="w-full sm:w-64 space-y-2 text-xs">
                <div class="flex justify-between text-slate-600">
                    <span>Subtotal:</span>
                    <span class="font-mono">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>PPN (0%):</span>
                    <span class="font-mono">Rp 0</span>
                </div>
                <div class="flex justify-between text-slate-900 font-extrabold text-sm pt-2 border-t border-slate-200">
                    <span>Total Tagihan:</span>
                    <span class="font-mono text-blue-600">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

    </div>

</div>

<style>
@media print {
    body {
        background-color: #ffffff !important;
    }
    .no-print, header, aside {
        display: none !important;
    }
    #printableInvoice {
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
    }
}
</style>
@endsection
