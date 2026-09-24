<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REST API Documentation &amp; Interactive Playground &mdash; Sistem Akuntansi Enterprise</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                        },
                        slate: {
                            850: '#0f172a',
                            900: '#0b0f19',
                            950: '#070a12',
                        }
                    }
                }
            }
        }
    </script>

    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        pre, code {
            font-family: 'JetBrains Mono', monospace;
        }
        .custom-scroll::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        .custom-scroll::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }
        .dark .custom-scroll::-webkit-scrollbar-thumb {
            background: #334155;
        }
        .badge-get { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.25); }
        .badge-post { background: rgba(59, 130, 246, 0.1); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.25); }
        .badge-put { background: rgba(245, 158, 11, 0.1); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.25); }
        .badge-delete { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.25); }
    </style>
</head>

<body x-data="apiDocsApp()" :class="isDarkMode ? 'dark bg-slate-950 text-slate-100' : 'bg-slate-50 text-slate-800'" class="min-h-screen antialiased flex flex-col selection:bg-brand-500 selection:text-white">

    <!-- Top Sticky Navigation Bar -->
    <header class="sticky top-0 z-50 border-b backdrop-blur-md transition-colors"
        :class="isDarkMode ? 'bg-slate-900/90 border-slate-800 text-white' : 'bg-white/90 border-slate-200 text-slate-900'">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
            
            <!-- Brand & Version -->
            <div class="flex items-center space-x-3">
                <a href="{{ url('/') }}" class="flex items-center space-x-2.5 group">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-brand-600 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-brand-500/20 group-hover:scale-105 transition-transform">
                        <i class="fa-solid fa-code text-sm"></i>
                    </div>
                    <div>
                        <div class="font-extrabold text-sm sm:text-base tracking-tight flex items-center gap-1.5">
                            <span>Sistem Akuntansi API</span>
                            <span class="text-[10px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-brand-500/10 text-brand-500 border border-brand-500/20">v1.0 REST</span>
                        </div>
                        <p class="text-[11px] text-slate-400 font-medium">Interactive Developer Portal &amp; Sandbox</p>
                    </div>
                </a>
            </div>

            <!-- Global Search Bar -->
            <div class="hidden md:flex flex-1 max-w-xs mx-2 relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" x-model="searchQuery" placeholder="Cari endpoint (login, accounts, trx)..."
                    class="w-full pl-9 pr-4 py-1.5 text-xs rounded-xl border transition-all focus:outline-none focus:ring-2 focus:ring-brand-500"
                    :class="isDarkMode ? 'bg-slate-800/80 border-slate-700 text-slate-200 placeholder-slate-500 focus:bg-slate-800' : 'bg-slate-100/80 border-slate-200 text-slate-800 placeholder-slate-400 focus:bg-white'">
            </div>

            <!-- Actions & Settings -->
            <div class="flex items-center space-x-2">
                <!-- Base URL Switcher -->
                <div class="hidden xl:flex items-center space-x-1.5 px-2.5 py-1 rounded-xl text-xs border"
                    :class="isDarkMode ? 'bg-slate-800/50 border-slate-700 text-slate-300' : 'bg-slate-100 border-slate-200 text-slate-700'">
                    <span class="text-[11px] font-semibold text-slate-400"><i class="fa-solid fa-globe mr-1"></i>Base URL:</span>
                    <input type="text" x-model="baseUrl" class="bg-transparent font-mono text-[11px] border-none focus:outline-none w-48 text-brand-500 font-medium" title="Ubah sesuai domain hosting Anda">
                </div>

                <!-- Bearer Token Input (Live Sandbox Auth) -->
                <div class="hidden lg:flex items-center space-x-1.5 px-2.5 py-1 rounded-xl text-xs border"
                    :class="isDarkMode ? 'bg-slate-800/50 border-slate-700 text-slate-300' : 'bg-slate-100 border-slate-200 text-slate-700'">
                    <span class="text-[11px] font-semibold text-slate-400"><i class="fa-solid fa-key mr-1 text-amber-500"></i>Token:</span>
                    <input type="text" x-model="apiToken" placeholder="Bearer Token..." class="bg-transparent font-mono text-[11px] border-none focus:outline-none w-32 text-emerald-500 font-medium" title="Masukkan Bearer Token untuk testing">
                </div>

                <!-- Company ID Config -->
                <div class="hidden sm:flex items-center space-x-1.5 px-2.5 py-1 rounded-xl text-xs border"
                    :class="isDarkMode ? 'bg-slate-800/50 border-slate-700 text-slate-300' : 'bg-slate-100 border-slate-200 text-slate-700'">
                    <span class="text-[11px] font-semibold text-slate-400"><i class="fa-solid fa-building mr-1"></i>Company ID:</span>
                    <input type="number" x-model="companyId" class="bg-transparent font-mono text-[11px] border-none focus:outline-none w-8 text-center font-bold text-amber-500">
                </div>

                <!-- Download Postman Collection Button -->
                <a href="{{ asset('akuntansi_api_postman_collection.json') }}" download="Akuntansi_API_Postman_Collection.json"
                    class="px-2 py-1.5 rounded-xl text-xs font-semibold flex items-center space-x-1.5 bg-orange-500/10 text-orange-600 hover:bg-orange-500/20 border border-orange-500/20 transition"
                    title="Download Postman Collection JSON">
                    <i class="fa-solid fa-paper-plane text-xs"></i>
                    <span class="hidden sm:inline">Postman</span>
                </a>

                <!-- Download OpenAPI Spec -->
                <a href="{{ asset('openapi.json') }}" target="_blank"
                    class="px-2 py-1.5 rounded-xl text-xs font-semibold flex items-center space-x-1.5 bg-emerald-500/10 text-emerald-600 hover:bg-emerald-500/20 border border-emerald-500/20 transition"
                    title="View OpenAPI 3.0 Specification">
                    <i class="fa-solid fa-file-code text-xs"></i>
                    <span class="hidden sm:inline">OpenAPI</span>
                </a>

                <!-- Theme Toggle -->
                <button @click="toggleTheme()" class="w-8 h-8 rounded-xl flex items-center justify-center transition border"
                    :class="isDarkMode ? 'bg-slate-800 border-slate-700 text-amber-400 hover:bg-slate-700' : 'bg-slate-100 border-slate-200 text-slate-600 hover:bg-slate-200'">
                    <i :class="isDarkMode ? 'fa-solid fa-sun' : 'fa-solid fa-moon'" class="text-xs"></i>
                </button>

                <!-- Back to Main App -->
                <a href="{{ url('/dashboard') }}" class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-brand-600 hover:bg-brand-700 text-white shadow-sm transition flex items-center space-x-1.5">
                    <i class="fa-solid fa-arrow-left text-[10px]"></i>
                    <span class="hidden sm:inline">Kembali</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <div class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Left Sidebar Navigation -->
        <aside class="lg:col-span-3 custom-scroll lg:sticky lg:top-24 lg:max-h-[calc(100vh-7rem)] overflow-y-auto pr-2 space-y-6">
            
            <!-- Quick Info Card -->
            <div class="p-3.5 rounded-2xl border transition-all"
                :class="isDarkMode ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200 shadow-sm'">
                <div class="flex items-center space-x-2 text-xs font-bold text-brand-500 mb-2">
                    <i class="fa-solid fa-shield-halved"></i>
                    <span>Autentikasi &amp; Keamanan Tenant</span>
                </div>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed mb-2.5">
                    Sistem menggunakan <b>Bearer Token (Sanctum)</b> dan <b>Tenant Guard</b>. Token melindungi data agar tidak bisa diakses sembarang pihak.
                </p>
                <div class="space-y-1 text-[11px] font-mono text-slate-400">
                    <div class="flex items-center justify-between">
                        <span>Header:</span>
                        <span class="text-amber-500 font-bold truncate">Authorization: Bearer</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Tenant Guard:</span>
                        <span class="text-emerald-500 font-bold">403 Blocked</span>
                    </div>
                </div>
            </div>

            <!-- Navigation Links by Group -->
            <nav class="space-y-4">
                <template x-for="(group, gIndex) in endpointGroups" :key="gIndex">
                    <div class="space-y-1.5" x-show="filteredEndpoints(group.endpoints).length > 0">
                        <div class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500 px-2 flex items-center justify-between">
                            <span x-text="group.title"></span>
                            <span class="text-[10px] font-semibold px-1.5 py-0.2 rounded-full"
                                :class="isDarkMode ? 'bg-slate-800 text-slate-400' : 'bg-slate-200 text-slate-600'"
                                x-text="filteredEndpoints(group.endpoints).length"></span>
                        </div>
                        
                        <div class="space-y-1">
                            <template x-for="ep in filteredEndpoints(group.endpoints)" :key="ep.id">
                                <a :href="'#' + ep.id"
                                    class="group flex items-center justify-between px-2.5 py-1.5 rounded-xl text-xs font-medium transition-all"
                                    :class="activeEndpointId === ep.id 
                                        ? (isDarkMode ? 'bg-brand-600/20 text-brand-400 border border-brand-500/30 font-semibold' : 'bg-brand-50 text-brand-700 border border-brand-200 font-semibold')
                                        : (isDarkMode ? 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100')">
                                    <div class="flex items-center space-x-2 truncate">
                                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded tracking-wide font-mono"
                                            :class="getMethodBadgeClass(ep.method)" x-text="ep.method"></span>
                                        <span class="truncate" x-text="ep.name"></span>
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-[8px] opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                </a>
                            </template>
                        </div>
                    </div>
                </template>
            </nav>
        </aside>

        <!-- Main Content Area: Endpoint Catalog & Interactive Sandbox -->
        <main class="lg:col-span-9 space-y-10">
            
            <!-- Hero / Overview Banner -->
            <section class="p-6 sm:p-8 rounded-3xl border relative overflow-hidden transition-all"
                :class="isDarkMode ? 'bg-gradient-to-br from-slate-900 via-slate-900/90 to-brand-950/40 border-slate-800' : 'bg-gradient-to-br from-white via-slate-50 to-blue-50/40 border-slate-200 shadow-sm'">
                <div class="max-w-2xl space-y-3 relative z-10">
                    <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full text-xs font-semibold bg-brand-500/10 text-brand-600 dark:text-brand-400 border border-brand-500/20">
                        <i class="fa-solid fa-shield-check"></i>
                        <span>Sanctum Token &amp; Multi-Tenant Guard Active</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                        Dokumentasi &amp; Sandbox REST API Akuntansi
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                        Gunakan endpoint <b>POST /auth/login</b> untuk mendapatkan Bearer Token dan Company ID Anda, atau masukkan token langsung di kolom atas untuk menguji transaksi &amp; laporan keuangan.
                    </p>
                    
                    <div class="pt-2 flex flex-wrap gap-2 text-xs">
                        <span class="px-2.5 py-1 rounded-lg border font-mono"
                            :class="isDarkMode ? 'bg-slate-800/80 border-slate-700 text-slate-300' : 'bg-white border-slate-200 text-slate-700'">
                            <i class="fa-solid fa-key text-amber-500 mr-1.5"></i>Header: <code>Authorization: Bearer &lt;token&gt;</code>
                        </span>
                        <span class="px-2.5 py-1 rounded-lg border font-mono"
                            :class="isDarkMode ? 'bg-slate-800/80 border-slate-700 text-slate-300' : 'bg-white border-slate-200 text-slate-700'">
                            <i class="fa-solid fa-building text-blue-500 mr-1.5"></i>Header: <code>X-Company-Id: 1</code> (Opsional)
                        </span>
                    </div>
                </div>
            </section>

            <!-- Endpoints Iteration -->
            <div class="space-y-12">
                <template x-for="(ep, index) in allEndpoints" :key="ep.id">
                    <section :id="ep.id" x-show="matchesSearch(ep)" class="rounded-3xl border overflow-hidden transition-all duration-200 scroll-mt-24"
                        :class="isDarkMode ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200 shadow-sm'">
                        
                        <!-- Endpoint Header Bar -->
                        <div class="p-5 sm:p-6 border-b flex flex-col md:flex-row md:items-center justify-between gap-4"
                            :class="isDarkMode ? 'border-slate-800 bg-slate-900/50' : 'border-slate-100 bg-slate-50/50'">
                            <div class="space-y-1.5">
                                <div class="flex items-center space-x-2.5">
                                    <span class="px-2.5 py-0.5 rounded-lg text-xs font-extrabold font-mono uppercase tracking-wider"
                                        :class="getMethodBadgeClass(ep.method)" x-text="ep.method"></span>
                                    <h2 class="text-base sm:text-lg font-bold" x-text="ep.name"></h2>
                                </div>
                                <div class="flex items-center space-x-2 font-mono text-xs text-slate-500 dark:text-slate-400">
                                    <span class="px-2 py-0.5 rounded bg-slate-200/60 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold" x-text="ep.path"></span>
                                    <button @click="copyToClipboard(baseUrl + ep.path)" class="hover:text-brand-500 transition" title="Copy Full URL">
                                        <i class="fa-regular fa-copy text-xs"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="flex items-center space-x-2">
                                <span class="text-[11px] font-medium px-2.5 py-1 rounded-full border"
                                    :class="isDarkMode ? 'bg-slate-800 text-slate-400 border-slate-700' : 'bg-white text-slate-500 border-slate-200'"
                                    x-text="ep.category"></span>
                            </div>
                        </div>

                        <!-- Endpoint Body: 2 Columns (Documentation & Live Sandbox / Code) -->
                        <div class="grid grid-cols-1 xl:grid-cols-12">
                            
                            <!-- Left Sub-column: Specs & Parameters (7 Cols) -->
                            <div class="xl:col-span-7 p-5 sm:p-6 space-y-6 border-b xl:border-b-0 xl:border-r"
                                :class="isDarkMode ? 'border-slate-800' : 'border-slate-100'">
                                
                                <!-- Description -->
                                <div>
                                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Deskripsi</h3>
                                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed" x-text="ep.description"></p>
                                </div>

                                <!-- Headers Required -->
                                <div>
                                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">HTTP Headers</h3>
                                    <div class="rounded-xl border overflow-hidden text-xs"
                                        :class="isDarkMode ? 'border-slate-800 bg-slate-950/50' : 'border-slate-200 bg-slate-50/50'">
                                        <div class="p-2.5 border-b flex justify-between font-mono"
                                            :class="isDarkMode ? 'border-slate-800' : 'border-slate-200'">
                                            <span class="font-bold text-brand-500">Accept</span>
                                            <span class="text-slate-500">application/json</span>
                                        </div>
                                        <template x-if="ep.authRequired">
                                            <div class="p-2.5 border-b flex justify-between font-mono"
                                                :class="isDarkMode ? 'border-slate-800' : 'border-slate-200'">
                                                <span class="font-bold text-amber-500">Authorization</span>
                                                <span class="text-slate-500">Bearer &lt;token&gt;</span>
                                            </div>
                                        </template>
                                        <template x-if="ep.method === 'POST'">
                                            <div class="p-2.5 border-b flex justify-between font-mono"
                                                :class="isDarkMode ? 'border-slate-800' : 'border-slate-200'">
                                                <span class="font-bold text-brand-500">Content-Type</span>
                                                <span class="text-slate-500">application/json</span>
                                            </div>
                                        </template>
                                        <div class="p-2.5 flex justify-between font-mono">
                                            <span class="font-bold text-slate-400">X-Company-Id (Opsional)</span>
                                            <span class="text-slate-500">ID Perusahaan (Otomatis dari Token jika kosong)</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Parameters (Query or Body) -->
                                <div x-show="ep.params && ep.params.length > 0">
                                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Parameter Request</h3>
                                    <div class="rounded-2xl border overflow-hidden text-xs"
                                        :class="isDarkMode ? 'border-slate-800' : 'border-slate-200'">
                                        <table class="w-full text-left">
                                            <thead :class="isDarkMode ? 'bg-slate-800/80 text-slate-300' : 'bg-slate-100 text-slate-700'">
                                                <tr>
                                                    <th class="p-2.5 font-bold">Field</th>
                                                    <th class="p-2.5 font-bold">Tipe</th>
                                                    <th class="p-2.5 font-bold">Wajib?</th>
                                                    <th class="p-2.5 font-bold">Keterangan</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y" :class="isDarkMode ? 'divide-slate-800' : 'divide-slate-200'">
                                                <template x-for="param in ep.params" :key="param.name">
                                                    <tr :class="isDarkMode ? 'hover:bg-slate-800/40' : 'hover:bg-slate-50'">
                                                        <td class="p-2.5 font-mono font-bold text-brand-500" x-text="param.name"></td>
                                                        <td class="p-2.5 font-mono text-[11px] text-purple-400" x-text="param.type"></td>
                                                        <td class="p-2.5">
                                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold"
                                                                :class="param.required ? 'bg-rose-500/10 text-rose-500' : 'bg-slate-500/10 text-slate-400'"
                                                                x-text="param.required ? 'Wajib' : 'Opsional'"></span>
                                                        </td>
                                                        <td class="p-2.5 text-slate-500 dark:text-slate-400 text-[11px]" x-text="param.desc"></td>
                                                    </tr>
                                                </template>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Response Example Accordion -->
                                <div x-data="{ openRes: false }">
                                    <button @click="openRes = !openRes" class="w-full flex items-center justify-between p-3 rounded-xl border text-xs font-semibold transition"
                                        :class="isDarkMode ? 'bg-slate-800/50 border-slate-700 text-slate-300 hover:bg-slate-800' : 'bg-slate-100 border-slate-200 text-slate-700 hover:bg-slate-200'">
                                        <span class="flex items-center space-x-2">
                                            <i class="fa-solid fa-code text-emerald-500"></i>
                                            <span>Contoh Response Schema (HTTP 200/201)</span>
                                        </span>
                                        <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="openRes ? 'rotate-180' : ''"></i>
                                    </button>
                                    <div x-show="openRes" x-collapse class="mt-2">
                                        <pre class="p-3.5 rounded-xl text-[11px] font-mono leading-relaxed overflow-x-auto custom-scroll"
                                            :class="isDarkMode ? 'bg-slate-950 text-emerald-400 border border-slate-800' : 'bg-slate-900 text-emerald-300'"><code x-text="JSON.stringify(ep.sampleResponse, null, 2)"></code></pre>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Sub-column: Interactive Sandbox Playground & Code Snippets (5 Cols) -->
                            <div class="xl:col-span-5 p-5 sm:p-6 space-y-4 flex flex-col justify-between"
                                :class="isDarkMode ? 'bg-slate-950/40' : 'bg-slate-50/40'">
                                
                                <div class="space-y-4">
                                    <!-- Sandbox Title & Lang Switcher -->
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-2 text-xs font-bold">
                                            <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                                            <span>Live Playground &amp; Code</span>
                                        </div>
                                        
                                        <!-- Code Tab Switcher -->
                                        <div class="flex items-center space-x-1 p-0.5 rounded-lg border text-[10px] font-bold"
                                            :class="isDarkMode ? 'bg-slate-800 border-slate-700' : 'bg-slate-200 border-slate-300'">
                                            <template x-for="lang in ['cURL', 'JS', 'PHP', 'Python']" :key="lang">
                                                <button @click="ep.activeLang = lang"
                                                    class="px-2 py-0.5 rounded transition"
                                                    :class="ep.activeLang === lang ? (isDarkMode ? 'bg-brand-600 text-white' : 'bg-white text-brand-700 shadow-xs') : 'text-slate-400 hover:text-slate-200'"
                                                    x-text="lang"></button>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Code Generator Display -->
                                    <div class="relative group">
                                        <pre class="p-3.5 rounded-2xl text-[11px] font-mono leading-relaxed overflow-x-auto custom-scroll max-h-52 border"
                                            :class="isDarkMode ? 'bg-slate-950 text-slate-300 border-slate-800' : 'bg-slate-900 text-slate-200 border-slate-800'"><code x-text="generateSnippet(ep)"></code></pre>
                                        
                                        <button @click="copyToClipboard(generateSnippet(ep))"
                                            class="absolute right-3 top-3 px-2 py-1 rounded-lg text-[10px] font-semibold bg-slate-800/90 text-slate-300 hover:text-white border border-slate-700 shadow-sm transition opacity-0 group-hover:opacity-100 flex items-center space-x-1">
                                            <i class="fa-regular fa-copy"></i>
                                            <span>Copy</span>
                                        </button>
                                    </div>

                                    <!-- Payload Editor for POST Endpoints -->
                                    <div x-show="ep.method === 'POST'" class="space-y-1.5">
                                        <div class="flex items-center justify-between text-xs font-semibold text-slate-400">
                                            <span>Request Body (JSON Payload):</span>
                                            <button @click="ep.customPayload = JSON.stringify(ep.sampleBody, null, 2)" class="text-[10px] text-brand-500 hover:underline">Reset Default</button>
                                        </div>
                                        <textarea x-model="ep.customPayload" rows="5"
                                            class="w-full p-3 rounded-2xl font-mono text-[11px] leading-relaxed border transition focus:outline-none focus:ring-2 focus:ring-brand-500"
                                            :class="isDarkMode ? 'bg-slate-900 border-slate-800 text-slate-200 focus:bg-slate-950' : 'bg-white border-slate-200 text-slate-800'"></textarea>
                                    </div>

                                    <!-- Sandbox Action Button -->
                                    <button @click="sendTestRequest(ep)" :disabled="ep.loading"
                                        class="w-full py-2.5 px-4 rounded-2xl font-bold text-xs flex items-center justify-center space-x-2 bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 text-white shadow-md shadow-brand-500/20 transition active:scale-[0.98] disabled:opacity-50">
                                        <template x-if="!ep.loading">
                                            <div class="flex items-center space-x-2">
                                                <i class="fa-solid fa-play text-[11px]"></i>
                                                <span>Send Test Request (Live Sandbox)</span>
                                            </div>
                                        </template>
                                        <template x-if="ep.loading">
                                            <div class="flex items-center space-x-2">
                                                <i class="fa-solid fa-circle-notch fa-spin text-xs"></i>
                                                <span>Mengirim Request ke Server...</span>
                                            </div>
                                        </template>
                                    </button>
                                </div>

                                <!-- Test Result Viewer -->
                                <div x-show="ep.testResult" class="space-y-2 pt-2 border-t" :class="isDarkMode ? 'border-slate-800' : 'border-slate-200'">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="font-bold flex items-center space-x-1.5">
                                            <i class="fa-solid fa-terminal text-brand-500 text-[10px]"></i>
                                            <span>Live Response</span>
                                        </span>
                                        <div class="flex items-center space-x-2 font-mono text-[11px]">
                                            <span class="px-1.5 py-0.5 rounded font-bold"
                                                :class="ep.testResult?.status < 300 ? 'bg-emerald-500/10 text-emerald-500 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-500 border border-rose-500/20'"
                                                x-text="ep.testResult?.status + ' ' + ep.testResult?.statusText"></span>
                                            <span class="text-slate-400" x-text="ep.testResult?.time + ' ms'"></span>
                                        </div>
                                    </div>

                                    <pre class="p-3 rounded-xl font-mono text-[11px] leading-relaxed max-h-48 overflow-y-auto custom-scroll border"
                                        :class="isDarkMode ? 'bg-slate-950 text-slate-200 border-slate-800' : 'bg-slate-900 text-emerald-300 border-slate-800'"><code x-text="JSON.stringify(ep.testResult?.data, null, 2)"></code></pre>
                                </div>

                            </div>

                        </div>

                    </section>
                </template>
            </div>

            <!-- Footer -->
            <footer class="pt-8 pb-12 border-t text-center space-y-3" :class="isDarkMode ? 'border-slate-800 text-slate-500' : 'border-slate-200 text-slate-400'">
                <p class="text-xs">
                    Sistem Akuntansi Enterprise REST API &bull; Siap dideploy ke Shared Hosting / VPS &bull; Standar JSON &amp; OpenAPI 3.0 &bull; Laravel Sanctum Token Secured
                </p>
                <div class="flex items-center justify-center space-x-4 text-xs font-medium">
                    <a href="{{ url('/panduan') }}" class="hover:text-brand-500 transition">Panduan Sistem</a>
                    <span>&bull;</span>
                    <a href="{{ asset('openapi.json') }}" target="_blank" class="hover:text-brand-500 transition">OpenAPI Spec</a>
                    <span>&bull;</span>
                    <a href="{{ asset('akuntansi_api_postman_collection.json') }}" download class="hover:text-brand-500 transition">Download Postman</a>
                </div>
            </footer>

        </main>
    </div>

    <!-- Toast Notification -->
    <div x-show="toast.show" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-2"
        class="fixed bottom-6 right-6 z-50 px-4 py-2.5 rounded-2xl shadow-xl border flex items-center space-x-2 text-xs font-semibold"
        :class="isDarkMode ? 'bg-slate-900 border-slate-700 text-white shadow-black/50' : 'bg-slate-900 text-white shadow-slate-500/20'">
        <i class="fa-solid fa-circle-check text-emerald-400"></i>
        <span x-text="toast.message"></span>
    </div>

    <!-- Application Script Data & Logic -->
    <script>
        function apiDocsApp() {
            return {
                isDarkMode: localStorage.getItem('api_docs_theme') === 'dark' || window.matchMedia('(prefers-color-scheme: dark)').matches,
                searchQuery: '',
                activeEndpointId: 'auth-login',
                baseUrl: window.location.origin + (window.location.pathname.includes('/public') ? '/akutansi/public/api/v1' : '/api/v1'),
                apiToken: localStorage.getItem('api_docs_token') || '',
                companyId: 1,
                toast: { show: false, message: '' },

                toggleTheme() {
                    this.isDarkMode = !this.isDarkMode;
                    localStorage.setItem('api_docs_theme', this.isDarkMode ? 'dark' : 'light');
                },

                showToast(msg) {
                    this.toast.message = msg;
                    this.toast.show = true;
                    setTimeout(() => { this.toast.show = false; }, 2500);
                },

                copyToClipboard(text) {
                    navigator.clipboard.writeText(text);
                    this.showToast('Berhasil disalin ke clipboard!');
                },

                getMethodBadgeClass(method) {
                    switch (method) {
                        case 'GET': return 'badge-get';
                        case 'POST': return 'badge-post';
                        case 'PUT': return 'badge-put';
                        case 'DELETE': return 'badge-delete';
                        default: return 'bg-slate-500/10 text-slate-500';
                    }
                },

                filteredEndpoints(endpoints) {
                    if (!this.searchQuery) return endpoints;
                    const q = this.searchQuery.toLowerCase();
                    return endpoints.filter(ep => 
                        ep.name.toLowerCase().includes(q) || 
                        ep.path.toLowerCase().includes(q) || 
                        ep.description.toLowerCase().includes(q)
                    );
                },

                matchesSearch(ep) {
                    if (!this.searchQuery) return true;
                    const q = this.searchQuery.toLowerCase();
                    return ep.name.toLowerCase().includes(q) || 
                           ep.path.toLowerCase().includes(q) || 
                           ep.description.toLowerCase().includes(q);
                },

                generateSnippet(ep) {
                    const fullUrl = this.baseUrl + ep.path;
                    const lang = ep.activeLang || 'cURL';
                    const payload = ep.customPayload || JSON.stringify(ep.sampleBody, null, 2);
                    const authHeader = this.apiToken ? ` \\\n  -H "Authorization: Bearer ${this.apiToken}"` : '';

                    if (lang === 'cURL') {
                        if (ep.method === 'GET') {
                            return `curl -X GET "${fullUrl}" \\\n  -H "Accept: application/json"${authHeader} \\\n  -H "X-Company-Id: ${this.companyId}"`;
                        } else {
                            return `curl -X POST "${fullUrl}" \\\n  -H "Content-Type: application/json" \\\n  -H "Accept: application/json"${authHeader} \\\n  -H "X-Company-Id: ${this.companyId}" \\\n  -d '${payload.replace(/'/g, "\\'")}'`;
                        }
                    } else if (lang === 'JS') {
                        const jsHeaders = {
                            "Accept": "application/json",
                            "X-Company-Id": this.companyId.toString()
                        };
                        if (this.apiToken) jsHeaders["Authorization"] = `Bearer ${this.apiToken}`;
                        if (ep.method === 'POST') jsHeaders["Content-Type"] = "application/json";

                        if (ep.method === 'GET') {
                            return `const res = await fetch("${fullUrl}", {\n  headers: ${JSON.stringify(jsHeaders, null, 4)}\n});\nconst data = await res.json();\nconsole.log(data);`;
                        } else {
                            return `const res = await fetch("${fullUrl}", {\n  method: "POST",\n  headers: ${JSON.stringify(jsHeaders, null, 4)},\n  body: JSON.stringify(${payload})\n});\nconst data = await res.json();\nconsole.log(data);`;
                        }
                    } else if (lang === 'PHP') {
                        const phpTag = '<' + '?php';
                        return `${phpTag}\n$curl = curl_init();\ncurl_setopt_array($curl, [\n  CURLOPT_URL => "${fullUrl}",\n  CURLOPT_RETURNTRANSFER => true,\n  CURLOPT_CUSTOMREQUEST => "${ep.method}",\n  CURLOPT_HTTPHEADER => [\n    "Content-Type: application/json",\n    "Accept: application/json",\n    "Authorization: Bearer ${this.apiToken || '<TOKEN>'}",\n    "X-Company-Id: ${this.companyId}"\n  ]${ep.method === 'POST' ? ',\n  CURLOPT_POSTFIELDS => json_encode(' + payload + ')' : ''}\n]);\n$response = curl_exec($curl);\ncurl_close($curl);\necho $response;`;
                    } else if (lang === 'Python') {
                        if (ep.method === 'GET') {
                            return `import requests\n\nheaders = {\n    "Accept": "application/json",\n    "Authorization": "Bearer ${this.apiToken || '<TOKEN>'}",\n    "X-Company-Id": "${this.companyId}"\n}\nres = requests.get("${fullUrl}", headers=headers)\nprint(res.json())`;
                        } else {
                            return `import requests\n\nheaders = {\n    "Content-Type": "application/json",\n    "Accept": "application/json",\n    "Authorization": "Bearer ${this.apiToken || '<TOKEN>'}",\n    "X-Company-Id": "${this.companyId}"\n}\npayload = ${payload}\nres = requests.post("${fullUrl}", json=payload, headers=headers)\nprint(res.json())`;
                        }
                    }
                },

                async sendTestRequest(ep) {
                    ep.loading = true;
                    ep.testResult = null;
                    const startTime = performance.now();
                    const fullUrl = this.baseUrl + ep.path;

                    try {
                        const headers = {
                            'Accept': 'application/json',
                            'X-Company-Id': this.companyId.toString(),
                        };

                        if (this.apiToken) {
                            headers['Authorization'] = `Bearer ${this.apiToken}`;
                        }

                        const options = {
                            method: ep.method,
                            headers: headers
                        };

                        if (ep.method === 'POST') {
                            headers['Content-Type'] = 'application/json';
                            options.body = ep.customPayload || JSON.stringify(ep.sampleBody);
                        }

                        const response = await fetch(fullUrl, options);
                        const endTime = performance.now();
                        let responseData;
                        try {
                            responseData = await response.json();
                        } catch(e) {
                            responseData = { text: await response.text() };
                        }

                        // Auto save token & company ID if testing login / register
                        if ((ep.id === 'auth-login' || ep.id === 'auth-register') && responseData.token) {
                            this.apiToken = responseData.token;
                            localStorage.setItem('api_docs_token', this.apiToken);
                            if (responseData.active_company?.id) {
                                this.companyId = responseData.active_company.id;
                            }
                            this.showToast('Token & Company ID berhasil disimpan ke Sandbox!');
                        }

                        ep.testResult = {
                            status: response.status,
                            statusText: response.statusText || (response.ok ? 'OK' : 'Error'),
                            time: Math.round(endTime - startTime),
                            data: responseData
                        };
                        this.showToast(`Response ${response.status} diterima (${ep.testResult.time} ms)`);
                    } catch (err) {
                        const endTime = performance.now();
                        ep.testResult = {
                            status: 0,
                            statusText: 'Network / CORS Error',
                            time: Math.round(endTime - startTime),
                            data: {
                                error: err.message,
                                hint: 'Pastikan server aktif dan URL Base benar.'
                            }
                        };
                        this.showToast('Gagal terhubung ke server');
                    } finally {
                        ep.loading = false;
                    }
                },

                endpointGroups: [
                    {
                        title: '0. Autentikasi & Token (Sanctum)',
                        endpoints: [
                            {
                                id: 'auth-login',
                                category: 'Authentication',
                                method: 'POST',
                                name: 'Login API & Ambil Token (Bearer)',
                                path: '/auth/login',
                                authRequired: false,
                                description: 'Mengautentikasi pengguna menggunakan email dan password, mengembalikan Bearer Token, Company ID, dan daftar perusahaan yang dapat diakses.',
                                params: [
                                    { name: 'email', type: 'email', required: true, desc: 'Alamat email akun terdaftar.' },
                                    { name: 'password', type: 'string', required: true, desc: 'Password akun.' },
                                    { name: 'device_name', type: 'string', required: false, desc: 'Nama perangkat / aplikasi klien (contoh: POS_Kasir_01).' }
                                ],
                                sampleBody: {
                                    email: "admin@dapurgemoy.com",
                                    password: "password123",
                                    device_name: "Mobile_POS_Kasir"
                                },
                                sampleResponse: {
                                    status: "success",
                                    message: "Login berhasil.",
                                    token: "1|eyJhbGciOi...",
                                    token_type: "Bearer",
                                    user: { id: 1, name: "Admin Utama", email: "admin@dapurgemoy.com" },
                                    active_company: { id: 1, name: "Dapur Gemoy", plan_type: "premium" },
                                    accessible_companies: [
                                        { id: 1, name: "Dapur Gemoy", role: "admin" }
                                    ]
                                },
                                activeLang: 'cURL',
                                customPayload: '{\n  "email": "admin@dapurgemoy.com",\n  "password": "password123",\n  "device_name": "Mobile_POS_Kasir"\n}'
                            },
                            {
                                id: 'auth-register',
                                category: 'Authentication',
                                method: 'POST',
                                name: 'Register Akun & Perusahaan Baru',
                                path: '/auth/register',
                                authRequired: false,
                                description: 'Mendaftarkan akun pemilik baru sekaligus melakukan provisioning otomatis perusahaan (120 COA, Pajak, dan Trial SaaS).',
                                params: [
                                    { name: 'name', type: 'string', required: true, desc: 'Nama lengkap pemilik.' },
                                    { name: 'email', type: 'email', required: true, desc: 'Email unik pengguna.' },
                                    { name: 'password', type: 'string', required: true, desc: 'Password minimal 6 karakter.' },
                                    { name: 'company_name', type: 'string', required: true, desc: 'Nama bisnis / perusahaan baru.' },
                                    { name: 'city', type: 'string', required: false, desc: 'Kota domisili perusahaan.' }
                                ],
                                sampleBody: {
                                    name: "Budi Santoso",
                                    email: "budi@restoberkah.com",
                                    password: "password123",
                                    company_name: "Resto Berkah Nusantara",
                                    city: "Surabaya"
                                },
                                sampleResponse: {
                                    status: "success",
                                    message: "Registrasi perusahaan dan pengguna berhasil.",
                                    token: "2|vK9sQ2...",
                                    active_company: { id: 2, name: "Resto Berkah Nusantara", role: "admin" }
                                },
                                activeLang: 'cURL',
                                customPayload: '{\n  "name": "Budi Santoso",\n  "email": "budi@restoberkah.com",\n  "password": "password123",\n  "company_name": "Resto Berkah Nusantara",\n  "city": "Surabaya"\n}'
                            },
                            {
                                id: 'auth-me',
                                category: 'Authentication',
                                method: 'GET',
                                name: 'Profil Saya & Daftar Perusahaan',
                                path: '/auth/me',
                                authRequired: true,
                                description: 'Mengambil data profil pengguna yang sedang login berdasarkan Bearer Token beserta seluruh daftar Company ID yang bisa diakses.',
                                params: [],
                                sampleResponse: {
                                    status: "success",
                                    user: { id: 1, name: "Admin Utama", email: "admin@dapurgemoy.com" },
                                    active_company: { id: 1, name: "Dapur Gemoy" },
                                    accessible_companies: [
                                        { id: 1, name: "Dapur Gemoy", role: "admin" }
                                    ]
                                },
                                activeLang: 'cURL'
                            },
                            {
                                id: 'auth-logout',
                                category: 'Authentication',
                                method: 'POST',
                                name: 'Logout & Cabut Token',
                                path: '/auth/logout',
                                authRequired: true,
                                description: 'Mencabut (revoke) Bearer Token yang sedang digunakan.',
                                params: [],
                                sampleResponse: {
                                    status: "success",
                                    message: "Token berhasil dicabut (Logout berhasil)."
                                },
                                activeLang: 'cURL'
                            }
                        ]
                    },
                    {
                        title: '1. Master Data & COA',
                        endpoints: [
                            {
                                id: 'accounts-list',
                                category: 'Master Data',
                                method: 'GET',
                                name: 'List Chart of Accounts (COA)',
                                path: '/accounts',
                                authRequired: true,
                                description: 'Mengambil seluruh daftar Akun Perkiraan (COA) aktif yang terdaftar pada perusahaan.',
                                params: [
                                    { name: 'category', type: 'string', required: false, desc: 'Filter kategori: kas_bank, piutang, persediaan, beban_operasional, dll.' },
                                    { name: 'type', type: 'string', required: false, desc: 'Filter tipe: asset, liability, equity, revenue, expense.' },
                                    { name: 'search', type: 'string', required: false, desc: 'Cari berdasarkan kode atau nama akun.' }
                                ],
                                sampleResponse: {
                                    status: "success",
                                    data: [
                                        { id: 1, company_id: 1, code: "1111", name: "Kas Utama", type: "asset", category: "kas_bank", initial_debit: 15000000, initial_credit: 0, is_active: true },
                                        { id: 2, company_id: 1, code: "1112", name: "Bank BCA Rekening Operasional", type: "asset", category: "kas_bank", initial_debit: 45000000, initial_credit: 0, is_active: true }
                                    ]
                                },
                                activeLang: 'cURL',
                                customPayload: ''
                            },
                            {
                                id: 'accounts-initial-balances',
                                category: 'Master Data',
                                method: 'POST',
                                name: 'Update Saldo Awal COA',
                                path: '/accounts/initial-balances',
                                authRequired: true,
                                description: 'Memperbarui saldo awal akun COA untuk proses migrasi neraca saldo.',
                                params: [
                                    { name: 'conversion_date', type: 'date', required: true, desc: 'Tanggal konversi saldo awal (contoh: 2026-01-01).' },
                                    { name: 'balances', type: 'array', required: true, desc: 'Array berisi { id, initial_debit, initial_credit }.' }
                                ],
                                sampleBody: {
                                    conversion_date: "2026-01-01",
                                    balances: [
                                        { id: 1, initial_debit: 20000000, initial_credit: 0 },
                                        { id: 2, initial_debit: 50000000, initial_credit: 0 }
                                    ]
                                },
                                sampleResponse: {
                                    status: "success",
                                    message: "Saldo awal akun berhasil diperbarui."
                                },
                                activeLang: 'cURL',
                                customPayload: '{\n  "conversion_date": "2026-01-01",\n  "balances": [\n    { "id": 1, "initial_debit": 20000000, "initial_credit": 0 }\n  ]\n}'
                            },
                            {
                                id: 'contacts-list',
                                category: 'Master Data',
                                method: 'GET',
                                name: 'List Kontak (Pelanggan / Vendor)',
                                path: '/contacts',
                                authRequired: true,
                                description: 'Mengambil master kontak pelanggan, vendor, atau karyawan.',
                                params: [
                                    { name: 'type', type: 'string', required: false, desc: 'Pilihan: customer, vendor, employee, other.' },
                                    { name: 'search', type: 'string', required: false, desc: 'Cari nama, no telp, atau email.' }
                                ],
                                sampleResponse: {
                                    status: "success",
                                    data: [
                                        { id: 1, name: "PT Sinergi Abadi", type: "customer", phone: "081234567890", email: "finance@sinergi.com", address: "Jakarta" }
                                    ]
                                },
                                activeLang: 'cURL'
                            },
                            {
                                id: 'contacts-create',
                                category: 'Master Data',
                                method: 'POST',
                                name: 'Tambah Kontak Baru',
                                path: '/contacts',
                                authRequired: true,
                                description: 'Menambahkan data master kontak baru (Pelanggan / Vendor / Karyawan).',
                                params: [
                                    { name: 'name', type: 'string', required: true, desc: 'Nama lengkap kontak atau badan usaha.' },
                                    { name: 'type', type: 'string', required: true, desc: 'customer, vendor, employee, other.' },
                                    { name: 'phone', type: 'string', required: false, desc: 'Nomor telepon / WhatsApp.' },
                                    { name: 'email', type: 'string', required: false, desc: 'Alamat email aktif.' },
                                    { name: 'address', type: 'string', required: false, desc: 'Alamat lengkap.' }
                                ],
                                sampleBody: {
                                    name: "Toko Sembako Berkah Jaya",
                                    type: "vendor",
                                    phone: "081398765432",
                                    email: "berkah@sembako.com",
                                    address: "Pasar Induk Kramat Jati"
                                },
                                sampleResponse: {
                                    status: "success",
                                    message: "Kontak berhasil ditambahkan.",
                                    data: { id: 5, name: "Toko Sembako Berkah Jaya", type: "vendor" }
                                },
                                activeLang: 'cURL',
                                customPayload: '{\n  "name": "Toko Sembako Berkah Jaya",\n  "type": "vendor",\n  "phone": "081398765432",\n  "email": "berkah@sembako.com",\n  "address": "Pasar Induk Kramat Jati"\n}'
                            },
                            {
                                id: 'departments-list',
                                category: 'Master Data',
                                method: 'GET',
                                name: 'List Departemen / Divisi',
                                path: '/departments',
                                authRequired: true,
                                description: 'Mengambil daftar divisi atau departemen perusahaan.',
                                params: [
                                    { name: 'is_active', type: 'boolean', required: false, desc: 'Filter status aktif.' },
                                    { name: 'search', type: 'string', required: false, desc: 'Cari nama atau kode divisi.' }
                                ],
                                sampleResponse: {
                                    status: "success",
                                    data: [
                                        { id: 1, name: "Operasional Dapur", code: "OPS", is_active: true }
                                    ]
                                },
                                activeLang: 'cURL'
                            },
                            {
                                id: 'projects-list',
                                category: 'Master Data',
                                method: 'GET',
                                name: 'List Proyek (Cost Center)',
                                path: '/projects',
                                authRequired: true,
                                description: 'Mengambil daftar proyek, tender, atau event cost center.',
                                params: [
                                    { name: 'status', type: 'string', required: false, desc: 'active, completed, on_hold.' },
                                    { name: 'search', type: 'string', required: false, desc: 'Cari nama proyek.' }
                                ],
                                sampleResponse: {
                                    status: "success",
                                    data: [
                                        { id: 1, name: "Katering Wedding Gemoy Q3", code: "PRJ-01", contract_amount: 45000000, status: "active" }
                                    ]
                                },
                                activeLang: 'cURL'
                            },
                            {
                                id: 'master-bundle',
                                category: 'Master Data',
                                method: 'GET',
                                name: 'Master Bundle Lengkap (All-in-One)',
                                path: '/master-bundle',
                                authRequired: true,
                                description: 'Mengambil seluruh master data (COA, Vendor, Customer, Departemen, Proyek, Tag, Metode Bayar, Pajak) dalam 1 request tunggal.',
                                params: [],
                                sampleResponse: {
                                    status: "success",
                                    data: {
                                        accounts: [],
                                        vendors: [],
                                        customers: [],
                                        departments: [],
                                        projects: [],
                                        tags: [],
                                        payment_methods: [],
                                        taxes: []
                                    }
                                },
                                activeLang: 'cURL'
                            }
                        ]
                    },
                    {
                        title: '2. Transaksi & Jurnal Akuntansi',
                        endpoints: [
                            {
                                id: 'transactions-list',
                                category: 'Transactions',
                                method: 'GET',
                                name: 'List Riwayat Transaksi',
                                path: '/transactions',
                                authRequired: true,
                                description: 'Mengambil riwayat transaksi keuangan lengkap dengan pagination dan filter tanggal.',
                                params: [
                                    { name: 'start_date', type: 'date', required: false, desc: 'Format: YYYY-MM-DD.' },
                                    { name: 'end_date', type: 'date', required: false, desc: 'Format: YYYY-MM-DD.' },
                                    { name: 'type', type: 'string', required: false, desc: 'income, expense, transfer, journal.' },
                                    { name: 'search', type: 'string', required: false, desc: 'Cari nomor transaksi atau catatan.' },
                                    { name: 'per_page', type: 'integer', required: false, desc: 'Jumlah data per halaman (default: 25).' }
                                ],
                                sampleResponse: {
                                    status: "success",
                                    data: {
                                        current_page: 1,
                                        data: [
                                            { id: 10, transaction_number: "TRX/202609/0001", date: "2026-09-24", type: "income", amount: 2500000, notes: "Penjualan Katering" }
                                        ]
                                    }
                                },
                                activeLang: 'cURL'
                            },
                            {
                                id: 'transactions-create-fast',
                                category: 'Transactions',
                                method: 'POST',
                                name: 'Catat Transaksi Cepat (1 Debit - 1 Kredit)',
                                path: '/transactions',
                                authRequired: true,
                                description: 'Mencatat transaksi cepat (1 debit & 1 kredit) dengan auto-create nama kontak/dept/proyek/tag jika belum terdaftar.',
                                params: [
                                    { name: 'date', type: 'date', required: true, desc: 'Tanggal transaksi (YYYY-MM-DD).' },
                                    { name: 'type', type: 'string', required: true, desc: 'income, expense, transfer, journal.' },
                                    { name: 'debit_account_code', type: 'string', required: true, desc: 'Kode akun debit (contoh: 1111 Kas).' },
                                    { name: 'credit_account_code', type: 'string', required: true, desc: 'Kode akun kredit (contoh: 4101 Pendapatan).' },
                                    { name: 'amount', type: 'number', required: true, desc: 'Nominal transaksi (Rupiah).' },
                                    { name: 'notes', type: 'string', required: false, desc: 'Keterangan transaksi.' },
                                    { name: 'customer_name', type: 'string', required: false, desc: 'Nama pelanggan (Auto-Create).' },
                                    { name: 'department_name', type: 'string', required: false, desc: 'Nama divisi (Auto-Create).' },
                                    { name: 'project_name', type: 'string', required: false, desc: 'Nama proyek (Auto-Create).' }
                                ],
                                sampleBody: {
                                    date: "2026-09-24",
                                    time: "14:00:00",
                                    type: "income",
                                    debit_account_code: "1111",
                                    credit_account_code: "4101",
                                    amount: 1500000,
                                    notes: "Penjualan Katering Paket VIP",
                                    customer_name: "PT Graha Gemilang",
                                    department_name: "Divisi Katering",
                                    project_name: "Event Corporate 2026"
                                },
                                sampleResponse: {
                                    status: "success",
                                    message: "Transaksi berhasil disimpan dan dijurnal otomatis.",
                                    data: { id: 101, transaction_number: "TRX/202609/0002", amount: 1500000 }
                                },
                                activeLang: 'cURL',
                                customPayload: '{\n  "date": "2026-09-24",\n  "time": "14:00:00",\n  "type": "income",\n  "debit_account_code": "1111",\n  "credit_account_code": "4101",\n  "amount": 1500000,\n  "notes": "Penjualan Katering Paket VIP",\n  "customer_name": "PT Graha Gemilang",\n  "department_name": "Divisi Katering",\n  "project_name": "Event Corporate 2026"\n}'
                            },
                            {
                                id: 'transactions-create-compound',
                                category: 'Transactions',
                                method: 'POST',
                                name: 'Catat Transaksi Majemuk (Multi-Akun)',
                                path: '/transactions',
                                authRequired: true,
                                description: 'Mencatat transaksi majemuk multi-line jurnal dengan validasi total Debit HARUS SAMA DENGAN total Kredit.',
                                params: [
                                    { name: 'date', type: 'date', required: true, desc: 'Tanggal transaksi (YYYY-MM-DD).' },
                                    { name: 'notes', type: 'string', required: false, desc: 'Catatan umum jurnal majemuk.' },
                                    { name: 'items', type: 'array', required: true, desc: 'Array akun { account_code, debit, credit, memo }.' }
                                ],
                                sampleBody: {
                                    date: "2026-09-24",
                                    notes: "Pembelian Bahan Baku & Perlengkapan (Kas + Tempo)",
                                    vendor_name: "Toko Sembako Makmur",
                                    department_name: "Dapur Utama",
                                    items: [
                                        { account_code: "5101", debit: 3000000, credit: 0, memo: "Bahan Baku" },
                                        { account_code: "6103", debit: 1000000, credit: 0, memo: "Perlengkapan" },
                                        { account_code: "1111", debit: 0, credit: 2000000, memo: "Kas Tunai" },
                                        { account_code: "2101", debit: 0, credit: 2000000, memo: "Hutang Usaha Tempo" }
                                    ]
                                },
                                sampleResponse: {
                                    status: "success",
                                    message: "Transaksi majemuk berhasil disimpan dan dijurnal otomatis.",
                                    data: { id: 102, amount: 4000000 }
                                },
                                activeLang: 'cURL',
                                customPayload: '{\n  "date": "2026-09-24",\n  "notes": "Pembelian Bahan Baku & Perlengkapan",\n  "vendor_name": "Toko Sembako Makmur",\n  "items": [\n    { "account_code": "5101", "debit": 3000000, "credit": 0, "memo": "Bahan Baku" },\n    { "account_code": "1111", "debit": 0, "credit": 3000000, "memo": "Kas Tunai" }\n  ]\n}'
                            }
                        ]
                    },
                    {
                        title: '3. Laporan Keuangan & Dashboard',
                        endpoints: [
                            {
                                id: 'dashboard-summary',
                                category: 'Reports',
                                method: 'GET',
                                name: 'Dashboard Executive Summary',
                                path: '/dashboard/summary',
                                authRequired: true,
                                description: 'Mengambil ringkasan metrik eksekutif: Total Pendapatan, Total Beban, Laba Bersih, Posisi Kas/Bank, Piutang, Hutang, & Riwayat Terkini.',
                                params: [
                                    { name: 'start_date', type: 'date', required: false, desc: 'Periode awal.' },
                                    { name: 'end_date', type: 'date', required: false, desc: 'Periode akhir.' }
                                ],
                                sampleResponse: {
                                    status: "success",
                                    data: {
                                        total_revenue: 125000000,
                                        total_expense: 80000000,
                                        net_profit: 45000000,
                                        cash_bank: 65000000,
                                        receivables: 15000000,
                                        payables: 8000000
                                    }
                                },
                                activeLang: 'cURL'
                            },
                            {
                                id: 'reports-profit-loss',
                                category: 'Reports',
                                method: 'GET',
                                name: 'Laporan Laba Rugi (P&L)',
                                path: '/reports/profit-loss',
                                authRequired: true,
                                description: 'Mengambil rincian akun pendapatan, HPP, laba kotor, beban operasional, dan laba bersih perusahaan.',
                                params: [
                                    { name: 'start_date', type: 'date', required: false, desc: 'Tanggal awal periode.' },
                                    { name: 'end_date', type: 'date', required: false, desc: 'Tanggal akhir periode.' }
                                ],
                                sampleResponse: {
                                    status: "success",
                                    data: {
                                        revenue_accounts: [],
                                        cogs_accounts: [],
                                        expense_accounts: [],
                                        net_profit: 35000000
                                    }
                                },
                                activeLang: 'cURL'
                            },
                            {
                                id: 'reports-balance-sheet',
                                category: 'Reports',
                                method: 'GET',
                                name: 'Laporan Neraca Keuangan (Balance Sheet)',
                                path: '/reports/balance-sheet',
                                authRequired: true,
                                description: 'Mengambil posisi neraca (Aset Lancar, Aset Tetap, Liabilitas Hutang, Ekuitas Modal) per tanggal tertentu.',
                                params: [
                                    { name: 'as_of_date', type: 'date', required: false, desc: 'Posisi per tanggal (contoh: 2026-12-31).' }
                                ],
                                sampleResponse: {
                                    status: "success",
                                    data: {
                                        total_assets: 250000000,
                                        total_liabilities: 50000000,
                                        total_equity: 200000000
                                    }
                                },
                                activeLang: 'cURL'
                            },
                            {
                                id: 'reports-journal',
                                category: 'Reports',
                                method: 'GET',
                                name: 'Laporan Jurnal Umum (Audit Trail)',
                                path: '/reports/journal',
                                authRequired: true,
                                description: 'Mengambil seluruh entri jurnal berpasangan debit dan kredit untuk kepentingan audit akuntansi.',
                                params: [
                                    { name: 'start_date', type: 'date', required: false, desc: 'Filter awal tanggal.' },
                                    { name: 'end_date', type: 'date', required: false, desc: 'Filter akhir tanggal.' },
                                    { name: 'search', type: 'string', required: false, desc: 'Cari deskripsi atau nomor jurnal.' }
                                ],
                                sampleResponse: {
                                    status: "success",
                                    data: []
                                },
                                activeLang: 'cURL'
                            }
                        ]
                    },
                    {
                        title: '4. AI Natural Language Helper',
                        endpoints: [
                            {
                                id: 'ai-parse',
                                category: 'AI Helper',
                                method: 'POST',
                                name: 'AI NLP Natural Language Parser',
                                path: '/ai/parse',
                                authRequired: true,
                                description: 'Mengekstrak kalimat bebas bahasa Indonesia menjadi objek transaksi & akun debit-kredit siap simpan menggunakan Gemini AI.',
                                params: [
                                    { name: 'prompt', type: 'string', required: true, desc: 'Kalimat transaksi (contoh: "Beli daging ayam 2jt via transfer BCA ke Toko Berkah").' }
                                ],
                                sampleBody: {
                                    prompt: "Beli bahan baku ayam segar 50kg senilai 2.500.000 bayar via transfer BCA ke Toko Berkah"
                                },
                                sampleResponse: {
                                    status: "success",
                                    data: {
                                        type: "expense",
                                        amount: 2500000,
                                        debit_account_name: "Beban Pokok Pendapatan (HPP)",
                                        credit_account_name: "Bank BCA",
                                        contact_name: "Toko Berkah",
                                        notes: "Beli bahan baku ayam segar 50kg"
                                    }
                                },
                                activeLang: 'cURL',
                                customPayload: '{\n  "prompt": "Beli bahan baku ayam segar 50kg senilai 2.500.000 bayar via transfer BCA ke Toko Berkah"\n}'
                            }
                        ]
                    }
                ],

                get allEndpoints() {
                    const list = [];
                    this.endpointGroups.forEach(g => {
                        g.endpoints.forEach(e => list.push(e));
                    });
                    return list;
                }
            }
        }
    </script>
</body>

</html>
