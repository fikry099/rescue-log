<?php

namespace App\Http\Controllers\Komando;

use App\Http\Controllers\Controller;
use App\Models\Armada;
use App\Models\KendalaJalan;
use App\Models\PengajuanKebutuhan;
use App\Models\PengirimanInventaris;
use App\Models\StokInventaris;
use App\Models\Posko;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KomandoDistribusiController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // 1. Ambil posko milik user
        $posko = null;
        if ($user->posko_id) {
            $posko = Posko::find($user->posko_id);
        }

        if (!$posko) {
            $posko = Posko::where('tipe_posko', 'komando')
                ->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                      ->orWhere('bpbd_id', $user->bpbd_id);
                })
                ->first();
        }

        $poskoId = $posko ? $posko->id : null;

        // 2. Data Pengajuan Masuk dari Sub-Posko (Perbaikan: Hapus relasi 'items.inventaris' yang tidak ada)
        $pengajuans = PengajuanKebutuhan::with(['posko', 'user', 'bencana'])
            ->whereIn('status', ['pending', 'menunggu'])
            ->orderBy('created_at', 'desc')
            ->get();

        // 3. Data Pengajuan Siap Kirim
        $pengajuanSiapKirim = PengajuanKebutuhan::whereIn('status', ['disetujui', 'disetujui_sebagian'])->get();

        // 4. Data Armada Siaga
        $armadas = Armada::orderBy('created_at', 'desc')->get();

        // 5. Data Riwayat / Proses Pengiriman
        $pengirimans = PengirimanInventaris::with(['pengajuan.posko', 'armada', 'posko'])
            ->orderBy('created_at', 'desc')
            ->get();

        // 6. Data Stok Inventaris Komando
        $stoks = StokInventaris::with('inventaris')
            ->where('posko_id', $poskoId)
            ->get();

        // 7. Data Kendala Jalan
        $kendalaJalans = KendalaJalan::orderBy('created_at', 'desc')->get();

        return view('dashboard.komando.distribusi.index', compact(
            'posko',
            'pengajuans',
            'pengajuanSiapKirim',
            'armadas',
            'pengirimans',
            'stoks',
            'kendalaJalans'
        ));
    }

    /**
     * Menyimpan Plotting Rute & Pengiriman Inventaris Baru
     */
    public function store(Request $request)
    {
        $pengajuanId = $request->input('pengajuan_id') ?? $request->input('pengajuan_kebutuhan_id');

        $validated = $request->validate([
            'armada_id'  => 'required|exists:armadas,id',
            'nomor_resi' => 'nullable|string|max:100',
            'catatan'    => 'nullable|string',
        ]);

        if (!$pengajuanId) {
            return redirect()->back()->with('error', 'ID Pengajuan kebutuhan tidak ditemukan.');
        }

        $user = auth()->user();
        
        // Ambil data Pengajuan Kebutuhan beserta Posko tujuannya
        $pengajuan = PengajuanKebutuhan::with('posko')->find($pengajuanId);

        if (!$pengajuan) {
            return redirect()->back()->with('error', 'Data Pengajuan Kebutuhan tidak ditemukan.');
        }

        // Tentukan Posko Tujuan dan Posko Asal (Komando)
        $poskoTujuanId = $pengajuan->posko_id ?? $user->posko_id;
        $poskoKomando  = $user->posko_id ? Posko::find($user->posko_id) : Posko::where('tipe_posko', 'komando')->first();

        $pengirimanId = null;

        DB::transaction(function () use ($validated, $pengajuan, $user, $poskoTujuanId, $poskoKomando, &$pengirimanId) {
            // 1. Buat Record Pengiriman Inventaris
            $pengiriman = PengirimanInventaris::create([
                'pengajuan_kebutuhan_id' => $pengajuan->id,
                'posko_id'               => $poskoTujuanId,
                'armada_id'              => $validated['armada_id'],
                'nomor_resi'             => $validated['nomor_resi'] ?? 'TRX-' . strtoupper(uniqid()),
                'status_pengiriman'      => 'dalam_perjalanan',
                'status_distribusi'      => 'dalam_perjalanan',
                'catatan'                => $validated['catatan'] ?? null,
                'lat_asal'               => $poskoKomando?->latitude ?? -7.8893,
                'long_asal'              => $poskoKomando?->longitude ?? 110.3288,
                'lat_tujuan'             => $pengajuan->posko?->latitude ?? $pengajuan->latitude ?? -7.8000,
                'long_tujuan'            => $pengajuan->posko?->longitude ?? $pengajuan->longitude ?? 110.3800,
                'tanggal_dikirim'        => now(),
                'user_id'                => $user->id,
                'bpbd_id'                => $user->bpbd_id,
            ]);

            $pengirimanId = $pengiriman->id;

            // 2. Update Status Armada menjadi 'dalam_tugas'
            $armada = Armada::find($validated['armada_id']);
            if ($armada) {
                $armada->update(['status' => 'dalam_tugas']);
            }

            // 3. Update Status Pengajuan Kebutuhan menjadi 'dalam_pengiriman'
            $pengajuan->update(['status' => 'dalam_pengiriman']);
        });

        return redirect()->route('komando.distribusi.index')
            ->with('success', 'Plotting rute & instruksi pengiriman berhasil diproses!')
            ->with('active_pengiriman_id', $pengirimanId);
    }

    /**
     * Update Status Pengiriman Inventaris
     */
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status_pengiriman' => 'required|string|in:siap,dalam_perjalanan,terkendala,terkirim,selesai',
        ]);

        $pengiriman = PengirimanInventaris::findOrFail($id);

        DB::transaction(function () use ($pengiriman, $validated) {
            $pengiriman->update([
                'status_pengiriman' => $validated['status_pengiriman'],
                'status_distribusi' => $validated['status_pengiriman'],
                'tanggal_diterima'  => $validated['status_pengiriman'] === 'terkirim' ? now() : $pengiriman->tanggal_diterima,
            ]);

            // Jika pengiriman selesai/terkirim, bebaskan armada kembali 'tersedia' dan pengajuan ke 'selesai'
            if (in_array($validated['status_pengiriman'], ['terkirim', 'selesai'])) {
                if ($pengiriman->armada) {
                    $pengiriman->armada->update(['status' => 'tersedia']);
                }
                if ($pengiriman->pengajuan) {
                    $pengiriman->pengajuan->update(['status' => 'selesai']);
                }
            }
        });

        return redirect()->route('komando.distribusi.index')
            ->with('success', 'Status pengiriman berhasil diperbarui!');
    }

    /**
     * Menyimpan data Armada baru ke Database
     */
    public function storeArmada(Request $request)
    {
        $validated = $request->validate([
            'nama_armada'   => 'required|string|max:255',
            'plat_nomor'    => 'required|string|max:50|unique:armadas,plat_nomor',
            'nama_driver'   => 'required|string|max:255',
            'no_hp'         => 'nullable|string|max:50',
            'status'        => 'required|string|in:tersedia,dalam_tugas,maintenance',
        ]);

        Armada::create([
            'nama_armada'   => $validated['nama_armada'],
            'plat_nomor'    => $validated['plat_nomor'],
            'nama_driver'   => $validated['nama_driver'],
            'no_hp'         => $validated['no_hp'] ?? null,
            'status'        => $validated['status'],
        ]);

        return redirect()->route('komando.distribusi.index')->with('success', 'Armada pengiriman berhasil ditambahkan!');
    }

    /**
     * Menyimpan data Laporan Kendala Jalan baru ke Database (Smart Routing GIS)
     */
    public function storeKendala(Request $request)
    {
        $validated = $request->validate([
            'nama_lokasi'   => 'required|string|max:255',
            'jenis_kendala' => 'required|string|max:100',
            'latitude'      => 'required|numeric',
            'longitude'     => 'required|numeric',
            'deskripsi'     => 'nullable|string',
        ]);

        $user = auth()->user();

        KendalaJalan::create([
            'nama_lokasi'   => $validated['nama_lokasi'],
            'jenis_kendala' => $validated['jenis_kendala'],
            'latitude'      => $validated['latitude'],
            'longitude'     => $validated['longitude'],
            'deskripsi'     => $validated['deskripsi'] ?? null,
            'is_active'     => true,
            'user_id'       => $user->id,
            'bpbd_id'       => $user->bpbd_id,
        ]);

        return redirect()->route('komando.distribusi.index')->with('success', 'Laporan kendala jalan berhasil ditambahkan ke sistem rute!');
    }

    /**
     * Mengubah status aktif / non-aktif Kendala Jalan (Selesai/Aktif)
     */
    public function toggleKendala($id)
    {
        $kendala = KendalaJalan::findOrFail($id);
        $kendala->is_active = !$kendala->is_active;
        $kendala->save();

        $statusMessage = $kendala->is_active ? 'diaktifkan kembali' : 'ditandai selesai / dapat dilalui';

        return redirect()->route('komando.distribusi.index')->with('success', "Status kendala jalan berhasil {$statusMessage}!");
    }
}