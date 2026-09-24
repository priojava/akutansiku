@extends('layouts.app')

@section('title', 'Profil Pengguna')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div>
        <h1 class="text-xl font-bold text-slate-900">Profil & Keamanan</h1>
        <p class="text-xs text-slate-500 mt-1">Kelola data profil pengguna, login alternatif, dan hak akses</p>
    </div>

    <!-- 1. Card Profil -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 bg-slate-50/50">
            <h3 class="font-bold text-slate-800 text-sm">Profil</h3>
        </div>

        <form method="POST" action="{{ route('settings.profile.update') }}" class="p-6 space-y-5">
            @csrf

            <!-- Avatar & Nama -->
            <div class="flex items-center space-x-6 pb-2">
                <div class="w-16 h-16 rounded-full bg-slate-200 flex items-center justify-center text-slate-400 font-bold text-xl">
                    <i class="fa-solid fa-user"></i>
                </div>
                <div class="flex-1">
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-semibold text-slate-700">Nama</label>
                        <button type="button" class="text-xs text-blue-600 hover:underline">Ganti foto profil</button>
                    </div>
                    <input type="text" name="name" value="{{ old('name', $user->name ?? 'Priojaval') }}" required
                           class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs font-medium focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
            </div>

            <!-- No HP -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">No HP</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone ?? '+6282220073300') }}"
                       class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs font-medium focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            <!-- Email -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">
                    Email <i class="fa-solid fa-check text-emerald-500 ml-1 text-xs"></i>
                </label>
                <input type="email" name="email" value="{{ old('email', $user->email ?? 'priojaval@gmail.com') }}" required
                       class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs font-medium focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            <!-- Alamat -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat</label>
                <textarea name="address" rows="3" placeholder="your address here"
                          class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs font-medium focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">{{ old('address', $user->address ?? '') }}</textarea>
            </div>

            <div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-6 py-2.5 rounded-lg shadow-sm transition cursor-pointer">
                    Simpan Perubahan Profil
                </button>
            </div>
        </form>
    </div>

    <!-- 2. Card Ganti Password -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden" x-data="{ showCurrent: false, showNew: false, showConfirm: false }">
        <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-key text-blue-600"></i>
                    <span>Ganti Password</span>
                </h3>
                <p class="text-[11px] text-slate-500 mt-0.5">Perbarui kata sandi akun Anda untuk meningkatkan keamanan</p>
            </div>
        </div>

        <form method="POST" action="{{ route('settings.profile.update_password') }}" class="p-6 space-y-4 text-xs">
            @csrf

            @if($errors->any())
                <div class="p-3.5 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-xs space-y-1">
                    @foreach($errors->all() as $error)
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-circle-exclamation text-rose-600"></i>
                            <span>{{ $error }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Password Saat Ini -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">
                    Password Saat Ini <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <input :type="showCurrent ? 'text' : 'password'" name="current_password" required placeholder="Masukkan password saat ini"
                           class="w-full pl-3.5 pr-10 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-xs font-medium focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <button type="button" @click="showCurrent = !showCurrent" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 focus:outline-none cursor-pointer">
                        <i class="fa-regular" :class="showCurrent ? 'fa-eye-slash' : 'fa-eye'"></i>
                    </button>
                </div>
            </div>

            <!-- Password Baru & Konfirmasi (Grid 2 Kolom) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Password Baru <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input :type="showNew ? 'text' : 'password'" name="password" required placeholder="Minimal 6 karakter"
                               class="w-full pl-3.5 pr-10 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-xs font-medium focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <button type="button" @click="showNew = !showNew" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 focus:outline-none cursor-pointer">
                            <i class="fa-regular" :class="showNew ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                    <span class="text-[10px] text-slate-400 mt-1 block">Minimal 6 karakter</span>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Konfirmasi Password Baru <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input :type="showConfirm ? 'text' : 'password'" name="password_confirmation" required placeholder="Ulangi password baru"
                               class="w-full pl-3.5 pr-10 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-xs font-medium focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <button type="button" @click="showConfirm = !showConfirm" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 focus:outline-none cursor-pointer">
                            <i class="fa-regular" :class="showConfirm ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-xs font-bold px-6 py-2.5 rounded-lg shadow-sm transition flex items-center space-x-2 cursor-pointer">
                    <i class="fa-solid fa-shield-halved text-xs"></i>
                    <span>Perbarui Password</span>
                </button>
            </div>
        </form>
    </div>

    <!-- 2. Card Tambahkan Alternatif Login -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden p-6">
        <h3 class="font-bold text-slate-800 text-sm mb-1">Tambahkan Alternatif Login Untuk Keamanan Akun</h3>
        <p class="text-[11px] text-slate-500 mb-4">Hubungkan akun Google Anda untuk login instan tanpa mengetik password secara manual.</p>
        
        <div class="flex items-center justify-between p-3.5 bg-slate-50 rounded-xl border border-slate-200">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 rounded-lg bg-white border border-slate-200 flex items-center justify-center shadow-xs">
                    <svg class="w-4 h-4" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                    </svg>
                </div>
                <div>
                    <span class="text-xs font-bold text-slate-800 block">Google Single Sign-On</span>
                    @if(!empty($user?->google_id) || !empty(Auth::user()?->google_id))
                        <span class="text-[10px] text-emerald-600 font-semibold flex items-center gap-1">
                            <i class="fa-solid fa-circle-check text-[9px]"></i> Akun Google Terhubung (ID: {{ Str::limit($user->google_id ?? Auth::user()?->google_id, 10) }})
                        </span>
                    @else
                        <span class="text-[10px] text-slate-400">Belum ditautkan ke akun Google</span>
                    @endif
                </div>
            </div>

            @if(!empty($user?->google_id) || !empty(Auth::user()?->google_id))
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                    <i class="fa-solid fa-check mr-1"></i> Aktif
                </span>
            @else
                <a href="{{ route('auth.google') }}" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-semibold shadow-xs transition">
                    Tautkan Sekarang
                </a>
            @endif
        </div>
    </div>


    <!-- 3. Card Developer & API Tokens (Integrasi Sistem) -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden" x-data="{ showGenModal: false }">
        <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-key text-brand-600"></i>
                    <span>Kredensial API &amp; Integrasi Developer</span>
                </h3>
                <p class="text-[11px] text-slate-500 mt-0.5">Gunakan Kredensial &amp; Token ini untuk mengintegrasikan aplikasi Kasir POS, Mobile App, atau ERP eksternal.</p>
            </div>
            <a href="{{ route('docs.api') }}" target="_blank" class="px-3 py-1.5 bg-brand-50 hover:bg-brand-100 text-brand-700 rounded-lg text-xs font-semibold border border-brand-200 transition flex items-center gap-1.5">
                <i class="fa-solid fa-book-bookmark text-[11px]"></i>
                <span>Buka API Docs</span>
            </a>
        </div>

        <div class="p-6 space-y-5">
            <!-- Info Company ID & Base URL -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                    <span class="block text-[11px] font-semibold text-slate-500 mb-1">Company ID Aktif Anda</span>
                    <div class="flex items-center justify-between">
                        <span class="text-base font-extrabold text-brand-600 font-mono">ID: {{ $company->id ?? 1 }}</span>
                        <span class="text-[10px] bg-brand-100 text-brand-800 font-bold px-2 py-0.5 rounded-full">{{ $company->name ?? 'Perusahaan Utama' }}</span>
                    </div>
                </div>
                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                    <span class="block text-[11px] font-semibold text-slate-500 mb-1">Base Endpoint API</span>
                    <span class="text-xs font-mono font-bold text-slate-700 truncate block">{{ url('/api/v1') }}</span>
                </div>
            </div>

            <!-- Notifikasi Token Baru Digenerate -->
            @if(session('generated_token'))
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 space-y-2" x-data="{ copied: false }">
                    <div class="flex items-center space-x-2 font-bold text-xs text-emerald-800">
                        <i class="fa-solid fa-circle-check"></i>
                        <span>Token API Baru Berhasil Dibuat!</span>
                    </div>
                    <p class="text-[11px] text-emerald-700 leading-relaxed">
                        Salin token ini sekarang. Demi keamanan, token rahasia ini <b>tidak akan pernah ditampilkan lagi</b> di halaman ini.
                    </p>
                    <div class="flex items-center space-x-2">
                        <input type="text" id="newTokenVal" readonly value="{{ session('generated_token') }}"
                               class="flex-1 px-3 py-2 bg-white border border-emerald-300 rounded-lg text-xs font-mono font-bold text-slate-800 focus:outline-none">
                        <button type="button" @click="navigator.clipboard.writeText('{{ session('generated_token') }}'); copied = true; setTimeout(() => copied = false, 3000)"
                                class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold shadow-xs transition flex items-center space-x-1.5">
                            <i class="fa-solid fa-copy"></i>
                            <span x-text="copied ? 'Tersalin!' : 'Salin Token'"></span>
                        </button>
                    </div>
                </div>
            @endif

            <!-- Daftar Token yang Aktif -->
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-slate-700">Daftar API Token Pengguna ({{ count($tokens ?? []) }})</span>
                    <form method="POST" action="{{ route('settings.profile.token.generate') }}" class="inline">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-semibold shadow-xs transition flex items-center space-x-1.5">
                            <i class="fa-solid fa-plus text-[10px]"></i>
                            <span>Buat Token Baru</span>
                        </button>
                    </form>
                </div>

                @if(count($tokens ?? []) > 0)
                    <div class="border border-slate-200 rounded-xl overflow-hidden text-xs">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                                <tr>
                                    <th class="p-3">Nama Token</th>
                                    <th class="p-3">Dibuat Pada</th>
                                    <th class="p-3">Terakhir Dipakai</th>
                                    <th class="p-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700">
                                @foreach($tokens as $tok)
                                    <tr class="hover:bg-slate-50">
                                        <td class="p-3 font-semibold flex items-center gap-2">
                                            <i class="fa-solid fa-key text-amber-500 text-[11px]"></i>
                                            <span>{{ $tok->name }}</span>
                                        </td>
                                        <td class="p-3 text-slate-500">{{ $tok->created_at ? $tok->created_at->format('d M Y H:i') : '-' }}</td>
                                        <td class="p-3 text-slate-500">{{ $tok->last_used_at ? $tok->last_used_at->diffForHumans() : 'Belum pernah' }}</td>
                                        <td class="p-3 text-right">
                                            <form method="POST" action="{{ route('settings.profile.token.revoke', $tok->id) }}" onsubmit="return confirm('Cabut token ini? Aplikasi yang menggunakan token ini tidak akan bisa mengakses data lagi.')" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-rose-600 hover:text-rose-800 font-semibold hover:underline">
                                                    Cabut
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-4 text-center rounded-xl bg-slate-50 border border-slate-200 text-slate-500 text-xs">
                        Belum ada Token API aktif. Klik tombol <b>"Buat Token Baru"</b> untuk mulai menghubungkan aplikasi eksternal Anda.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- 4. Card Detail User -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 bg-slate-50/50">
            <h3 class="font-bold text-slate-800 text-sm">Detail User</h3>
        </div>
        <div class="p-6 space-y-4 text-xs">
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 mb-1">User ID</label>
                <input type="text" value="{{ $user->id ?? 1 }}" readonly class="w-full px-3.5 py-2 bg-slate-100 border border-slate-200 rounded-lg text-slate-700 font-mono">
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 mb-1">Tanggal Pendaftaran</label>
                <input type="text" value="{{ $user->created_at ?? now() }}" readonly class="w-full px-3.5 py-2 bg-slate-100 border border-slate-200 rounded-lg text-slate-700 font-mono">
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 mb-1">Default Company</label>
                <input type="text" value="{{ $company->name ?? 'Dapur Gemoy' }}" readonly class="w-full px-3.5 py-2 bg-slate-100 border border-slate-200 rounded-lg text-slate-700 font-semibold">
            </div>
        </div>
    </div>

    <!-- 5. Card Hak Akses Saya -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden p-6">
        <h3 class="font-bold text-slate-800 text-sm mb-2">Hak Akses Saya</h3>
        <p class="text-xs text-slate-500 mb-4">Peran Anda saat ini pada perusahaan aktif</p>
        <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 font-bold text-xs border border-blue-200">
            <i class="fa-solid fa-shield-halved mr-2"></i> Administrator (Full Access)
        </span>
    </div>

</div>
@endsection
