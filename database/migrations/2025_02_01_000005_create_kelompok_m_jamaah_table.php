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
        if (!Schema::hasTable('kelompok_m_jamaah')) {
            Schema::create('kelompok_m_jamaah', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('kelompok_id')->nullable()->index();
                $table->unsignedBigInteger('murid_id')->nullable()->index();
                $table->unsignedBigInteger('divisi_id')->nullable()->index();
                $table->string('nama_lengkap');
                $table->tinyInteger('jenis_kelamin')->comment('1: Laki-laki, 0: Perempuan');
                $table->boolean('status')->default(true);
                $table->timestamps();

                $table->foreign('kelompok_id')->references('id')->on('kelompok_m_kelompok')->onDelete('set null');
                $table->foreign('murid_id')->references('id')->on('murid')->onDelete('set null');
                $table->foreign('divisi_id')->references('id')->on('m_divisi')->onDelete('set null');
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
        Schema::dropIfExists('kelompok_m_jamaah');
    }
};

