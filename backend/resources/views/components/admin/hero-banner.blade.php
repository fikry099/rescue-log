<div class="relative overflow-hidden rounded-2xl h-48 px-6 py-5 text-white shadow-sm bg-cover bg-[center_top_15%]"
    style="background-image: url('{{ asset('img/bpbd1.png') }}');">
    
    <!-- Gradient tipis di sebelah kiri agar latar belakang gunung tetap jelas -->
    <div class="absolute inset-0 bg-gradient-to-r from-slate-950/80 via-slate-900/20 to-transparent"></div>

    <div class="relative z-10 flex flex-col justify-start items-start gap-3">
        <!-- Judul & Subjudul -->
        <div>
            <h1 class="text-2xl font-black tracking-tight drop-shadow-md">Selamat Datang di BPBD</h1>
            <p class="text-xs text-gray-100 font-medium mt-1 drop-shadow-md">Sistem Informasi Penanggulangan Bencana Terpadu</p>
        </div>
        <!-- Badge Hari & Jam tepat di bawah teks -->
        <div class="inline-flex items-center gap-1.5 bg-slate-950/60 backdrop-blur-md px-3 py-1 rounded-full text-[11px] border border-white/20 shadow-sm">
            <x-heroicon-o-calendar class="w-3.5 h-3.5 text-orange-400" />
            <span class="font-semibold text-white">Senin, 19 Mei 2025</span>
            <span class="opacity-40">|</span>
            <span class="font-semibold text-white">14:32 WIB</span>
        </div>
    </div>

</div>