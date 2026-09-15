@extends('layouts.app')

@section('title', 'Buku Panduan & Edukasi Akuntansi Lengkap')

@section('content')
<div class="space-y-6 pb-12 animate-in fade-in duration-200">

    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-blue-700 via-indigo-700 to-slate-900 rounded-2xl p-6 text-white shadow-md relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-60 h-60 bg-blue-500/20 rounded-full blur-2xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-wrap items-center justify-between gap-4">
            <div class="space-y-1.5">
                <div class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full text-xs font-bold bg-white/15 backdrop-blur-md border border-white/20 text-blue-100">
                    <i class="fa-solid fa-graduation-cap text-amber-300"></i>
                    <span>Edukasi Akuntansi & Materi Presentasi</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-black tracking-tight">Buku Panduan Akuntansi & Fitur Aplikasi</h1>
                <p class="text-xs sm:text-sm text-blue-100 max-w-2xl leading-relaxed">
                    Panduan terstruktur untuk pemula yang ingin memahami konsep akuntansi, alur kerja setiap menu, hingga bahan simulasi presentasi.
                </p>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('panduan.cetak') }}" target="_blank" class="px-4 py-2.5 bg-white hover:bg-slate-50 text-blue-800 text-xs font-bold rounded-xl shadow-sm transition flex items-center space-x-2">
                    <i class="fa-solid fa-file-pdf text-rose-600"></i>
                    <span>Buka Versi Cetak / PDF</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Navigation Pills -->
    <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-2xs flex items-center gap-2 overflow-x-auto text-xs font-semibold whitespace-nowrap">
        <span class="text-slate-400 mr-1 text-[11px] font-bold uppercase"><i class="fa-solid fa-compass mr-1"></i>Pintasan:</span>
        <a href="#konsep-dasar" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-blue-50 hover:text-blue-700 transition">1. Konsep Awam</a>
        <a href="#menu-dashboard" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-blue-50 hover:text-blue-700 transition">2. Dashboard</a>
        <a href="#menu-transaksi" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-blue-50 hover:text-blue-700 transition">3. Transaksi & AI</a>
        <a href="#menu-master" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-blue-50 hover:text-blue-700 transition">4. Master & Aset</a>
        <a href="#menu-laporan" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-blue-50 hover:text-blue-700 transition">5. 7 Laporan Keuangan</a>
        <a href="#live-demo" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-blue-50 hover:text-blue-700 transition">6. Alur Demo</a>
        <a href="#tanya-jawab" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-blue-50 hover:text-blue-700 transition">7. Tanya Jawab Penguji</a>
    </div>

    <!-- Section 1: Konsep Dasar Akuntansi untuk Orang Awam -->
    <div id="konsep-dasar" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
        <div class="flex items-center space-x-2.5 pb-3 border-b border-slate-100">
            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm">
                1
            </div>
            <div>
                <h2 class="text-base font-bold text-slate-900">Konsep Dasar Akuntansi untuk Pemula</h2>
                <p class="text-xs text-slate-500">Tiga aturan emas yang mendasari cara kerja seluruh sistem ini</p>
            </div>
        </div>

        <!-- Formula Box -->
        <div class="bg-slate-900 text-white rounded-xl p-4 text-center font-mono font-bold text-sm tracking-wide border border-slate-800">
            ASET (HARTA) = KEWAJIBAN (HUTANG) + EKUITAS (MODAL)
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
            <div class="p-4 bg-blue-50/60 rounded-xl border border-blue-100 space-y-1">
                <span class="font-bold text-blue-900 block text-sm">Aset (Harta)</span>
                <p class="text-slate-600">Seluruh kekayaan milik perusahaan: Kas tunai, Saldo Bank, Piutang ke pembeli, Laptop, Kendaraan, dan Gedung.</p>
            </div>
            <div class="p-4 bg-amber-50/60 rounded-xl border border-amber-100 space-y-1">
                <span class="font-bold text-amber-900 block text-sm">Kewajiban (Hutang)</span>
                <p class="text-slate-600">Uang pihak ketiga yang dipinjam perusahaan dan wajib dilunasi: Hutang Usaha ke Vendor, Hutang Bank, atau Pajak.</p>
            </div>
            <div class="p-4 bg-emerald-50/60 rounded-xl border border-emerald-100 space-y-1">
                <span class="font-bold text-emerald-900 block text-sm">Ekuitas (Modal)</span>
                <p class="text-slate-600">Hak pemilik atas perusahaan: Modal disetor awal ditambah akumulasi sisa keuntungan usaha (Laba Ditahan).</p>
            </div>
        </div>

        <!-- Aturan Debit Kredit Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border border-slate-200 rounded-xl overflow-hidden">
                <thead class="bg-slate-100 text-slate-700 font-bold uppercase text-[10px]">
                    <tr>
                        <th class="p-3">Golongan Akun</th>
                        <th class="p-3">Jika Bertambah</th>
                        <th class="p-3">Jika Berkurang</th>
                        <th class="p-3">Saldo Normal Baku</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr>
                        <td class="p-3 font-semibold text-slate-800">1. Aset (Harta / Kas / Bank)</td>
                        <td class="p-3"><span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 font-bold">DEBIT</span></td>
                        <td class="p-3"><span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold">KREDIT</span></td>
                        <td class="p-3 font-medium text-slate-600">Debit</td>
                    </tr>
                    <tr>
                        <td class="p-3 font-semibold text-slate-800">2. Beban / Biaya Pengeluaran</td>
                        <td class="p-3"><span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 font-bold">DEBIT</span></td>
                        <td class="p-3"><span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold">KREDIT</span></td>
                        <td class="p-3 font-medium text-slate-600">Debit</td>
                    </tr>
                    <tr>
                        <td class="p-3 font-semibold text-slate-800">3. Kewajiban (Hutang)</td>
                        <td class="p-3"><span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold">KREDIT</span></td>
                        <td class="p-3"><span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 font-bold">DEBIT</span></td>
                        <td class="p-3 font-medium text-slate-600">Kredit</td>
                    </tr>
                    <tr>
                        <td class="p-3 font-semibold text-slate-800">4. Ekuitas (Modal)</td>
                        <td class="p-3"><span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold">KREDIT</span></td>
                        <td class="p-3"><span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 font-bold">DEBIT</span></td>
                        <td class="p-3 font-medium text-slate-600">Kredit</td>
                    </tr>
                    <tr>
                        <td class="p-3 font-semibold text-slate-800">5. Pendapatan (Penjualan)</td>
                        <td class="p-3"><span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold">KREDIT</span></td>
                        <td class="p-3"><span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 font-bold">DEBIT</span></td>
                        <td class="p-3 font-medium text-slate-600">Kredit</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section 2: Dashboard -->
    <div id="menu-dashboard" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
        <div class="flex items-center space-x-2.5 pb-3 border-b border-slate-100">
            <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm">
                2
            </div>
            <div>
                <h2 class="text-base font-bold text-slate-900">Menu Dashboard Eksekutif</h2>
                <p class="text-xs text-slate-500">Pusat kendali dan ringkasan kilat kesehatan keuangan bisnis</p>
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
            <div class="p-3.5 bg-blue-50 rounded-xl border border-blue-100">
                <span class="font-bold text-blue-900 block">Total Pendapatan</span>
                <p class="text-slate-600 mt-1">Akumulasi seluruh penjualan dan penerimaan operasional bulan berjalan.</p>
            </div>
            <div class="p-3.5 bg-rose-50 rounded-xl border border-rose-100">
                <span class="font-bold text-rose-900 block">Total Beban</span>
                <p class="text-slate-600 mt-1">Seluruh pengeluaran biaya operasional yang dikeluarkan perusahaan.</p>
            </div>
            <div class="p-3.5 bg-emerald-50 rounded-xl border border-emerald-100">
                <span class="font-bold text-emerald-900 block">Laba Bersih</span>
                <p class="text-slate-600 mt-1">Keuntungan murni (Pendapatan - Beban) yang didapatkan bulan ini.</p>
            </div>
            <div class="p-3.5 bg-purple-50 rounded-xl border border-purple-100">
                <span class="font-bold text-purple-900 block">Kas & Bank</span>
                <p class="text-slate-600 mt-1">Total uang tunai riil yang siap digunakan kapan saja.</p>
            </div>
        </div>
    </div>

    <!-- Section 3: Transaksi & AI -->
    <div id="menu-transaksi" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
        <div class="flex items-center space-x-2.5 pb-3 border-b border-slate-100">
            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-sm">
                3
            </div>
            <div>
                <h2 class="text-base font-bold text-slate-900">Menu Transaksi (Catat Cepat & Asisten AI)</h2>
                <p class="text-xs text-slate-500">Mencatat mutasi kas harian dengan proteksi otomatis seimbang</p>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
            <div class="border border-slate-200 rounded-xl p-4 space-y-2">
                <span class="font-bold text-slate-800 text-sm flex items-center"><i class="fa-solid fa-bolt text-amber-500 mr-1.5"></i> Mode Catat Cepat (Single Entry)</span>
                <p class="text-slate-600">Pengguna cukup memilih Jenis (Pemasukan / Pengeluaran / Transfer), Akun Simpan, Akun Sumber, dan Jumlah. Di belakang layar, sistem otomatis membuat jurnal berpasangan (Double Entry).</p>
            </div>
            <div class="border border-slate-200 rounded-xl p-4 space-y-2">
                <span class="font-bold text-slate-800 text-sm flex items-center"><i class="fa-solid fa-robot text-purple-600 mr-1.5"></i> Asisten AI Google Gemini</span>
                <p class="text-slate-600">Ketik instruksi bahasa Indonesia bebas (misal: <em>"Beli bensin motor delivery 50rb tunai"</em>), AI menganalisis nominal dan memetakan akun Beban Bensin (Debit) dan Kas (Kredit) otomatis.</p>
            </div>
            <div class="border border-slate-200 rounded-xl p-4 space-y-2">
                <span class="font-bold text-slate-800 text-sm flex items-center"><i class="fa-solid fa-tags text-blue-600 mr-1.5"></i> Tag Proyek / Cabang</span>
                <p class="text-slate-600">Menempelkan label proyek (seperti <code>lokatara</code>) langsung pada form transaksi, sehingga pendapatan dan biaya proyek tersebut bisa disaring secara mandiri di laporan Laba Rugi.</p>
            </div>
            <div class="border border-slate-200 rounded-xl p-4 space-y-2">
                <span class="font-bold text-slate-800 text-sm flex items-center"><i class="fa-solid fa-shield-halved text-emerald-600 mr-1.5"></i> Proteksi Transaksi Majemuk</span>
                <p class="text-slate-600">Pada pencatatan majemuk multi-baris, tombol simpan otomatis terkunci jika penjumlahan Debit belum sama dengan penjumlahan Kredit.</p>
            </div>
        </div>
    </div>

    <!-- Section 4: Master Data & Aset -->
    <div id="menu-master" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
        <div class="flex items-center space-x-2.5 pb-3 border-b border-slate-100">
            <div class="w-8 h-8 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center font-bold text-sm">
                4
            </div>
            <div>
                <h2 class="text-base font-bold text-slate-900">Menu Master Data & Aset Tetap</h2>
                <p class="text-xs text-slate-500">Pondasi struktur pembukuan dan inventarisasi aktiva perusahaan</p>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs">
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                <span class="font-bold text-slate-900">Bagan Akun (COA)</span>
                <p class="text-slate-600 mt-1">Kamus akun 5 digit: Harta (1-), Hutang (2-), Modal (3-), Pendapatan (4-), dan Beban (6-/8-).</p>
            </div>
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                <span class="font-bold text-slate-900">Tag / Proyek</span>
                <p class="text-slate-600 mt-1">Label pengelompokan biaya multidimensi per Proyek (cth: lokatara) tanpa merusak atau membengkakkan daftar COA.</p>
            </div>
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                <span class="font-bold text-slate-900">Tutup Buku (Closing)</span>
                <p class="text-slate-600 mt-1">Memindahkan saldo laba bersih ke Laba Ditahan dan mengunci transaksi masa lampau dari perubahan sepihak.</p>
            </div>
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                <span class="font-bold text-slate-900">Aset Tetap & Depresiasi</span>
                <p class="text-slate-600 mt-1">Inventaris aktiva (kendaraan, laptop, tanah) lengkap dengan upload foto dan penyusutan otomatis.</p>
            </div>
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                <span class="font-bold text-slate-900">Metode Pembayaran</span>
                <p class="text-slate-600 mt-1">Menghubungkan cara bayar riil kasir (Tunai, QRIS, EDC BCA) ke akun Kas/Bank yang tepat.</p>
            </div>
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                <span class="font-bold text-slate-900">Master Pajak</span>
                <p class="text-slate-600 mt-1">Tarif resmi PPN (11%) dan PPh yang otomatis terkoneksi ke akun hutang/piutang pajak.</p>
            </div>
        </div>
    </div>

    <!-- Section 5: Laporan Keuangan Lengkap -->
    <div id="menu-laporan" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
        <div class="flex items-center space-x-2.5 pb-3 border-b border-slate-100">
            <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-sm">
                5
            </div>
            <div>
                <h2 class="text-base font-bold text-slate-900">Modul 7 Laporan Keuangan Standar SAK</h2>
                <p class="text-xs text-slate-500">Laporan otomatis berstandar akuntansi yang siap diaudit dan dicetak</p>
            </div>
        </div>
        <div class="space-y-3 text-xs">
            <div class="p-3.5 bg-blue-50/50 rounded-xl border border-blue-100 flex items-start space-x-3">
                <span class="w-6 h-6 rounded-full bg-blue-600 text-white font-bold flex items-center justify-center text-[10px] shrink-0">1</span>
                <div>
                    <strong class="text-blue-900 text-sm">Laporan Jurnal Umum</strong>
                    <p class="text-slate-600 mt-0.5">Buku harian kronologis seluruh ayat jurnal debit-kredit. Dilengkapi filter akun, <strong>filter Tag Proyek</strong>, cetak bukti voucher transaksi, dan export Excel.</p>
                </div>
            </div>
            <div class="p-3.5 bg-emerald-50/50 rounded-xl border border-emerald-100 flex items-start space-x-3">
                <span class="w-6 h-6 rounded-full bg-emerald-600 text-white font-bold flex items-center justify-center text-[10px] shrink-0">2</span>
                <div>
                    <strong class="text-emerald-900 text-sm">Laporan Laba Rugi (Profit & Loss)</strong>
                    <p class="text-slate-600 mt-0.5">Menghitung Laba Kotor, Laba Operasional, dan Laba Bersih. Dilengkapi <strong>Filter Tag Proyek</strong> untuk mengukur keuntungan khusus proyek tertentu (misal: proyek <em>lokatara</em>).</p>
                </div>
            </div>
            <div class="p-3.5 bg-amber-50/50 rounded-xl border border-amber-100 flex items-start space-x-3">
                <span class="w-6 h-6 rounded-full bg-amber-600 text-white font-bold flex items-center justify-center text-[10px] shrink-0">3</span>
                <div>
                    <strong class="text-amber-900 text-sm">Neraca Keuangan (Balance Sheet)</strong>
                    <p class="text-slate-600 mt-0.5">Potret posisi kekayaan per tanggal tertentu dengan format skontro formal: Sisi Kiri (Aset) wajib sama persis dengan Sisi Kanan (Kewajiban + Modal).</p>
                </div>
            </div>
            <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200 flex items-start space-x-3">
                <span class="w-6 h-6 rounded-full bg-slate-700 text-white font-bold flex items-center justify-center text-[10px] shrink-0">4</span>
                <div>
                    <strong class="text-slate-900 text-sm">Neraca Saldo (Trial Balance)</strong>
                    <p class="text-slate-600 mt-0.5">Lembar pengujian seluruh akun buku besar untuk memastikan saldo Debit dan saldo Kredit seimbang.</p>
                </div>
            </div>
            <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200 flex items-start space-x-3">
                <span class="w-6 h-6 rounded-full bg-slate-700 text-white font-bold flex items-center justify-center text-[10px] shrink-0">5</span>
                <div>
                    <strong class="text-slate-900 text-sm">Buku Besar (General Ledger)</strong>
                    <p class="text-slate-600 mt-0.5">Histori mutasi mendalam per 1 akun spesifik (misal Kas atau Piutang) lengkap dengan saldo berjalan (running balance).</p>
                </div>
            </div>
            <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200 flex items-start space-x-3">
                <span class="w-6 h-6 rounded-full bg-slate-700 text-white font-bold flex items-center justify-center text-[10px] shrink-0">6</span>
                <div>
                    <strong class="text-slate-900 text-sm">Laporan Arus Kas (Cash Flow)</strong>
                    <p class="text-slate-600 mt-0.5">Melacak arus uang tunai riil ke dalam 3 aktivitas utama: Operasional, Investasi (aset), dan Pendanaan (modal/pinjaman).</p>
                </div>
            </div>
            <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200 flex items-start space-x-3">
                <span class="w-6 h-6 rounded-full bg-slate-700 text-white font-bold flex items-center justify-center text-[10px] shrink-0">7</span>
                <div>
                    <strong class="text-slate-900 text-sm">Laporan Beban Operasional</strong>
                    <p class="text-slate-600 mt-0.5">Audit rincian seluruh pos beban operasional lengkap dengan filter Tag Proyek untuk efisiensi biaya.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 6: Skenario Live Demo -->
    <div id="live-demo" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
        <div class="flex items-center space-x-2.5 pb-3 border-b border-slate-100">
            <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center font-bold text-sm">
                6
            </div>
            <div>
                <h2 class="text-base font-bold text-slate-900">Urutan Skenario Live Demo Presentasi (7-10 Menit)</h2>
                <p class="text-xs text-slate-500">Gunakan langkah ini saat Anda mendemonstrasikan aplikasi di depan penguji</p>
            </div>
        </div>
        <div class="space-y-3 text-xs">
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 flex items-center space-x-3">
                <span class="font-bold text-blue-600 w-16">Menit 01</span>
                <span class="text-slate-700"><strong>Dashboard</strong>: Buka Dashboard, tunjukkan 4 kartu KPI (Omset, Beban, Laba Bersih, Kas), dan jelaskan bahwa pemilik bisnis langsung paham status finansialnya dalam 5 detik.</span>
            </div>
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 flex items-center space-x-3">
                <span class="font-bold text-blue-600 w-16">Menit 03</span>
                <span class="text-slate-700"><strong>Input AI & Tag Proyek</strong>: Buka Catat Transaksi, ketik instruksi bahasa bebas di Asisten AI, pilih Tag Proyek <code>🏷️ lokatara</code>, lalu simpan transaksi.</span>
            </div>
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 flex items-center space-x-3">
                <span class="font-bold text-blue-600 w-16">Menit 05</span>
                <span class="text-slate-700"><strong>Buku Jurnal</strong>: Buka Jurnal Umum, tunjukkan jurnal otomatis Debit-Kredit yang seimbang, dan buka modal Bukti Voucher Jurnal.</span>
            </div>
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 flex items-center space-x-3">
                <span class="font-bold text-blue-600 w-16">Menit 07</span>
                <span class="text-slate-700"><strong>Laba Rugi Proyek</strong>: Buka Laba Rugi, ubah filter Tag Proyek menjadi <code>lokatara</code> &rarr; Tunjukkan bahwa laporan langsung menyaring laba khusus proyek tersebut. Buka Neraca untuk membuktikan Harta = Hutang + Modal seimbang.</span>
            </div>
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 flex items-center space-x-3">
                <span class="font-bold text-blue-600 w-16">Menit 08</span>
                <span class="text-slate-700"><strong>Modul Aset Tetap</strong>: Buka Daftar Aset, perlihatkan foto fisik aset dengan modal preview lightbox, serta jelaskan nilai buku dan akumulasi penyusutan otomatis.</span>
            </div>
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 flex items-center space-x-3">
                <span class="font-bold text-blue-600 w-16">Menit 10</span>
                <span class="text-slate-700"><strong>Penutup</strong>: Tunjukkan fitur Multi-Perusahaan (Tenant Switcher) dan hak akses kasir terisolasi.</span>
            </div>
        </div>
    </div>

    <!-- Section 7: Tanya Jawab Penguji -->
    <div id="tanya-jawab" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
        <div class="flex items-center space-x-2.5 pb-3 border-b border-slate-100">
            <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-sm">
                7
            </div>
            <div>
                <h2 class="text-base font-bold text-slate-900">Bocoran Pertanyaan Penguji & Kunci Jawabannya</h2>
                <p class="text-xs text-slate-500">Hafalkan jawaban ini untuk menjawab pertanyaan teknis penguji dengan percaya diri</p>
            </div>
        </div>
        <div class="space-y-3 text-xs">
            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-1.5">
                <strong class="text-slate-900 block text-sm">Q1: "Bagaimana sistem menjamin pembukuan tidak akan pernah berat sebelah (unbalanced)?"</strong>
                <p class="text-slate-600 leading-relaxed"><strong>Jawab:</strong> <em>"Sistem menggunakan validasi ganda. Pada form majemuk, tombol simpan dinonaktifkan jika Debit &ne; Kredit. Di backend, seluruh proses dibungkus Database Transaction (ACID), sehingga jika salah satu baris gagal disimpan, seluruh transaksi otomatis dibatalkan (rollback) tanpa merusak jurnal."</em></p>
            </div>
            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-1.5">
                <strong class="text-slate-900 block text-sm">Q2: "Apa bedanya mencatat transaksi pakai COA saja dibanding pakai Tag Proyek?"</strong>
                <p class="text-slate-600 leading-relaxed"><strong>Jawab:</strong> <em>"COA adalah struktur baku akuntansi. Jika kita membuat akun baru setiap ada proyek, COA akan membengkak ratusan baris. Dengan Tag Proyek, COA tetap rapi dan ramping, namun transaksi memiliki dimensi analitik tambahan sehingga Laba Rugi per proyek bisa difilter secara mandiri."</em></p>
            </div>
            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-1.5">
                <strong class="text-slate-900 block text-sm">Q3: "Bagaimana AI Gemini bekerja di aplikasi ini?"</strong>
                <p class="text-slate-600 leading-relaxed"><strong>Jawab:</strong> <em>"Aplikasi mengirimkan teks bahasa Indonesia pengguna bersama daftar COA perusahaan ke Google Gemini API. Gemini mengekstrak nilai nominal, mengenali maksud transaksi, dan memilih akun Debit & Kredit yang paling cocok dalam format JSON terstruktur untuk dieksekusi oleh form."</em></p>
            </div>
        </div>
    </div>

</div>
@endsection
