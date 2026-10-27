<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKelompokMKegiatanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('kelompok_m_kegiatan')) {
            Schema::create('kelompok_m_kegiatan', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('nama');
                $table->tinyInteger('lingkup')->comment('1: Penerobosan Pusat, 2: Daerahan, 3: Desa');
                $table->text('keterangan')->nullable();
                $table->boolean('status')->default(true);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kelompok_m_kegiatan');
    }
}

