@extends('layouts.app')

@section('title', 'Langganan & Tagihan')

@section('content')
<div class="max-w-5xl mx-auto space-y-6 animate-in fade-in duration-300" x-data="{ 
    renewModalOpen: false, 
    selectedPlan: 'standard', 
    selectedDuration: 1, 
    paymentMethod: '{{ $paymentSettings->midtrans_enabled ? 'midtrans' : ($paymentSettings->xendit_enabled ? 'xendit' : 'manual_bca') }}',
    isSubmitting: false,
    checkoutData: null,
    
    // Midtrans Sandbox State
    midtransModalOpen: false,
    midtransTab: 'va',
    selectedBank: 'bca',
    instructionTab: 'mbanking',
    copied: false,
    isSimulatingPay: false,
    paymentSuccess: false,
    successMessage: '',

    // Xendit State
    xenditModalOpen: false,
    xenditTab: 'va',
    xenditBank: 'bca',

    // Manual Transfer State
    manualModalOpen: false,

    standardPrice: {{ $plans['standard']->price_monthly ?? 99000 }},
    premiumPrice: {{ $plans['premium']->price_monthly ?? 249000 }},
    discount6: {{ ($plans['standard']->discount_6_months ?? 10) / 100 }},
    discount12: {{ ($plans['standard']->discount_12_months ?? 20) / 100 }},

    getAmount() {
        let monthly = this.selectedPlan === 'premium' ? this.premiumPrice : this.standardPrice;
        let discount = this.selectedDuration == 12 ? this.discount12 : (this.selectedDuration == 6 ? this.discount6 : 0);
        return (monthly * this.selectedDuration) * (1 - discount);
    },
    formatRp(val) {
        return 'Rp ' + Number(val || 0).toLocaleString('id-ID');
    },
    getVaNumber(bank) {
        let invId = this.checkoutData?.invoice_id || '{{ $latestInvoice?->id ?? 101 }}';
        let idPad = String(invId).padStart(4, '0');
        if (bank === 'bca') return '80777' + idPad + '492810';
        if (bank === 'mandiri') return '70012 9900' + idPad;
        if (bank === 'bni') return '8808' + idPad + '583921';
        if (bank === 'bri') return '12800' + idPad + '674829';
        if (bank === 'permata') return '8528' + idPad + '381924';
        return '80777' + idPad + '492810';
    },
    copyText(text) {
        navigator.clipboard.writeText(text);
        this.copied = true;
        setTimeout(() => this.copied = false, 2500);
    },
    async submitRenewal() {
        this.isSubmitting = true;
        try {
            const resp = await fetch('{{ route('subscription.renew') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    plan_name: this.selectedPlan,
                    duration_months: this.selectedDuration,
                    payment_method: this.paymentMethod
                })
            });
            const data = await resp.json();
            this.isSubmitting = false;

            if (data.success) {
                this.checkoutData = data;
                this.renewModalOpen = false;
                this.paymentSuccess = false;

                if (this.paymentMethod === 'midtrans') {
                    // Jika token resmi Midtrans Snap tersedia, HANYA buka popup resmi Midtrans Snap
                    if (data.snap_token && window.snap) {
                        window.snap.pay(data.snap_token, {
                            onSuccess: (result) => {
                                this.simulasiBayar();
                            },
                            onPending: (result) => {
                                alert('Transaksi pembayaran berhasil dibuat. Silakan selesaikan pembayaran sesuai instruksi bank.');
                                window.location.reload();
                            },
                            onError: (result) => {
                                alert('Pembayaran melalui Midtrans gagal atau dibatalkan.');
                            },
                            onClose: () => {
                                // Pengguna menutup popup Midtrans Snap
                            }
                        });
                    } else {
                        // Fallback: Jika belum ada API Key Midtrans resmi / mode offline, tampilkan simulator internal
                        this.midtransModalOpen = true;
                    }
                } else if (this.paymentMethod === 'xendit') {
                    this.xenditModalOpen = true;
                } else {
                    this.manualModalOpen = true;
                }
            } else {
                alert(data.message || 'Terjadi kesalahan saat memproses perpanjangan.');
            }
        } catch (e) {
            this.isSubmitting = false;
            alert('Gagal menghubungi server aplikasi.');
        }
    },
    async simulasiBayar() {
        if (!this.checkoutData?.invoice_id) return;
        this.isSimulatingPay = true;
        try {
            const resp = await fetch('{{ url('/subscription/pay-complete') }}/' + this.checkoutData.invoice_id, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            const data = await resp.json();
            this.isSimulatingPay = false;
            if (data.success) {
                this.paymentSuccess = true;
                this.successMessage = data.message;
                this.midtransModalOpen = true;
            } else {
                alert(data.message || 'Gagal verifikasi pembayaran.');
            }
        } catch (e) {
            this.isSimulatingPay = false;
            alert('Gagal menghubungi server verifikasi pembayaran.');
        }
    },
    finishAndReload() {
        window.location.reload();
    },
    resumePendingInvoice(token, invoiceId, invNumber, amount) {
        if (token && window.snap) {
            this.checkoutData = {
                invoice_id: invoiceId,
                invoice_number: invNumber,
                amount: amount
            };
            window.snap.pay(token, {
                onSuccess: (result) => {
                    this.simulasiBayar();
                },
                onPending: (result) => {
                    alert('Transaksi pembayaran Anda masih berstatus pending. Silakan lakukan pembayaran di bank pilihan Anda.');
                    window.location.reload();
                },
                onError: (result) => {
                    alert('Pembayaran melalui Midtrans gagal atau dibatalkan.');
                },
                onClose: () => {
                    // Closed popup
                }
            });
        } else {
            this.checkoutData = {
                invoice_id: invoiceId,
                invoice_number: invNumber,
                amount: amount
            };
            this.midtransModalOpen = true;
        }
    }
}">

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

    @if($pendingInvoice)
        <div class="p-5 rounded-2xl bg-amber-50 border border-amber-200/90 text-amber-900 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-start space-x-3.5">
                <div class="w-10 h-10 rounded-xl bg-amber-100 border border-amber-200 flex items-center justify-center text-amber-700 shrink-0 mt-0.5 shadow-inner">
                    <i class="fa-solid fa-clock-rotate-left text-lg"></i>
                </div>
                <div>
                    <div class="flex items-center space-x-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-amber-800">Menunggu Pembayaran</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-200/80 text-amber-900">Virtual Account Aktif</span>
                    </div>
                    <p class="text-xs text-amber-900 mt-1 font-medium">
                        Invoice <strong>{{ $pendingInvoice->invoice_number }}</strong> ({{ $pendingInvoice->description }}) sebesar <strong>Rp {{ number_format($pendingInvoice->amount, 0, ',', '.') }}</strong> sedang menunggu pembayaran.
                    </p>
                    <p class="text-[11px] text-amber-700/80 mt-0.5">
                        Kode Virtual Account / instruksi pembayaran sebelumnya masih aktif (belum expired). Klik tombol untuk melihat kode VA atau ganti jika ingin memilih bank lain.
                    </p>
                </div>
            </div>
            <div class="flex items-center space-x-2 shrink-0">
                @if($pendingInvoice->snap_token)
                    <button type="button" 
                        @click="resumePendingInvoice('{{ $pendingInvoice->snap_token }}', {{ $pendingInvoice->id }}, '{{ $pendingInvoice->invoice_number }}', {{ $pendingInvoice->amount }})" 
                        class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold shadow-sm shadow-amber-600/20 hover:shadow-md transition flex items-center space-x-1.5 cursor-pointer">
                        <i class="fa-solid fa-credit-card text-xs"></i>
                        <span>Lihat Kode VA</span>
                    </button>
                @endif
                <form action="{{ route('subscription.cancel_pending', $pendingInvoice->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan tagihan ini dan memilih bank / metode lain?')">
                    @csrf
                    <button type="submit" class="px-3.5 py-2 bg-white hover:bg-rose-50 text-rose-600 border border-rose-200 rounded-xl text-xs font-semibold shadow-xs transition flex items-center space-x-1 cursor-pointer">
                        <i class="fa-solid fa-xmark text-xs"></i>
                        <span>Ganti Bank / Batalkan</span>
                    </button>
                </form>
            </div>
        </div>
    @endif

    <!-- TOP HEADER PROFILE & RENEW ACTION -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-8 shadow-sm flex flex-col items-center justify-center text-center relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600"></div>

        <!-- Avatar / Icon -->
        <div class="w-20 h-20 rounded-2xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400 mb-3 shadow-inner">
            <i class="fa-regular fa-image text-3xl"></i>
        </div>

        <h2 class="text-xl font-bold text-slate-800">{{ $company->name ?? 'Dapur Gemoy' }}</h2>
        <div class="text-xs text-slate-500 mt-1 flex items-center space-x-2">
            <span>Status :</span>
            <span class="font-bold uppercase px-2.5 py-0.5 rounded-full text-[11px] {{ $company->subscription_plan === 'premium' ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-blue-50 text-blue-700 border border-blue-200' }}">
                {{ $company->subscription_plan === 'premium' ? 'PRO ENTERPRISE' : ($company->subscription_plan ?? 'STANDARD') }}
            </span>
        </div>

        <!-- Tombol Perpanjang Langganan -->
        <div class="mt-5">
            <button @click="renewModalOpen = true" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs sm:text-sm font-bold shadow-md shadow-blue-500/20 hover:shadow-lg hover:shadow-blue-500/30 transition-all duration-200 hover:scale-[1.02] active:scale-[0.98] flex items-center space-x-2 cursor-pointer">
                <i class="fa-solid fa-arrows-rotate text-xs"></i>
                <span>Perpanjang Langganan</span>
            </button>
        </div>
    </div>

    <!-- CARD 1: TAGIHAN TERBARU -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Tagihan Terbaru</h3>
        </div>

        <div class="p-6 space-y-4 text-xs">
            @if($latestInvoice)
                <div>
                    <span class="text-slate-400 block text-[11px] font-medium">Tanggal</span>
                    <span class="font-bold text-slate-800 text-sm">
                        {{ $latestInvoice->created_at->format('d M Y H:i:s') }}
                    </span>
                </div>

                <div>
                    <span class="text-slate-400 block text-[11px] font-medium">No Invoice</span>
                    <a href="{{ route('subscription.invoice', $latestInvoice->id) }}" class="font-bold text-blue-600 hover:text-blue-800 transition">
                        {{ $latestInvoice->invoice_number }}
                    </a>
                </div>

                <div>
                    <span class="text-slate-400 block text-[11px] font-medium">Keterangan</span>
                    <span class="font-bold text-slate-800">{{ $latestInvoice->description ?: 'Trial fitur premium' }}</span>
                </div>

                <div>
                    <span class="text-slate-400 block text-[11px] font-medium">Total</span>
                    <span class="font-extrabold text-slate-900 text-base">
                        Rp {{ number_format($latestInvoice->amount, 0, ',', '.') }}
                    </span>
                </div>

                <div>
                    <span class="text-slate-400 block text-[11px] font-medium">Status</span>
                    @if($latestInvoice->status === 'paid')
                        <span class="inline-flex items-center text-emerald-600 font-bold text-xs mt-0.5">
                            <i class="fa-solid fa-circle-check mr-1.5 text-emerald-500"></i> Pembayaran Sukses
                        </span>
                    @elseif($latestInvoice->status === 'pending')
                        <span class="inline-flex items-center text-amber-600 font-bold text-xs mt-0.5">
                            <i class="fa-solid fa-clock mr-1.5 text-amber-500"></i> Menunggu Pembayaran
                        </span>
                    @else
                        <span class="inline-flex items-center text-rose-600 font-bold text-xs mt-0.5">
                            <i class="fa-solid fa-circle-xmark mr-1.5 text-rose-500"></i> {{ ucfirst($latestInvoice->status) }}
                        </span>
                    @endif
                </div>

                <div class="pt-2">
                    <a href="{{ route('subscription.invoice', $latestInvoice->id) }}" class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg font-semibold text-xs transition border border-slate-200 shadow-2xs">
                        <i class="fa-solid fa-file-invoice text-slate-500 text-[11px]"></i>
                        <span>Lihat Invoice</span>
                    </a>
                </div>
            @else
                <div class="text-slate-400 py-4 text-center">Belum ada data tagihan.</div>
            @endif
        </div>
    </div>

    <!-- CARD 2: STATUS LANGGANAN -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Status Langganan</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="text-[10px] text-slate-400 uppercase tracking-wider font-bold border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3.5">Kode</th>
                        <th class="px-6 py-3.5">Produk</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5">Masa Aktif</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="hover:bg-slate-50/50 transition font-medium">
                        <td class="px-6 py-4 font-mono text-slate-600">100{{ $company->id }}</td>
                        <td class="px-6 py-4 capitalize font-bold text-slate-900">{{ $company->subscription_plan === 'premium' ? 'Pro Enterprise' : ($company->subscription_plan ?? 'Standard') }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $company->subscription_status === 'trial' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                                {{ ucfirst($company->subscription_status ?? 'Trial') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-slate-700">
                            @if($company->subscription_expires_at)
                                <span>Sampai {{ $company->remaining_days }} hari lagi</span>
                                <span class="text-[10px] text-slate-400 block">({{ $company->subscription_expires_at->isoFormat('D MMMM Y') }})</span>
                            @else
                                <span>Sampai 14 hari lagi</span>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- CARD 3: RIWAYAT PEMBAYARAN SEBELUMNYA -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Riwayat Pembayaran Sebelumnya</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="text-[10px] text-slate-400 uppercase tracking-wider font-bold border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3.5">No.</th>
                        <th class="px-6 py-3.5">Tanggal</th>
                        <th class="px-6 py-3.5">No. Invoice</th>
                        <th class="px-6 py-3.5 text-right">Total</th>
                        <th class="px-6 py-3.5">Tipe Pembayaran</th>
                        <th class="px-6 py-3.5">Keterangan</th>
                        <th class="px-6 py-3.5 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($invoices as $idx => $inv)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-6 py-4 text-slate-400 font-mono">{{ $idx + 1 }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $inv->created_at->format('d M Y') }}</td>
                            <td class="px-6 py-4 font-bold text-blue-600">
                                <a href="{{ route('subscription.invoice', $inv->id) }}" class="hover:underline">
                                    {{ $inv->invoice_number }}
                                </a>
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-slate-900">
                                Rp {{ number_format($inv->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-slate-600">{{ $inv->payment_method ?: 'Transfer' }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $inv->description ?: 'Langganan' }}</td>
                            <td class="px-6 py-4 text-center">
                                @if($inv->status === 'paid')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Sukses
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        {{ ucfirst($inv->status) }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-slate-400">Belum ada riwayat pembayaran.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL 1: PERPANJANG LANGGANAN (PILIH PAKET, DURASI & METODE) -->
    <div x-show="renewModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="renewModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="renewModalOpen = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="renewModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-slate-100">
                
                <form @submit.prevent="submitRenewal()">
                    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-5 text-white flex items-center justify-between">
                        <div class="flex items-center space-x-2.5">
                            <div class="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center">
                                <i class="fa-solid fa-crown text-amber-300 text-sm"></i>
                            </div>
                            <h3 class="font-bold text-base">Perpanjang Langganan SaaS</h3>
                        </div>
                        <button type="button" @click="renewModalOpen = false" class="text-white/80 hover:text-white text-lg cursor-pointer">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <div class="p-6 space-y-5 text-xs text-slate-700">
                        <!-- 1. Pilih Paket -->
                        <div>
                            <label class="block font-bold text-slate-800 mb-2">1. Pilih Paket Layanan</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="p-3.5 rounded-xl border-2 cursor-pointer transition flex flex-col justify-between" :class="selectedPlan === 'standard' ? 'border-blue-600 bg-blue-50/50 shadow-xs' : 'border-slate-200 hover:border-slate-300'">
                                    <input type="radio" name="plan_name" value="standard" x-model="selectedPlan" class="sr-only">
                                    <div>
                                        <span class="font-bold text-slate-900 block text-sm">Standard</span>
                                        <span class="text-[11px] text-slate-500">1 Perusahaan, Manual Entry, Laporan Standar</span>
                                    </div>
                                    <span class="font-extrabold text-blue-600 mt-2 block" x-text="formatRp(standardPrice) + ' / bln'">Rp 99.000 / bln</span>
                                </label>

                                <label class="p-3.5 rounded-xl border-2 cursor-pointer transition flex flex-col justify-between relative" :class="selectedPlan === 'premium' ? 'border-indigo-600 bg-indigo-50/50 shadow-xs' : 'border-slate-200 hover:border-slate-300'">
                                    <span class="absolute -top-2.5 right-3 px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-xs">POPULER</span>
                                    <input type="radio" name="plan_name" value="premium" x-model="selectedPlan" class="sr-only">
                                    <div>
                                        <span class="font-bold text-slate-900 block text-sm">Pro Enterprise</span>
                                        <span class="text-[11px] text-slate-500">Multi-Cabang, AI Otomatis, Aset & Tutup Buku</span>
                                    </div>
                                    <span class="font-extrabold text-indigo-600 mt-2 block" x-text="formatRp(premiumPrice) + ' / bln'">Rp 249.000 / bln</span>
                                </label>
                            </div>
                        </div>

                        <!-- 2. Pilih Durasi -->
                        <div>
                            <label class="block font-bold text-slate-800 mb-2">2. Pilih Durasi Berlangganan</label>
                            <div class="grid grid-cols-3 gap-2.5">
                                <label class="p-2.5 rounded-xl border text-center cursor-pointer transition" :class="selectedDuration == 1 ? 'border-blue-600 bg-blue-50 text-blue-700 font-bold' : 'border-slate-200 hover:bg-slate-50'">
                                    <input type="radio" name="duration_months" value="1" x-model="selectedDuration" class="sr-only">
                                    <span>1 Bulan</span>
                                </label>
                                <label class="p-2.5 rounded-xl border text-center cursor-pointer transition relative" :class="selectedDuration == 6 ? 'border-blue-600 bg-blue-50 text-blue-700 font-bold' : 'border-slate-200 hover:bg-slate-50'">
                                    <span class="text-[8px] px-1 bg-emerald-500 text-white rounded font-bold absolute -top-1.5 right-1">Hemat 10%</span>
                                    <input type="radio" name="duration_months" value="6" x-model="selectedDuration" class="sr-only">
                                    <span>6 Bulan</span>
                                </label>
                                <label class="p-2.5 rounded-xl border text-center cursor-pointer transition relative" :class="selectedDuration == 12 ? 'border-blue-600 bg-blue-50 text-blue-700 font-bold' : 'border-slate-200 hover:bg-slate-50'">
                                    <span class="text-[8px] px-1 bg-purple-600 text-white rounded font-bold absolute -top-1.5 right-1">Hemat 20%</span>
                                    <input type="radio" name="duration_months" value="12" x-model="selectedDuration" class="sr-only">
                                    <span>1 Tahun</span>
                                </label>
                            </div>
                        </div>

                        <!-- 3. Metode Pembayaran -->
                        <div>
                            <label class="block font-bold text-slate-800 mb-2">3. Pilih Metode Pembayaran</label>
                            
                            <div class="space-y-2.5">
                                @if($paymentSettings->midtrans_enabled)
                                    <label class="p-3.5 rounded-xl border-2 cursor-pointer transition flex items-center justify-between" :class="paymentMethod === 'midtrans' ? 'border-blue-600 bg-blue-50/50' : 'border-slate-200 hover:border-slate-300'">
                                        <div class="flex items-center space-x-3">
                                            <input type="radio" name="payment_method" value="midtrans" x-model="paymentMethod" class="text-blue-600 focus:ring-blue-500">
                                            <div>
                                                <div class="flex items-center space-x-2">
                                                    <span class="font-bold text-slate-900 text-xs">Midtrans Payment Gateway</span>
                                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold {{ $paymentSettings->midtrans_is_production ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                                        {{ $paymentSettings->midtrans_is_production ? 'Live' : 'Sandbox' }}
                                                    </span>
                                                </div>
                                                <p class="text-[11px] text-slate-500 mt-0.5">BCA, Mandiri, BNI, BRI, Permata VA, QRIS Gopay, Kartu Kredit</p>
                                            </div>
                                        </div>
                                        <div class="w-7 h-7 rounded-lg bg-blue-600 text-white flex items-center justify-center font-bold text-xs shadow-xs">
                                            M
                                        </div>
                                    </label>
                                @endif

                                @if($paymentSettings->xendit_enabled)
                                    <label class="p-3.5 rounded-xl border-2 cursor-pointer transition flex items-center justify-between" :class="paymentMethod === 'xendit' ? 'border-slate-900 bg-slate-100/70' : 'border-slate-200 hover:border-slate-300'">
                                        <div class="flex items-center space-x-3">
                                            <input type="radio" name="payment_method" value="xendit" x-model="paymentMethod" class="text-slate-900 focus:ring-slate-900">
                                            <div>
                                                <div class="flex items-center space-x-2">
                                                    <span class="font-bold text-slate-900 text-xs">Xendit Checkout Invoice</span>
                                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold {{ $paymentSettings->xendit_is_production ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                                        {{ $paymentSettings->xendit_is_production ? 'Live' : 'Test' }}
                                                    </span>
                                                </div>
                                                <p class="text-[11px] text-slate-500 mt-0.5">Virtual Account Multi-Bank, QRIS Instant, OVO, Dana, ShopeePay</p>
                                            </div>
                                        </div>
                                        <div class="w-7 h-7 rounded-lg bg-slate-900 text-white flex items-center justify-center font-bold text-xs shadow-xs">
                                            X
                                        </div>
                                    </label>
                                @endif

                                @if($paymentSettings->manual_transfer_enabled)
                                    <label class="p-3.5 rounded-xl border-2 cursor-pointer transition flex items-center justify-between" :class="paymentMethod === 'manual_bca' ? 'border-amber-600 bg-amber-50/50' : 'border-slate-200 hover:border-slate-300'">
                                        <div class="flex items-center space-x-3">
                                            <input type="radio" name="payment_method" value="manual_bca" x-model="paymentMethod" class="text-amber-600 focus:ring-amber-500">
                                            <div>
                                                <span class="font-bold text-slate-900 text-xs block">Transfer Bank Manual (Konfirmasi Admin)</span>
                                                <p class="text-[11px] text-slate-500 mt-0.5">{{ Str::limit($paymentSettings->bank_accounts_info ?: 'Transfer langsung ke rekening Bank resmi', 65) }}</p>
                                            </div>
                                        </div>
                                        <div class="w-7 h-7 rounded-lg bg-amber-500 text-white flex items-center justify-center text-xs shadow-xs">
                                            <i class="fa-solid fa-building-columns"></i>
                                        </div>
                                    </label>
                                @endif
                            </div>
                        </div>

                        <!-- Ringkasan Total -->
                        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/80 flex items-center justify-between">
                            <div>
                                <span class="text-slate-500 text-[11px] block">Total Biaya Perpanjangan:</span>
                                <span class="text-base font-extrabold text-slate-900" x-text="formatRp(getAmount())"></span>
                            </div>
                            <span class="text-[11px] text-slate-400 font-medium" x-text="'Paket ' + (selectedPlan === 'premium' ? 'Pro' : 'Standard') + ' (' + selectedDuration + ' Bulan)'"></span>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                        <button type="button" @click="renewModalOpen = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" :disabled="isSubmitting" class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 disabled:opacity-50 text-white text-xs font-bold rounded-xl shadow-md transition flex items-center space-x-2 cursor-pointer">
                            <i x-show="isSubmitting" class="fa-solid fa-spinner fa-spin text-xs"></i>
                            <span x-text="isSubmitting ? 'Memproses Gateway...' : 'Lanjut ke Pembayaran'"></span>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- MODAL 2: MIDTRANS SANDBOX VIRTUAL ACCOUNT & QRIS CHECKOUT -->
    <div x-show="midtransModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="midtransModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/75 backdrop-blur-xs transition-opacity" @click="midtransModalOpen = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="midtransModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200">
                
                <!-- HEADER MIDTRANS BRANDING -->
                <div class="bg-gradient-to-r from-[#002855] via-[#023e8a] to-[#0077b6] text-white p-6 relative">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-2xl bg-white/15 border border-white/20 flex items-center justify-center font-black text-lg text-white shadow-inner">
                                M
                            </div>
                            <div>
                                <div class="flex items-center space-x-2">
                                    <h3 class="font-extrabold text-base tracking-wide">midtrans</h3>
                                    <span class="px-2 py-0.5 rounded-md text-[9px] font-extrabold uppercase bg-amber-400 text-slate-950 tracking-wider shadow-2xs">
                                        SANDBOX SIMULATOR
                                    </span>
                                </div>
                                <p class="text-[11px] text-blue-100 mt-0.5">Layanan Pembayaran Resmi Midtrans Virtual Account & QRIS</p>
                            </div>
                        </div>

                        <button type="button" @click="midtransModalOpen = false" class="text-white/80 hover:text-white p-1 rounded-lg text-lg cursor-pointer">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <!-- RINGKASAN TAGIHAN BAR -->
                    <div class="mt-5 p-3.5 bg-white/10 rounded-2xl border border-white/15 backdrop-blur-sm flex items-center justify-between text-xs">
                        <div>
                            <span class="text-blue-200 text-[10px] block font-medium">TOTAL PEMBAYARAN</span>
                            <span class="text-xl font-black text-white" x-text="checkoutData?.amount_formatted || formatRp(getAmount())"></span>
                        </div>
                        <div class="text-right">
                            <span class="text-blue-200 text-[10px] block font-medium">ORDER ID</span>
                            <span class="font-mono font-bold text-amber-300 text-xs" x-text="checkoutData?.invoice_number || 'INV2026...'"></span>
                        </div>
                    </div>
                </div>

                <!-- JIKA BELUM SUKSES BAYAR: FORM PILIH BANK / QRIS -->
                <div x-show="!paymentSuccess" class="p-6 space-y-5 text-xs text-slate-700">
                    
                    <!-- TAB METODE MIDTRANS -->
                    <div class="grid grid-cols-2 gap-2 p-1 bg-slate-100 rounded-xl">
                        <button type="button" @click="midtransTab = 'va'" :class="midtransTab === 'va' ? 'bg-white text-blue-700 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900 font-medium'" class="py-2 rounded-lg text-center transition flex items-center justify-center space-x-2 cursor-pointer">
                            <i class="fa-solid fa-building-columns text-xs"></i>
                            <span>Virtual Account Bank</span>
                        </button>
                        <button type="button" @click="midtransTab = 'qris'" :class="midtransTab === 'qris' ? 'bg-white text-blue-700 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900 font-medium'" class="py-2 rounded-lg text-center transition flex items-center justify-center space-x-2 cursor-pointer">
                            <i class="fa-solid fa-qrcode text-xs"></i>
                            <span>QRIS Instant</span>
                        </button>
                    </div>

                    <!-- VIEW 1: VIRTUAL ACCOUNT -->
                    <div x-show="midtransTab === 'va'" class="space-y-4">
                        <label class="block font-bold text-slate-800">Pilih Bank Virtual Account:</label>
                        
                        <!-- GRID BANK CHOICES -->
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                            <!-- BCA -->
                            <button type="button" @click="selectedBank = 'bca'" class="p-3 rounded-xl border-2 text-left transition flex items-center justify-between cursor-pointer" :class="selectedBank === 'bca' ? 'border-blue-600 bg-blue-50/50 shadow-xs' : 'border-slate-200 hover:border-slate-300'">
                                <div class="flex items-center space-x-2">
                                    <span class="w-6 h-6 rounded bg-blue-600 text-white font-black text-[10px] flex items-center justify-center">BCA</span>
                                    <span class="font-bold text-xs text-slate-800">BCA VA</span>
                                </div>
                                <i x-show="selectedBank === 'bca'" class="fa-solid fa-circle-check text-blue-600 text-xs"></i>
                            </button>

                            <!-- Mandiri -->
                            <button type="button" @click="selectedBank = 'mandiri'" class="p-3 rounded-xl border-2 text-left transition flex items-center justify-between cursor-pointer" :class="selectedBank === 'mandiri' ? 'border-blue-600 bg-blue-50/50 shadow-xs' : 'border-slate-200 hover:border-slate-300'">
                                <div class="flex items-center space-x-2">
                                    <span class="w-6 h-6 rounded bg-blue-900 text-white font-black text-[9px] flex items-center justify-center">MDR</span>
                                    <span class="font-bold text-xs text-slate-800">Mandiri</span>
                                </div>
                                <i x-show="selectedBank === 'mandiri'" class="fa-solid fa-circle-check text-blue-600 text-xs"></i>
                            </button>

                            <!-- BNI -->
                            <button type="button" @click="selectedBank = 'bni'" class="p-3 rounded-xl border-2 text-left transition flex items-center justify-between cursor-pointer" :class="selectedBank === 'bni' ? 'border-blue-600 bg-blue-50/50 shadow-xs' : 'border-slate-200 hover:border-slate-300'">
                                <div class="flex items-center space-x-2">
                                    <span class="w-6 h-6 rounded bg-orange-500 text-white font-black text-[9px] flex items-center justify-center">BNI</span>
                                    <span class="font-bold text-xs text-slate-800">BNI VA</span>
                                </div>
                                <i x-show="selectedBank === 'bni'" class="fa-solid fa-circle-check text-blue-600 text-xs"></i>
                            </button>

                            <!-- BRI -->
                            <button type="button" @click="selectedBank = 'bri'" class="p-3 rounded-xl border-2 text-left transition flex items-center justify-between cursor-pointer" :class="selectedBank === 'bri' ? 'border-blue-600 bg-blue-50/50 shadow-xs' : 'border-slate-200 hover:border-slate-300'">
                                <div class="flex items-center space-x-2">
                                    <span class="w-6 h-6 rounded bg-blue-700 text-white font-black text-[9px] flex items-center justify-center">BRI</span>
                                    <span class="font-bold text-xs text-slate-800">BRIVA</span>
                                </div>
                                <i x-show="selectedBank === 'bri'" class="fa-solid fa-circle-check text-blue-600 text-xs"></i>
                            </button>

                            <!-- Permata -->
                            <button type="button" @click="selectedBank = 'permata'" class="p-3 rounded-xl border-2 text-left transition flex items-center justify-between cursor-pointer" :class="selectedBank === 'permata' ? 'border-blue-600 bg-blue-50/50 shadow-xs' : 'border-slate-200 hover:border-slate-300'">
                                <div class="flex items-center space-x-2">
                                    <span class="w-6 h-6 rounded bg-emerald-600 text-white font-black text-[9px] flex items-center justify-center">PER</span>
                                    <span class="font-bold text-xs text-slate-800">Permata</span>
                                </div>
                                <i x-show="selectedBank === 'permata'" class="fa-solid fa-circle-check text-blue-600 text-xs"></i>
                            </button>
                        </div>

                        <!-- KOTAK TAMPILAN NOMOR VIRTUAL ACCOUNT -->
                        <div class="p-4 bg-gradient-to-br from-slate-50 to-blue-50/40 rounded-2xl border border-blue-200/80 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-600 font-bold flex items-center gap-1.5 uppercase tracking-wide text-[11px]">
                                    <i class="fa-solid fa-credit-card text-blue-600"></i>
                                    <span x-text="'Nomor Virtual Account ' + selectedBank.toUpperCase()"></span>
                                </span>
                                <span class="text-[10px] text-amber-700 bg-amber-100 font-bold px-2 py-0.5 rounded">
                                    Berlaku 24 Jam
                                </span>
                            </div>

                            <div class="flex items-center justify-between p-3.5 bg-white rounded-xl border border-slate-200 shadow-2xs">
                                <div>
                                    <span class="font-mono font-black text-slate-900 text-lg tracking-wider" x-text="getVaNumber(selectedBank)"></span>
                                    <span class="text-[10px] text-slate-400 block mt-0.5">Atas Nama: PT AKUNTANSI CLOUD (MIDTRANS)</span>
                                </div>
                                <button type="button" @click="copyText(getVaNumber(selectedBank))" class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold rounded-lg border border-blue-200 transition flex items-center space-x-1.5 cursor-pointer">
                                    <i :class="copied ? 'fa-solid fa-check text-emerald-600' : 'fa-regular fa-copy'"></i>
                                    <span x-text="copied ? 'Tersalin!' : 'Salin'"></span>
                                </button>
                            </div>

                            <!-- PETUNJUK TRANSFER TABS -->
                            <div class="pt-2 border-t border-slate-200/60">
                                <div class="flex space-x-2 text-[11px] font-semibold text-slate-600 mb-2">
                                    <button type="button" @click="instructionTab = 'mbanking'" :class="instructionTab === 'mbanking' ? 'text-blue-600 border-b-2 border-blue-600 pb-0.5 font-bold' : 'hover:text-slate-900'">m-Banking</button>
                                    <button type="button" @click="instructionTab = 'atm'" :class="instructionTab === 'atm' ? 'text-blue-600 border-b-2 border-blue-600 pb-0.5 font-bold' : 'hover:text-slate-900'">ATM</button>
                                    <button type="button" @click="instructionTab = 'ibanking'" :class="instructionTab === 'ibanking' ? 'text-blue-600 border-b-2 border-blue-600 pb-0.5 font-bold' : 'hover:text-slate-900'">Internet Banking</button>
                                </div>

                                <div class="text-[11px] text-slate-500 space-y-1 bg-white p-3 rounded-xl border border-slate-100">
                                    <template x-if="instructionTab === 'mbanking'">
                                        <ol class="list-decimal list-inside space-y-1">
                                            <li>Buka aplikasi Mobile Banking bank Anda (BCA Mobile, Livin Mandiri, BRImo, dll).</li>
                                            <li>Pilih menu <strong>m-Transfer &rarr; Virtual Account</strong>.</li>
                                            <li>Masukkan Nomor Virtual Account di atas dan klik <strong>Kirim / Lanjut</strong>.</li>
                                            <li>Periksa rincian pembayaran, masukkan PIN Anda untuk menyelesaikan transaksi.</li>
                                        </ol>
                                    </template>
                                    <template x-if="instructionTab === 'atm'">
                                        <ol class="list-decimal list-inside space-y-1">
                                            <li>Masukkan Kartu ATM dan PIN Anda di mesin ATM.</li>
                                            <li>Pilih menu <strong>Transaksi Lainnya &rarr; Transfer &rarr; Virtual Account</strong>.</li>
                                            <li>Ketik Nomor Virtual Account di atas dan konfirmasi nama & jumlah.</li>
                                            <li>Selesaikan transaksi dan simpan struk pembayaran Anda.</li>
                                        </ol>
                                    </template>
                                    <template x-if="instructionTab === 'ibanking'">
                                        <ol class="list-decimal list-inside space-y-1">
                                            <li>Login ke portal Internet Banking bank Anda.</li>
                                            <li>Pilih menu <strong>Transfer Dana &rarr; Transfer ke Virtual Account</strong>.</li>
                                            <li>Masukkan Nomor Virtual Account dan ikuti verifikasi token bank Anda.</li>
                                        </ol>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- VIEW 2: QRIS INSTANT -->
                    <div x-show="midtransTab === 'qris'" class="space-y-4 text-center">
                        <div class="p-6 bg-slate-50 rounded-2xl border border-slate-200 max-w-sm mx-auto flex flex-col items-center">
                            <!-- QRIS HEADER LOGO -->
                            <div class="w-full flex items-center justify-between pb-3 border-b border-slate-200 mb-3">
                                <span class="font-black text-slate-800 text-sm tracking-tight">QRIS</span>
                                <span class="text-[10px] text-slate-400 font-bold uppercase">GPN Terintegrasi</span>
                            </div>

                            <!-- QR CODE DISPLAY -->
                            <div class="w-48 h-48 bg-white p-3 rounded-2xl border-2 border-slate-300 shadow-inner flex items-center justify-center relative">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=MIDTRANS-SANDBOX-AKUNTANSI-SAAS" alt="QRIS Code" class="w-40 h-40 object-contain">
                            </div>

                            <p class="text-[11px] text-slate-600 mt-3">
                                Buka aplikasi <strong>GoPay, OVO, Dana, ShopeePay</strong>, atau Mobile Banking apa saja, lalu scan kode QRIS di atas.
                            </p>
                        </div>
                    </div>

                    <!-- ACTION BUTTONS: SIMULASI BAYAR SANDBOX -->
                    <div class="pt-2 space-y-2.5">
                        <button type="button" @click="simulasiBayar()" :disabled="isSimulatingPay" class="w-full py-3.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 disabled:opacity-50 text-white font-bold text-xs sm:text-sm rounded-2xl shadow-lg shadow-emerald-600/25 transition transform hover:scale-[1.01] active:scale-[0.99] flex items-center justify-center space-x-2 cursor-pointer">
                            <i x-show="!isSimulatingPay" class="fa-solid fa-bolt text-amber-300"></i>
                            <i x-show="isSimulatingPay" class="fa-solid fa-spinner fa-spin text-white"></i>
                            <span x-text="isSimulatingPay ? 'Sedang Memverifikasi Pembayaran VA...' : '⚡ Simulasi Bayar di Sandbox (Bayar Berhasil Sekarang)'"></span>
                        </button>

                        <div class="flex items-center justify-between text-[11px] text-slate-500 pt-1">
                            <a href="https://simulator.sandbox.midtrans.com/" target="_blank" class="text-blue-600 hover:underline flex items-center gap-1 font-semibold">
                                <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                                <span>Buka Simulator Midtrans Sandbox Resmi</span>
                            </a>
                            <button type="button" @click="midtransModalOpen = false" class="text-slate-500 hover:text-slate-700 cursor-pointer">
                                Tutup & Bayar Nanti
                            </button>
                        </div>
                    </div>

                </div>

                <!-- JIKA SUDAH SUKSES BAYAR: CELEBRATION SCREEN -->
                <div x-show="paymentSuccess" class="p-8 text-center space-y-5 animate-in zoom-in-95 duration-300">
                    <div class="w-20 h-20 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto shadow-lg shadow-emerald-500/20 text-3xl animate-bounce">
                        <i class="fa-solid fa-check"></i>
                    </div>

                    <div>
                        <h4 class="text-xl font-black text-slate-900">Pembayaran Berhasil Diterima!</h4>
                        <p class="text-xs text-slate-600 mt-1.5 max-w-md mx-auto" x-text="successMessage || 'Transaksi Virtual Account Midtrans Sandbox berhasil diverifikasi oleh sistem.'"></p>
                    </div>

                    <div class="p-4 bg-emerald-50/80 rounded-2xl border border-emerald-200 text-left text-xs max-w-md mx-auto space-y-2">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Nomor Invoice:</span>
                            <span class="font-bold text-slate-800 font-mono" x-text="checkoutData?.invoice_number"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Metode:</span>
                            <span class="font-bold text-slate-800">Midtrans Virtual Account</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Status Langganan:</span>
                            <span class="font-bold text-emerald-700 uppercase bg-emerald-100 px-2 py-0.5 rounded text-[10px]">Aktif</span>
                        </div>
                    </div>

                    <div class="pt-3">
                        <button type="button" @click="finishAndReload()" class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-bold rounded-xl shadow-lg shadow-blue-600/25 transition cursor-pointer">
                            Lihat Status Langganan Baru & Selesai &rarr;
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- MODAL 3: XENDIT CHECKOUT MODAL -->
    <div x-show="xenditModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="xenditModalOpen" class="fixed inset-0 bg-slate-900/75 backdrop-blur-xs transition-opacity" @click="xenditModalOpen = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="xenditModalOpen" class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-slate-200">
                <div class="bg-slate-950 text-white p-6 flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-2xl bg-white/10 flex items-center justify-center font-black text-lg text-white">X</div>
                        <div>
                            <div class="flex items-center space-x-2">
                                <h3 class="font-bold text-base">Xendit Invoice</h3>
                                <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-amber-400 text-slate-900 uppercase">Test Mode</span>
                            </div>
                            <p class="text-[11px] text-slate-400">Virtual Account Multi-Bank & e-Wallet</p>
                        </div>
                    </div>
                    <button type="button" @click="xenditModalOpen = false" class="text-white/80 hover:text-white text-lg"><i class="fa-solid fa-xmark"></i></button>
                </div>

                <div x-show="!paymentSuccess" class="p-6 space-y-5 text-xs text-slate-700">
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 flex items-center justify-between">
                        <div>
                            <span class="text-slate-400 text-[10px] block">TOTAL TAGIHAN</span>
                            <span class="text-lg font-black text-slate-900" x-text="checkoutData?.amount_formatted"></span>
                        </div>
                        <div class="text-right">
                            <span class="text-slate-400 text-[10px] block">NO INVOICE</span>
                            <span class="font-mono font-bold text-blue-600" x-text="checkoutData?.invoice_number"></span>
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-800 mb-2">Nomor Virtual Account Xendit:</label>
                        <div class="p-4 bg-slate-900 text-white rounded-2xl flex items-center justify-between">
                            <div>
                                <span class="font-mono font-black text-lg tracking-wider text-amber-300" x-text="getVaNumber('bca')"></span>
                                <span class="text-[10px] text-slate-400 block">Bank Central Asia (BCA Virtual Account)</span>
                            </div>
                            <button type="button" @click="copyText(getVaNumber('bca'))" class="px-3 py-1.5 bg-white/10 hover:bg-white/20 text-white font-bold rounded-lg text-xs transition">
                                <span x-text="copied ? 'Tersalin!' : 'Salin'"></span>
                            </button>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="button" @click="simulasiBayar()" :disabled="isSimulatingPay" class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs sm:text-sm rounded-2xl shadow-lg transition flex items-center justify-center space-x-2 cursor-pointer">
                            <i class="fa-solid fa-bolt text-amber-300"></i>
                            <span x-text="isSimulatingPay ? 'Sedang Memproses...' : '⚡ Simulasi Bayar di Sandbox Xendit (Sukses Sekarang)'"></span>
                        </button>
                    </div>
                </div>

                <div x-show="paymentSuccess" class="p-8 text-center space-y-4">
                    <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto text-2xl font-bold">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <h4 class="text-lg font-black text-slate-900">Pembayaran Xendit Berhasil!</h4>
                    <p class="text-xs text-slate-500" x-text="successMessage"></p>
                    <button type="button" @click="finishAndReload()" class="px-6 py-2.5 bg-blue-600 text-white text-xs font-bold rounded-xl shadow-md">
                        Selesai & Muat Ulang &rarr;
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 4: MANUAL TRANSFER MODAL -->
    <div x-show="manualModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="manualModalOpen" class="fixed inset-0 bg-slate-900/75 backdrop-blur-xs transition-opacity" @click="manualModalOpen = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="manualModalOpen" class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-200">
                <div class="bg-amber-500 text-white p-6 flex items-center justify-between">
                    <div class="flex items-center space-x-2.5">
                        <i class="fa-solid fa-building-columns text-lg"></i>
                        <h3 class="font-bold text-base">Instruksi Transfer Manual</h3>
                    </div>
                    <button type="button" @click="manualModalOpen = false" class="text-white/80 hover:text-white text-lg"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <div class="p-6 space-y-4 text-xs text-slate-700">
                    <p class="text-slate-600">Invoice Anda telah dibuat dengan status <strong>Menunggu Pembayaran</strong>.</p>
                    <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                        <span class="font-bold text-slate-900 block">Rekening Resmi Pembayaran:</span>
                        <p class="font-mono text-slate-700 whitespace-pre-line leading-relaxed">{{ $paymentSettings->bank_accounts_info ?: 'BCA: 800-123-4567 a/n PT Akuntansi Cloud Indonesia' }}</p>
                    </div>
                    <p class="text-[11px] text-slate-500">Setelah melakukan transfer, silakan kirimkan bukti bayar ke Super Admin untuk diverifikasi dan diaktifkan.</p>
                    <button type="button" @click="finishAndReload()" class="w-full py-2.5 bg-slate-800 text-white font-bold rounded-xl text-xs">
                        Tutup & Lihat Riwayat Invoice
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- MIDTRANS SNAP CLIENT SCRIPT -->
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ $paymentSettings->midtrans_client_key ?? 'SB-Mid-client-DemoExampleKey123' }}"></script>
@endsection
