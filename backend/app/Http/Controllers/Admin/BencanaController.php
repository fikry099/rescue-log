<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Bencana;
use App\Models\BencanaPending;
use App\Models\Posko;
use App\Models\StokPosko;
use App\Services\SpatialCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BencanaController extends Controller
{
    /**
     * Tampilkan Halaman Utama Pusat Komando Bencana (Daftar & Statistik)
     */
    public function index()
    {
        $user = Auth::user();
        $userBpbd = $user->bpbd;
        $regionName = $userBpbd ? strtolower(trim($userBpbd->nama_kabupaten_kota)) : null;

        $pendingQuery = BencanaPending::where('status', 'pending');

        if ($regionName) {
            $cleanRegion = str_replace(['kabupaten ', 'kota '], '', $regionName);

            $pendingQuery->where(function ($query) use ($regionName, $cleanRegion) {
                $query->orWhereRaw('LOWER(wilayah) LIKE ?', ['%' . $regionName . '%'])
                    ->orWhereRaw('LOWER(wilayah) LIKE ?', ['%' . $cleanRegion . '%'])
                    ->orWhere('external_id', 'LIKE', 'MANUAL-%');
            });
        }

        $pendingDisasters = $pendingQuery->orderBy('waktu_kejadian', 'desc')->get();
        $todayQuery = BencanaPending::whereDate('created_at', today());

        // Ambil bencana yang 'sedang_berjalan' DAN yang 'menunggu_posko'
        $activeDisasters = Bencana::whereIn('status', ['menunggu_posko', 'sedang_berjalan'])
            ->orderBy('tanggal_aktivasi', 'desc')
            ->get();

        $completedDisasters = Bencana::where('status', 'selesai')
            ->orderBy('tanggal_selesai', 'desc')
            ->get();

        $stats = [
            'terdeteksi_hari_ini' => $todayQuery->count(), 
            'perlu_validasi'      => $pendingDisasters->count(),
            'sedang_berjalan'     => $activeDisasters->count(),
            'selesai'             => $completedDisasters->count(),
        ];

        return view('dashboard.admin.bencana.index', compact(
            'pendingDisasters', 
            'activeDisasters', 
            'completedDisasters', 
            'stats'
        ));
    }

    /**
     * Tampilkan Halaman Canvas GIS (Inisiasi Manual ATAU Validasi BMKG)
     */
    public function create(Request $request)
    {
        $bpbd = Auth::user()->bpbd;
        $pending = null;

        if ($request->has('pending_id')) {
            $pending = BencanaPending::find($request->pending_id);
        }

        return view('dashboard.admin.bencana.create', compact('bpbd', 'pending'));
    }

    /**
     * Simpan Insiden Bencana (Manual / BMKG), Upload SK Darurat & Eksekusi Analisis Spasial
     */
    public function storeManual(Request $request)
    {
        $validated = $request->validate([
            'pending_id'        => 'nullable|exists:bencana_pendings,id',
            'jenis_bencana'     => 'required|string|max:255',
            'sumber_laporan'    => 'required|string|max:255',
            'wilayah'           => 'required|string|max:255',
            'latitude'          => 'required|numeric',
            'longitude'         => 'required|numeric',
            'sk_status_darurat' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'geojson_polygon'   => 'nullable|string',
            'deskripsi'         => 'nullable|string',
        ], [
            'sk_status_darurat.required' => 'Dokumen SK Status Darurat wajib diunggah.',
            'sk_status_darurat.mimes'    => 'Dokumen SK harus berformat PDF, JPG, JPEG, atau PNG.',
            'sk_status_darurat.max'      => 'Ukuran file SK Status Darurat maksimal 5MB.',
        ]);

        DB::beginTransaction();
        try {
            // 1. Simpan Berkas SK Status Darurat
            $skPath = $request->file('sk_status_darurat')->store('sk_darurat', 'public');

            // 2. Kalkulasi Data Spasial
            $geojsonPolygon = null;
            $luasAreaKm2 = 0;
            $totalJiwa = 0;
            $totalKK = 0;
            $totalBangunan = 0;

            if (!empty($validated['geojson_polygon'])) {
                $geoJsonData = is_string($validated['geojson_polygon']) 
                    ? json_decode($validated['geojson_polygon'], true) 
                    : $validated['geojson_polygon'];

                if (is_array($geoJsonData) && isset($geoJsonData['coordinates'])) {
                    $geojsonPolygon = $geoJsonData; // Simpan GeoJSON Geometry murni (type + coordinates)
                    
                    // Ambil ring pertama koordinat [lng, lat]
                    $coords = $geoJsonData['coordinates'][0] ?? [];

                    if (count($coords) >= 3) {
                        $demo = SpatialCalculationService::fetchRealDataFromOSM($coords);
                        
                        $luasAreaKm2   = $demo['luas_area_km2'];
                        $totalJiwa     = $demo['total_jiwa_terdampak'];
                        $totalKK       = $demo['total_kk_terdampak'];
                        $totalBangunan = $demo['total_bangunan_terdampak'];
                    }
                }
            }

            // Estimasi Pengungsi Awal
            $estimasiPengungsi = $totalJiwa > 0 ? (int) round($totalJiwa * 0.35) : 50;

            // 3. Simpan Ke Tabel Bencana Utama
            $bencana = Bencana::create([
                'jenis_bencana'             => $validated['jenis_bencana'],
                'lokasi_bencana'            => $validated['wilayah'],
                'koordinat_operasional_lat' => $validated['latitude'],
                'koordinat_operasional_lng' => $validated['longitude'],
                'geojson_polygon'           => $geojsonPolygon,
                'luas_area_km2'            => $luasAreaKm2,
                'total_jiwa_terdampak'     => $totalJiwa,
                'total_kk_terdampak'       => $totalKK,
                'total_bangunan_terdampak' => $totalBangunan,
                'estimasi_pengungsi_awal'   => $estimasiPengungsi,
                'sk_status_darurat_path'    => $skPath,
                'tanggal_aktivasi'          => now(),
                'status'                    => 'menunggu_posko',
            ]);

            // 4. Update Status Bencana Pending jika berasal dari BMKG
            if (!empty($validated['pending_id'])) {
                BencanaPending::where('id', $validated['pending_id'])->update(['status' => 'validated']);
            }

            // 5. AUTO GENERATE STOK REKOMENDASI LOGISTIK
            $rekomendasiStok = [
                'Beras'           => ceil($estimasiPengungsi * 0.4 * 7),
                'Air Minum'       => ceil($estimasiPengungsi * 0.5),
                'Makanan Kaleng'  => ceil($estimasiPengungsi * 2),
                'Makanan Bayi'    => ceil($estimasiPengungsi * 0.2),
                'Minyak Goreng'   => ceil($estimasiPengungsi * 0.1),
                'Popok Bayi'      => ceil($estimasiPengungsi * 0.5),
                'Popok Dewasa'    => ceil($estimasiPengungsi * 0.2),
                'Pembalut Wanita' => ceil($estimasiPengungsi * 0.3),
                'Hygiene Kit'     => ceil($estimasiPengungsi * 0.25),
                'Selimut'         => ceil($estimasiPengungsi * 0.8),
                'Matras Terpal'   => ceil($estimasiPengungsi * 0.5),
                'Obat P3K'        => ceil($estimasiPengungsi * 0.15),
            ];

            $poskosKomando = Posko::where('tipe_posko', 'komando')->get();

            foreach ($poskosKomando as $posko) {
                foreach ($rekomendasiStok as $namaBarang => $jumlah) {
                    $barang = Barang::firstOrCreate(['nama_barang' => $namaBarang]);
                    
                    $stokExist = StokPosko::where('posko_id', $posko->id)
                        ->where('barang_id', $barang->id)
                        ->first();

                    if ($stokExist) {
                        $stokExist->increment('jumlah_stok', $jumlah);
                    } else {
                        StokPosko::create([
                            'posko_id'    => $posko->id,
                            'barang_id'   => $barang->id,
                            'jumlah_stok' => $jumlah,
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('admin.posko.create', ['bencana_id' => $bencana->id])
                ->with('success', "Insiden '{$bencana->jenis_bencana}' berhasil divalidasi dengan analisis spasial. Silakan lanjutkan setup Posko Komando.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memproses pendaftaran bencana: ' . $e->getMessage());
        }
    }

    /**
     * Abaikan Laporan Bencana Pending
     */
    public function rejectPending($pendingId)
    {
        $pending = BencanaPending::findOrFail($pendingId);
        $pending->update(['status' => 'rejected']);

        return redirect()->back()->with('success', 'Deteksi bencana berhasil diabaikan.');
    }

    /**
     * Selesaikan Operasi Penanganan Bencana
     */
    public function finish($id)
    {
        $bencana = Bencana::findOrFail($id);

        DB::beginTransaction();
        try {
            $bencana->update([
                'status'          => 'selesai',
                'tanggal_selesai' => now(),
            ]);

            // PERBAIKAN: Gunakan Query Builder murni tanpa pemanggilan fungsi statis yang tidak ada
            Posko::where('tipe_posko', 'komando')
                ->where('bencana_id', $bencana->id)
                ->update([
                    'status'     => 'terdaftar_nonaktif',
                    'bencana_id' => null,
                ]);

            Posko::where('tipe_posko', 'lapangan')
                ->where('bencana_id', $bencana->id)
                ->update([
                    'status'     => 'ditutup',
                    'bencana_id' => null,
                ]);

            DB::commit();

            return redirect()->back()->with('success', 'Operasi bencana berhasil diselesaikan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyelesaikan bencana: ' . $e->getMessage());
        }
    }

    /**
     * Endpoint API AJAX: Hitung Spasial Live Via Overpass API saat Poligon Digambar
     */
    public function calculateSpatial(Request $request)
    {
        // Terima payload baik dalam bentuk String JSON maupun Array
        $request->validate([
            'geojson' => 'required',
        ]);

        $geoJson = $request->geojson;

        // Decode jika data dikirim sebagai JSON String dari JavaScript
        if (is_string($geoJson)) {
            $geoJson = json_decode($geoJson, true);
        }

        // Ambil ring koordinat pertama
        $coordinates = $geoJson['coordinates'][0] ?? [];

        if (!is_array($coordinates) || count($coordinates) < 3) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Koordinat poligon tidak valid.'
            ], 422);
        }

        // Panggil Engine Overpass API Live Sensus Bangunan
        $demographics = SpatialCalculationService::fetchRealDataFromOSM($coordinates);

        return response()->json([
            'status' => 'success',
            'data'   => $demographics
        ]);
    }
}