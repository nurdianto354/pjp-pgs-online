<?php

namespace App\Models\Kelompok\MasterData;

use App\Models\MasterData\Divisi;
use App\Models\Murid\Murid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jamaah extends Model
{
    use HasFactory;

    protected $connection   = 'mysql';
    protected $table        = 'kelompok_m_jamaah';
    protected $primaryKey   = 'id';
    protected $keyType      = 'int';
    public $incrementing    = true;
    public $timestamps      = true;
    protected $guarded      = [];

    protected $casts = [
        'status' => 'boolean',
        'jenis_kelamin' => 'integer',
    ];

    public function getMurid()
    {
        return $this->belongsTo(Murid::class, 'murid_id');
    }

    public function getDivisi()
    {
        return $this->belongsTo(Divisi::class, 'divisi_id');
    }
}

