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
        <h3 class="font-bold text-slate-800 text-sm mb-4">Tambahkan Alternatif Login Untuk Keamanan Akun</h3>
        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-200">
            <div class="flex items-center space-x-3">
                <i class="fa-brands fa-google text-rose-500 text-lg"></i>
                <span class="text-xs font-semibold text-slate-800">Google</span>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" checked class="sr-only peer">
                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
            </label>
        </div>
    </div>

    <!-- 3. Card Detail User -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 bg-slate-50/50">
            <h3 class="font-bold text-slate-800 text-sm">Detail User</h3>
        </div>
        <div class="p-6 space-y-4 text-xs">
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 mb-1">User ID</label>
                <input type="text" value="MTYzNTk0" readonly class="w-full px-3.5 py-2 bg-slate-100 border border-slate-200 rounded-lg text-slate-700 font-mono">
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 mb-1">Tanggal Pendaftaran</label>
                <input type="text" value="2026-09-08 16:14:55" readonly class="w-full px-3.5 py-2 bg-slate-100 border border-slate-200 rounded-lg text-slate-700 font-mono">
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 mb-1">Default Company</label>
                <input type="text" value="{{ $company->name ?? 'Dapur Gemoy' }}" readonly class="w-full px-3.5 py-2 bg-slate-100 border border-slate-200 rounded-lg text-slate-700 font-semibold">
            </div>
        </div>
    </div>

    <!-- 4. Card Hak Akses Saya -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden p-6">
        <h3 class="font-bold text-slate-800 text-sm mb-2">Hak Akses Saya</h3>
        <p class="text-xs text-slate-500 mb-4">Peran Anda saat ini pada perusahaan aktif</p>
        <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 font-bold text-xs border border-blue-200">
            <i class="fa-solid fa-shield-halved mr-2"></i> Administrator (Full Access)
        </span>
    </div>

</div>
@endsection
