<?php

namespace App\Models\Kelompok\MasterData;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kegiatan extends Model
{
    use HasFactory;

    protected $connection   = 'mysql';
    protected $table        = 'kelompok_m_kegiatan';
    protected $primaryKey   = 'id';
    protected $keyType      = 'int';
    public $incrementing    = true;
    public $timestamps      = true;
    protected $guarded      = [];

    const LINGKUP_PENEROBOSAN_PUSAT = 1;
    const LINGKUP_DAERAHAN          = 2;
    const LINGKUP_DESA              = 3;

    const listLingkup = [
        self::LINGKUP_PENEROBOSAN_PUSAT => 'Penerobosan Pusat',
        self::LINGKUP_DAERAHAN          => 'Daerahan',
        self::LINGKUP_DESA              => 'Desa',
    ];

    public function getLingkupLabelAttribute()
    {
        return self::listLingkup[$this->lingkup] ?? '-';
    }
}

