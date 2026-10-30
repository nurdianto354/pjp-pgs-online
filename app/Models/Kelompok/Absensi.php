<?php

namespace App\Models\Kelompok;

use App\Models\Kelompok\MasterData\Jamaah;
use App\Models\Kelompok\MasterData\Lokasi;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    use HasFactory;

    protected $connection   = 'mysql';
    protected $table        = 'kelompok_absensi';
    protected $primaryKey   = 'id';
    protected $keyType      = 'int';
    public $incrementing    = true;
    public $timestamps      = true;
    protected $guarded      = [];

    protected $casts = [
        'is_in_radius'   => 'boolean',
        'latitude'       => 'float',
        'longitude'      => 'float',
        'jarak_meter'    => 'integer',
        'waktu_presensi' => 'datetime',
    ];

    public function getJadwal()
    {
        return $this->belongsTo(Jadwal::class, 'jadwal_id');
    }

    public function getJamaah()
    {
        return $this->belongsTo(Jamaah::class, 'jamaah_id');
    }

    public function getLokasi()
    {
        return $this->belongsTo(Lokasi::class, 'lokasi_id');
    }
}
