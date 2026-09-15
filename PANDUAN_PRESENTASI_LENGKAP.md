# PANDUAN LENGKAP & MATERI PRESENTASI SISTEM INFORMASI AKUNTANSI CLOUD (AKUTANSIKU)
**Edisi Khusus:** Disusun runtut, praktis, dan ramah untuk pemula yang baru belajar akuntansi.

---

## DAFTAR ISI
1. **Konsep Dasar Akuntansi untuk Pemula (Cheat Sheet Teori)**
   - Persamaan Dasar Akuntansi
   - Logika Debit dan Kredit yang Mudah Dipahami
   - Siklus Akuntansi Modern (Otomatisasi Jurnal)
2. **Arsitektur & Keunggulan Utama Aplikasi**
   - Single Source of Truth & Realtime Double Entry
   - Multi-Tenancy (Multi-Perusahaan) & Multi-Role Akses
   - AI Natural Language Journaling (Integrasi Google Gemini)
3. **Bedah Detail Per Menu & Sub-Menu (Fungsi, Input, dan Output)**
   - **Menu 1: Dashboard Eksekutif**
   - **Menu 2: Modul Transaksi**
     - Submenu 2.1: Catat Transaksi Baru (Mode Standar, Mode Majemuk, AI Assistant)
     - Submenu 2.2: Riwayat Transaksi & Audit Log
   - **Menu 3: Modul Master Data & Aset Tetap**
     - Submenu 3.1: Bagan Akun (Chart of Accounts / COA)
     - Submenu 3.2: Pelanggan, Vendor & Kontak
     - Submenu 3.3: Metode Pembayaran (Kas & Bank)
     - Submenu 3.4: Master Pajak (PPN & PPh)
     - Submenu 3.5: Tag / Label Proyek & Cabang (Multidimensi)
     - Submenu 3.6: Tutup Buku Periode (Closing Period)
     - Submenu 3.7: Modul Aset Tetap & Depresiasi Otomatis
   - **Menu 4: Modul Laporan Keuangan Standar SAK**
     - Submenu 4.1: Laporan Jurnal Umum (General Journal & Voucher)
     - Submenu 4.2: Laporan Laba Rugi (Profit and Loss / P&L)
     - Submenu 4.3: Neraca Keuangan (Balance Sheet)
     - Submenu 4.4: Neraca Saldo (Trial Balance)
     - Submenu 4.5: Buku Besar (General Ledger)
     - Submenu 4.6: Laporan Arus Kas (Cash Flow)
     - Submenu 4.7: Laporan Beban Operasional
   - **Menu 5: Modul Pengaturan & Akses Karyawan**
     - Submenu 5.1: Pengaturan Utama & Integrasi AI
     - Submenu 5.2: Pemetaan Akun Default (Account Mappings)
     - Submenu 5.3: Manajemen Karyawan & Hak Akses (Role-Based Access)
     - Submenu 5.4: Profil Pengguna & Reset Data
   - **Menu 6: Modul Multi-Perusahaan (Tenant Switching)**
   - **Menu 7: Modul Billing & SaaS Super Admin**
4. **Skenario Simulasi Live Demo Presentasi (Langkah demi Langkah)**
5. **Daftar Pertanyaan Kritis yang Sering Ditanyakan Penguji/Dosen & Kunci Jawabannya**

---

## BAB 1: KONSEP DASAR AKUNTANSI UNTUK PEMULA

Jika Anda belum pernah belajar akuntansi, jangan khawatir! Konsep dasar akuntansi di aplikasi ini sebenarnya hanya bertumpu pada **3 aturan emas**:

### 1. Persamaan Dasar Akuntansi
Semua kekayaan perusahaan harus ada sumber asalnya:
$$\text{Aset (Harta)} = \text{Kewajiban (Hutang)} + \text{Ekuitas (Modal)}$$

* **Aset (Harta)**: Semua milik perusahaan yang bernilai uang (Kas, Rekening Bank, Piutang, Stok Barang, Laptop, Bangunan).
* **Kewajiban (Hutang)**: Uang orang lain yang dipinjam perusahaan dan wajib dikembalikan (Hutang Usaha, Hutang Bank, Hutang Pajak).
* **Ekuitas (Modal)**: Hak milik pemilik bisnis atas perusahaan (Modal Disetor, Saldo Laba Ditahan dari keuntungan usaha).

### 2. Aturan Debit dan Kredit (Double Entry)
Dalam akuntansi modern, setiap ada uang masuk atau keluar, **harus ada minimal 2 akun yang bergerak** agar nilainya seimbang (*balance*).
* **Debit** BUKAN berarti "uang bertambah", dan **Kredit** BUKAN berarti "uang berkurang".
* Aturan sederhananya:
  | Golongan Akun | Bertambah Di Sisi | Berkurang Di Sisi | Saldo Normal |
  | :--- | :---: | :---: | :---: |
  | **Aset (Harta / Kas / Bank)** | **DEBIT** | **KREDIT** | Debit |
  | **Beban / Biaya (Pengeluaran)** | **DEBIT** | **KREDIT** | Debit |
  | **Kewajiban (Hutang)** | **KREDIT** | **DEBIT** | Kredit |
  | **Modal (Ekuitas)** | **KREDIT** | **DEBIT** | Kredit |
  | **Pendapatan (Penjualan)** | **KREDIT** | **DEBIT** | Kredit |

* **Contoh Riil**:
  - *Membeli bensin Rp 50.000 tunai*:
    - Beban Bensin bertambah = **Debit Rp 50.000**
    - Kas Tunai berkurang = **Kredit Rp 50.000**
    *(Total Debit = Total Kredit, SEIMBANG!)*

### 3. Otomatisasi Jurnal di Aplikasi Kita
Keunggulan sistem ini: **Pengguna tidak perlu menghafal debit-kredit secara manual untuk transaksi harian**. Pengguna cukup memilih jenis transaksi (Pemasukan, Pengeluaran, Transfer), lalu sistem otomatis membuat jurnal berpasangan di belakang layar.

---

## BAB 2: ARSITEKTUR & KEUNGGULAN UTAMA APLIKASI

Saat presentasi, sampaikan 4 pilar kekuatan sistem ini:
1. **Real-time Double Entry Engine**: Begitu tombol "Simpan Transaksi" diklik, sistem langsung membuat Header Jurnal (`journal_entries`) dan Rincian Jurnal (`journal_items`). Laporan Laba Rugi dan Neraca langsung berubah detik itu juga tanpa perlu proses *posting* manual.
2. **AI-Powered Natural Language Journaling**: Dilengkapi AI (Google Gemini API) yang mampu membaca kalimat bahasa Indonesia santai (misal: *"beli kopi dan snack rapat kantor 75rb tunai"*), membedah nominal, mendeteksi akun debit & kredit yang tepat, lalu mengisi form secara otomatis.
3. **Multi-Tenancy & Multi-Company**: Mendukung banyak badan usaha dalam satu akun (misal PT Induk dan Cabang Restoran), data masing-masing perusahaan 100% terisolasi aman.
4. **Role-Based Access Control (RBAC)**: Tersedia 4 tingkatan peran:
   - **Administrator (Owner)**: Akses penuh semua fitur, pengaturan, karyawan, dan reset data.
   - **Akuntan (Finance)**: Mengelola transaksi, tutup buku, dan seluruh laporan keuangan.
   - **Kasir / Staf**: Mode terisolasi — hanya bisa mencatat transaksi kasir dan melihat transaksi buatannya sendiri.
   - **Super Admin (SaaS Provider)**: Mengelola paket sewa platform, memantau total omset tenant, dan verifikasi invoice langganan.

---

## BAB 3: BEDAH LENGKAP PER MENU DAN SUBMENU

### MENU 1: DASHBOARD EKSEKUTIF (`/dashboard`)
* **Tujuan**: Memberikan gambaran kilat kesehatan keuangan perusahaan kepada pemilik bisnis tanpa harus membaca tabel laporan yang rumit.
* **Komponen & Metrik yang Ditampilkan**:
  1. **Kartu KPI Utama**:
     - *Total Pendapatan Bulan Ini*: Jumlah seluruh omset penjualan.
     - *Total Beban Biaya Bulan Ini*: Jumlah seluruh pengeluaran operasional.
     - *Laba Bersih Bulan Ini*: Hasil bersih (Pendapatan dikurangi Beban).
     - *Saldo Kas & Bank*: Likuiditas kas riil yang siap dipakai.
  2. **Grafik Tren Pendapatan vs Beban**: Grafik visual komparasi performa keuangan dari bulan ke bulan.
  3. **Struktur Komposisi Beban (Donut Chart)**: Menunjukkan pos biaya mana yang paling banyak menyedot anggaran (misal: Gaji Karyawan, Sewa, atau Listrik).
  4. **Tabel Transaksi Terakhir**: Feed aktivitas kas masuk dan keluar secara langsung.
* **Poin Presentasi**: *"Dashboard ini dirancang untuk pemilik bisnis. Cukup 5 detik melihat dashboard, pemilik langsung tahu apakah perusahaannya bulan ini untung atau rugi, dan berapa sisa kas riil yang tersedia."*

---

### MENU 2: TRANSAKSI (`/transactions`)

#### Submenu 2.1: Catat Transaksi Baru (`/transactions/create`)
* **Tujuan**: Pintu masuk utama mencatat seluruh arus uang kas masuk, uang keluar, transfer, maupun jurnal penyesuaian.
* **Fitur Unggulan**:
  1. **Mode 1: Catat Cepat Standar (Single)**:
     - Didesain untuk operasional cepat (kasir/admin umum).
     - Pengguna cukup memilih: Jenis (Pemasukan / Pengeluaran / Transfer), Akun Simpan ke, Akun Diterima dari, Jumlah, dan Catatan.
  2. **Mode 2: Transaksi Majemuk (Multi-Baris)**:
     - Digunakan untuk transaksi kompleks (misal: Penjualan paket barang Rp 10 jt, diterima DP Kas Rp 3 jt, sisanya Piutang Rp 7 jt).
     - Dilengkapi validasi otomatis realtime: Tombol simpan terkunci jika baris Debit dan Kredit belum seimbang (*unbalanced*).
  3. **Mode 3: Asisten Jurnal AI (Natural Language)**:
     - Kotak input prompt pintar. Pengguna mengetik kalimat bebas, AI membedah dan memilih akun COA yang paling relevan.
  4. **Kolom Kontak (Pelanggan / Vendor)**: Mengaitkan transaksi ke pelanggan atau vendor untuk rekap jejak audit.
  5. **Kolom Tag Proyek / Cabang**: Memungkinkan transaksi dilabeli proyek tertentu (misal: Tag `lokatara`).
  6. **Opsi Pajak (PPN/PPh)**: Otomatis memisahkan nilai dasar dan nilai pajak transaksi.

#### Submenu 2.2: Riwayat Transaksi (`/transactions/history`)
* **Tujuan**: Log histori seluruh mutasi transaksi yang tersimpan di sistem.
* **Fitur Utama**:
  - Filter multi-kriteria: Berdasarkan Kata Kunci, Jenis Transaksi, **Tag Proyek**, dan Rentang Tanggal.
  - Tampilan Badge Tag Proyek berwarna untuk memudahkan pemindaian visual.
  - **Keamanan Hapus Data**: Menghapus transaksi otomatis menghapus ayat jurnal (`journal_entries` dan `journal_items`) terkait demi menjaga integritas data agar laporan tidak timpang.
  - **Mode Kasir Terisolasi**: Kasir hanya melihat transaksi yang dia input sendiri.

---

### MENU 3: MASTER DATA & ASET TETAP

#### Submenu 3.1: Bagan Akun (Chart of Accounts / COA) (`/master/accounts`)
* **Tujuan**: Daftar seluruh rekening penampung transaksi keuangan perusahaan yang tersusun dengan nomor kode terstruktur.
* **5 Kategori Baku Akun**:
  1. `1-xxxxx`: **Harta (Aset)** -> Kas, Bank, Piutang, Persediaan, Aktiva Tetap.
  2. `2-xxxxx`: **Kewajiban (Liabilitas)** -> Hutang Usaha, Hutang Gaji, Hutang Pajak.
  3. `3-xxxxx`: **Modal (Ekuitas)** -> Modal Pemilik, Modal Disetor, Laba Ditahan.
  4. `4-xxxxx`: **Pendapatan (Revenue)** -> Pendapatan Penjualan, Jasa Konsultasi.
  5. `6-xxxxx` / `8-xxxxx`: **Beban (Expenses)** -> Biaya Gaji, Listrik, Sewa, Iklan.
* **Fitur**: Input saldo awal, aktivasi/nonaktif akun, penentuan posisi saldo normal (Debit/Kredit).

#### Submenu 3.2: Pelanggan & Vendor (Kontak) (`/master/contacts`)
* **Tujuan**: Buku direktori rekanan bisnis.
* **Klasifikasi**: Pelanggan (*Customer*), Pemasok (*Vendor*), dan Karyawan (*Employee*).
* **Fungsi Akuntansi**: Mencegah salah penagihan dan menjadi dasar jejak audit transaksi.

#### Submenu 3.3: Metode Pembayaran (`/master/payment-methods`)
* **Tujuan**: Menghubungkan cara bayar riil konsumen (QRIS, Tunai Kasir, EDC BCA, Mandiri Transfer) dengan Akun Kas & Bank spesifik di COA.
* **Manfaat**: Kasir di lapangan cukup memilih "Bayar via QRIS", sistem otomatis mendebit rekening bank penampung QRIS.

#### Submenu 3.4: Master Pajak (`/master/taxes`)
* **Tujuan**: Mengatur tarif perpajakan yang berlaku (misal PPN 11%, PPh 23 2%).
* **Integrasi Akuntansi**: Terkoneksi ke akun Hutang Pajak Penjualan atau Piutang Pajak Pembelian.

#### Submenu 3.5: Tag / Label Proyek & Cabang (`/master/tags`)
* **Tujuan**: Mengelompokkan transaksi secara multidimensi tanpa harus merusak struktur COA.
* **Contoh Kasus Riil**:
  - Perusahaan mengerjakan *Proyek A* dan *Proyek B*.
  - Tanpa Tag: Perusahaan harus membuat Akun "Beban Bensin Proyek A" dan "Beban Bensin Proyek B" (COA jadi menumpuk ratusan akun).
  - Dengan Tag: Cukup 1 akun "Beban Bensin", lalu beri tag `🏷️ Proyek A` atau `🏷️ Proyek B`. Laporan Laba Rugi per proyek bisa langsung difilter!

#### Submenu 3.6: Tutup Buku Periode (`/closing`)
* **Tujuan**: Menutup siklus akuntansi bulanan atau tahunan secara resmi.
* **Mekanisme Kerja Otomatis**:
  1. Menghitung total seluruh Pendapatan dan Beban pada bulan berjalan.
  2. Menghasilkan selisih laba/rugi bersih.
  3. Memindahkan laba bersih tersebut ke akun **Laba Ditahan (Retained Earnings)** melalui jurnal penutup otomatis berpola `CLO/...`.
  4. Mengunci periode transaksi lampau agar data historis tidak dapat diedit atau dimanipulasi oleh staf.

#### Submenu 3.7: Modul Aset Tetap & Depresiasi (`/assets`)
* **Tujuan**: Mengelola aktiva berwujud yang berumur lebih dari 1 tahun (Mesin, Motor Delivery, Laptop, Gedung, Tanah).
* **Fitur**:
  - **Upload Foto & Preview**: Menyimpan bukti fisik aset lengkap dengan modal lightbox gambar.
  - **Jadwal Depresiasi Otomatis**: Mendukung metode *Garis Lurus (Straight Line)* dan *Saldo Menurun (Declining Balance)* berdasarkan masa manfaat (tahun/bulan) dan nilai residu.
  - **Pencatatan Nilai Buku**: Biaya Perolehan dikurangi Akumulasi Penyusutan secara otomatis.
  - **Tombol Stop Depresiasi**: Menghentikan penyusutan jika aset rusak total atau dijual.
  - **Export Excel**: Rekap daftar inventaris aset siap audit.

---

### MENU 4: LAPORAN KEUANGAN LENGKAP (STANDAR SAK)

#### Submenu 4.1: Laporan Jurnal Umum (`/reports/journal`)
* **Fungsi**: Buku harian historis kronologis yang mencatat semua mutasi debit dan kredit seluruh akun secara mendalam.
* **Fitur**: Pencarian cepat, filter jenis, filter tanggal, filter akun, **filter Tag Proyek**, badge status seimbang (*balanced*), tombol export Excel, cetak PDF, serta pop-up **Bukti Voucher Jurnal**.

#### Submenu 4.2: Laporan Laba Rugi (Profit and Loss) (`/reports/profit-loss`)
* **Fungsi**: Mengukur kinerja profitabilitas perusahaan selama periode tertentu.
* **Rumus**:
  $$\text{Laba Kotor} = \text{Pendapatan} - \text{HPP}$$
  $$\text{Laba Operasional} = \text{Laba Kotor} - \text{Beban Operasional}$$
  $$\text{Laba Bersih} = \text{Laba Operasional} \pm \text{Pendapatan/Beban Non-Operasional} - \text{Pajak}$$
* **Fitur Kunci**: Dilengkapi **Filter Tag / Proyek** untuk melihat profitabilitas per unit usaha/proyek mandiri, serta tombol sembunyikan/tampilkan kode akun.

#### Submenu 4.3: Neraca Keuangan (Balance Sheet) (`/reports/balance-sheet`)
* **Fungsi**: Foto posisi kekayaan perusahaan per tanggal tertentu (As of Date).
* **Format**: Format Skontro formal (Sisi Kiri: Aktiva/Harta; Sisi Kanan: Kewajiban & Ekuitas Modal). Harus selalu **Seimbang (Balance)**.

#### Submenu 4.4: Neraca Saldo (Trial Balance) (`/reports/trial-balance`)
* **Fungsi**: Lembar kerja pengujian untuk memastikan total debit seluruh akun buku besar sama persis dengan total kredit.

#### Submenu 4.5: Buku Besar (General Ledger) (`/reports/general-ledger`)
* **Fungsi**: Kartu histori mutasi untuk 1 akun spesifik (misal hanya melihat keluar-masuk Akun Kas Utama atau Akun Piutang) lengkap dengan saldo berjalan (*running balance*).

#### Submenu 4.6: Laporan Arus Kas (Cash Flow) (`/reports/cash-flow`)
* **Fungsi**: Menelusuri dari mana uang kas riil masuk dan ke mana uang kas riil keluar, terbagi atas 3 aktivitas:
  1. *Aktivitas Operasi*: Arus kas dari penjualan produk dan pembayaran operasional harian.
  2. *Aktivitas Investasi*: Pembelian atau penjualan aset tetap.
  3. *Aktivitas Pendanaan*: Suntikan modal pemilik atau pelunasan pinjaman bank.

#### Submenu 4.7: Laporan Beban Operasional (`/reports/operating-expenses`)
* **Fungsi**: Audit terperinci khusus seluruh pos beban biaya operasional untuk memangkas pemborosan perusahaan.

---

### MENU 5: PENGATURAN & AKSES KARYAWAN

#### Submenu 5.1: Pengaturan Utama (`/settings/main`)
* Identitas perusahaan (Nama, Logo, Alamat, No. Telepon, Mata Uang, Zona Waktu).
* **Konfigurasi AI Google Gemini**: Memasukkan API Key Gemini untuk mengaktifkan asisten pencatatan jurnal pintar.

#### Submenu 5.2: Pemetaan Akun Default (Account Mappings) (`/settings/account_mappings`)
* Menentukan akun penampung standar: Kas Utama, Piutang Usaha, Hutang Usaha, Pendapatan Penjualan, Beban HPP, dan Akun Laba Ditahan. Ini memastikan sistem otomatisasi jurnal tidak pernah salah kamar akun.

#### Submenu 5.3: Karyawan & Hak Akses (`/settings/employees`)
* Menambah staf baru dan menetapkan wewenang (Role: Administrator, Akuntan, Kasir, Auditor).

#### Submenu 5.4: Profil & Reset Data (`/settings/profile` & `/settings/reset-data`)
* Pengaturan kata sandi akun dan fitur **Reset Data Transaksi** (berguna bagi perusahaan yang ingin menghapus seluruh data simulasi/testing sebelum mulai dipakai secara riil).

---

### MENU 6: MULTI-PERUSAHAAN (TENANT SWITCHER) (`/company/switch`)
* **Fungsi**: Memungkinkan 1 akun pengguna mengelola banyak cabang/badan usaha sekaligus (misal: "PT Berkah Abadi" dan "CV Maju Lancar"). Berpindah perusahaan hanya dengan 1 klik tanpa perlu logout.

---

### MENU 7: LANGGANAN & SUPER ADMIN SAAS

#### Submenu 7.1: Langganan Tenant (`/subscription`)
* Halaman tagihan untuk tenant/klien: Menampilkan sisa masa aktif langganan, paket yang sedang aktif (Basic, Pro, Enterprise), serta histori invoice langganan.

#### Submenu 7.2: Super Admin Platform Master (`/superadmin/dashboard`)
* Portal khusus pemilik platform SaaS:
  - Melihat grafik total tenant pengguna aktif.
  - Memverifikasi pembayaran langganan.
  - Mengatur paket dan menghentikan (*suspend*) tenant yang menunggak tagihan.

---

## BAB 4: SKENARIO SIMULASI LIVE DEMO SAAT PRESENTASI

Agar presentasi Anda memukau dosen/penguji, ikuti urutan demo selama 7-10 menit berikut:

1. **Pembukaan & Tunjukkan Dashboard (1 Menit)**:
   - Login sebagai Administrator.
   - Buka halaman Dashboard, jelaskan 4 kartu KPI (Omset, Beban, Laba Bersih, Kas), dan grafik pendapatan.
2. **Demo Pencatatan Transaksi Cepat dengan AI (2 Menit)**:
   - Buka menu **Transaksi > Catat Transaksi**.
   - Tunjukkan fitur Asisten AI: ketik kalimat bahasa alami (misal: *"Beli ATK dan kertas printer 150rb tunai"*). Klik tombol proses AI -> form otomatis terisi Akun Beban ATK (Debit) dan Kas (Kredit).
   - Simulasikan pemilihan **Tag Proyek** (misal: pilih `🏷️ lokatara`). Klik **Simpan Transaksi**.
3. **Demo Audit Jurnal Realtime (1.5 Menit)**:
   - Masuk ke **Laporan > Jurnal Umum**.
   - Tunjukkan transaksi tadi langsung tercatat berpasangan (Double Entry) dan statusnya *Seimbang*.
   - Buka modal **Lihat Bukti Voucher Jurnal**.
4. **Demo Laporan Keuangan per Proyek (2 Menit)**:
   - Masuk ke **Laporan > Laba Rugi**.
   - Tunjukkan Laba Rugi Konsolidasi (seluruh perusahaan).
   - Ubah dropdown filter menjadi **`🏷️ lokatara`**, klik **Tampilkan** -> Tunjukkan bahwa Laba Rugi berubah menampilkan pendapatan dan pengeluaran khusus proyek lokatara tersebut.
   - Tunjukkan menu **Neraca** untuk membuktikan bahwa Aset = Kewajiban + Modal selalu seimbang.
5. **Demo Modul Aset Tetap & Depresiasi (1.5 Menit)**:
   - Buka menu **Aset Tetap > Daftar Aset**.
   - Tunjukkan daftar aset, klik foto aset tanah/kendaraan hingga muncul modal preview besar.
   - Jelaskan bahwa nilai buku dan akumulasi penyusutan dihitung otomatis oleh sistem tanpa perlu hitung kalkulator manual.
6. **Penutup (1 Menit)**:
   - Jelaskan fitur multi-cabang (Perusahaan Switcher) dan kontrol hak akses kasir.

---

## BAB 5: ANTISIPASI PERTANYAAN DOSEN/PENGUJI & KUNCI JAWABANNYA

### Q1: "Bagaimana sistem ini menjamin bahwa pembukuan tidak akan pernah berat sebelah (unbalanced)?"
> **Jawaban**:
> *"Sistem kami menggunakan validasi tingkat basis data transaksi dan validasi form di level antarmuka. Pada transaksi standar, sistem otomatis membentuk 1 sisi debit dan 1 sisi kredit dengan nominal identik. Pada mode majemuk, tombol simpan secara otomatis dinonaktifkan jika penjumlahan Debit tidak persis sama dengan penjumlahan Kredit. Selain itu, seluruh proses dibungkus dalam Database Transaction (ACID), sehingga jika salah satu baris gagal disimpan, seluruh transaksi otomatis dibatalkan (rollback) tanpa merusak keseimbangan jurnal."*

### Q2: "Apa bedanya mencatat transaksi menggunakan COA saja dibanding menggunakan Tag / Proyek?"
> **Jawaban**:
> *"Bagan Akun (COA) bersifat struktural dan standar akuntansi untuk kategori akun keuangan. Jika kita mengandalkan COA untuk memantau proyek, struktur akun akan membengkak ratusan baris baru setiap ada proyek baru. Dengan Tag Proyek, struktur COA tetap rapi dan ramping, namun transaksi memiliki dimensi analitik tambahan. Hasilnya, manajemen bisa memfilter Laba Rugi atau Beban khusus per proyek tanpa mengubah bagan akun utama perusahaan."*

### Q3: "Bagaimana sistem menangani penyusutan aset jika metode yang dipakai adalah Garis Lurus?"
> **Jawaban**:
> *"Pada metode Garis Lurus, sistem mengambil Biaya Akuisisi dikurangi Nilai Residu, lalu dibagi dengan Masa Manfaat (dalam bulan). Nilai beban penyusutan per bulan ini otomatis dialokasikan ke Akun Beban Penyusutan dan mengkredit Akun Akumulasi Penyusutan, sehingga Nilai Buku Bersih aset berkurang secara proporsional setiap periodenya."*

### Q4: "Apa fungsi Tutup Buku (Closing Period) dan kenapa staf tidak boleh mengedit data setelah tutup buku?"
> **Jawaban**:
> *"Tutup buku adalah proses memindahkan laba/rugi bersih periode berjalan ke akun permanen 'Laba Ditahan' (Retained Earnings) dan mengenolkan kembali akun pendapatan serta beban untuk menyambut periode baru. Penguncian data historis pasca tutup buku wajib dilakukan demi integritas audit dan kepatuhan perpajakan, agar laporan keuangan yang sudah dilaporkan ke pemegang saham atau kantor pajak tidak berubah-ubah secara sepihak."*

### Q5: "Apa teknologi di balik kecerdasan buatan (AI) pada aplikasi ini?"
> **Jawaban**:
> *"Aplikasi ini mengintegrasikan Google Gemini API melalui service khusus (`AiJournalingService`). Saat pengguna mengetik teks instruksi harian, sistem menyuntikkan daftar COA perusahaan yang relevan sebagai konteks prompt, lalu Gemini menganalisis entitas nominal, intent, serta memetakan akun debit dan kredit yang paling cocok dalam format JSON terstruktur untuk dieksekusi oleh sistem."*

---
*Dokumen ini disusun untuk bahan acuan resmi presentasi dan panduan penguasaan fitur Akutansiku.*
