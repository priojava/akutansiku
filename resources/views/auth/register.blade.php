<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Baru & Free Trial 14 Hari &mdash; Akuntansi Cloud SaaS</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: radial-gradient(circle at top right, #eff6ff 0%, #f8fafc 100%);
            letter-spacing: -0.01em;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 lg:p-8 antialiased">

    <div class="max-w-4xl w-full grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
        
        <!-- LEFT SIDE: VALUE PROPOSITIONS & PERKS -->
        <div class="lg:col-span-5 space-y-6 text-slate-800">
            <!-- Brand Logo -->
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-blue-700 via-indigo-600 to-blue-500 text-white flex items-center justify-center shadow-lg shadow-blue-500/25">
                    <i class="fa-solid fa-shapes text-xl"></i>
                </div>
                <div>
                    <span class="font-extrabold text-slate-900 text-lg tracking-tight block">Akuntansi Cloud SaaS</span>
                    <span class="text-[11px] text-blue-600 font-semibold uppercase tracking-wider">Enterprise Multi-Tenant</span>
                </div>
            </div>

            <div class="space-y-2">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight leading-tight">
                    Mulai Pembukuan & Keuangan Bisnis dalam 60 Detik.
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 leading-relaxed">
                    Daftar akun sekarang dan nikmati akses penuh ke seluruh fitur akuntansi standar industri tanpa syarat kartu kredit.
                </p>
            </div>

            <!-- Features Checklist -->
            <div class="space-y-3.5 pt-2">
                <div class="flex items-start space-x-3">
                    <div class="w-6 h-6 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xs flex-shrink-0 mt-0.5 border border-emerald-200">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <div>
                        <strong class="text-xs font-bold text-slate-800 block">Free Trial 14 Hari Penuh</strong>
                        <span class="text-[11px] text-slate-500">Coba semua fitur Pro Enterprise tanpa batasan.</span>
                    </div>
                </div>

                <div class="flex items-start space-x-3">
                    <div class="w-6 h-6 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs flex-shrink-0 mt-0.5 border border-blue-200">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <div>
                        <strong class="text-xs font-bold text-slate-800 block">Otomatis 120 Bagan Akun (COA)</strong>
                        <span class="text-[11px] text-slate-500">Langsung siap pakai sesuai standar SAK EMKM Indonesia.</span>
                    </div>
                </div>

                <div class="flex items-start space-x-3">
                    <div class="w-6 h-6 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs flex-shrink-0 mt-0.5 border border-indigo-200">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <div>
                        <strong class="text-xs font-bold text-slate-800 block">Terminal Kasir & AI Journaling</strong>
                        <span class="text-[11px] text-slate-500">Isolasi shift kasir & input transaksi dari bahasa sehari-hari.</span>
                    </div>
                </div>

                <div class="flex items-start space-x-3">
                    <div class="w-6 h-6 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-xs flex-shrink-0 mt-0.5 border border-purple-200">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <div>
                        <strong class="text-xs font-bold text-slate-800 block">Laporan Keuangan & Pajak DJP</strong>
                        <span class="text-[11px] text-slate-500">P&L, Neraca Saldo, Arus Kas, dan Tutup Buku Otomatis.</span>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-200 text-xs text-slate-400">
                <span>Dipercaya oleh UMKM, Cafe & Resto, Retail, dan Jasa.</span>
            </div>
        </div>

        <!-- RIGHT SIDE: REGISTRATION FORM CARD -->
        <div class="lg:col-span-7 bg-white rounded-3xl border border-slate-200/90 p-6 sm:p-8 shadow-xl shadow-slate-200/50 space-y-6">
            
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Form Pendaftaran Klien Baru</h2>
                    <p class="text-xs text-slate-400">Inisialisasi akun pemilik & perusahaan Anda</p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    TRIAL 14 HARI
                </span>
            </div>

            <!-- 1-Click Google Signup -->
            <a href="{{ route('auth.google') }}" id="btn-google-register"
               class="w-full py-2.5 px-4 bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 text-xs font-bold rounded-xl shadow-sm flex items-center justify-center space-x-2 transition hover:shadow-md hover:border-slate-400">
                <svg class="w-4 h-4 flex-shrink-0" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                </svg>
                <span>Daftar Cepat dengan Akun Google</span>
            </a>

            <!-- Divider -->
            <div class="relative flex py-0.5 items-center">
                <div class="flex-grow border-t border-slate-200"></div>
                <span class="flex-shrink mx-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider">atau isi form manual</span>
                <div class="flex-grow border-t border-slate-200"></div>
            </div>


            @if($errors->any())
                <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium space-y-1">
                    <div class="font-bold flex items-center space-x-1">
                        <i class="fa-solid fa-circle-exclamation text-rose-600"></i>
                        <span>Mohon perbaiki kesalahan berikut:</span>
                    </div>
                    <ul class="list-disc list-inside space-y-0.5 text-[11px]">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register.post') }}" class="space-y-4 text-xs">
                @csrf

                <!-- 1. DATA PERUSAHAAN -->
                <div class="space-y-3 p-4 bg-slate-50/75 rounded-2xl border border-slate-200/60">
                    <span class="font-extrabold text-blue-700 uppercase tracking-wider text-[10px] block">1. Data Unit Bisnis / Usaha</span>
                    
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nama Perusahaan / Usaha <span class="text-rose-500">*</span></label>
                        <input type="text" name="company_name" value="{{ old('company_name') }}" placeholder="Contoh: TOKO SEMBAKO TAJI / Dapur Mantap" required
                               class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Kota Domisili</label>
                            <input type="text" name="city" value="{{ old('city', 'Bekasi') }}" placeholder="Contoh: Bekasi / Jakarta"
                                   class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">No. WhatsApp / Telepon</label>
                            <input type="text" name="phone" value="{{ old('phone') }}" placeholder="Contoh: 08123456789"
                                   class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                        </div>
                    </div>
                </div>

                <!-- 2. DATA AKUN PEMILIK -->
                <div class="space-y-3 p-4 bg-slate-50/75 rounded-2xl border border-slate-200/60">
                    <span class="font-extrabold text-blue-700 uppercase tracking-wider text-[10px] block">2. Data Pemilik Akun (Owner)</span>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nama Lengkap Anda <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="Contoh: Ahmad Suprianto" required
                               class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Email Login <span class="text-rose-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="Contoh: owner@tokotaji.com" required
                               class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Password <span class="text-rose-500">*</span></label>
                            <input type="password" name="password" placeholder="Minimal 6 karakter" required
                                   class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Ulangi Password <span class="text-rose-500">*</span></label>
                            <input type="password" name="password_confirmation" placeholder="Konfirmasi password" required
                                   class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                        </div>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3.5 px-4 bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 hover:from-blue-700 hover:to-indigo-800 text-white font-bold rounded-2xl shadow-lg shadow-blue-500/25 transition-all duration-200 hover:scale-[1.01] active:scale-[0.99] flex items-center justify-center space-x-2 text-sm cursor-pointer">
                        <i class="fa-solid fa-rocket text-xs text-amber-300"></i>
                        <span>Daftar & Mulai Trial 14 Hari Gratis</span>
                    </button>
                </div>

                <div class="text-center pt-2">
                    <p class="text-slate-500">
                        Sudah memiliki akun?
                        <a href="{{ route('login') }}" class="font-bold text-blue-600 hover:text-blue-800 hover:underline">
                            Masuk di Sini &rarr;
                        </a>
                    </p>
                </div>
            </form>

        </div>

    </div>

</body>
</html>
