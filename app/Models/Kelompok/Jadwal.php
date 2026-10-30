<?php

namespace App\Models\Kelompok;

use App\Models\Kelompok\MasterData\Kegiatan;
use App\Models\Kelompok\MasterData\Kelompok;
use App\Models\Kelompok\MasterData\Lokasi;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
    use HasFactory;

    protected $connection   = 'mysql';
    protected $table        = 'kelompok_jadwal';
    protected $primaryKey   = 'id';
    protected $keyType      = 'int';
    public $incrementing    = true;
    public $timestamps      = true;
    protected $guarded      = [];

    protected $casts = [
        'status' => 'boolean',
        'tanggal' => 'date',
    ];

    public function getKegiatan()
    {
        return $this->belongsTo(Kegiatan::class, 'kegiatan_id');
    }

    public function getKelompok()
    {
        return $this->belongsTo(Kelompok::class, 'kelompok_id');
    }

    public function getLokasi()
    {
        return $this->belongsToMany(Lokasi::class, 'kelompok_jadwal_lokasi', 'jadwal_id', 'lokasi_id')
            ->withTimestamps();
    }
}

