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
        if (!Schema::hasTable('kelompok_absensi')) {
            Schema::create('kelompok_absensi', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('jadwal_id')->index();
                $table->unsignedBigInteger('jamaah_id')->index();
                $table->enum('status', ['hadir', 'sdc', 'izin']);
                $table->text('keterangan')->nullable()->comment('Wajib diisi jika status sdc atau izin');
                $table->decimal('latitude', 10, 8)->nullable();
                $table->decimal('longitude', 11, 8)->nullable();
                $table->unsignedBigInteger('lokasi_id')->nullable()->index();
                $table->integer('jarak_meter')->nullable()->comment('Jarak terdekat dalam meter ke titik lokasi');
                $table->boolean('is_in_radius')->default(false);
                $table->timestamp('waktu_presensi')->useCurrent();
                $table->timestamps();

                $table->foreign('jadwal_id')->references('id')->on('kelompok_jadwal')->onDelete('cascade');
                $table->foreign('jamaah_id')->references('id')->on('kelompok_m_jamaah')->onDelete('cascade');

                $table->unique(['jadwal_id', 'jamaah_id'], 'unique_jadwal_jamaah_presensi');
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
        Schema::dropIfExists('kelompok_absensi');
    }
};

