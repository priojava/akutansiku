@extends('layouts.app')

@section('title', 'Cara Pembayaran Invoice')

@section('content')
<div class="space-y-6" x-data="{ openModal: false, search: '' }">

    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm flex items-center justify-between">
        <button @click="openModal = true" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-4 py-2.5 rounded-lg shadow-sm transition">
            Tambah Cara Pembayaran
        </button>

        <div class="relative">
            <input type="text" x-model="search" placeholder="Cari..."
                   class="w-64 pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-300 rounded-lg text-xs focus:bg-white focus:outline-none">
            <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2 text-slate-400 text-xs"></i>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <table class="w-full text-left text-xs text-slate-700">
            <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[10px] tracking-wider font-semibold">
                <tr>
                    <th class="px-6 py-4 w-16">No</th>
                    <th class="px-6 py-4">Nama Pembayaran</th>
                    <th class="px-6 py-4">Akun Terkait (COA)</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($methods as $index => $pm)
                    <tr class="hover:bg-slate-50 transition" x-show="!search || '{{ strtolower($pm->name) }}'.includes(search.toLowerCase())">
                        <td class="px-6 py-4 font-semibold text-slate-500">{{ $index + 1 }}</td>
                        <td class="px-6 py-4 font-bold text-slate-800">{{ $pm->name }}</td>
                        <td class="px-6 py-4 text-slate-600">
                            {{ $pm->account ? $pm->account->name . ' (' . $pm->account->code . ')' : '-' }}
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <button class="text-blue-600 hover:text-blue-800 font-semibold">Edit</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Modal Tambah -->
    <div x-show="openModal" x-cloak class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100" @click.away="openModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="font-bold text-slate-800 text-sm">Tambah Cara Pembayaran</h3>
                <button @click="openModal = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('master.payment_methods.store') }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Pembayaran <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" placeholder="Misal: QRIS, ShopeePay, Bank Jago" required
                           class="w-full px-3.5 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Hubungkan ke Akun Kas/Bank</label>
                    <x-searchable-account-select 
                        name="account_id" 
                        :options="$accounts" 
                        placeholder="Pilih Akun Kas/Bank..." 
                        :required="false" 
                        bg-color="bg-white" />
                </div>
                <div class="flex justify-end space-x-2 pt-2">
                    <button type="button" @click="openModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg">Batal</button>
                    <button type="submit" class="px-5 py-2 text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
