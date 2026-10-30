<?php

namespace App\Models\Kelompok\MasterData;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lokasi extends Model
{
    use HasFactory;

    protected $connection   = 'mysql';
    protected $table        = 'kelompok_m_lokasi';
    protected $primaryKey   = 'id';
    protected $keyType      = 'int';
    public $incrementing    = true;
    public $timestamps      = true;
    protected $guarded      = [];

    public function getKelompok()
    {
        return $this->belongsTo(Kelompok::class, 'kelompok_id');
    }
}
