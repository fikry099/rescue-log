@extends('layouts.app-lapangan')

@section('title', 'Penyaluran & Pencatatan Stok')

@section('content')
    <div class="w-full space-y-4 sm:space-y-6 font-sans" x-data="{ isLoading: true, showModal: false }" x-init="setTimeout(() => isLoading = false, 300)">

        <!-- 1. HEADER RINGKAS KHUSUS MOBILE (SMARTPHONE) -->
        <div class="flex flex-col gap-3 sm:hidden mb-1">
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2.5">
                    <a href="{{ route('lapangan.dashboard') }}"
                        class="w-9 h-9 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-600 hover:text-blue-600 transition shadow-2xs shrink-0 cursor-pointer">
                        <x-heroicon-s-arrow-left class="w-4 h-4" />
                    </a>
                    <div>
                        <h1 class="text-base font-bold text-slate-900 leading-tight">Penyaluran Stok</h1>
                        <p class="text-[11px] text-slate-500 mt-0.5 line-clamp-1">Catat logistik keluar untuk pengungsi.</p>
                    </div>
                </div>

                <button type="button" @click="showModal = true"
                    class="inline-flex items-center gap-1.5 px-3 py-2 bg-blue-700 hover:bg-blue-800 text-white font-bold text-xs rounded-xl shadow-xs transition shrink-0 cursor-pointer">
                    <x-heroicon-s-plus class="w-3.5 h-3.5 text-white stroke-[3]" />
                    <span>Catat Salur</span>
                </button>
            </div>
        </div>

        <!-- 2. HEADER KHUSUS DESKTOP & TABLET -->
        <div class="hidden sm:flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('lapangan.dashboard') }}"
                    class="p-2.5 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-blue-600 transition shadow-xs shrink-0 cursor-pointer">
                    <x-heroicon-s-arrow-left class="w-5 h-5 text-slate-600" />
                </a>
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Penyaluran & Pencatatan Stok</h1>
                    <p class="text-sm text-slate-500 mt-0.5 leading-relaxed">
                        Catat logistik yang disalurkan langsung kepada pengungsi dan pantau pengurangan stok secara otomatis.
                    </p>
                </div>
            </div>

            <button type="button" @click="showModal = true"
                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-blue-700 hover:bg-blue-800 text-white rounded-xl text-sm font-bold shadow-md shadow-blue-500/20 transition shrink-0 cursor-pointer ml-auto">
                <x-heroicon-s-plus class="w-5 h-5 text-white shrink-0 stroke-[3]" />
                <span>Catat Penyaluran Baru</span>
            </button>
        </div>

        <!-- 3. LOADING SKELETON STATE -->
        <div x-show="isLoading" class="space-y-4 sm:space-y-6">
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                @include('components.skeleton.card')
                @include('components.skeleton.card')
                @include('components.skeleton.card')
            </div>
            @include('components.skeleton.table')
        </div>

        <!-- 4. KONTEN UTAMA -->
        <div x-show="!isLoading" style="display: none;" class="space-y-4 sm:space-y-6">

            <!-- STATISTIK KARTU -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 sm:gap-4">
                <x-sub-posko.penyaluran.stat-card title="Total Riwayat" :value="$riwayatPenyaluran->total() . ' Transaksi'" bg-icon="bg-amber-50" text-icon="text-amber-600">
                    <x-heroicon-s-clipboard-document-check class="w-5 h-5 sm:w-6 sm:h-6 text-amber-600" />
                </x-sub-posko.penyaluran.stat-card>

                <x-sub-posko.penyaluran.stat-card title="Stok Siap Salur" :value="$stoks->count() . ' Item'" bg-icon="bg-blue-50" text-icon="text-blue-600">
                    <x-heroicon-s-cube class="w-5 h-5 sm:w-6 sm:h-6 text-blue-600" />
                </x-sub-posko.penyaluran.stat-card>

                <div class="col-span-2 sm:col-span-1">
                    <x-sub-posko.penyaluran.stat-card title="Status Sistem" value="Aktif" bg-icon="bg-emerald-50" text-icon="text-emerald-600" text-value="text-emerald-600">
                        <x-heroicon-s-check-circle class="w-5 h-5 sm:w-6 sm:h-6 text-emerald-600" />
                    </x-sub-posko.penyaluran.stat-card>
                </div>
            </div>

            <!-- TABEL / LIST RIWAYAT PENYALURAN -->
            <x-sub-posko.penyaluran.table-penyaluran :riwayatPenyaluran="$riwayatPenyaluran" />

        </div>

        <!-- MODAL FORM PENYALURAN BARU -->
        <x-sub-posko.penyaluran.modal-penyaluran :stoks="$stoks" />

    </div>

    <!-- 5. HANDLER SWEETALERT2 DARI SESSION BACKEND -->
    @if (session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Berhasil! 🎉',
                    text: "{{ session('success') }}",
                    icon: 'success',
                    confirmButtonColor: '#1d4ed8',
                    customClass: { popup: 'rounded-2xl font-sans' }
                });
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Perhatian! ⚠️',
                    text: "{{ session('error') }}",
                    icon: 'error',
                    confirmButtonColor: '#1d4ed8',
                    customClass: { popup: 'rounded-2xl font-sans' }
                });
            });
        </script>
    @endif
@endsection