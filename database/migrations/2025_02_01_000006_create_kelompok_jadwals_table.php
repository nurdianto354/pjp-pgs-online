<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('kelompok_jadwal')) {
            Schema::create('kelompok_jadwal', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('kegiatan_id')->index();
                $table->unsignedBigInteger('kelompok_id')->nullable()->index();
                $table->enum('tipe', ['turba', 'total'])->default('total')->comment('turba: Turun ke bawah / keliling, total: Terpusat');
                $table->date('tanggal');
                $table->time('jam_mulai');
                $table->time('jam_selesai');
                $table->text('keterangan')->nullable();
                $table->boolean('status')->default(true);
                $table->timestamps();

                if (Schema::hasTable('kelompok_m_kegiatan')) {
                    $table->foreign('kegiatan_id')->references('id')->on('kelompok_m_kegiatan')->onDelete('cascade');
                }
                if (Schema::hasTable('kelompok_m_kelompok')) {
                    $table->foreign('kelompok_id')->references('id')->on('kelompok_m_kelompok')->onDelete('set null');
                }
            });
        }

        if (!Schema::hasTable('kelompok_jadwal_lokasi')) {
            Schema::create('kelompok_jadwal_lokasi', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('jadwal_id')->index();
                $table->unsignedBigInteger('lokasi_id')->index();
                $table->timestamps();

                $table->foreign('jadwal_id')->references('id')->on('kelompok_jadwal')->onDelete('cascade');
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
        Schema::dropIfExists('kelompok_jadwal_lokasi');
        Schema::dropIfExists('kelompok_jadwal');
    }
};

