@extends('layouts.app-lapangan')

@section('title', 'Status Distribusi & Stok Logistik')

@section('content')
    <div class="w-full space-y-4 sm:space-y-6 font-sans">

        <!-- 1. HEADER RINGKAS KHUSUS MOBILE (SMARTPHONE) -->
        <div class="flex flex-col gap-3 sm:hidden mb-2">
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2.5">
                    <a href="{{ route('lapangan.dashboard') }}"
                        class="w-9 h-9 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-600 hover:text-blue-600 transition shadow-2xs shrink-0 cursor-pointer">
                        <x-heroicon-s-arrow-left class="w-4 h-4" />
                    </a>
                    <div>
                        <h1 class="text-base font-bold text-slate-900 leading-tight">Distribusi & Stok</h1>
                        <p class="text-[11px] text-slate-500 mt-0.5 line-clamp-1">Status pengiriman & ketersediaan stok posko.</p>
                    </div>
                </div>

                <a href="{{ route('lapangan.pengajuan.index') }}"
                    class="inline-flex items-center gap-1.5 px-3 py-2 bg-blue-700 hover:bg-blue-800 text-white font-bold text-xs rounded-xl shadow-xs transition shrink-0 cursor-pointer">
                    <x-heroicon-s-plus class="w-3.5 h-3.5 text-white stroke-[3]" />
                    <span>Pengajuan</span>
                </a>
            </div>
        </div>

        <!-- 2. HEADER DESKTOP & TABLET -->
        <div class="hidden sm:flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('lapangan.dashboard') }}"
                    class="p-2.5 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-blue-600 transition shadow-xs shrink-0 cursor-pointer">
                    <x-heroicon-s-arrow-left class="w-5 h-5 text-slate-600" />
                </a>
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Status Distribusi & Stok Logistik</h1>
                    <p class="text-sm text-slate-500 mt-0.5 leading-relaxed">
                        Pantau status pengiriman dari Posko Komando serta ketersediaan stok barang di posko lapangan secara real-time.
                    </p>
                </div>
            </div>

            <a href="{{ route('lapangan.pengajuan.index') }}"
                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-blue-700 hover:bg-blue-800 text-white rounded-xl text-sm font-bold shadow-md shadow-blue-500/20 transition shrink-0 cursor-pointer">
                <x-heroicon-s-plus class="w-5 h-5 text-white shrink-0 stroke-[3]" />
                <span>Pengajuan Baru</span>
            </a>
        </div>

        <!-- 3. KONTEN UTAMA TABLE / CARDS -->
        <div class="w-full space-y-5" x-data="{ isLoading: true }" x-init="setTimeout(() => isLoading = false, 300)">

            <div x-show="isLoading" class="w-full">
                <x-skeleton.tabel-distribusi-loading />
            </div>

            <div x-show="!isLoading" style="display: none;" class="space-y-6 w-full">
                <!-- Tabel / Kartu Status Pengiriman -->
                <x-sub-posko.distribusi.status-pengiriman-table :pengirimans="$pengirimans" :pengajuans="$pengajuans" />

                <!-- Tabel / Kartu Stok Logistik Posko -->
                <x-sub-posko.distribusi.inventaris-stok-table :stoks="$stoks" />
            </div>

        </div>

    </div>

    <x-sub-posko.distribusi.modal-distribusi-script />
@endsection