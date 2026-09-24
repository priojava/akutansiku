<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sistem Akuntansi Enterprise') &mdash; {{ $company->name ?? 'Dapur Gemoy' }}</title>

    <!-- Google Fonts: Plus Jakarta Sans & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap"
        rel="stylesheet">

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

    <!-- Early Tab Mode Detector (Accurate MDI) -->
    <script>
        const isChildTab = (window.self !== window.top);
        if (isChildTab) {
            document.documentElement.classList.add('in-tab-mode');
        }
    </script>

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

        /* Accurate MDI Multi-Tab Display Management */
        html.in-tab-mode #top-app-header,
        html.in-tab-mode #app-sidebar,
        html.in-tab-mode #master-tab-workspace {
            display: none !important;
        }

        html.in-tab-mode body {
            overflow-y: auto !important;
            height: auto !important;
            min-height: 100vh !important;
            background-color: #f8fafc !important;
        }

        html.in-tab-mode #in-tab-content-area {
            display: block !important;
            min-height: 100vh !important;
        }

        /* Master Workspace (Parent Window) */
        html:not(.in-tab-mode) #in-tab-content-area {
            display: none !important;
        }

        html:not(.in-tab-mode) #master-tab-workspace {
            display: flex !important;
        }

        /* Accurate Tab Item Styling */
        .accurate-tab-item {
            transition: all 0.12s ease-in-out;
            border-top: 3px solid transparent;
        }

        .accurate-tab-item.active {
            background-color: #ffffff !important;
            color: #1e40af !important;
            border-top: 3px solid #2563eb !important;
            border-bottom: 1px solid #ffffff !important;
            box-shadow: 0 -2px 6px -1px rgba(0, 0, 0, 0.04) !important;
            font-weight: 700 !important;
        }

        .accurate-tab-item:not(.active) {
            background-color: #e2e8f0;
            color: #475569;
            border-bottom: 1px solid #cbd5e1;
        }

        .accurate-tab-item:not(.active):hover {
            background-color: #cbd5e1;
            color: #0f172a;
        }

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
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

<body class="h-screen overflow-hidden flex flex-col antialiased bg-slate-50 text-slate-800"
    x-data="{ sidebarOpen: true, mobileMenuOpen: false }">

    @php
        $currentUser = auth()->user() ?? \App\Models\User::first();
        $currentRole = $currentUser ? $currentUser->getRoleInCompany($company->id ?? 1) : 'admin';
        $activeCompanyCoaCount = isset($company) && $company->id
            ? \App\Models\Account::where('company_id', $company->id)->count()
            : 120;

        $roleLabel = match ($currentRole) {
            'accountant' => 'Akuntan (Finance)',
            'auditor' => 'Auditor (Pemeriksa)',
            'staff', 'cashier' => 'Staff Operasional',
            default => 'Owner (Administrator)'
        };

        $roleBadgeColor = match ($currentRole) {
            'accountant' => 'bg-blue-100 text-blue-800 border-blue-200',
            'auditor' => 'bg-amber-100 text-amber-800 border-amber-200',
            'staff', 'cashier' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            default => 'bg-purple-100 text-purple-800 border-purple-200'
        };
    @endphp

    <!-- TOP HEADER (Fixed at Top) -->
    <header id="top-app-header"
        class="glass-panel border-b border-slate-200/80 sticky top-0 z-30 h-16 shrink-0 flex items-center justify-between px-4 lg:px-6 shadow-xs">
        <div class="flex items-center space-x-4">
            <!-- Mobile Toggle -->
            <button @click="mobileMenuOpen = !mobileMenuOpen"
                class="lg:hidden p-2 rounded-xl text-slate-500 hover:text-blue-600 hover:bg-blue-50 focus:outline-none transition">
                <i class="fa-solid fa-bars text-lg"></i>
            </button>

            <!-- Company / Tenant Profile -->
            <a href="{{ route('company.switch') }}" class="flex items-center space-x-3 group open-in-tab"
                data-tab-title="Kelola Usaha" data-tab-icon="fa-solid fa-shapes">
                <div
                    class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-blue-700 via-indigo-600 to-blue-500 text-white flex items-center justify-center font-bold text-sm shadow-md shadow-blue-500/20 ring-2 ring-blue-100 group-hover:scale-105 transition-transform duration-200">
                    <i class="fa-solid fa-shapes text-white text-base"></i>
                </div>
                <div>
                    <div class="flex items-center space-x-1.5">
                        <span
                            class="font-bold text-slate-900 group-hover:text-blue-600 transition text-sm sm:text-base tracking-tight">{{ $company->name ?? 'Dapur Gemoy' }}</span>
                        @if(($company->subscription_plan ?? $company->plan_type) === 'premium')
                            <span
                                class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-50 text-amber-700 border border-amber-200 shadow-2xs">
                                <i class="fa-solid fa-crown text-amber-500 mr-1 text-[9px]"></i> PRO
                            </span>
                        @else
                            <span
                                class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-extrabold bg-blue-50 text-blue-700 border border-blue-200 shadow-2xs">
                                STANDARD
                            </span>
                        @endif
                    </div>
                    <div class="flex items-center space-x-2 text-[11px] text-slate-400 font-medium -mt-0.5">
                        <span>{{ ($company->subscription_plan ?? $company->plan_type) === 'premium' ? 'Multi-Cabang' : '1 Cabang' }}</span>
                        <span>&bull;</span>
                        <span class="text-emerald-600 font-semibold flex items-center">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1 animate-pulse"></span> Cloud Sync
                            Active
                        </span>
                    </div>
                </div>
            </a>

            <!-- MDI Multi-Tab Indicator Badge -->
            <div
                class="hidden xl:flex items-center space-x-1.5 px-2.5 py-1 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200/80 rounded-xl text-blue-800 text-[11px] font-bold shadow-2xs">
                <i class="fa-solid fa-table-columns text-blue-600 text-xs"></i>
                <span>Multi-Tab Active</span>
            </div>
        </div>

        <!-- Right Quick Actions, AI Shortcut & User Profile -->
        <div class="flex items-center space-x-2.5 sm:space-x-3">

            <!-- Quick New Transaction Button -->
            @if(isset($company) && $company->isReadOnly())
                <a href="{{ route('subscription.index') }}"
                    class="hidden sm:inline-flex items-center space-x-1.5 text-xs font-bold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 px-3.5 py-2 rounded-xl shadow-2xs transition open-in-tab"
                    data-tab-title="Langganan" data-tab-icon="fa-solid fa-lock"
                    title="Akun Terkunci (Read-Only) - Klik untuk perpanjang langganan">
                    <i class="fa-solid fa-lock text-rose-600 text-xs"></i>
                    <span>Terkunci (Read-Only)</span>
                </a>
            @else
                <a href="{{ route('transactions.create') }}"
                    class="open-in-tab hidden sm:inline-flex items-center space-x-1.5 text-xs font-bold text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 px-3.5 py-2 rounded-xl shadow-md shadow-blue-600/20 hover:shadow-lg hover:shadow-blue-600/30 transition-all duration-200 group"
                    data-tab-title="Catat Transaksi" data-tab-icon="fa-solid fa-circle-plus">
                    <i class="fa-solid fa-plus text-xs group-hover:rotate-90 transition-transform duration-200"></i>
                    <span>Catat Transaksi</span>
                </a>
            @endif

            <!-- REST API Link Badge -->
            <a href="{{ url('/api/v1/dashboard/summary?company_id=' . ($company->id ?? session('active_company_id', 1))) }}"
                target="_blank"
                class="hidden md:inline-flex items-center space-x-1.5 text-xs bg-slate-50 hover:bg-blue-50/80 text-slate-700 hover:text-blue-700 border border-slate-200 hover:border-blue-200 px-3 py-2 rounded-xl font-semibold transition"
                title="Buka REST API Documentation JSON">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>REST API</span>
            </a>

            <!-- User Profile & Quick Switch Dropdown -->
            <div class="relative" x-data="{ userMenuOpen: false }">
                <button @click="userMenuOpen = !userMenuOpen"
                    class="flex items-center space-x-2.5 text-slate-700 hover:text-blue-600 hover:border-blue-300 text-xs font-semibold bg-white hover:bg-slate-50 p-1.5 px-3 rounded-xl border border-slate-200 shadow-2xs transition focus:outline-none cursor-pointer">
                    <div
                        class="w-7 h-7 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center text-xs font-extrabold shadow-sm">
                        {{ strtoupper(substr($currentUser->name ?? 'P', 0, 1)) }}
                    </div>
                    <div class="text-left hidden md:block">
                        <span
                            class="block font-bold text-slate-800 text-xs leading-none">{{ $currentUser->name ?? 'Priojaval' }}</span>
                        <span class="text-[10px] text-slate-400 font-medium">{{ strtoupper($currentRole) }}</span>
                    </div>
                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 ml-0.5"></i>
                </button>

                <!-- Dropdown Menu -->
                <div x-show="userMenuOpen" x-cloak @click.away="userMenuOpen = false"
                    class="absolute right-0 mt-2 w-64 bg-white rounded-2xl shadow-2xl border border-slate-100 py-2 z-50 animate-in fade-in zoom-in duration-150">
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
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-2 mb-1.5">Ganti Akun
                            Role (Testing):</p>
                        <div class="space-y-1">
                            <a href="{{ route('login.quick', 'admin') }}"
                                class="flex items-center justify-between text-xs px-2.5 py-1.5 rounded-lg hover:bg-purple-50 text-purple-900 font-semibold transition">
                                <span class="flex items-center"><i
                                        class="fa-solid fa-crown text-purple-600 w-4 mr-1.5"></i> Owner (Admin)</span>
                                @if($currentRole === 'admin') <i
                                class="fa-solid fa-circle-check text-purple-600 text-xs"></i> @endif
                            </a>
                            <a href="{{ route('login.quick', 'accountant') }}"
                                class="flex items-center justify-between text-xs px-2.5 py-1.5 rounded-lg hover:bg-blue-50 text-blue-900 font-semibold transition">
                                <span class="flex items-center"><i
                                        class="fa-solid fa-briefcase text-blue-600 w-4 mr-1.5"></i> Akuntan</span>
                                @if($currentRole === 'accountant') <i
                                class="fa-solid fa-circle-check text-blue-600 text-xs"></i> @endif
                            </a>
                            <a href="{{ route('login.quick', 'auditor') }}"
                                class="flex items-center justify-between text-xs px-2.5 py-1.5 rounded-lg hover:bg-amber-50 text-amber-900 font-semibold transition">
                                <span class="flex items-center"><i
                                        class="fa-solid fa-magnifying-glass text-amber-600 w-4 mr-1.5"></i> Auditor
                                    (Audit)</span>
                                @if($currentRole === 'auditor') <i
                                class="fa-solid fa-circle-check text-amber-600 text-xs"></i> @endif
                            </a>
                            <a href="{{ route('login.quick', 'staff') }}"
                                class="flex items-center justify-between text-xs px-2.5 py-1.5 rounded-lg hover:bg-emerald-50 text-emerald-900 font-semibold transition">
                                <span class="flex items-center"><i
                                        class="fa-solid fa-file-pen text-emerald-600 w-4 mr-1.5"></i> Staff
                                    Operasional</span>
                                @if(in_array($currentRole, ['staff', 'cashier'])) <i
                                class="fa-solid fa-circle-check text-emerald-600 text-xs"></i> @endif
                            </a>
                        </div>
                    </div>

                    <div class="p-1.5 space-y-0.5">
                        <!-- <a href="{{ route('subscription.index') }}"
                            class="flex items-center space-x-2 px-3 py-2 text-xs text-blue-700 hover:bg-blue-50 rounded-xl font-bold transition">
                            <i class="fa-solid fa-credit-card text-blue-600 w-4"></i>
                            <span>Status Langganan & Tagihan</span>
                        </a> -->
                        <a href="{{ route('settings.profile') }}"
                            class="flex items-center space-x-2 px-3 py-2 text-xs text-slate-700 hover:bg-slate-50 rounded-xl font-medium transition">
                            <i class="fa-regular fa-user text-slate-400 w-4"></i>
                            <span>Profil Pengguna</span>
                        </a>
                        <!-- <a href="{{ route('panduan') }}"
                            class="flex items-center space-x-2 px-3 py-2 text-xs text-emerald-700 hover:bg-emerald-50 rounded-xl font-bold transition">
                            <i class="fa-solid fa-book-open text-emerald-600 w-4"></i>
                            <span>Buku Panduan Akuntansi</span>
                        </a> -->

                        <!-- Form Logout -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="w-full flex items-center space-x-2 px-3 py-2 text-xs text-rose-600 hover:bg-rose-50 rounded-xl font-bold transition text-left cursor-pointer">
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
        <div x-show="mobileMenuOpen" @click="mobileMenuOpen = false" x-cloak
            class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-10 lg:hidden transition-opacity"></div>

        <!-- LEFT SIDEBAR (Fixed / Sticky on Left) -->
        <aside id="app-sidebar"
            class="w-64 bg-white/95 backdrop-blur-md border-r border-slate-200/80 flex-shrink-0 flex flex-col fixed lg:static inset-y-0 left-0 top-16 z-20 transition-transform duration-200 ease-in-out lg:translate-x-0 h-[calc(100vh-4rem)]"
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
                        <a href="{{ route('dashboard') }}"
                            class="flex items-center justify-between px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold nav-item-parent {{ request()->routeIs('dashboard') ? 'nav-item-parent-active text-blue-700 font-bold' : 'text-slate-600' }}">
                            <div class="flex items-center space-x-2.5">
                                <div
                                    class="w-7 h-7 rounded-lg flex items-center justify-center shadow-2xs {{ request()->routeIs('dashboard') ? 'bg-gradient-to-tr from-blue-600 to-indigo-600 text-white shadow-blue-500/20' : 'bg-blue-50 text-blue-600 border border-blue-100/60' }}">
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
                            <button @click="open = !open"
                                class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold nav-item-parent {{ request()->is('transactions*') ? 'nav-item-parent-active text-indigo-700 font-bold' : 'text-slate-600' }}">
                                <div class="flex items-center space-x-2.5">
                                    <div
                                        class="w-7 h-7 rounded-lg flex items-center justify-center shadow-2xs {{ request()->is('transactions*') ? 'bg-gradient-to-tr from-indigo-600 to-blue-600 text-white shadow-indigo-500/20' : 'bg-indigo-50 text-indigo-600 border border-indigo-100/60' }}">
                                        <i class="fa-solid fa-money-bill-transfer text-xs"></i>
                                    </div>
                                    <span>Transaksi</span>
                                </div>
                                <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200"
                                    :class="open ? 'rotate-180 text-indigo-600' : 'text-slate-400'"></i>
                            </button>
                            <div x-show="open" x-collapse class="nav-tree-container">
                                <a href="{{ route('transactions.create') }}"
                                    class="nav-sub-link {{ request()->routeIs('transactions.create') ? 'nav-sub-link-active' : '' }}">
                                    <span class="flex items-center space-x-1.5">
                                        <i
                                            class="fa-solid fa-circle-plus text-[10px] {{ request()->routeIs('transactions.create') ? 'text-white' : 'text-emerald-500' }}"></i>
                                        <span>Catat Transaksi</span>
                                    </span>
                                    <span
                                        class="sub-badge text-[9px] px-1.5 py-0.2 rounded bg-emerald-50 text-emerald-600 border border-emerald-200">Baru</span>
                                </a>
                                <a href="{{ route('transactions.history') }}"
                                    class="nav-sub-link {{ request()->routeIs('transactions.history') ? 'nav-sub-link-active' : '' }}">
                                    <span class="flex items-center space-x-1.5">
                                        <i
                                            class="fa-solid fa-clock-rotate-left text-[10px] {{ request()->routeIs('transactions.history') ? 'text-white' : 'text-slate-400' }}"></i>
                                        <span>Riwayat Transaksi</span>
                                    </span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: MASTER DATA & ASSET -->
                @if(!in_array($currentRole, ['staff', 'cashier']))
                    <div>
                        <div class="sidebar-section-title">
                            <span>Master & Aset</span>
                            <i class="fa-solid fa-layer-group text-[9px] text-slate-400"></i>
                        </div>

                        <div class="space-y-1">
                            <!-- 2.1. Master Data -->
                            <div
                                x-data="{ open: {{ request()->is('master*') || request()->is('closing*') ? 'true' : 'false' }} }">
                                <button @click="open = !open"
                                    class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold nav-item-parent {{ request()->is('master*') || request()->is('closing*') ? 'nav-item-parent-active text-sky-700 font-bold' : 'text-slate-600' }}">
                                    <div class="flex items-center space-x-2.5">
                                        <div
                                            class="w-7 h-7 rounded-lg flex items-center justify-center shadow-2xs {{ request()->is('master*') || request()->is('closing*') ? 'bg-gradient-to-tr from-sky-600 to-blue-600 text-white shadow-sky-500/20' : 'bg-sky-50 text-sky-600 border border-sky-100/60' }}">
                                            <i class="fa-solid fa-database text-xs"></i>
                                        </div>
                                        <span>Master Data</span>
                                    </div>
                                    <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200"
                                        :class="open ? 'rotate-180 text-sky-600' : 'text-slate-400'"></i>
                                </button>
                                <div x-show="open" x-collapse class="nav-tree-container">
                                    <a href="{{ route('master.accounts') }}"
                                        class="nav-sub-link {{ request()->routeIs('master.accounts') ? 'nav-sub-link-active' : '' }}">
                                        <span>Akun (COA)</span>
                                        <span
                                            class="sub-badge text-[9px] px-1.5 py-0.2 rounded bg-sky-50 text-sky-600 border border-sky-200">{{ $activeCompanyCoaCount }}
                                            COA</span>
                                    </a>
                                    <a href="{{ route('master.departments') }}"
                                        class="nav-sub-link {{ request()->routeIs('master.departments*') ? 'nav-sub-link-active' : '' }}">
                                        <span>Departemen</span>
                                        <span
                                            class="sub-badge text-[9px] px-1.5 py-0.2 rounded bg-blue-50 text-blue-600 border border-blue-200">Divisi</span>
                                    </a>
                                    <a href="{{ route('master.projects') }}"
                                        class="nav-sub-link {{ request()->routeIs('master.projects*') ? 'nav-sub-link-active' : '' }}">
                                        <span>Proyek (Project)</span>
                                        <span
                                            class="sub-badge text-[9px] px-1.5 py-0.2 rounded bg-indigo-50 text-indigo-600 border border-indigo-200">Cost</span>
                                    </a>
                                    <a href="{{ route('closing.index') }}"
                                        class="nav-sub-link {{ request()->is('closing*') ? 'nav-sub-link-active' : '' }}">
                                        <span>Tutup Buku</span>
                                        <span
                                            class="sub-badge text-[9px] px-1.5 py-0.2 rounded bg-indigo-50 text-indigo-600 border border-indigo-200">Auto</span>
                                    </a>
                                    <a href="{{ route('master.contacts') }}"
                                        class="nav-sub-link {{ request()->routeIs('master.contacts') ? 'nav-sub-link-active' : '' }}">
                                        <span>Pelanggan & Vendor</span>
                                    </a>
                                    <a href="{{ route('master.payment_methods') }}"
                                        class="nav-sub-link {{ request()->routeIs('master.payment_methods') ? 'nav-sub-link-active' : '' }}">
                                        <span>Metode Bayar</span>
                                    </a>
                                    <a href="{{ route('master.taxes') }}"
                                        class="nav-sub-link {{ request()->routeIs('master.taxes') || request()->routeIs('taxes.index') ? 'nav-sub-link-active' : '' }}">
                                        <span>Master Pajak</span>
                                        <span
                                            class="sub-badge text-[9px] px-1.5 py-0.2 rounded bg-amber-50 text-amber-700 border border-amber-200">DJP</span>
                                    </a>
                                    <a href="{{ route('master.tags') }}"
                                        class="nav-sub-link {{ request()->routeIs('master.tags') ? 'nav-sub-link-active' : '' }}">
                                        <span>Tag / Label</span>
                                        <span
                                            class="sub-badge text-[9px] px-1.5 py-0.2 rounded bg-slate-100 text-slate-600 border border-slate-200">Bebas</span>
                                    </a>
                                </div>
                            </div>

                            <!-- 2.2. Aset Tetap -->
                            <div x-data="{ open: {{ request()->is('assets*') ? 'true' : 'false' }} }">
                                <button @click="open = !open"
                                    class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold nav-item-parent {{ request()->is('assets*') ? 'nav-item-parent-active text-emerald-700 font-bold' : 'text-slate-600' }}">
                                    <div class="flex items-center space-x-2.5">
                                        <div
                                            class="w-7 h-7 rounded-lg flex items-center justify-center shadow-2xs {{ request()->is('assets*') ? 'bg-gradient-to-tr from-emerald-600 to-teal-600 text-white shadow-emerald-500/20' : 'bg-emerald-50 text-emerald-600 border border-emerald-100/60' }}">
                                            <i class="fa-solid fa-boxes-stacked text-xs"></i>
                                        </div>
                                        <span>Aset Tetap</span>
                                    </div>
                                    <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200"
                                        :class="open ? 'rotate-180 text-emerald-600' : 'text-slate-400'"></i>
                                </button>
                                <div x-show="open" x-collapse class="nav-tree-container">
                                    <a href="{{ route('assets.index') }}"
                                        class="nav-sub-link {{ request()->routeIs('assets.index') ? 'nav-sub-link-active' : '' }}"
                                        data-tab-title="Daftar Aset" data-tab-icon="fa-solid fa-boxes-stacked">
                                        <span>Daftar Aset & Susut</span>
                                    </a>
                                    <a href="{{ route('assets.create') }}"
                                        class="nav-sub-link {{ request()->routeIs('assets.create') ? 'nav-sub-link-active' : '' }}"
                                        data-tab-title="Tambah Aset" data-tab-icon="fa-solid fa-plus">
                                        <span>+ Tambah Aset</span>
                                    </a>
                                    <a href="{{ route('master.asset_types') }}"
                                        class="nav-sub-link {{ request()->routeIs('master.asset_types') ? 'nav-sub-link-active' : '' }}"
                                        data-tab-title="Master Tipe Aset" data-tab-icon="fa-solid fa-tags">
                                        <span>Tipe Aset Tetap</span>
                                        <span
                                            class="sub-badge text-[9px] px-1.5 py-0.2 rounded bg-indigo-50 text-indigo-700 border border-indigo-200">Aktiva</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- SECTION 3: LAPORAN KEUANGAN -->
                @if(!in_array($currentRole, ['staff', 'cashier']))
                    <div>
                        <div class="sidebar-section-title">
                            <span>Laporan Keuangan</span>
                            <i class="fa-solid fa-chart-column text-[9px] text-slate-400"></i>
                        </div>

                        <div class="space-y-1">
                            <div x-data="{ open: {{ request()->is('reports*') ? 'true' : 'false' }} }">
                                <button @click="open = !open"
                                    class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold nav-item-parent {{ request()->is('reports*') ? 'nav-item-parent-active text-purple-700 font-bold' : 'text-slate-600' }}">
                                    <div class="flex items-center space-x-2.5">
                                        <div
                                            class="w-7 h-7 rounded-lg flex items-center justify-center shadow-2xs {{ request()->is('reports*') ? 'bg-gradient-to-tr from-purple-600 to-indigo-600 text-white shadow-purple-500/20' : 'bg-purple-50 text-purple-600 border border-purple-100/60' }}">
                                            <i class="fa-solid fa-file-invoice-dollar text-xs"></i>
                                        </div>
                                        <span>Laporan</span>
                                    </div>
                                    <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200"
                                        :class="open ? 'rotate-180 text-purple-600' : 'text-slate-400'"></i>
                                </button>
                                <div x-show="open" x-collapse class="nav-tree-container">
                                    <a href="{{ route('reports.journal') }}"
                                        class="nav-sub-link {{ request()->routeIs('reports.journal') ? 'nav-sub-link-active' : '' }}">
                                        <span>Jurnal Umum</span>
                                        <span
                                            class="sub-badge text-[9px] px-1.5 py-0.2 rounded bg-purple-50 text-purple-700 border border-purple-200">Audit</span>
                                    </a>
                                    <a href="{{ route('reports.profit_loss') }}"
                                        class="nav-sub-link {{ request()->routeIs('reports.profit_loss') ? 'nav-sub-link-active' : '' }}">
                                        <span>Laba Rugi (P&L)</span>
                                    </a>
                                    <a href="{{ route('reports.balance_sheet') }}"
                                        class="nav-sub-link {{ request()->routeIs('reports.balance_sheet') ? 'nav-sub-link-active' : '' }}">
                                        <span>Neraca Keuangan</span>
                                        <span
                                            class="sub-badge text-[9px] px-1.5 py-0.2 rounded bg-emerald-50 text-emerald-700 border border-emerald-200">Skontro</span>
                                    </a>
                                    <a href="{{ route('reports.trial_balance') }}"
                                        class="nav-sub-link {{ request()->routeIs('reports.trial_balance') ? 'nav-sub-link-active' : '' }}">
                                        <span>Neraca Saldo</span>
                                    </a>
                                    <a href="{{ route('reports.general_ledger') }}"
                                        class="nav-sub-link {{ request()->routeIs('reports.general_ledger') ? 'nav-sub-link-active' : '' }}">
                                        <span>Buku Besar (Ledger)</span>
                                    </a>
                                    <a href="{{ route('reports.cash_flow') }}"
                                        class="nav-sub-link {{ request()->routeIs('reports.cash_flow') ? 'nav-sub-link-active' : '' }}">
                                        <span>Arus Kas (Cash Flow)</span>
                                    </a>
                                    <a href="{{ route('reports.operating_expenses') }}"
                                        class="nav-sub-link {{ request()->routeIs('reports.operating_expenses') ? 'nav-sub-link-active' : '' }}">
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
                                <button @click="open = !open"
                                    class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold nav-item-parent {{ request()->is('settings*') ? 'nav-item-parent-active text-slate-900 font-bold' : 'text-slate-600' }}">
                                    <div class="flex items-center space-x-2.5">
                                        <div
                                            class="w-7 h-7 rounded-lg flex items-center justify-center shadow-2xs {{ request()->is('settings*') ? 'bg-gradient-to-tr from-slate-700 to-slate-900 text-white shadow-slate-500/20' : 'bg-slate-100 text-slate-700 border border-slate-200/60' }}">
                                            <i class="fa-solid fa-gear text-xs"></i>
                                        </div>
                                        <span>Pengaturan</span>
                                    </div>
                                    <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200"
                                        :class="open ? 'rotate-180 text-slate-700' : 'text-slate-400'"></i>
                                </button>
                                <div x-show="open" x-collapse class="nav-tree-container">
                                    <a href="{{ route('settings.main') }}"
                                        class="nav-sub-link {{ request()->routeIs('settings.main') ? 'nav-sub-link-active' : '' }}">
                                        <span>Pengaturan Utama</span>
                                    </a>
                                    <a href="{{ route('settings.account_mappings') }}"
                                        class="nav-sub-link {{ request()->routeIs('settings.account_mappings') ? 'nav-sub-link-active' : '' }}">
                                        <span>Akun Perkiraan</span>
                                        <span
                                            class="sub-badge text-[9px] px-1.5 py-0.2 rounded bg-indigo-50 text-indigo-600 border border-indigo-200">Default</span>
                                    </a>
                                    <a href="{{ route('settings.employees') }}"
                                        class="nav-sub-link {{ request()->routeIs('settings.employees') ? 'nav-sub-link-active' : '' }}">
                                        <span>Karyawan & Akses</span>
                                    </a>
                                    <a href="{{ route('settings.profile') }}"
                                        class="nav-sub-link {{ request()->routeIs('settings.profile') ? 'nav-sub-link-active' : '' }}">
                                        <span>Profil & Keamanan</span>
                                    </a>
                                </div>
                            </div>

                            <!-- 4.2. Perusahaan Switcher -->
                            <a href="{{ route('company.switch') }}"
                                class="flex items-center justify-between px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold nav-item-parent {{ request()->routeIs('company.switch') ? 'nav-item-parent-active text-amber-700 font-bold' : 'text-slate-600' }}">
                                <div class="flex items-center space-x-2.5">
                                    <div
                                        class="w-7 h-7 rounded-lg flex items-center justify-center shadow-2xs {{ request()->routeIs('company.switch') ? 'bg-gradient-to-tr from-amber-600 to-orange-600 text-white shadow-amber-500/20' : 'bg-amber-50 text-amber-600 border border-amber-100/60' }}">
                                        <i class="fa-solid fa-building text-xs"></i>
                                    </div>
                                    <span>Perusahaan</span>
                                </div>
                                <span
                                    class="text-[9px] font-bold text-amber-600 bg-amber-50 border border-amber-200 px-1.5 py-0.2 rounded">Multi</span>
                            </a>

                            <!-- 4.3. Langganan & Billing -->
                            <a href="{{ route('subscription.index') }}"
                                class="flex items-center justify-between px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold nav-item-parent {{ request()->routeIs('subscription.*') ? 'nav-item-parent-active text-blue-700 font-bold' : 'text-slate-600' }}">
                                <div class="flex items-center space-x-2.5">
                                    <div
                                        class="w-7 h-7 rounded-lg flex items-center justify-center shadow-2xs {{ request()->routeIs('subscription.*') ? 'bg-gradient-to-tr from-blue-600 to-indigo-600 text-white shadow-blue-500/20' : 'bg-blue-50 text-blue-600 border border-blue-100/60' }}">
                                        <i class="fa-solid fa-credit-card text-xs"></i>
                                    </div>
                                    <span>Langganan</span>
                                </div>
                                <span
                                    class="text-[9px] font-bold uppercase {{ ($company->subscription_plan ?? $company->plan_type) === 'premium' ? 'text-amber-700 bg-amber-50 border border-amber-200' : 'text-blue-700 bg-blue-50 border border-blue-200' }} px-1.5 py-0.2 rounded">
                                    {{ ($company->subscription_plan ?? $company->plan_type) === 'premium' ? 'PRO' : 'SaaS' }}
                                </span>
                            </a>

                            <!-- 4.4. Panduan Akuntansi -->
                            <a href="{{ route('panduan') }}"
                                class="flex items-center justify-between px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold nav-item-parent {{ request()->routeIs('panduan*') ? 'nav-item-parent-active text-emerald-700 font-bold' : 'text-slate-600' }}">
                                <div class="flex items-center space-x-2.5">
                                    <div
                                        class="w-7 h-7 rounded-lg flex items-center justify-center shadow-2xs {{ request()->routeIs('panduan*') ? 'bg-gradient-to-tr from-emerald-600 to-teal-600 text-white shadow-emerald-500/20' : 'bg-emerald-50 text-emerald-600 border border-emerald-100/60' }}">
                                        <i class="fa-solid fa-book-open text-xs"></i>
                                    </div>
                                    <span>Panduan Sistem</span>
                                </div>
                                <span
                                    class="text-[9px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-1.5 py-0.2 rounded">Buku</span>
                            </a>

                            <!-- 4.5. REST API Docs & Playground -->
                            <a href="{{ route('docs.api') }}" target="_blank"
                                class="flex items-center justify-between px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold nav-item-parent {{ request()->routeIs('docs.api*') ? 'nav-item-parent-active text-brand-700 font-bold' : 'text-slate-600' }}">
                                <div class="flex items-center space-x-2.5">
                                    <div
                                        class="w-7 h-7 rounded-lg flex items-center justify-center shadow-2xs {{ request()->routeIs('docs.api*') ? 'bg-gradient-to-tr from-brand-600 to-indigo-600 text-white shadow-brand-500/20' : 'bg-brand-50 text-brand-600 border border-brand-100/60' }}">
                                        <i class="fa-solid fa-code text-xs"></i>
                                    </div>
                                    <span>API Docs</span>
                                </div>
                                <span
                                    class="text-[9px] font-bold text-brand-700 bg-brand-50 border border-brand-200 px-1.5 py-0.2 rounded">REST</span>
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
                            <a href="{{ route('superadmin.dashboard') }}"
                                class="flex items-center justify-between px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold nav-item-parent {{ request()->routeIs('superadmin.dashboard') ? 'nav-item-parent-active text-purple-700 font-bold' : 'text-slate-600' }}">
                                <div class="flex items-center space-x-2.5">
                                    <div
                                        class="w-7 h-7 rounded-lg flex items-center justify-center shadow-2xs {{ request()->routeIs('superadmin.dashboard') ? 'bg-gradient-to-tr from-purple-700 to-indigo-800 text-white shadow-purple-500/20' : 'bg-purple-50 text-purple-700 border border-purple-200' }}">
                                        <i class="fa-solid fa-chart-line text-xs"></i>
                                    </div>
                                    <span>SaaS Overview</span>
                                </div>
                            </a>

                            <a href="{{ route('superadmin.tenants') }}"
                                class="flex items-center justify-between px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold nav-item-parent {{ request()->routeIs('superadmin.tenants') ? 'nav-item-parent-active text-purple-700 font-bold' : 'text-slate-600' }}">
                                <div class="flex items-center space-x-2.5">
                                    <div
                                        class="w-7 h-7 rounded-lg flex items-center justify-center shadow-2xs {{ request()->routeIs('superadmin.tenants') ? 'bg-gradient-to-tr from-purple-700 to-indigo-800 text-white shadow-purple-500/20' : 'bg-purple-50 text-purple-700 border border-purple-200' }}">
                                        <i class="fa-solid fa-building-user text-xs"></i>
                                    </div>
                                    <span>Semua Tenant</span>
                                </div>
                            </a>

                            <a href="{{ route('superadmin.invoices') }}"
                                class="flex items-center justify-between px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold nav-item-parent {{ request()->routeIs('superadmin.invoices') ? 'nav-item-parent-active text-purple-700 font-bold' : 'text-slate-600' }}">
                                <div class="flex items-center space-x-2.5">
                                    <div
                                        class="w-7 h-7 rounded-lg flex items-center justify-center shadow-2xs {{ request()->routeIs('superadmin.invoices') ? 'bg-gradient-to-tr from-purple-700 to-indigo-800 text-white shadow-purple-500/20' : 'bg-purple-50 text-purple-700 border border-purple-200' }}">
                                        <i class="fa-solid fa-receipt text-xs"></i>
                                    </div>
                                    <span>Semua Tagihan</span>
                                </div>
                            </a>

                            <a href="{{ route('superadmin.payment_settings') }}"
                                class="flex items-center justify-between px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold nav-item-parent {{ request()->routeIs('superadmin.payment_settings*') ? 'nav-item-parent-active text-purple-700 font-bold' : 'text-slate-600' }}">
                                <div class="flex items-center space-x-2.5">
                                    <div
                                        class="w-7 h-7 rounded-lg flex items-center justify-center shadow-2xs {{ request()->routeIs('superadmin.payment_settings*') ? 'bg-gradient-to-tr from-purple-700 to-indigo-800 text-white shadow-purple-500/20' : 'bg-purple-50 text-purple-700 border border-purple-200' }}">
                                        <i class="fa-solid fa-credit-card text-xs"></i>
                                    </div>
                                    <span>Payment Gateway</span>
                                </div>
                            </a>

                            <a href="{{ route('superadmin.plans') }}"
                                class="flex items-center justify-between px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold nav-item-parent {{ request()->routeIs('superadmin.plans*') ? 'nav-item-parent-active text-purple-700 font-bold' : 'text-slate-600' }}">
                                <div class="flex items-center space-x-2.5">
                                    <div
                                        class="w-7 h-7 rounded-lg flex items-center justify-center shadow-2xs {{ request()->routeIs('superadmin.plans*') ? 'bg-gradient-to-tr from-purple-700 to-indigo-800 text-white shadow-purple-500/20' : 'bg-purple-50 text-purple-700 border border-purple-200' }}">
                                        <i class="fa-solid fa-tags text-xs"></i>
                                    </div>
                                    <span>Kelola Paket SaaS</span>
                                </div>
                            </a>
                        </div>
                    </div>
                @endif

            </div>

            <!-- Sidebar Footer -->
            <div
                class="p-3 border-t border-slate-200/80 text-[11px] text-slate-400 flex items-center justify-between bg-slate-50/70">
                <span class="font-medium text-slate-400">v1.2.0 &bull; Enterprise</span>
                <span
                    class="flex items-center text-blue-700 font-bold bg-blue-50 px-2 py-0.5 rounded-lg border border-blue-100 shadow-2xs">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500 mr-1.5 animate-pulse"></span>
                    {{ strtoupper($currentRole) }}
                </span>
            </div>
        </aside>

        <!-- MASTER ACCURATE MDI WORKSPACE (Hanya aktif & tampil di top-level window) -->
        <div id="master-tab-workspace"
            class="flex-1 min-w-0 flex flex-col h-[calc(100vh-4rem)] overflow-hidden bg-slate-200/80">

            <!-- ACCURATE TAB BAR STRIP -->
            <div id="workspace-tab-bar"
                class="bg-slate-200 border-b border-slate-300 px-2 pt-1.5 flex items-center justify-between shrink-0 select-none shadow-xs z-10">

                <!-- Left Scroll Arrow -->
                <button type="button" onclick="TabManager.scrollTabs(-180)"
                    class="w-6 h-7 text-slate-500 hover:text-slate-900 hover:bg-slate-300/80 rounded flex items-center justify-center transition shrink-0 mr-1 cursor-pointer"
                    title="Geser Tab ke Kiri">
                    <i class="fa-solid fa-chevron-left text-[10px]"></i>
                </button>

                <!-- TAB HEADERS CONTAINER -->
                <div class="flex items-end space-x-1.5 overflow-x-auto no-scrollbar flex-1 py-0.5"
                    id="tab-headers-container">
                    <!-- Dynamic Tabs bergaya Accurate Accounting dirender di sini oleh TabManager -->
                </div>

                <!-- QUICK ADD TAB BUTTON (+) -->
                <div class="relative ml-1 shrink-0" x-data="{ quickMenuOpen: false }">
                    <button type="button" @click="quickMenuOpen = !quickMenuOpen"
                        class="w-7 h-7 rounded-lg bg-white/70 hover:bg-white text-blue-600 hover:text-blue-800 border border-slate-300 flex items-center justify-center text-xs shadow-2xs transition cursor-pointer"
                        title="Buka Menu Cepat ke Tab Baru (+)">
                        <i class="fa-solid fa-plus text-[11px]"></i>
                    </button>

                    <!-- Quick Menu Launcher Dropdown -->
                    <div x-show="quickMenuOpen" x-cloak @click.away="quickMenuOpen = false"
                        class="absolute left-0 top-full mt-1.5 w-64 bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl border border-slate-200 p-2 z-50 text-xs animate-in fade-in zoom-in duration-150">
                        <div
                            class="px-2.5 py-1.5 font-bold text-slate-400 uppercase tracking-wider text-[10px] border-b border-slate-100 flex items-center justify-between">
                            <span>Buka Tab Baru</span>
                            <i class="fa-solid fa-bolt text-amber-500"></i>
                        </div>
                        <div class="py-1 max-h-72 overflow-y-auto sidebar-scroll space-y-0.5">
                            <button type="button"
                                @click="TabManager.openTab('{{ route('transactions.create') }}', 'Catat Transaksi', 'fa-solid fa-circle-plus'); quickMenuOpen = false;"
                                class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-blue-50 text-slate-700 hover:text-blue-700 font-semibold flex items-center space-x-2 transition">
                                <i class="fa-solid fa-circle-plus text-emerald-500 w-4"></i>
                                <span>Catat Transaksi Baru</span>
                            </button>
                            <button type="button"
                                @click="TabManager.openTab('{{ route('reports.profit_loss') }}', 'Laba Rugi (P&L)', 'fa-solid fa-chart-line'); quickMenuOpen = false;"
                                class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-blue-50 text-slate-700 hover:text-blue-700 font-semibold flex items-center space-x-2 transition">
                                <i class="fa-solid fa-chart-line text-blue-600 w-4"></i>
                                <span>Laporan Laba Rugi</span>
                            </button>
                            <button type="button"
                                @click="TabManager.openTab('{{ route('reports.balance_sheet') }}', 'Neraca Keuangan', 'fa-solid fa-scale-balanced'); quickMenuOpen = false;"
                                class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-blue-50 text-slate-700 hover:text-blue-700 font-semibold flex items-center space-x-2 transition">
                                <i class="fa-solid fa-scale-balanced text-indigo-600 w-4"></i>
                                <span>Neraca (Balance Sheet)</span>
                            </button>
                            <button type="button"
                                @click="TabManager.openTab('{{ route('reports.general_ledger') }}', 'Buku Besar', 'fa-solid fa-book-journal-whills'); quickMenuOpen = false;"
                                class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-blue-50 text-slate-700 hover:text-blue-700 font-semibold flex items-center space-x-2 transition">
                                <i class="fa-solid fa-book-journal-whills text-sky-600 w-4"></i>
                                <span>Buku Besar (General Ledger)</span>
                            </button>
                            <button type="button"
                                @click="TabManager.openTab('{{ route('reports.journal') }}', 'Jurnal Umum', 'fa-solid fa-receipt'); quickMenuOpen = false;"
                                class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-blue-50 text-slate-700 hover:text-blue-700 font-semibold flex items-center space-x-2 transition">
                                <i class="fa-solid fa-receipt text-amber-600 w-4"></i>
                                <span>Jurnal Umum</span>
                            </button>
                            <button type="button"
                                @click="TabManager.openTab('{{ route('master.accounts') }}', 'Master Akun (COA)', 'fa-solid fa-database'); quickMenuOpen = false;"
                                class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-blue-50 text-slate-700 hover:text-blue-700 font-semibold flex items-center space-x-2 transition">
                                <i class="fa-solid fa-database text-purple-600 w-4"></i>
                                <span>Master Akun (COA)</span>
                            </button>
                            <button type="button"
                                @click="TabManager.openTab('{{ route('assets.index') }}', 'Aset Tetap', 'fa-solid fa-boxes-stacked'); quickMenuOpen = false;"
                                class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-blue-50 text-slate-700 hover:text-blue-700 font-semibold flex items-center space-x-2 transition">
                                <i class="fa-solid fa-boxes-stacked text-teal-600 w-4"></i>
                                <span>Aset & Depresiasi</span>
                            </button>
                            <button type="button"
                                @click="TabManager.openTab('{{ route('closing.index') }}', 'Tutup Buku', 'fa-solid fa-calculator'); quickMenuOpen = false;"
                                class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-blue-50 text-slate-700 hover:text-blue-700 font-semibold flex items-center space-x-2 transition">
                                <i class="fa-solid fa-calculator text-rose-600 w-4"></i>
                                <span>Tutup Buku Akhir Bulan</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Right Scroll Arrow -->
                <button type="button" onclick="TabManager.scrollTabs(180)"
                    class="w-6 h-7 text-slate-500 hover:text-slate-900 hover:bg-slate-300/80 rounded flex items-center justify-center transition shrink-0 ml-1 mr-2 cursor-pointer"
                    title="Geser Tab ke Kanan">
                    <i class="fa-solid fa-chevron-right text-[10px]"></i>
                </button>

                <!-- RIGHT ACTION BUTTONS (Accurate Tools) -->
                <div class="flex items-center space-x-1 shrink-0 pb-0.5 border-l border-slate-300 pl-2">
                    <!-- Refresh Current Tab -->
                    <button type="button" onclick="TabManager.refreshActiveTab()"
                        class="w-7 h-7 rounded-lg bg-white/80 hover:bg-white text-slate-600 hover:text-blue-600 border border-slate-300 flex items-center justify-center text-xs shadow-2xs transition cursor-pointer"
                        title="Muat Ulang (Refresh) Tab Aktif">
                        <i class="fa-solid fa-rotate-right text-[11px]"></i>
                    </button>

                    <!-- Close Other Tabs -->
                    <button type="button" onclick="TabManager.closeOtherTabs()"
                        class="w-7 h-7 rounded-lg bg-white/80 hover:bg-white text-slate-600 hover:text-amber-600 border border-slate-300 flex items-center justify-center text-xs shadow-2xs transition cursor-pointer"
                        title="Tutup Tab Lainnya">
                        <i class="fa-solid fa-xmark text-[11px]"></i>
                    </button>

                    <!-- Close All Tabs -->
                    <button type="button" onclick="TabManager.closeAllTabs()"
                        class="w-7 h-7 rounded-lg bg-white/80 hover:bg-white text-slate-600 hover:text-rose-600 border border-slate-300 flex items-center justify-center text-xs shadow-2xs transition cursor-pointer"
                        title="Tutup Semua Tab (Kembali ke Dashboard)">
                        <i class="fa-solid fa-rectangle-xmark text-[11px]"></i>
                    </button>

                    <!-- Maximize Workspace (Toggle Sidebar Collapse) -->
                    <button type="button" onclick="TabManager.toggleMaximize()" id="btn-maximize-tab"
                        class="w-7 h-7 rounded-lg bg-white/80 hover:bg-white text-slate-600 hover:text-indigo-600 border border-slate-300 flex items-center justify-center text-xs shadow-2xs transition cursor-pointer"
                        title="Perluas Layar Kerja (Maximize)">
                        <i class="fa-solid fa-expand text-[11px]"></i>
                    </button>
                </div>
            </div>

            <!-- TAB IFRAME VIEWPORTS CONTAINER -->
            <div id="tab-viewports-container" class="flex-1 w-full h-full relative overflow-hidden bg-slate-50">
                <!-- Iframes untuk setiap tab dimuat di sini secara terisolasi tanpa reload shell -->
            </div>
        </div>

        <!-- CONTEXT MENU UNTUK TAB (Klik Kanan Tab) -->
        <div id="tab-context-menu"
            class="hidden fixed bg-white/95 backdrop-blur-md border border-slate-200 rounded-2xl shadow-2xl py-1.5 z-50 text-xs w-48 animate-in fade-in zoom-in duration-100">
            <button type="button" id="ctx-refresh"
                class="w-full text-left px-3 py-1.5 hover:bg-blue-50 text-slate-700 hover:text-blue-700 font-semibold flex items-center space-x-2 transition cursor-pointer">
                <i class="fa-solid fa-rotate-right text-blue-600 w-4 text-[11px]"></i>
                <span>Muat Ulang Tab</span>
            </button>
            <button type="button" id="ctx-close"
                class="w-full text-left px-3 py-1.5 hover:bg-rose-50 text-slate-700 hover:text-rose-700 font-semibold flex items-center space-x-2 transition cursor-pointer">
                <i class="fa-solid fa-xmark text-rose-600 w-4 text-[11px]"></i>
                <span>Tutup Tab Ini</span>
            </button>
            <button type="button" id="ctx-close-others"
                class="w-full text-left px-3 py-1.5 hover:bg-amber-50 text-slate-700 hover:text-amber-700 font-semibold flex items-center space-x-2 transition cursor-pointer">
                <i class="fa-solid fa-ban text-amber-600 w-4 text-[11px]"></i>
                <span>Tutup Tab Lainnya</span>
            </button>
            <button type="button" id="ctx-close-right"
                class="w-full text-left px-3 py-1.5 hover:bg-slate-100 text-slate-700 font-semibold flex items-center space-x-2 transition cursor-pointer">
                <i class="fa-solid fa-arrow-right-from-bracket text-slate-500 w-4 text-[11px]"></i>
                <span>Tutup Tab di Kanan</span>
            </button>
            <div class="my-1 border-t border-slate-100"></div>
            <button type="button" id="ctx-open-new-window"
                class="w-full text-left px-3 py-1.5 hover:bg-blue-50 text-slate-700 hover:text-blue-700 font-semibold flex items-center space-x-2 transition cursor-pointer">
                <i class="fa-solid fa-arrow-up-right-from-square text-indigo-600 w-4 text-[11px]"></i>
                <span>Buka di Tab Browser</span>
            </button>
        </div>

        <!-- CHILD VIEWPORT CONTENT (Hanya tampil saat halaman dirender di dalam iframe tab) -->
        <main id="in-tab-content-area" class="flex-1 min-w-0 p-4 lg:p-6 overflow-y-auto hidden">
            @if(session('success'))
                <div
                    class="mb-5 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-sm flex items-center space-x-3 shadow-xs animate-in fade-in duration-200">
                    <div
                        class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-circle-check text-base"></i>
                    </div>
                    <span class="font-semibold">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div
                    class="mb-5 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 text-sm flex items-center space-x-3 shadow-xs animate-in fade-in duration-200">
                    <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-circle-exclamation text-base"></i>
                    </div>
                    <span class="font-semibold">{{ session('error') }}</span>
                </div>
            @endif

            @if(session('warning'))
                <div
                    class="mb-5 p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-sm flex items-center space-x-3 shadow-xs animate-in fade-in duration-200">
                    <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-triangle-exclamation text-base"></i>
                    </div>
                    <span class="font-semibold">{{ session('warning') }}</span>
                </div>
            @endif

            <!-- 1. GRACE PERIOD BANNER (KELONGGARAN 7 HARI) -->
            @if(isset($company) && $company->isInGracePeriod())
                <div
                    class="mb-6 p-4.5 rounded-2xl bg-gradient-to-r from-amber-500/10 via-orange-500/10 to-amber-500/5 border border-amber-300 text-amber-950 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-sm animate-in fade-in duration-200">
                    <div class="flex items-start sm:items-center space-x-3.5">
                        <div
                            class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-amber-500 to-orange-500 text-white flex items-center justify-center shrink-0 shadow-md shadow-amber-500/20">
                            <i class="fa-solid fa-clock-rotate-left text-base"></i>
                        </div>
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h4 class="font-extrabold text-sm text-slate-900">Masa Trial / Langganan Berakhir</h4>
                                <span
                                    class="bg-amber-100 text-amber-900 text-[10px] font-black uppercase px-2.5 py-0.5 rounded-full border border-amber-300">Masa
                                    Kelonggaran: Sisa {{ $company->grace_days_remaining }} Hari</span>
                            </div>
                            <p class="text-xs text-slate-600 mt-0.5 leading-relaxed">
                                Anda sedang dalam masa toleransi kelonggaran 7 hari (transaksi masih aktif). Segera lakukan
                                perpanjangan sebelum sistem otomatis dikunci ke mode <strong>Read-Only</strong>.
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('subscription.index') }}"
                        class="shrink-0 px-4 py-2.5 bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 text-white text-xs font-bold rounded-xl shadow-md shadow-amber-600/20 hover:shadow-lg transition-all duration-200 flex items-center justify-center space-x-1.5 cursor-pointer">
                        <i class="fa-solid fa-credit-card text-xs"></i>
                        <span>Perpanjang Sekarang</span>
                    </a>
                </div>
            @endif

            <!-- 2. READ-ONLY / EXPIRED LOCKOUT BANNER (LEWAT 7 HARI KELONGGARAN) -->
            @if(isset($company) && $company->isReadOnly())
                <div
                    class="mb-6 p-4.5 rounded-2xl bg-gradient-to-r from-rose-600 via-rose-700 to-red-700 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-lg shadow-rose-600/20 animate-in fade-in duration-200">
                    <div class="flex items-start sm:items-center space-x-3.5">
                        <div
                            class="w-10 h-10 rounded-2xl bg-white/20 backdrop-blur-md text-white flex items-center justify-center shrink-0 border border-white/20 shadow-inner">
                            <i class="fa-solid fa-lock text-base text-rose-100"></i>
                        </div>
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h4 class="font-extrabold text-sm text-white">Akun Terkunci (Mode Read-Only Aktif)</h4>
                                <span
                                    class="bg-white/20 text-white text-[10px] font-black uppercase px-2.5 py-0.5 rounded-full border border-white/30 backdrop-blur-xs">Transaksi
                                    Baru Dikunci</span>
                            </div>
                            <p class="text-xs text-rose-100 mt-0.5 leading-relaxed">
                                Masa toleransi 7 hari telah berakhir. Anda tetap dapat membuka & mengekspor laporan
                                keuangan, namun pencatatan transaksi baru dikunci hingga paket langganan diperpanjang.
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('subscription.index') }}"
                        class="shrink-0 px-4 py-2.5 bg-white text-rose-700 hover:bg-rose-50 text-xs font-bold rounded-xl shadow-md transition-all duration-200 flex items-center justify-center space-x-1.5 cursor-pointer">
                        <i class="fa-solid fa-arrows-rotate text-xs"></i>
                        <span>Buka Kunci / Bayar Paket</span>
                    </a>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    @yield('scripts')

    <!-- ACCURATE MULTI-TAB WORKSPACE CONTROLLER (TabManager) -->
    <script>
        (function () {
            // HELPER: Resolver URL yang menjamin origin selalu sama dengan browser aktif
            function resolveAppUrl(rawUrl) {
                try {
                    if (!rawUrl) return window.location.href;
                    const currentLoc = window.location;

                    // Buat objek URL dengan basis window.location
                    const parsed = new URL(rawUrl, currentLoc.href);

                    // Deteksi sub-path bila aplikasi berjalan di subfolder (seperti /akutansi/public)
                    let basePrefix = '';
                    const pathParts = currentLoc.pathname.split('/');
                    // Cari apakah ada subfolder project
                    const subfolderIndex = currentLoc.pathname.indexOf('/public');
                    if (subfolderIndex !== -1) {
                        basePrefix = currentLoc.pathname.substring(0, subfolderIndex + 7);
                    }

                    let targetPath = parsed.pathname;
                    if (basePrefix && !targetPath.startsWith(basePrefix)) {
                        // Hilangkan slash depan ganda
                        targetPath = basePrefix + (targetPath.startsWith('/') ? targetPath : '/' + targetPath);
                    }

                    // Kembalikan URL utuh dengan protocol, host & port yang persis sama dengan browser window
                    return currentLoc.origin + targetPath + parsed.search + parsed.hash;
                } catch (e) {
                    return rawUrl;
                }
            }

            // ==========================================
            // 1. JIKA DI DALAM IFRAME TAB (CHILD WINDOW)
            // ==========================================
            if (window.self !== window.top) {
                window.openParentTab = function (url, title, icon) {
                    const targetUrl = resolveAppUrl(url);

                    // 1. Coba postMessage ke window.top / parent (aman dari cross-origin / frame context)
                    try {
                        if (window.top && window.top !== window) {
                            window.top.postMessage({
                                type: 'AKUTANSI_OPEN_TAB',
                                url: targetUrl,
                                title: title,
                                icon: icon
                            }, '*');
                            return;
                        }
                    } catch (e) { }

                    try {
                        if (window.parent && window.parent !== window) {
                            window.parent.postMessage({
                                type: 'AKUTANSI_OPEN_TAB',
                                url: targetUrl,
                                title: title,
                                icon: icon
                            }, '*');
                            return;
                        }
                    } catch (e) { }

                    // 2. Fallback direct call jika postMessage gagal
                    try {
                        if (window.parent && window.parent.TabManager && typeof window.parent.TabManager.openTab === 'function') {
                            window.parent.TabManager.openTab(targetUrl, title, icon);
                            return;
                        }
                    } catch (e) { }

                    // 3. Fallback navigasi lokal jika tidak di dalam MDI
                    window.location.href = targetUrl;
                };

                // Tangkap klik di dalam iframe: otomatis teruskan ke parent tab manager bila link internal
                document.addEventListener('click', function (e) {
                    const link = e.target.closest('a');
                    if (!link) return;

                    const href = link.getAttribute('href');
                    const isExplicitTab = link.classList.contains('open-in-tab') || link.getAttribute('data-open-tab') === 'true';

                    if (!href || href.startsWith('#') || href.startsWith('javascript:') || (!isExplicitTab && link.target === '_blank') || link.hasAttribute('download')) {
                        return;
                    }

                    // Abaikan tombol logout, export download file, atau REST API
                    if (href.includes('/logout') || href.includes('/api/') || href.includes('.pdf') || href.includes('.xlsx')) {
                        return;
                    }

                    const isNavigational = href.includes('/transactions') || href.includes('/reports') || href.includes('/master') || href.includes('/assets') || href.includes('/closing') || href.includes('/company') || href.includes('/settings') || href.includes('/subscription') || href.includes('/panduan');

                    // Jika link memiliki open-in-tab, atau link utama navigasi
                    const resolvedTarget = resolveAppUrl(href);
                    const currentResolved = resolveAppUrl(window.location.href);

                    if (isExplicitTab || (isNavigational && resolvedTarget.split('?')[0] !== currentResolved.split('?')[0])) {
                        e.preventDefault();
                        e.stopPropagation();

                        let title = link.getAttribute('data-tab-title') || link.innerText.trim();
                        let icon = link.getAttribute('data-tab-icon') || 'fa-solid fa-file-lines';
                        const iconEl = link.querySelector('i');
                        if (iconEl && !link.getAttribute('data-tab-icon')) {
                            icon = iconEl.className;
                        }

                        window.openParentTab(resolvedTarget, title, icon);
                    }
                }, true);
                return; // Selesai untuk konteks iframe anak
            }

            // ==========================================
            // 2. TOP-LEVEL WINDOW: TAB MANAGER ACCURATE
            // ==========================================
            const TabManager = {
                tabs: [],
                activeTabId: null,
                tabCounter: 0,
                contextTabId: null,

                init: function (initialUrl, initialTitle, initialIcon) {
                    // Setup Message Listener dari Iframe (AKUTANSI_OPEN_TAB)
                    window.addEventListener('message', (e) => {
                        if (e.data && e.data.type === 'AKUTANSI_OPEN_TAB') {
                            const { url, title, icon } = e.data;
                            this.openTab(url, title, icon);
                        }
                    });

                    // Setup Context Menu Listener
                    this.setupContextMenu();

                    // Setup Horizontal Mouse Wheel on Tab Bar
                    this.setupMouseWheelScroll();

                    // Coba restore tabs dari sessionStorage
                    const restored = this.restoreState();
                    if (!restored) {
                        const resolvedInitial = resolveAppUrl(initialUrl);
                        this.openTab(resolvedInitial, initialTitle || 'Dashboard', initialIcon || 'fa-solid fa-chart-pie', true);
                    }

                    // Intersepsi otomatis semua link di sidebar parent
                    document.addEventListener('click', function (e) {
                        const link = e.target.closest('#app-sidebar a, a.open-in-tab, a[data-open-tab="true"]');
                        if (!link) return;

                        const href = link.getAttribute('href');
                        if (!href || href === '#' || href.startsWith('javascript:') || link.target === '_blank' || link.hasAttribute('download')) {
                            return;
                        }

                        if (href.includes('/logout') || href.includes('/api/')) {
                            return;
                        }

                        e.preventDefault();

                        // Bersihkan teks judul dari badge
                        let title = link.getAttribute('data-tab-title');
                        if (!title) {
                            const clone = link.cloneNode(true);
                            const badges = clone.querySelectorAll('.sub-badge, span[class*="rounded"]');
                            badges.forEach(b => b.remove());
                            title = clone.innerText.replace(/\s+/g, ' ').trim();
                        }

                        // Ambil icon FontAwesome
                        let icon = link.getAttribute('data-tab-icon');
                        if (!icon) {
                            const iconEl = link.querySelector('i');
                            if (iconEl) {
                                icon = iconEl.className;
                            }
                        }

                        const resolvedUrl = resolveAppUrl(href);
                        TabManager.openTab(resolvedUrl, title || 'Dokumen', icon || 'fa-solid fa-file-lines');
                    });
                },

                generateTabId: function (url) {
                    try {
                        const parsed = new URL(url, window.location.href);
                        let path = parsed.pathname.replace(/^\/+|\/+$/g, '').replace(/\//g, '-');
                        // Buang prefix subfolder umum
                        path = path.replace(/^akutansi-public-?/, '');
                        if (!path) return 'tab-dashboard';
                        return 'tab-' + path;
                    } catch (e) {
                        return 'tab-' + (++this.tabCounter);
                    }
                },

                openTab: function (url, title, icon, isInitial = false) {
                    const fullUrl = resolveAppUrl(url);
                    const tabId = this.generateTabId(fullUrl);

                    // Cek apakah tab sudah ada
                    const existingTab = this.tabs.find(t => t.id === tabId);
                    if (existingTab) {
                        // Jika URL berbeda (misal query param baru), reload iframe dengan URL baru
                        if (existingTab.url !== fullUrl) {
                            existingTab.url = fullUrl;
                            const iframe = document.getElementById('iframe-' + tabId);
                            if (iframe) iframe.src = fullUrl;
                        }
                        this.activateTab(tabId);
                        this.saveState();
                        return;
                    }

                    // Buat data tab baru
                    const newTab = {
                        id: tabId,
                        url: fullUrl,
                        title: title || 'Dokumen',
                        icon: icon || 'fa-solid fa-file-lines',
                        isHome: isInitial || tabId === 'tab-dashboard'
                    };
                    this.tabs.push(newTab);

                    // Render Header Tab
                    this.renderTabHeader(newTab);

                    // Render Viewport Iframe dengan Loading Overlay
                    this.renderTabIframe(newTab);

                    // Aktifkan tab
                    this.activateTab(tabId);

                    // Simpan state
                    this.saveState();
                },

                renderTabHeader: function (tab) {
                    const container = document.getElementById('tab-headers-container');
                    if (!container) return;

                    const tabEl = document.createElement('div');
                    tabEl.id = 'header-' + tab.id;
                    tabEl.className = 'accurate-tab-item flex items-center space-x-2 px-3 py-1.5 text-xs font-semibold rounded-t-lg border border-b-0 border-slate-300 cursor-pointer select-none group max-w-[210px] shrink-0 transition-all';
                    tabEl.title = tab.title;

                    // Klik kiri untuk aktifkan tab
                    tabEl.onclick = () => this.activateTab(tab.id);

                    // Klik tengah mouse (Auxclick) untuk tutup tab
                    tabEl.onauxclick = (e) => {
                        if (e.button === 1 && !tab.isHome) {
                            e.preventDefault();
                            this.closeTab(tab.id);
                        }
                    };

                    // Klik kanan untuk Context Menu
                    tabEl.oncontextmenu = (e) => {
                        e.preventDefault();
                        this.showContextMenu(e.clientX, e.clientY, tab.id);
                    };

                    // Tombol tutup [x]
                    const closeBtnHtml = tab.isHome
                        ? ''
                        : `<button type="button" class="w-4 h-4 rounded-full flex items-center justify-center text-slate-400 hover:text-rose-600 hover:bg-rose-100 transition text-[10px] ml-1 shrink-0 cursor-pointer" onclick="event.stopPropagation(); TabManager.closeTab('${tab.id}');" title="Tutup Tab (Klik Tengah juga bisa)">
                        <i class="fa-solid fa-xmark"></i>
                       </button>`;

                    tabEl.innerHTML = `
                    <i class="${tab.icon} text-xs shrink-0 text-blue-600"></i>
                    <span class="truncate text-[11px] leading-tight font-medium">${tab.title}</span>
                    ${closeBtnHtml}
                `;

                    container.appendChild(tabEl);
                    tabEl.scrollIntoView({ behavior: 'smooth', inline: 'nearest' });
                },

                renderTabIframe: function (tab) {
                    const container = document.getElementById('tab-viewports-container');
                    if (!container) return;

                    // Wrapper per tab
                    const wrapper = document.createElement('div');
                    wrapper.id = 'viewport-' + tab.id;
                    wrapper.className = 'w-full h-full absolute inset-0 bg-slate-50';
                    wrapper.style.display = 'none';

                    // Loading Spinner Overlay
                    const loader = document.createElement('div');
                    loader.id = 'loader-' + tab.id;
                    loader.className = 'absolute inset-0 bg-slate-50/90 backdrop-blur-xs flex flex-col items-center justify-center z-20 transition-opacity duration-300';
                    loader.innerHTML = `
                    <div class="flex flex-col items-center space-y-3">
                        <div class="w-9 h-9 border-3 border-blue-200 border-t-blue-600 rounded-full animate-spin"></div>
                        <span class="text-xs font-bold text-slate-600 tracking-wide animate-pulse">Memuat data...</span>
                    </div>
                `;

                    // Iframe
                    const iframe = document.createElement('iframe');
                    iframe.id = 'iframe-' + tab.id;
                    iframe.src = tab.url;
                    iframe.className = 'w-full h-full border-0 absolute inset-0 bg-slate-50';

                    // Event onload: sembunyikan spinner
                    iframe.onload = () => {
                        loader.classList.add('opacity-0', 'pointer-events-none');
                        setTimeout(() => {
                            if (loader.parentNode) loader.style.display = 'none';
                        }, 300);
                    };

                    // Fallback error handler
                    iframe.onerror = () => {
                        loader.innerHTML = `
                        <div class="p-6 bg-white rounded-2xl border border-rose-200 shadow-xl max-w-sm text-center">
                            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center mx-auto mb-3 text-lg">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                            </div>
                            <h4 class="font-bold text-slate-800 text-sm mb-1">Gagal Menghubungkan</h4>
                            <p class="text-xs text-slate-500 mb-4">Halaman tidak dapat dimuat. Pastikan server web aktif.</p>
                            <button onclick="TabManager.refreshTab('${tab.id}')" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-md shadow-blue-500/20 cursor-pointer">
                                <i class="fa-solid fa-rotate-right mr-1.5"></i> Coba Muat Ulang
                            </button>
                        </div>
                    `;
                    };

                    wrapper.appendChild(iframe);
                    wrapper.appendChild(loader);
                    container.appendChild(wrapper);
                },

                activateTab: function (tabId) {
                    const tab = this.tabs.find(t => t.id === tabId);
                    if (!tab) return;

                    this.activeTabId = tabId;

                    // Update visual header & viewport
                    this.tabs.forEach(t => {
                        const header = document.getElementById('header-' + t.id);
                        const viewport = document.getElementById('viewport-' + t.id);
                        if (t.id === tabId) {
                            if (header) {
                                header.classList.add('active');
                                header.scrollIntoView({ behavior: 'smooth', inline: 'nearest' });
                            }
                            if (viewport) {
                                viewport.style.display = 'block';
                            }
                        } else {
                            if (header) header.classList.remove('active');
                            if (viewport) viewport.style.display = 'none';
                        }
                    });

                    // Update title dokumen browser
                    document.title = tab.title + ' — {{ $company->name ?? "Sistem Akuntansi Enterprise" }}';

                    // Sinkronisasi highlight sidebar
                    this.syncSidebarActive(tab.url);

                    this.saveState();
                },

                closeTab: function (tabId) {
                    const tabIndex = this.tabs.findIndex(t => t.id === tabId);
                    if (tabIndex === -1) return;

                    const tab = this.tabs[tabIndex];
                    if (tab.isHome && this.tabs.length === 1) {
                        return; // Jangan tutup tab utama jika tersisa satu-satunya
                    }

                    // Hapus DOM
                    const header = document.getElementById('header-' + tabId);
                    if (header) header.remove();

                    const viewport = document.getElementById('viewport-' + tabId);
                    if (viewport) viewport.remove();

                    // Hapus dari state
                    this.tabs.splice(tabIndex, 1);

                    // Ganti tab aktif bila ditutup
                    if (this.activeTabId === tabId) {
                        if (this.tabs.length > 0) {
                            const nextIndex = Math.min(tabIndex, this.tabs.length - 1);
                            this.activateTab(this.tabs[nextIndex].id);
                        } else {
                            this.openTab('{{ route('dashboard') }}', 'Dashboard', 'fa-solid fa-chart-pie', true);
                        }
                    }

                    this.saveState();
                },

                refreshTab: function (tabId) {
                    const id = tabId || this.activeTabId;
                    if (!id) return;

                    const loader = document.getElementById('loader-' + id);
                    if (loader) {
                        loader.style.display = 'flex';
                        loader.classList.remove('opacity-0', 'pointer-events-none');
                    }

                    const iframe = document.getElementById('iframe-' + id);
                    if (iframe && iframe.contentWindow) {
                        iframe.contentWindow.location.reload();
                    }
                },

                refreshActiveTab: function () {
                    this.refreshTab(this.activeTabId);
                },

                closeOtherTabs: function (keepTabId) {
                    const targetId = keepTabId || this.activeTabId;
                    if (!targetId) return;
                    const toClose = this.tabs.filter(t => t.id !== targetId && !t.isHome);
                    toClose.forEach(t => this.closeTab(t.id));
                },

                closeTabsToRight: function (tabId) {
                    const index = this.tabs.findIndex(t => t.id === tabId);
                    if (index === -1) return;
                    const toClose = this.tabs.slice(index + 1).filter(t => !t.isHome);
                    toClose.forEach(t => this.closeTab(t.id));
                },

                closeAllTabs: function () {
                    const toClose = this.tabs.filter(t => !t.isHome);
                    toClose.forEach(t => this.closeTab(t.id));
                    const homeTab = this.tabs.find(t => t.isHome);
                    if (homeTab) {
                        this.activateTab(homeTab.id);
                    } else {
                        this.openTab('{{ route('dashboard') }}', 'Dashboard', 'fa-solid fa-chart-pie', true);
                    }
                },

                scrollTabs: function (offset) {
                    const container = document.getElementById('tab-headers-container');
                    if (container) {
                        container.scrollBy({ left: offset, behavior: 'smooth' });
                    }
                },

                setupMouseWheelScroll: function () {
                    const container = document.getElementById('tab-headers-container');
                    if (container) {
                        container.addEventListener('wheel', (e) => {
                            if (e.deltaY !== 0) {
                                e.preventDefault();
                                container.scrollLeft += e.deltaY;
                            }
                        }, { passive: false });
                    }
                },

                toggleMaximize: function () {
                    const sidebar = document.getElementById('app-sidebar');
                    const btn = document.getElementById('btn-maximize-tab');
                    if (!sidebar) return;

                    if (sidebar.classList.contains('lg:hidden')) {
                        sidebar.classList.remove('lg:hidden');
                        if (btn) btn.innerHTML = '<i class="fa-solid fa-expand text-[11px]"></i>';
                        if (btn) btn.title = 'Perluas Layar Kerja (Maximize)';
                    } else {
                        sidebar.classList.add('lg:hidden');
                        if (btn) btn.innerHTML = '<i class="fa-solid fa-compress text-[11px]"></i>';
                        if (btn) btn.title = 'Kembalikan Sidebar (Restore)';
                    }
                },

                setupContextMenu: function () {
                    const menu = document.getElementById('tab-context-menu');
                    if (!menu) return;

                    document.addEventListener('click', () => {
                        menu.classList.add('hidden');
                    });

                    document.getElementById('ctx-refresh').onclick = () => {
                        if (this.contextTabId) this.refreshTab(this.contextTabId);
                    };
                    document.getElementById('ctx-close').onclick = () => {
                        if (this.contextTabId) this.closeTab(this.contextTabId);
                    };
                    document.getElementById('ctx-close-others').onclick = () => {
                        if (this.contextTabId) this.closeOtherTabs(this.contextTabId);
                    };
                    document.getElementById('ctx-close-right').onclick = () => {
                        if (this.contextTabId) this.closeTabsToRight(this.contextTabId);
                    };
                    document.getElementById('ctx-open-new-window').onclick = () => {
                        if (this.contextTabId) {
                            const tab = this.tabs.find(t => t.id === this.contextTabId);
                            if (tab) window.open(tab.url, '_blank');
                        }
                    };
                },

                showContextMenu: function (x, y, tabId) {
                    const menu = document.getElementById('tab-context-menu');
                    if (!menu) return;
                    this.contextTabId = tabId;

                    const tab = this.tabs.find(t => t.id === tabId);
                    const closeBtn = document.getElementById('ctx-close');
                    if (closeBtn) {
                        closeBtn.style.display = (tab && tab.isHome) ? 'none' : 'flex';
                    }

                    menu.style.left = Math.min(x, window.innerWidth - 200) + 'px';
                    menu.style.top = y + 'px';
                    menu.classList.remove('hidden');
                },

                syncSidebarActive: function (url) {
                    try {
                        const targetPath = new URL(url, window.location.origin).pathname;
                        const sidebarLinks = document.querySelectorAll('#app-sidebar a');
                        sidebarLinks.forEach(link => {
                            const linkHref = link.getAttribute('href');
                            if (linkHref) {
                                const linkPath = new URL(linkHref, window.location.origin).pathname;
                                if (linkPath === targetPath) {
                                    if (link.classList.contains('nav-sub-link')) {
                                        link.classList.add('nav-sub-link-active');
                                    } else if (link.classList.contains('nav-item-parent')) {
                                        link.classList.add('nav-item-parent-active');
                                    }
                                } else {
                                    if (link.classList.contains('nav-sub-link')) {
                                        link.classList.remove('nav-sub-link-active');
                                    } else if (link.classList.contains('nav-item-parent')) {
                                        link.classList.remove('nav-item-parent-active');
                                    }
                                }
                            }
                        });
                    } catch (e) {
                        // Ignore
                    }
                },

                saveState: function () {
                    try {
                        const data = {
                            tabs: this.tabs.map(t => ({ id: t.id, url: t.url, title: t.title, icon: t.icon, isHome: t.isHome })),
                            activeTabId: this.activeTabId
                        };
                        sessionStorage.setItem('akutansi_mdi_tabs', JSON.stringify(data));
                    } catch (e) { }
                },

                restoreState: function () {
                    try {
                        const raw = sessionStorage.getItem('akutansi_mdi_tabs');
                        if (!raw) return false;
                        const data = JSON.parse(raw);
                        if (!data || !Array.isArray(data.tabs) || data.tabs.length === 0) return false;

                        data.tabs.forEach(t => {
                            this.openTab(t.url, t.title, t.icon, t.isHome);
                        });

                        if (data.activeTabId) {
                            this.activateTab(data.activeTabId);
                        }
                        return true;
                    } catch (e) {
                        return false;
                    }
                }
            };

            window.TabManager = TabManager;

            // Inisialisasi TabManager saat DOM siap
            document.addEventListener('DOMContentLoaded', function () {
                @php
                    $initialUrl = request()->fullUrl();
                    $initialTitle = 'Dashboard';
                    $initialIcon = 'fa-solid fa-chart-pie';
                    if (request()->routeIs('transactions.create')) {
                        $initialTitle = 'Catat Transaksi';
                        $initialIcon = 'fa-solid fa-circle-plus';
                    } elseif (request()->routeIs('transactions.history')) {
                        $initialTitle = 'Riwayat Transaksi';
                        $initialIcon = 'fa-solid fa-clock-rotate-left';
                    } elseif (request()->routeIs('master.accounts')) {
                        $initialTitle = 'Master Akun (COA)';
                        $initialIcon = 'fa-solid fa-database';
                    } elseif (request()->routeIs('master.departments*')) {
                        $initialTitle = 'Master Departemen';
                        $initialIcon = 'fa-solid fa-building-user';
                    } elseif (request()->routeIs('master.projects*')) {
                        $initialTitle = 'Master Proyek';
                        $initialIcon = 'fa-solid fa-diagram-project';
                    } elseif (request()->routeIs('master.asset_types*')) {
                        $initialTitle = 'Master Tipe Aset';
                        $initialIcon = 'fa-solid fa-tags';
                    } elseif (request()->is('master*')) {
                        $initialTitle = 'Master Data';
                        $initialIcon = 'fa-solid fa-layer-group';
                    } elseif (request()->is('reports*')) {
                        $initialTitle = 'Laporan Keuangan';
                        $initialIcon = 'fa-solid fa-file-invoice-dollar';
                    } elseif (request()->is('assets*')) {
                        $initialTitle = 'Aset Tetap';
                        $initialIcon = 'fa-solid fa-boxes-stacked';
                    } elseif (request()->is('closing*')) {
                        $initialTitle = 'Tutup Buku';
                        $initialIcon = 'fa-solid fa-calculator';
                    } elseif (request()->routeIs('company.switch')) {
                        $initialTitle = 'Kelola Usaha';
                        $initialIcon = 'fa-solid fa-building';
                    } elseif (request()->is('settings*')) {
                        $initialTitle = 'Pengaturan';
                        $initialIcon = 'fa-solid fa-gear';
                    } elseif (request()->is('subscription*')) {
                        $initialTitle = 'Langganan';
                        $initialIcon = 'fa-solid fa-credit-card';
                        // } elseif (request()->routeIs('panduan')) {
                        //     $initialTitle = 'Panduan';
                        //     $initialIcon = 'fa-solid fa-book-open';
                    }
                @endphp
            TabManager.init("{{ $initialUrl }}", "{{ $initialTitle }}", "{{ $initialIcon }}");
                    });
    })();
    </script>





</body></html>
