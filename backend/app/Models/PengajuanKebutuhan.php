<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class PengajuanKebutuhan extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_kebutuhan';

    protected $fillable = [
        'kode_pengajuan',
        'user_id',
        'posko_id',
        'bencana_id',

        // 12 Kolom Barang
        'beras_kg',
        'air_minum_dus',
        'makanan_kaleng_pack',
        'makanan_bayi_pack',
        'minyak_goreng_liter',
        'popok_bayi_pcs',
        'popok_dewasa_pcs',
        'pembalut_wanita_pack',
        'hygiene_kit_paket',
        'selimut_pcs',
        'matras_terpal_pcs',
        'obat_p3k_paket',

        'tanggal_pengajuan',
        'status',
        'catatan_posko',
        'catatan_komando',
        'catatan_eskalasi',
    ];

    /**
     * Casts atribut tanggal ke Carbon instance
     */
    protected $casts = [
        'tanggal_pengajuan' => 'datetime',
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
    ];

    /**
     * Accessor dinamis $pengajuan->items
     * Mengonversi 12 kolom barang menjadi koleksi objek item untuk dibaca di view
     */
    public function getItemsAttribute()
    {
        $items = [];
        $map = [
            'beras_kg'             => ['nama' => 'Beras', 'satuan' => 'Kg'],
            'air_minum_dus'        => ['nama' => 'Air Minum', 'satuan' => 'Dus'],
            'makanan_kaleng_pack'  => ['nama' => 'Makanan Kaleng', 'satuan' => 'Pack'],
            'makanan_bayi_pack'    => ['nama' => 'Makanan Bayi', 'satuan' => 'Pack'],
            'minyak_goreng_liter'  => ['nama' => 'Minyak Goreng', 'satuan' => 'Liter'],
            'popok_bayi_pcs'       => ['nama' => 'Popok Bayi', 'satuan' => 'Pcs'],
            'popok_dewasa_pcs'     => ['nama' => 'Popok Dewasa', 'satuan' => 'Pcs'],
            'pembalut_wanita_pack' => ['nama' => 'Pembalut Wanita', 'satuan' => 'Pack'],
            'hygiene_kit_paket'    => ['nama' => 'Hygiene Kit', 'satuan' => 'Paket'],
            'selimut_pcs'          => ['nama' => 'Selimut', 'satuan' => 'Pcs'],
            'matras_terpal_pcs'    => ['nama' => 'Matras / Terpal', 'satuan' => 'Pcs'],
            'obat_p3k_paket'       => ['nama' => 'Obat P3K', 'satuan' => 'Paket'],
        ];

        foreach ($map as $field => $info) {
            $jumlah = (float) ($this->{$field} ?? 0);
            if ($jumlah > 0) {
                $items[] = (object) [
                    'nama_barang' => $info['nama'],
                    'satuan'      => $info['satuan'],
                    'jumlah'      => $jumlah,
                    'inventaris'  => (object) [
                        'nama_barang' => $info['nama'],
                        'satuan'      => $info['satuan'],
                    ]
                ];
            }
        }

        return collect($items);
    }

    public static function generateKode()
    {
        do {
            $kode = 'REQ-' . date('Ymd') . '-' . strtoupper(Str::random(4));
        } while (self::where('kode_pengajuan', $kode)->exists());

        return $kode;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function posko(): BelongsTo
    {
        return $this->belongsTo(Posko::class, 'posko_id');
    }

    public function bencana(): BelongsTo
    {
        return $this->belongsTo(Bencana::class, 'bencana_id');
    }

    public function pengiriman(): HasOne
    {
        return $this->hasOne(PengirimanInventaris::class, 'pengajuan_kebutuhan_id');
    }
}