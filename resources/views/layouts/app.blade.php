<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sistem Akuntansi Enterprise') &mdash; {{ $company->name ?? 'Dapur Gemoy' }}</title>
    
    <!-- Google Fonts: Plus Jakarta Sans & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'Inter', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                            950: '#172554',
                        }
                    },
                    boxShadow: {
                        'glow': '0 0 25px -5px rgba(37, 99, 235, 0.25)',
                        'glow-lg': '0 0 35px -5px rgba(37, 99, 235, 0.35)',
                        'card': '0 2px 12px -2px rgba(15, 23, 42, 0.06), 0 1px 3px 0 rgba(15, 23, 42, 0.04)',
                        'card-hover': '0 12px 28px -4px rgba(15, 23, 42, 0.09), 0 4px 10px -2px rgba(15, 23, 42, 0.04)',
                    }
                }
            }
        }
    </script>
    
    <!-- FontAwesome 6 Pro-level Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        body {
            background-color: #f8fafc;
            color: #0f172a;
            font-family: 'Plus Jakarta Sans', sans-serif;
            letter-spacing: -0.01em;
        }
        .sidebar-scroll::-webkit-scrollbar {
            width: 4px;
        }
        .sidebar-scroll::-webkit-scrollbar-thumb {
            background: #e2e8f0;
            border-radius: 9999px;
        }
        .sidebar-scroll::-webkit-scrollbar-thumb:hover {
            background: #cbd5e1;
        }
        /* Custom Modern Sidebar Navigation */
        .sidebar-section-title {
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #94a3b8;
            padding: 0.75rem 0.75rem 0.25rem 0.75rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .nav-item-parent {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .nav-item-parent:hover {
            background-color: #f8fafc;
            transform: translateX(2px);
        }
        .nav-item-parent-active {
            background: linear-gradient(135deg, rgba(239, 246, 255, 0.9) 0%, rgba(241, 245, 249, 0.6) 100%);
            border: 1px solid rgba(191, 219, 254, 0.6);
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.02);
        }
        /* Submenu Tree Styles */
        .nav-tree-container {
            position: relative;
            margin-left: 1.25rem;
            padding-left: 0.875rem;
            border-left: 2px solid #e2e8f0;
            margin-top: 0.25rem;
            margin-bottom: 0.375rem;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        .nav-sub-link {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.45rem 0.65rem;
            border-radius: 0.625rem;
            font-size: 0.75rem;
            font-weight: 500;
            color: #475569;
            transition: all 0.15s ease-in-out;
        }
        .nav-sub-link::before {
            content: '';
            position: absolute;
            left: -1rem;
            top: 50%;
            width: 0.625rem;
            height: 2px;
            background-color: #e2e8f0;
            transition: background-color 0.15s;
        }
        .nav-sub-link:hover {
            color: #2563eb;
            background-color: #eff6ff;
            transform: translateX(2px);
        }
        .nav-sub-link:hover::before {
            background-color: #3b82f6;
        }
        .nav-sub-link-active {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;
            color: #ffffff !important;
            font-weight: 700 !important;
            box-shadow: 0 4px 10px -2px rgba(37, 99, 235, 0.35);
        }
        .nav-sub-link-active::before {
            background-color: #2563eb !important;
            height: 2px;
        }
        .nav-sub-link-active .sub-badge {
            background-color: rgba(255, 255, 255, 0.25) !important;
            color: #ffffff !important;
            border-color: rgba(255, 255, 255, 0.3) !important;
        }
        /* Glassmorphism utility */
        .glass-panel {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
    </style>
</head>
<body class="h-screen overflow-hidden flex flex-col antialiased bg-slate-50 text-slate-800" x-data="{ sidebarOpen: true, mobileMenuOpen: false }">

    @php
        $currentUser = auth()->user() ?? \App\Models\User::first();
        $currentRole = $currentUser ? $currentUser->getRoleInCompany($company->id ?? 1) : 'admin';
        $activeCompanyCoaCount = isset($company) && $company->id 
            ? \App\Models\Account::where('company_id', $company->id)->count() 
            : 120;
        
        $roleLabel = match ($currentRole) {
            'accountant' => 'Akuntan (Finance)',
            'cashier' => 'Kasir / Staf',
            'auditor' => 'Auditor',
            default => 'Administrator (Owner)'
        };
        
        $roleBadgeColor = match ($currentRole) {
            'accountant' => 'bg-blue-100 text-blue-800 border-blue-200',
            'cashier' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            'auditor' => 'bg-amber-100 text-amber-800 border-amber-200',
            default => 'bg-purple-100 text-purple-800 border-purple-200'
        };
    @endphp

    <!-- TOP HEADER (Fixed at Top) -->
    <header class="glass-panel border-b border-slate-200/80 sticky top-0 z-30 h-16 shrink-0 flex items-center justify-between px-4 lg:px-6 shadow-xs">
        <div class="flex items-center space-x-4">
            <!-- Mobile Toggle -->
            <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden p-2 rounded-xl text-slate-500 hover:text-blue-600 hover:bg-blue-50 focus:outline-none transition">
                <i class="fa-solid fa-bars text-lg"></i>
            </button>
            
            <!-- Company / Tenant Profile -->
            <a href="{{ route('company.switch') }}" class="flex items-center space-x-3 group">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-blue-700 via-indigo-600 to-blue-500 text-white flex items-center justify-center font-bold text-sm shadow-md shadow-blue-500/20 ring-2 ring-blue-100 group-hover:scale-105 transition-transform duration-200">
                    <i class="fa-solid fa-shapes text-white text-base"></i>
                </div>
                <div>
                    <div class="flex items-center space-x-1.5">
                        <span class="font-bold text-slate-900 group-hover:text-blue-600 transition text-sm sm:text-base tracking-tight">{{ $company->name ?? 'Dapur Gemoy' }}</span>
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-50 text-amber-700 border border-amber-200 shadow-2xs">
                            <i class="fa-solid fa-crown text-amber-500 mr-1 text-[9px]"></i> PRO
                        </span>
                    </div>
                    <div class="flex items-center space-x-2 text-[11px] text-slate-400 font-medium -mt-0.5">
                        <span>Multi-Cabang</span>
                        <span>&bull;</span>
                        <span class="text-emerald-600 font-semibold flex items-center">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1 animate-pulse"></span> Cloud Sync Active
                        </span>
                    </div>
                </div>
            </a>
        </div>

        <!-- Right Quick Actions, AI Shortcut & User Profile -->
        <div class="flex items-center space-x-2.5 sm:space-x-3">
            
            <!-- Quick New Transaction Button -->
            <a href="{{ route('transactions.create') }}" class="hidden sm:inline-flex items-center space-x-1.5 text-xs font-bold text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 px-3.5 py-2 rounded-xl shadow-md shadow-blue-600/20 hover:shadow-lg hover:shadow-blue-600/30 transition-all duration-200 group">
                <i class="fa-solid fa-plus text-xs group-hover:rotate-90 transition-transform duration-200"></i>
                <span>Catat Transaksi</span>
            </a>

            <!-- REST API Link Badge -->
            <a href="/api/v1/dashboard/summary" target="_blank" class="hidden md:inline-flex items-center space-x-1.5 text-xs bg-slate-50 hover:bg-blue-50/80 text-slate-700 hover:text-blue-700 border border-slate-200 hover:border-blue-200 px-3 py-2 rounded-xl font-semibold transition" title="Buka REST API Documentation JSON">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>REST API</span>
            </a>

            <!-- User Profile & Quick Switch Dropdown -->
            <div class="relative" x-data="{ userMenuOpen: false }">
                <button @click="userMenuOpen = !userMenuOpen" class="flex items-center space-x-2.5 text-slate-700 hover:text-blue-600 hover:border-blue-300 text-xs font-semibold bg-white hover:bg-slate-50 p-1.5 px-3 rounded-xl border border-slate-200 shadow-2xs transition focus:outline-none cursor-pointer">
                    <div class="w-7 h-7 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center text-xs font-extrabold shadow-sm">
                        {{ strtoupper(substr($currentUser->name ?? 'P', 0, 1)) }}
                    </div>
                    <div class="text-left hidden md:block">
                        <span class="block font-bold text-slate-800 text-xs leading-none">{{ $currentUser->name ?? 'Priojaval' }}</span>
                        <span class="text-[10px] text-slate-400 font-medium">{{ strtoupper($currentRole) }}</span>
                    </div>
                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 ml-0.5"></i>
                </button>

                <!-- Dropdown Menu -->
                <div x-show="userMenuOpen" x-cloak @click.away="userMenuOpen = false" class="absolute right-0 mt-2 w-64 bg-white rounded-2xl shadow-2xl border border-slate-100 py-2 z-50 animate-in fade-in zoom-in duration-150">
                    <div class="px-4 py-3 border-b border-slate-100">
                        <div class="font-bold text-xs text-slate-900">{{ $currentUser->name }}</div>
                        <div class="text-[11px] text-slate-400 truncate">{{ $currentUser->email }}</div>
                        <div class="mt-2">
                            <span class="text-[10px] px-2.5 py-0.5 rounded-full font-bold border {{ $roleBadgeColor }}">
                                {{ $roleLabel }}
                            </span>
                        </div>
                    </div>

                    <!-- Quick Role Switcher for Demo -->
                    <div class="px-3 py-2 border-b border-slate-100 bg-slate-50/75">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-2 mb-1.5">Ganti Akun Role (Testing):</p>
                        <div class="space-y-1">
                            <a href="{{ route('login.quick', 'admin') }}" class="flex items-center justify-between text-xs px-2.5 py-1.5 rounded-lg hover:bg-purple-50 text-purple-900 font-semibold transition">
                                <span class="flex items-center"><i class="fa-solid fa-crown text-purple-600 w-4 mr-1.5"></i> Administrator</span>
                                @if($currentRole === 'admin') <i class="fa-solid fa-circle-check text-purple-600 text-xs"></i> @endif
                            </a>
                            <a href="{{ route('login.quick', 'accountant') }}" class="flex items-center justify-between text-xs px-2.5 py-1.5 rounded-lg hover:bg-blue-50 text-blue-900 font-semibold transition">
                                <span class="flex items-center"><i class="fa-solid fa-briefcase text-blue-600 w-4 mr-1.5"></i> Akuntan</span>
                                @if($currentRole === 'accountant') <i class="fa-solid fa-circle-check text-blue-600 text-xs"></i> @endif
                            </a>
                            <a href="{{ route('login.quick', 'cashier') }}" class="flex items-center justify-between text-xs px-2.5 py-1.5 rounded-lg hover:bg-emerald-50 text-emerald-900 font-semibold transition">
                                <span class="flex items-center"><i class="fa-solid fa-cart-shopping text-emerald-600 w-4 mr-1.5"></i> Kasir / Staf</span>
                                @if($currentRole === 'cashier') <i class="fa-solid fa-circle-check text-emerald-600 text-xs"></i> @endif
                            </a>
                            <a href="{{ route('login.quick', 'superadmin') }}" class="flex items-center justify-between text-xs px-2.5 py-1.5 rounded-lg hover:bg-amber-50 text-amber-900 font-bold transition border border-amber-200/60 bg-amber-50/40">
                                <span class="flex items-center"><i class="fa-solid fa-crown text-amber-500 w-4 mr-1.5"></i> Super Admin (SaaS Master)</span>
                                @if($currentUser && $currentUser->is_superadmin) <i class="fa-solid fa-circle-check text-amber-600 text-xs"></i> @endif
                            </a>
                        </div>
                    </div>

                    <div class="p-1.5 space-y-0.5">
                        <a href="{{ route('subscription.index') }}" class="flex items-center space-x-2 px-3 py-2 text-xs text-blue-700 hover:bg-blue-50 rounded-xl font-bold transition">
                            <i class="fa-solid fa-credit-card text-blue-600 w-4"></i>
                            <span>Status Langganan & Tagihan</span>
                        </a>
                        <a href="{{ route('settings.profile') }}" class="flex items-center space-x-2 px-3 py-2 text-xs text-slate-700 hover:bg-slate-50 rounded-xl font-medium transition">
                            <i class="fa-regular fa-user text-slate-400 w-4"></i>
                            <span>Profil Pengguna</span>
                        </a>

                        <!-- Form Logout -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center space-x-2 px-3 py-2 text-xs text-rose-600 hover:bg-rose-50 rounded-xl font-bold transition text-left cursor-pointer">
                                <i class="fa-solid fa-right-from-bracket text-rose-500 w-4"></i>
                                <span>Keluar (Logout)</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </header>

    <!-- MAIN BODY CONTAINER -->
    <div class="flex flex-1 overflow-hidden relative">
        <!-- Backdrop for mobile menu -->
        <div x-show="mobileMenuOpen" @click="mobileMenuOpen = false" x-cloak class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-10 lg:hidden transition-opacity"></div>

        <!-- LEFT SIDEBAR (Fixed / Sticky on Left) -->
        <aside class="w-64 bg-white/95 backdrop-blur-md border-r border-slate-200/80 flex-shrink-0 flex flex-col fixed lg:static inset-y-0 left-0 top-16 z-20 transition-transform duration-200 ease-in-out lg:translate-x-0 h-[calc(100vh-4rem)]"
               :class="mobileMenuOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
            
            <div class="flex-1 overflow-y-auto sidebar-scroll p-3 space-y-3">
                
                <!-- SECTION 1: UTAMA -->
                <div>
                    <div class="sidebar-section-title">
                        <span>Menu Utama</span>
                        <i class="fa-solid fa-compass text-[9px] text-slate-400"></i>
                    </div>

                    <div class="space-y-1">
                        <!-- 1.1. Dashboard -->
                        <a href="{{ route('dashboard') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold nav-item-parent {{ request()->routeIs('dashboard') ? 'nav-item-parent-active text-blue-700 font-bold' : 'text-slate-600' }}">
                            <div class="flex items-center space-x-2.5">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center shadow-2xs {{ request()->routeIs('dashboard') ? 'bg-gradient-to-tr from-blue-600 to-indigo-600 text-white shadow-blue-500/20' : 'bg-blue-50 text-blue-600 border border-blue-100/60' }}">
                                    <i class="fa-solid fa-chart-pie text-xs"></i>
                                </div>
                                <span>Dashboard</span>
                            </div>
                            @if(request()->routeIs('dashboard'))
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                            @endif
                        </a>

                        <!-- 1.2. Transaksi -->
                        <div x-data="{ open: {{ request()->is('transactions*') ? 'true' : 'false' }} }">
                            <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold nav-item-parent {{ request()->is('transactions*') ? 'nav-item-parent-active text-indigo-700 font-bold' : 'text-slate-600' }}">
                                <div class="flex items-center space-x-2.5">
                                    <div class="w-7 h-7 rounded-lg flex items-center justify-center shadow-2xs {{ request()->is('transactions*') ? 'bg-gradient-to-tr from-indigo-600 to-blue-600 text-white shadow-indigo-500/20' : 'bg-indigo-50 text-indigo-600 border border-indigo-100/60' }}">
                                        <i class="fa-solid fa-money-bill-transfer text-xs"></i>
                                    </div>
                                    <span>Transaksi</span>
                                </div>
                                <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200" :class="open ? 'rotate-180 text-indigo-600' : 'text-slate-400'"></i>
                            </button>
                            <div x-show="open" x-collapse class="nav-tree-container">
                                <a href="{{ route('transactions.create') }}" class="nav-sub-link {{ request()->routeIs('transactions.create') ? 'nav-sub-link-active' : '' }}">
                                    <span class="flex items-center space-x-1.5">
                                        <i class="fa-solid fa-circle-plus text-[10px] {{ request()->routeIs('transactions.create') ? 'text-white' : 'text-emerald-500' }}"></i>
                                        <span>Catat Transaksi</span>
                                    </span>
                                    <span class="sub-badge text-[9px] px-1.5 py-0.2 rounded bg-emerald-50 text-emerald-600 border border-emerald-200">Baru</span>
                                </a>
                                <a href="{{ route('transactions.history') }}" class="nav-sub-link {{ request()->routeIs('transactions.history') ? 'nav-sub-link-active' : '' }}">
                                    <span class="flex items-center space-x-1.5">
                                        <i class="fa-solid fa-clock-rotate-left text-[10px] {{ request()->routeIs('transactions.history') ? 'text-white' : 'text-slate-400' }}"></i>
                                        <span>Riwayat Transaksi</span>
                                    </span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: MASTER DATA & ASSET -->
                @if($currentRole !== 'cashier')
                <div>
                    <div class="sidebar-section-title">
                        <span>Master & Aset</span>
                        <i class="fa-solid fa-layer-group text-[9px] text-slate-400"></i>
                    </div>

                    <div class="space-y-1">
                        <!-- 2.1. Master Data -->
                        <div x-data="{ open: {{ request()->is('master*') || request()->is('closing*') ? 'true' : 'false' }} }">
                            <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold nav-item-parent {{ request()->is('master*') || request()->is('closing*') ? 'nav-item-parent-active text-sky-700 font-bold' : 'text-slate-600' }}">
                                <div class="flex items-center space-x-2.5">
                                    <div class="w-7 h-7 rounded-lg flex items-center justify-center shadow-2xs {{ request()->is('master*') || request()->is('closing*') ? 'bg-gradient-to-tr from-sky-600 to-blue-600 text-white shadow-sky-500/20' : 'bg-sky-50 text-sky-600 border border-sky-100/60' }}">
                                        <i class="fa-solid fa-database text-xs"></i>
                                    </div>
                                    <span>Master Data</span>
                                </div>
                                <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200" :class="open ? 'rotate-180 text-sky-600' : 'text-slate-400'"></i>
                            </button>
                            <div x-show="open" x-collapse class="nav-tree-container">
                                <a href="{{ route('master.accounts') }}" class="nav-sub-link {{ request()->routeIs('master.accounts') ? 'nav-sub-link-active' : '' }}">
                                    <span>Akun (COA)</span>
                                    <span class="sub-badge text-[9px] px-1.5 py-0.2 rounded bg-sky-50 text-sky-600 border border-sky-200">{{ $activeCompanyCoaCount }} COA</span>
                                </a>
                                <a href="{{ route('closing.index') }}" class="nav-sub-link {{ request()->is('closing*') ? 'nav-sub-link-active' : '' }}">
                                    <span>Tutup Buku</span>
                                    <span class="sub-badge text-[9px] px-1.5 py-0.2 rounded bg-indigo-50 text-indigo-600 border border-indigo-200">Auto</span>
                                </a>
                                <a href="{{ route('master.contacts') }}" class="nav-sub-link {{ request()->routeIs('master.contacts') ? 'nav-sub-link-active' : '' }}">
                                    <span>Pelanggan & Vendor</span>
                                </a>
                                <a href="{{ route('master.payment_methods') }}" class="nav-sub-link {{ request()->routeIs('master.payment_methods') ? 'nav-sub-link-active' : '' }}">
                                    <span>Metode Bayar</span>
                                </a>
                                <a href="{{ route('master.taxes') }}" class="nav-sub-link {{ request()->routeIs('master.taxes') || request()->routeIs('taxes.index') ? 'nav-sub-link-active' : '' }}">
                                    <span>Master Pajak</span>
                                    <span class="sub-badge text-[9px] px-1.5 py-0.2 rounded bg-amber-50 text-amber-700 border border-amber-200">DJP</span>
                                </a>
                            </div>
                        </div>

                        <!-- 2.2. Aset Tetap -->
                        <div x-data="{ open: {{ request()->is('assets*') ? 'true' : 'false' }} }">
                            <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold nav-item-parent {{ request()->is('assets*') ? 'nav-item-parent-active text-emerald-700 font-bold' : 'text-slate-600' }}">
                                <div class="flex items-center space-x-2.5">
                                    <div class="w-7 h-7 rounded-lg flex items-center justify-center shadow-2xs {{ request()->is('assets*') ? 'bg-gradient-to-tr from-emerald-600 to-teal-600 text-white shadow-emerald-500/20' : 'bg-emerald-50 text-emerald-600 border border-emerald-100/60' }}">
                                        <i class="fa-solid fa-boxes-stacked text-xs"></i>
                                    </div>
                                    <span>Aset Tetap</span>
                                </div>
                                <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200" :class="open ? 'rotate-180 text-emerald-600' : 'text-slate-400'"></i>
                            </button>
                            <div x-show="open" x-collapse class="nav-tree-container">
                                <a href="{{ route('assets.index') }}" class="nav-sub-link {{ request()->routeIs('assets.index') ? 'nav-sub-link-active' : '' }}">
                                    <span>Daftar Aset & Susut</span>
                                </a>
                                <a href="{{ route('assets.create') }}" class="nav-sub-link {{ request()->routeIs('assets.create') ? 'nav-sub-link-active' : '' }}">
                                    <span>+ Tambah Aset</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- SECTION 3: LAPORAN KEUANGAN -->
                @if($currentRole !== 'cashier')
                <div>
                    <div class="sidebar-section-title">
                        <span>Laporan Keuangan</span>
                        <i class="fa-solid fa-chart-column text-[9px] text-slate-400"></i>
                    </div>

                    <div class="space-y-1">
                        <div x-data="{ open: {{ request()->is('reports*') ? 'true' : 'false' }} }">
                            <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold nav-item-parent {{ request()->is('reports*') ? 'nav-item-parent-active text-purple-700 font-bold' : 'text-slate-600' }}">
                                <div class="flex items-center space-x-2.5">
                                    <div class="w-7 h-7 rounded-lg flex items-center justify-center shadow-2xs {{ request()->is('reports*') ? 'bg-gradient-to-tr from-purple-600 to-indigo-600 text-white shadow-purple-500/20' : 'bg-purple-50 text-purple-600 border border-purple-100/60' }}">
                                        <i class="fa-solid fa-file-invoice-dollar text-xs"></i>
                                    </div>
                                    <span>Laporan</span>
                                </div>
                                <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200" :class="open ? 'rotate-180 text-purple-600' : 'text-slate-400'"></i>
                            </button>
                            <div x-show="open" x-collapse class="nav-tree-container">
                                <a href="{{ route('reports.journal') }}" class="nav-sub-link {{ request()->routeIs('reports.journal') ? 'nav-sub-link-active' : '' }}">
                                    <span>Jurnal Umum</span>
                                    <span class="sub-badge text-[9px] px-1.5 py-0.2 rounded bg-purple-50 text-purple-700 border border-purple-200">Audit</span>
                                </a>
                                <a href="{{ route('reports.profit_loss') }}" class="nav-sub-link {{ request()->routeIs('reports.profit_loss') ? 'nav-sub-link-active' : '' }}">
                                    <span>Laba Rugi (P&L)</span>
                                </a>
                                <a href="{{ route('reports.balance_sheet') }}" class="nav-sub-link {{ request()->routeIs('reports.balance_sheet') ? 'nav-sub-link-active' : '' }}">
                                    <span>Neraca Keuangan</span>
                                    <span class="sub-badge text-[9px] px-1.5 py-0.2 rounded bg-emerald-50 text-emerald-700 border border-emerald-200">Skontro</span>
                                </a>
                                <a href="{{ route('reports.trial_balance') }}" class="nav-sub-link {{ request()->routeIs('reports.trial_balance') ? 'nav-sub-link-active' : '' }}">
                                    <span>Neraca Saldo</span>
                                </a>
                                <a href="{{ route('reports.general_ledger') }}" class="nav-sub-link {{ request()->routeIs('reports.general_ledger') ? 'nav-sub-link-active' : '' }}">
                                    <span>Buku Besar (Ledger)</span>
                                </a>
                                <a href="{{ route('reports.cash_flow') }}" class="nav-sub-link {{ request()->routeIs('reports.cash_flow') ? 'nav-sub-link-active' : '' }}">
                                    <span>Arus Kas (Cash Flow)</span>
                                </a>
                                <a href="{{ route('reports.operating_expenses') }}" class="nav-sub-link {{ request()->routeIs('reports.operating_expenses') ? 'nav-sub-link-active' : '' }}">
                                    <span>Beban Operasional</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- SECTION 4: PENGATURAN & PERUSAHAAN -->
                @if($currentRole === 'admin')
                <div>
                    <div class="sidebar-section-title">
                        <span>Sistem & Akses</span>
                        <i class="fa-solid fa-sliders text-[9px] text-slate-400"></i>
                    </div>

                    <div class="space-y-1">
                        <!-- 4.1. Pengaturan -->
                        <div x-data="{ open: {{ request()->is('settings*') ? 'true' : 'false' }} }">
                            <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold nav-item-parent {{ request()->is('settings*') ? 'nav-item-parent-active text-slate-900 font-bold' : 'text-slate-600' }}">
                                <div class="flex items-center space-x-2.5">
                                    <div class="w-7 h-7 rounded-lg flex items-center justify-center shadow-2xs {{ request()->is('settings*') ? 'bg-gradient-to-tr from-slate-700 to-slate-900 text-white shadow-slate-500/20' : 'bg-slate-100 text-slate-700 border border-slate-200/60' }}">
                                        <i class="fa-solid fa-gear text-xs"></i>
                                    </div>
                                    <span>Pengaturan</span>
                                </div>
                                <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200" :class="open ? 'rotate-180 text-slate-700' : 'text-slate-400'"></i>
                            </button>
                            <div x-show="open" x-collapse class="nav-tree-container">
                                <a href="{{ route('settings.main') }}" class="nav-sub-link {{ request()->routeIs('settings.main') ? 'nav-sub-link-active' : '' }}">
                                    <span>Pengaturan Utama</span>
                                </a>
                                <a href="{{ route('settings.account_mappings') }}" class="nav-sub-link {{ request()->routeIs('settings.account_mappings') ? 'nav-sub-link-active' : '' }}">
                                    <span>Akun Perkiraan</span>
                                    <span class="sub-badge text-[9px] px-1.5 py-0.2 rounded bg-indigo-50 text-indigo-600 border border-indigo-200">Default</span>
                                </a>
                                <a href="{{ route('settings.employees') }}" class="nav-sub-link {{ request()->routeIs('settings.employees') ? 'nav-sub-link-active' : '' }}">
                                    <span>Karyawan & Akses</span>
                                </a>
                                <a href="{{ route('settings.profile') }}" class="nav-sub-link {{ request()->routeIs('settings.profile') ? 'nav-sub-link-active' : '' }}">
                                    <span>Profil & Keamanan</span>
                                </a>
                            </div>
                        </div>

                        <!-- 4.2. Perusahaan Switcher -->
                        <a href="{{ route('company.switch') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold nav-item-parent {{ request()->routeIs('company.switch') ? 'nav-item-parent-active text-amber-700 font-bold' : 'text-slate-600' }}">
                            <div class="flex items-center space-x-2.5">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center shadow-2xs {{ request()->routeIs('company.switch') ? 'bg-gradient-to-tr from-amber-600 to-orange-600 text-white shadow-amber-500/20' : 'bg-amber-50 text-amber-600 border border-amber-100/60' }}">
                                    <i class="fa-solid fa-building text-xs"></i>
                                </div>
                                <span>Perusahaan</span>
                            </div>
                            <span class="text-[9px] font-bold text-amber-600 bg-amber-50 border border-amber-200 px-1.5 py-0.2 rounded">Multi</span>
                        </a>

                        <!-- 4.3. Langganan & Billing -->
                        <a href="{{ route('subscription.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold nav-item-parent {{ request()->routeIs('subscription.*') ? 'nav-item-parent-active text-blue-700 font-bold' : 'text-slate-600' }}">
                            <div class="flex items-center space-x-2.5">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center shadow-2xs {{ request()->routeIs('subscription.*') ? 'bg-gradient-to-tr from-blue-600 to-indigo-600 text-white shadow-blue-500/20' : 'bg-blue-50 text-blue-600 border border-blue-100/60' }}">
                                    <i class="fa-solid fa-credit-card text-xs"></i>
                                </div>
                                <span>Langganan</span>
                            </div>
                            <span class="text-[9px] font-bold text-purple-700 bg-purple-50 border border-purple-200 px-1.5 py-0.2 rounded">PRO</span>
                        </a>
                    </div>
                </div>
                @endif

                <!-- SECTION 5: SUPER ADMIN SAAS (MASTER CONTROL) -->
                @if($currentUser && ($currentUser->is_superadmin || $currentUser->email === 'superadmin@dapurgemoy.com' || request()->is('superadmin*')))
                <div>
                    <div class="sidebar-section-title">
                        <span>Master SaaS Portal</span>
                        <i class="fa-solid fa-crown text-[9px] text-amber-500"></i>
                    </div>

                    <div class="space-y-1">
                        <a href="{{ route('superadmin.dashboard') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold nav-item-parent {{ request()->routeIs('superadmin.dashboard') ? 'nav-item-parent-active text-purple-700 font-bold' : 'text-slate-600' }}">
                            <div class="flex items-center space-x-2.5">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center shadow-2xs {{ request()->routeIs('superadmin.dashboard') ? 'bg-gradient-to-tr from-purple-700 to-indigo-800 text-white shadow-purple-500/20' : 'bg-purple-50 text-purple-700 border border-purple-200' }}">
                                    <i class="fa-solid fa-chart-line text-xs"></i>
                                </div>
                                <span>SaaS Overview</span>
                            </div>
                        </a>

                        <a href="{{ route('superadmin.tenants') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold nav-item-parent {{ request()->routeIs('superadmin.tenants') ? 'nav-item-parent-active text-purple-700 font-bold' : 'text-slate-600' }}">
                            <div class="flex items-center space-x-2.5">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center shadow-2xs {{ request()->routeIs('superadmin.tenants') ? 'bg-gradient-to-tr from-purple-700 to-indigo-800 text-white shadow-purple-500/20' : 'bg-purple-50 text-purple-700 border border-purple-200' }}">
                                    <i class="fa-solid fa-building-user text-xs"></i>
                                </div>
                                <span>Semua Tenant</span>
                            </div>
                        </a>

                        <a href="{{ route('superadmin.invoices') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold nav-item-parent {{ request()->routeIs('superadmin.invoices') ? 'nav-item-parent-active text-purple-700 font-bold' : 'text-slate-600' }}">
                            <div class="flex items-center space-x-2.5">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center shadow-2xs {{ request()->routeIs('superadmin.invoices') ? 'bg-gradient-to-tr from-purple-700 to-indigo-800 text-white shadow-purple-500/20' : 'bg-purple-50 text-purple-700 border border-purple-200' }}">
                                    <i class="fa-solid fa-receipt text-xs"></i>
                                </div>
                                <span>Semua Tagihan</span>
                            </div>
                        </a>
                    </div>
                </div>
                @endif

            </div>

            <!-- Sidebar Footer -->
            <div class="p-3 border-t border-slate-200/80 text-[11px] text-slate-400 flex items-center justify-between bg-slate-50/70">
                <span class="font-medium text-slate-400">v1.2.0 &bull; Enterprise</span>
                <span class="flex items-center text-blue-700 font-bold bg-blue-50 px-2 py-0.5 rounded-lg border border-blue-100 shadow-2xs">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500 mr-1.5 animate-pulse"></span> {{ strtoupper($currentRole) }}
                </span>
            </div>
        </aside>

        <!-- MAIN CONTENT AREA -->
        <main class="flex-1 min-w-0 p-4 lg:p-8 overflow-y-auto">
            @if(session('success'))
                <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-sm flex items-center space-x-3 shadow-xs animate-in fade-in duration-200">
                    <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-circle-check text-base"></i>
                    </div>
                    <span class="font-semibold">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 text-sm flex items-center space-x-3 shadow-xs animate-in fade-in duration-200">
                    <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-circle-exclamation text-base"></i>
                    </div>
                    <span class="font-semibold">{{ session('error') }}</span>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    @yield('scripts')
</body>
</html>
