<?php

namespace App\Http\Controllers\Lapangan;

use App\Http\Controllers\Controller;
use App\Models\Pendataan;
use App\Models\PengajuanKebutuhan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PengajuanController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        Log::info('PengajuanController@index - Mengambil data pengajuan Posko ID: ' . $user->posko_id);

        // 1. Ambil data pendataan pengungsi terbaru khusus posko user
        $pendataan = Pendataan::where('posko_id', $user->posko_id)
            ->latest()
            ->first();

        if (!$pendataan) {
            Log::warning('PengajuanController@index - Pendataan tidak ditemukan untuk Posko ID: ' . $user->posko_id);
            return redirect()->route('lapangan.pengungsi.index')
                ->with('error', 'Silakan isi Form Pendataan Pengungsi terlebih dahulu sebelum mengajukan logistik.');
        }

        // 2. Tentukan Total Penerima Manfaat secara akurat dari model Pendataan
        $totalPenerimaManfaat = (int) ($pendataan->total_pengungsi 
                                    ?? $pendataan->jumlah_pengungsi 
                                    ?? (($pendataan->balita ?? 0) + ($pendataan->anak ?? 0) + ($pendataan->dewasa ?? 0) + ($pendataan->lansia ?? 0)));

        if ($totalPenerimaManfaat <= 0) {
            $totalPenerimaManfaat = 1; // Safeguard agar tidak 0
        }

        // 3. Format payload persis sesuai Pydantic Schema FastAPI (PengajuanLogistikInput)
        $payloadML = [
            'total_pengungsi'       => $totalPenerimaManfaat,
            'anak_balita'           => (int) ($pendataan->balita ?? 0),
            'dewasa'                => (int) ($pendataan->dewasa ?? $totalPenerimaManfaat),
            'ibu_hamil'             => (int) ($pendataan->ibu_hamil ?? 0),
            'lansia'                => (int) ($pendataan->lansia ?? 0),
            'disabilitas'           => (int) ($pendataan->disabilitas ?? 0),
            'tipe_tempat'           => (string) ($pendataan->tipe_tempat ?? 'Balai Desa'),
            'akses_air'             => (string) ($pendataan->akses_air ?? 'Cukup'),
            'suhu_celcius'          => (float) ($pendataan->suhu_celcius ?? 28.5),
            'cuaca'                 => (string) ($pendataan->cuaca ?? 'Hujan Deras'),
            'akses_jalan'           => (string) ($pendataan->akses_jalan ?? 'Mobil/Truk Bisa Masuk'),
            'lama_pengungsian_hari' => max(1, (int) ($pendataan->lama_pengungsian ?? $pendataan->lama_pengungsian_hari ?? 1)),
        ];

        $estimasi = [];
        try {
            $baseUrl = env('ML_SERVICE_URL', env('FASTAPI_URL', 'http://127.0.0.1:8001'));
            $fastApiUrl = rtrim($baseUrl, '/') . '/predict';

            // PERBAIKAN TIMEOUT: Ditiadakan atau diturunkan ke 1 detik max agar tidak menggantung server
            $response = Http::timeout(1)->post($fastApiUrl, $payloadML);

            if ($response->successful()) {
                $hasil = $response->json();
                $estimasi = $hasil['estimasi_kebutuhan'] ?? [];
            }
        } catch (\Exception $e) {
            Log::warning('PengajuanController@index - FastAPI ML tidak merespons (Offline / Timeout 1s): ' . $e->getMessage());
            // Nilai estimasi dibiarkan [] agar pengguna tetap bisa mengisi form pengajuan secara manual tanpa lag
        }

        // Ambil riwayat pengajuan posko
        $pengajuans = PengajuanKebutuhan::where('posko_id', $user->posko_id)
            ->latest()
            ->get();

        $totalJenis = 12;

        return view('dashboard.lapangan.pengajuan.index', compact(
            'pendataan', 
            'estimasi', 
            'pengajuans', 
            'totalPenerimaManfaat', 
            'totalJenis'
        ));
    }

    public function create()
    {
        return redirect()->route('lapangan.pengajuan.index');
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $pendataan = Pendataan::where('posko_id', $user->posko_id)
            ->latest()
            ->first();

        if (!$pendataan) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Silakan isi Form Pendataan Pengungsi terlebih dahulu.'
                ], 422);
            }

            return redirect()->route('lapangan.pengungsi.index')
                ->with('error', 'Silakan isi Form Pendataan Pengungsi terlebih dahulu.');
        }

        $validated = $request->validate([
            'beras_kg'             => 'nullable|numeric|min:0',
            'air_minum_dus'        => 'nullable|numeric|min:0',
            'makanan_kaleng_pack'  => 'nullable|numeric|min:0',
            'makanan_bayi_pack'    => 'nullable|numeric|min:0',
            'minyak_goreng_liter'  => 'nullable|numeric|min:0',
            'popok_bayi_pcs'       => 'nullable|numeric|min:0',
            'popok_dewasa_pcs'     => 'nullable|numeric|min:0',
            'pembalut_wanita_pack' => 'nullable|numeric|min:0',
            'hygiene_kit_paket'    => 'nullable|numeric|min:0',
            'selimut_pcs'          => 'nullable|numeric|min:0',
            'matras_terpal_pcs'    => 'nullable|numeric|min:0',
            'obat_p3k_paket'       => 'nullable|numeric|min:0',
            'catatan_posko'        => 'nullable|string',
        ]);

        $totalInput = (float) $request->input('beras_kg', 0)
            + (float) $request->input('air_minum_dus', 0)
            + (float) $request->input('makanan_kaleng_pack', 0)
            + (float) $request->input('makanan_bayi_pack', 0)
            + (float) $request->input('minyak_goreng_liter', 0)
            + (float) $request->input('popok_bayi_pcs', 0)
            + (float) $request->input('popok_dewasa_pcs', 0)
            + (float) $request->input('pembalut_wanita_pack', 0)
            + (float) $request->input('hygiene_kit_paket', 0)
            + (float) $request->input('selimut_pcs', 0)
            + (float) $request->input('matras_terpal_pcs', 0)
            + (float) $request->input('obat_p3k_paket', 0);

        if ($totalInput <= 0) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Minimal harus mengajukan 1 jenis logistik dengan jumlah lebih dari 0.'
                ], 422);
            }

            return redirect()->back()
                ->with('error', 'Minimal harus mengajukan 1 jenis logistik dengan jumlah lebih dari 0.')
                ->withInput();
        }

        try {
            $pengajuan = PengajuanKebutuhan::create([
                'kode_pengajuan'       => 'REQ-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
                'user_id'              => $user->id,
                'posko_id'             => $user->posko_id ?? null,
                'bencana_id'           => $pendataan->bencana_id ?? null,
                'beras_kg'             => round((float) $request->input('beras_kg', 0), 2),
                'air_minum_dus'        => round((float) $request->input('air_minum_dus', 0), 2),
                'makanan_kaleng_pack'  => round((float) $request->input('makanan_kaleng_pack', 0), 2),
                'makanan_bayi_pack'    => round((float) $request->input('makanan_bayi_pack', 0), 2),
                'minyak_goreng_liter'  => round((float) $request->input('minyak_goreng_liter', 0), 2),
                'popok_bayi_pcs'       => round((float) $request->input('popok_bayi_pcs', 0), 2),
                'popok_dewasa_pcs'     => round((float) $request->input('popok_dewasa_pcs', 0), 2),
                'pembalut_wanita_pack' => round((float) $request->input('pembalut_wanita_pack', 0), 2),
                'hygiene_kit_paket'    => round((float) $request->input('hygiene_kit_paket', 0), 2),
                'selimut_pcs'          => round((float) $request->input('selimut_pcs', 0), 2),
                'matras_terpal_pcs'    => round((float) $request->input('matras_terpal_pcs', 0), 2),
                'obat_p3k_paket'       => round((float) $request->input('obat_p3k_paket', 0), 2),
                'tanggal_pengajuan'    => now(),
                'status'               => 'pending',
                'catatan_posko'        => $request->catatan_posko,
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Pengajuan logistik berhasil dikirimkan ke Posko Komando!',
                    'data'    => $pengajuan
                ], 200);
            }

            return redirect()->route('lapangan.stok.index')
                ->with('success', 'Pengajuan kebutuhan logistik berhasil dikirimkan ke Posko Komando!');

        } catch (\Exception $e) {
            Log::error('Gagal menyimpan pengajuan: ' . $e->getMessage());

            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Gagal menyimpan pengajuan: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Gagal menyimpan pengajuan: ' . $e->getMessage())
                ->withInput();
        }
    }
}