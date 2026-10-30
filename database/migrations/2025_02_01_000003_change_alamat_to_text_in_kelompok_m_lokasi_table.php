<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeAlamatToTextInKelompokMLokasiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('kelompok_m_lokasi')) {
            DB::statement('ALTER TABLE kelompok_m_lokasi MODIFY alamat TEXT NULL');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('kelompok_m_lokasi')) {
            DB::statement('ALTER TABLE kelompok_m_lokasi MODIFY alamat VARCHAR(255) NULL');
        }
    }
}

