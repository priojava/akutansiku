<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk &mdash; Akuntansiku</title>
    
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
<body class="min-h-screen flex items-center justify-center p-4 antialiased">

    <div class="max-w-md w-full space-y-6">
        
        <!-- Header Brand -->
        <div class="text-center space-y-2">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-tr from-blue-700 via-indigo-600 to-blue-500 text-white shadow-xl shadow-blue-500/25 mb-2">
                <i class="fa-solid fa-shapes text-2xl text-white"></i>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Akuntansiku</h1>
            <p class="text-xs text-slate-500 font-medium">Sistem Pembukuan & Akuntansi Multi-User (RBAC)</p>
        </div>

        @if(session('success'))
            <div class="p-4 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 text-xs font-semibold flex items-center space-x-2">
                <i class="fa-solid fa-circle-check text-blue-600 text-sm"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center space-x-2">
                <i class="fa-solid fa-circle-exclamation text-rose-600 text-sm"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Form Login Card -->
        <div class="bg-white rounded-2xl border border-slate-200 p-8 shadow-xl shadow-slate-200/50 space-y-6">
            
            <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Email Akun</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                            <i class="fa-regular fa-envelope text-xs"></i>
                        </span>
                        <input type="email" name="email" id="input-email" value="{{ old('email', 'admin@dapurgemoy.com') }}" required
                               class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold text-slate-700">Password</label>
                        <a href="#" class="text-[11px] text-blue-600 hover:underline font-semibold">Lupa Password?</a>
                    </div>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                            <i class="fa-solid fa-lock text-xs"></i>
                        </span>
                        <input type="password" name="password" id="input-password" value="password123" required
                               class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300">
                        <span class="text-slate-600 font-medium">Ingat Saya</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-lg shadow-blue-500/25 transition">
                    Masuk ke Sistem
                </button>
            </form>

            <!-- 1-Click Quick Demo Login -->
            <div class="pt-4 border-t border-slate-100">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center mb-3">
                    🚀 1-Klik Masuk Sebagai (Test Role)
                </p>

                <div class="grid grid-cols-4 gap-2">
                    <!-- 1. Admin -->
                    <a href="{{ route('login.quick', 'admin') }}" class="p-2 bg-purple-50 hover:bg-purple-100 border border-purple-200 rounded-xl text-center group transition">
                        <div class="text-xs mb-0.5">👑</div>
                        <div class="font-bold text-[10px] text-purple-900">Admin</div>
                    </a>

                    <!-- 2. Akuntan -->
                    <a href="{{ route('login.quick', 'accountant') }}" class="p-2 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-xl text-center group transition">
                        <div class="text-xs mb-0.5">💼</div>
                        <div class="font-bold text-[10px] text-blue-900">Akuntan</div>
                    </a>

                    <!-- 3. Kasir -->
                    <a href="{{ route('login.quick', 'cashier') }}" class="p-2 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-xl text-center group transition">
                        <div class="text-xs mb-0.5">🛒</div>
                        <div class="font-bold text-[10px] text-emerald-900">Kasir</div>
                    </a>

                    <!-- 4. Super Admin -->
                    <a href="{{ route('login.quick', 'superadmin') }}" class="p-2 bg-amber-50 hover:bg-amber-100 border border-amber-200 rounded-xl text-center group transition">
                        <div class="text-xs mb-0.5">⚡</div>
                        <div class="font-bold text-[10px] text-amber-900">SuperAdmin</div>
                    </a>
                </div>

                <!-- Link to Register -->
                <div class="mt-4 pt-3 border-t border-slate-100 text-center">
                    <p class="text-xs text-slate-500">
                        Belum punya akun bisnis?
                        <a href="{{ route('register') }}" class="font-bold text-blue-600 hover:text-blue-800 hover:underline">
                            Daftar & Coba Gratis 14 Hari &rarr;
                        </a>
                    </p>
                </div>
            </div>

        </div>

        <div class="text-center text-xs text-slate-400">
            &copy; 2026 Akuntansiku &bull; Enterprise SaaS Platform
        </div>

    </div>

</body>
</html>
